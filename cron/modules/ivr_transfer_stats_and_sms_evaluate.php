<?php
function transfer_ivr_stats_and_evaluate(){
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			cdr.uniqueid AS id,
			cdr.calldate AS date_entered,
			if(length(cdr.src) = 9,CONCAT('256',cdr.src),cdr.src) AS msisdn,
			if(cdr.billsec > 0 and cdr.lastapp = 'Hangup', 'IVR No Input', cdr.lastapp) AS last_option_group,
			if(cdr.lastdata LIKE 'SIP/Avaya-trunk/%',cdr.dst,cdr.lastdata) AS last_option_value,
			cdr.billsec as ivr_duration
		FROM
			cdr
		WHERE
			cdr.calldate BETWEEN DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d %h:%i:00'),INTERVAL 10 MINUTE) AND NOW()
--			cdr.calldate <= NOW()
		ORDER BY
			cdr.calldate ASC
	";
	
	//echo $query."\n";
	
	$log[start_time] = date('Y-m-d H:i:s');
	
	echo date('Y-m-d H:i:s')." : Extract application Started \n";
	
	//DOUBLE SERVER START
	$fetched['10.31.34.35'] = $myquery->multiple($query,'asteriskcdrs.35','id');
	$fetched['10.31.34.36'] = $myquery->multiple($query,'asteriskcdrs.36','id');
	//DOUBLE SERVER END
	
	$log['fetched_10.31.34.35'] = count($fetched['10.31.34.35']);
	$log['fetched_10.31.34.36'] = count($fetched['10.31.34.36']);
	$log[fetched_total] = $log['fetched_10.31.34.35'] + $log['fetched_10.31.34.36'];
	
	echo date('Y-m-d H:i:s')." : Number of items fetched .35 = ".$log['fetched_10.31.34.35'].", .36 = ".$log['fetched_10.31.34.36'].", Total = ".$log[fetched_total].". Attempting to transfer ... \n";
	
	foreach($fetched as $server_ip=>$rows){
		foreach($rows as $key=>$row){
			++$i;
			
			//CATER FOR IVR INITIATED HANG UPS
			if($row[last_option_group] == 'IVR No Input'){
				$row[last_option_group] = 'IVR';
				$row[last_option_value] = 'IVR System initiated Hang up';
			}
			
			//CATER FOR 144 IVR IE option_value = 'SIP/VGateway3.20/145,300,M(setmusic^none)' AND option_group = 'Dial'
			if($row[last_option_group] == 'Dial' and $row[last_option_value] == 'SIP/VGateway3.20/145,300,M(setmusic^none)'){
				$row[last_option_group] = 'BackGround';
			}
			
			$db_last_option_group = translate_asterisk_option_group($row[last_option_group]);
			$insert_query = "
				INSERT INTO 
					asterisk_cdrs(`date_entered`,`msisdn`,`last_option_group`,`last_option_value`,`ivr_duration`)
				VALUES
					('".$row[date_entered]."','".$row[msisdn]."','".$db_last_option_group."','".$row[last_option_value]."','".$row[ivr_duration]."')
			";
			
			++$log['Stats Summary'][$db_last_option_group];
			
			echo "Qeury : ".$insert_query."\n";
			
			$insert_result = $myquery->addit($insert_query,'ccba02.ivrperformance');
			
			$last_id = mysql_insert_id();
			echo "The last id is [".$last_id."] \n\n";
			exit('Exiting ...\n');
			
			if($insert_result){
				//echo " SVD";
				++$log["transfered_".$server_ip];
				
				//BEGIN TEST MODEz
				/*$delete_query = "DELETE FROM cdr WHERE cdr.uniqueid = '".$row[id]."'";
				
				$delete_result = $myquery->no_row($delete_query,'asteriskcdrs');
				
				if($delete_result){
					echo " CLN";
					++$log['Deleted from source'];
				}else{
					echo " UND";
					++$log['NOT Deleted from source'];
				}*/
				//END OF TEST MODE
				//echo "\n";
			}else{
				//echo " NOT";
				++$log["not_transfered_".$server_ip];
				//$log[ERROR] .= mysql_error()."<br>"
				//echo "\n";
			}
			
			unset($rows[$key],$key);
		}
	}
	
	$log[transfered_total] = $log['transfered_10.31.34.35'] + $log['transfered_10.31.34.36'];
	
	$log[not_transfered_total] = $log['not_transfered_10.31.34.35'] + $log['not_transfered_10.31.34.36'];
	
	echo date('Y-m-d H:i:s')." : ".$date." Number of items transfered: .35 = ".intval($log['transfered_10.31.34.35']).", .36 = ".intval($log['transfered_10.31.34.36']).", Total = ".$log['transfered_total'].".\n";
	
	echo date('Y-m-d H:i:s')." : ".$date." Number not transfered: .35 = ".intval($log['not_transfered_10.31.34.35']).", .36 = ".intval($log['not_transfered_10.31.34.36']).", Total = ".$log[not_transfered_total]." \n";
	
	if($log['not_transfered_10.31.34.35'] == 0 and $log['fetched_10.31.34.35'] > 0){
		$delete_query = "DELETE FROM cdr WHERE cdr.calldate BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59'";
		
		$delete_result = $myquery->no_row($delete_query,'asteriskcdrs.35');
		
		if($delete_result){
			$log['10.31.34.35 Cleaned'] = $log['transfered_10.31.34.35'];
		}else{
			$log[ERROR] .= "<br>Entries have for ".$date." have not been deleted from 10.31.34.35.<br>".mysql_error()."<br>";
		}
	}else{
		$log[ERROR] .= "<br>Some entries have not been transfered on 10.31.34.35 either beccause there was an error or there was no data. Delete Operation on 10.31.34.35 aborted ...<br>";
	}
	
	echo date('Y-m-d H:i:s')." : ".$date." Number of items cleaned: .35 = ".intval($log['10.31.34.35 Cleaned'])." \n";
	
	if($log['not_transfered_10.31.34.36'] == 0 and $log['fetched_10.31.34.36'] > 0){
		$delete_query = "DELETE FROM cdr WHERE cdr.calldate BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59'";
		
		$delete_result = $myquery->no_row($delete_query,'asteriskcdrs.36');
		
		if($delete_result){
			$log['10.31.34.36 Cleaned'] = $log['transfered_10.31.34.36'];
		}else{
			$log[ERROR] .= "<br>Entries have for ".$date." have not been deleted from 10.31.34.36<br>".mysql_error()."<br>";
		}
	}else{
		$log[ERROR] .= "<br>Some entries have not been transfered on 10.31.34.36 either beccause there was an error or there was no data. Delete Operation on 10.31.34.36 aborted ..<br>";
	}
	
	$log['Cleaned Total'] = $log['10.31.34.35 Cleaned'] + $log['10.31.34.36 Cleaned'];
	
	echo date('Y-m-d H:i:s')." : ".$date." Number of items cleaned: .36 = ".intval($log['10.31.34.36 Cleaned'])." \n";
	
	echo date('Y-m-d H:i:s')." : ".$date." Total number of items cleaned = ".$log['Cleaned Total']." \n";
	
	$log[end_time] = date('Y-m-d H:i:s');
	$log[duration] = sec_to_time(strtotime($log[end_time]) - strtotime($log[start_time]));
	
	echo date('Y-m-d H:i:s')." : ".$date." Extract application Ended. Total duration ".$log[duration]."\n";
	
	print_r($log); //exit();
	
	return $log; 
}
/*
*/
?>