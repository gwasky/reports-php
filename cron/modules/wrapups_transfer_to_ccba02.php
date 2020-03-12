<?php
//TO BE RUN ON CCBA02

function transfer_wrapups_to_ccba02($from,$to = NULL){
	$log['Report Start'] = date('Y-m-d H:i:s');
	$myquery = new custom_query();
	
	if($from == ''){
		$from = date('Y-m-d', strtotime('- 1 days'));
	}

	if($to == NULL){
		$to = $from;
	}
	
	echo date('Y-m-d H:i:s')." STARTED Transfering wrap ups and CSAT evaluations from CCBA 01 to CCBA02 from ".$from." to ".$to." ....\n";
	
	$log['Period start'] = $from;
	$log['Period end'] = $to;
	
	$from .= " 00:00:00";
	$to .= " 23:59:59";
	
	$query = "
		SELECT
			id
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
		-- LIMIT 5
	";
	
	//echo $query."\n\n";
	
	$ids[wrapups] = $myquery->multiple($query,'ccba01.reportscrm');
	$log[retrieved_wrapups] = count($ids[wrapups]);
	
	echo date('Y-m-d H:i:s')." Retrived ".number_format($log[retrieved_wrapups],0)." Wrap Up ids ....\n";
	
	$query = "
		SELECT
			id
		FROM
			smsfeedback.sms_evaluation
		WHERE
			smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' AND '".$to."'
	--	LIMIT 5
	";
	
	//echo $query."\n\n";
	
	$ids[csats] = $myquery->multiple($query,'ccba01.smsfeedback');
	if(mysql_error()){
		$log[sms_evaluation_mysql_error] = mysql_error();
		
		$repair_query = "REPAIR TABLE sms_evaluation;";
		$repair_result_rows = $myquery->multiple($repair_query,'root@ccba01.smsfeedback');
		
		$log[sms_evaluation_repair_result] = print_r($repair_result_rows,true);
		
		$ids[csats] = $myquery->multiple($query,'ccba01.smsfeedback');
	}
	$log[retrieved_csats] = count($ids[csats]);
	
	echo date('Y-m-d H:i:s')." Retrived ".number_format($log[retrieved_csats],0)." CSAT ids ....\n";
	
	foreach($ids[wrapups] as $id_key=>$id_row){
		$query = "
			SELECT
				*
			FROM
				reportsphonecalls
			WHERE
				reportsphonecalls.id = ".$id_row[id]."
		";
		
		$row = $myquery->single($query,'ccba01.reportscrm');
		$row[id] = $id_row[id];
		
		$columns = array_keys($row);
		$column_count = count($columns);
		
		$query = "
			INSERT INTO
				reportsphonecalls(";
		
			unset($key);
			foreach($columns as $key=>$column){
				$query .= "`".$column."`"; if(($key+1) < $column_count) { $query .= ", "; }
			}
						
		$query .= ")
			VALUES(";
				   
			$key = 0;
			foreach($row as $value){
				$query .= '"'.mysql_real_escape_string($value).'"'; if((++$key) < $column_count) { $query .= ", "; }
			}
			
		$query .= ")";
			
		//echo "Query =>> ".str_replace(array('\n'),'',$query)."\n";
		
		$result = $myquery->addit($query,'ccba02.reportscrm');
		
		if($result){
			++$log[transfered_wrapups];
			
			$query = "
				DELETE FROM
					reportsphonecalls
				WHERE
					reportsphonecalls.id = ".$id_row[id]."	
			";
			
			$delete_result = $myquery->no_row($query,'ccba01.reportscrm');
			if($delete_result){
				++$log[cleaned_wrapups];
			}else{
				++$log[not_cleaned_wrapups];
			}
		}else{
			$log[not_txd_quries_wrapups] .= str_replace(array('\n'),'',$query)."<br>";
			++$log[not_transfered_wrapups];
		}
		
		//HOUSE KEEPING
		$this_percentage = round((($id_key+1)/$log[retrieved_wrapups])*100,1);
		if($low_perecentage == ''){
			$low_perecentage = $this_percentage;
		}
		if(($this_percentage - $low_perecentage) >= 5.00 or $this_percentage == 100.00){
			echo date('Y-m-d H:i:s')." On date = ".$row[createdon]."; ".number_format(($id_key+1),0)."/".number_format($log[retrieved_wrapups],0)." = ".$this_percentage."% \n";
			echo date('Y-m-d H:i:s')." Total Wrapups Transfered => [".number_format($log[transfered_wrapups],0)."]; Total Wrapups Not transfered => [".number_format($log[not_transfered_wrapups],0)."]; Total Wrapups cleaned => [".number_format($log[cleaned_wrapups],0)."]; Total Wrapups not cleaned => [".number_format($log[not_cleaned_wrapups],0)."]\n";
			$low_perecentage = $this_percentage;
		}
		
		//reduce Mem usage
		unset($ids[$id_key],$id_key,$id_row);
	}
	
	unset($id_key,$id_row,$low_perecentage);
	
	foreach($ids[csats] as $id_key=>$id_row){
		$query = "
			SELECT
				*
			FROM
				sms_evaluation
			WHERE
				sms_evaluation.id = ".$id_row[id]."
		";
		
		$row = $myquery->single($query,'ccba01.smsfeedback');
		$row[id] = $id_row[id];
		
		$columns = array_keys($row);
		$column_count = count($columns);
		
		$query = "
			INSERT INTO
				sms_evaluation(
		";
		
			unset($key);
			foreach($columns as $key=>$column){
				$query .= "`".$column."`"; if(($key+1) < $column_count) { $query .= ", "; }
			}
						
		$query .= ")
			VALUES(";
				   
			$key = 0;
			foreach($row as $value){
				$query .= '"'.mysql_real_escape_string($value).'"'; if((++$key) < $column_count) { $query .= ", "; }
			}
			
		$query .= ")";
			
		//echo "Query =>> ".str_replace(array('\n'),'',$query)."\n";
		
		$result = $myquery->addit($query,'ccba02.smsfeedback');
		
		if($result){
			++$log[transfered_csats];
			
			$query = "
				DELETE FROM
					sms_evaluation
				WHERE
					sms_evaluation.id = ".$id_row[id]."	
			";
			
			$delete_result = $myquery->no_row($query,'ccba01.smsfeedback');
			if($delete_result){
				++$log[cleaned_csats];
			}else{
				if(mysql_error()) { echo date('Y-m-d H:i:s')." MYSQL DELETE ERROR : [".str_replace(array('\n','\t'),' ',$query)."] => ".mysql_error()." \n"; }
				++$log[not_cleaned_csats];
			}
		}else{
			$mysql_error = mysql_error();
			if(substr(str_replace(' ','',$mysql_error), 0, 14) != 'Duplicateentry'){
				echo date('Y-m-d H:i:s')." MYSQL DELETE ERROR : [".str_replace(array('\n','\t'),' ',$query)."] => ".$mysql_error." \n";
				$log[not_txd_quries_csats] .= str_replace(array('\n'),'',$query)."<br>";
				++$log[not_transfered_csats];
			}else{
				++$log[transfered_csats];
			
				$query = "
					DELETE FROM
						sms_evaluation
					WHERE
						sms_evaluation.id = ".$id_row[id]."	
				";
				
				$delete_result = $myquery->no_row($query,'ccba01.smsfeedback');
				if($delete_result){
					++$log[cleaned_csats];
				}else{
					if(mysql_error()) { echo date('Y-m-d H:i:s')." MYSQL DELETE ERROR : [".str_replace(array('\n','\t'),' ',$query)."] => ".mysql_error()." \n"; sleep(5);}
					++$log[not_cleaned_csats];
				}
			}
		}
		
		//HOUSE KEEPING
		$this_percentage = round((($id_key+1)/$log[retrieved_csats])*100,1);
		if($low_perecentage == ''){
			$low_perecentage = $this_percentage;
		}
		if(($this_percentage - $low_perecentage) >= 5.00 or $this_percentage == 100.00){
			echo date('Y-m-d H:i:s')." On date = ".$row[createdon]."; ".number_format(($id_key+1),0)."/".number_format($log[retrieved_csats],0)." = ".$this_percentage."% \n";
			echo date('Y-m-d H:i:s')." Total CSATs Transfered => [".number_format($log[transfered_csats],0)."]; Total CSATs Not transfered => [".number_format($log[not_transfered_csats],0)."]; Total CSATs cleaned => [".number_format($log[cleaned_csats],0)."]; Total CSATs not cleaned => [".number_format($log[not_cleaned_csatss],0)."]\n";
			$low_perecentage = $this_percentage;
		}
		
		//reduce Mem usage
		unset($ids[$id_key],$id_key,$id_row);
	}
	
	echo date('Y-m-d H:i:s')." STOPPED Transfering CSATs from CCBA 01 to CCBA02 ....\n";
	
	$log['Report End'] = date('Y-m-d H:i:s');
	$log['Report Duration in seconds'] = strtotime($log['Report End'])-strtotime($log['Report Start']);
	return $log;	
}
?>