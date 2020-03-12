<?php
function transfer_in_ivr_stats(){
	
	$myquery = new custom_query();
	
	$root = '/srv/www/htdocs/vas_dumps/';
	$ivr_root = $root.'ivr/';
	$to_list = "ccbusinessanalysis@waridtel.co.ug";
$to_eng_list = "Joseph Kibuuka/Engineering/Kampala <joseph.kibuuka@waridtel.co.ug>, Victor Ssebugwawo/Engineering/Kampala <Victor.SSebugwawo@waridtel.co.ug>, George Lule/Engineering/Kampala <george.lule@waridtel.co.ug>";
	
	$ftp = new FTP($ftproot='/', $host='127.0.0.1', $username='vas_ftp', $password='1vas_ftp2', $debug=NULL);
	$file_names = $ftp->f_ls("ivr/");
	
	if(count($file_names) == 0){
		$error_mail = '
		Dear Engineering Team,
		<br><br>
		Date : '.date('Y-m-d H:i:s').'
		<br><br>
		There are <strong><span style="color: red;">NO</span> IN IVR files</strong> FTP-ed to CCCBA02. Please send the files manually and check the scheduler/script that sends them to re instate normal operations from tomorrow onwards.
		<br><hr>
		CCBA Team.';
		sendHTMLemail($to=$to_eng_list,$bcc=$to_list,$message=$error_mail,$subject="IN IVR loading ERROR",$from="IN IVR Loader <ccnotify@waridtel.co.ug>");
		
		return FALSE;
	}
	
	//print_r($file_names);
	
	foreach($file_names as $file_name){
		$path = $ivr_root.$file_name;
		if(file_exists($path)) {
			$file_data[substr($file_name,-8)] = split("\n",file_get_contents($path));
		}else{
			echo date('Y-m-d H:i:s')." Path [".$path."] does not exist:\n";
		}
	}
	
	//print_r($file_data);
	
	foreach($file_data as $date=>$date_data){
		$result = process_in_ivr_datedata($date,$date_data);
		if(!$result){
			$error_mail = '
			Dear Engineering Team,
			<br><br>
			Date : '.date('Y-m-d H:i:s').'
			<br><br>
			The date '.$date.' has no data. Please send the files with data manually and check the query that generatges the file data to re instate normal operations from tomorrow onwards.
			<br><br>
			Below are the file contents: <hr> '.my_print_r(file_get_contents($ivr_root."IVR_Report_".$date)).'
			<hr>
			CCBA Team.';
			sendHTMLemail($to=$to_eng_list,$bcc=$to_list,$message=$error_mail,$subject="IN IVR loading ERROR",$from="IN IVR Loader <ccnotify@waridtel.co.ug>");
			return FALSE;
		}
		
		$processed[in_ivr_datise($date)] = $result;
	}
	
	if(count($processed) == 0) {
		$error_mail = "
			Dear CCBA Team,
			<br><br>
			There is no data to insert in the Database.
			<br><br>
			CCBA Team.
		";
		sendHTMLemail($to=$to_list,$bcc,$message=$error_mail,$subject="IVR loading ERROR",$from="CC IVR Loader <ccnotify@waridtel.co.ug>");
		return FALSE;
	}
	
	foreach($processed as $date=>$data_row){
		foreach($data_row as $row){
			$query_insert= '
				INSERT INTO in_ivr_stats
					(`date_entered`,`option`,`pass`,`fail`)
				VALUES
					("'.$row[date_entered].'","'.$row[option].'","'.$row[pass].'","'.$row[fail].'");
			';
			
			$result = $myquery->no_row($query_insert,'ivrperformance');
			
			if(!$result){
				$query_update = '
				UPDATE 
					in_ivr_stats
				SET	
					in_ivr_stats.`pass` = "'.$row[pass].'",
					in_ivr_stats.`fail` = "'.$row[fail].'"
				WHERE
					in_ivr_stats.`date_entered` = "'.$row[date_entered].'" AND
					in_ivr_stats.`option` = "'.$row[option].'";
				';
				
				$result = $myquery->no_row($query_update,'ivrperformance');
				if(!$result) { ++$logs[$row[date_entered]][DB][SAMEDATA]; } else { ++$logs[$row[date_entered]][DB][OVERWRITTEN]; }
			}else{
				++$logs[$row[date_entered]][DB][SAVED];
			}
		}
	}
	
	foreach($file_names as $file_name){
		if(rename($ivr_root.$file_name,$root."uploaded/".$file_name)){
			$logs[in_ivr_datise(substr($file_name,-8))][FILES][MOVED][] = $ivr_root.$file_name;
		}else{
			$logs[in_ivr_datise(substr($file_name,-8))][FILES][NOTMOVED][] = $ivr_root.$file_name;
		}
	}
	
	print_r($logs);
	
	return $logs;
}

function process_in_ivr_datedata($date,$data){
	foreach($data as $row_index=>$row){
		$row=trim($row);
		if(substr($row,0,8) == $date){
			$option = str_replace($date." ","",$row);
			$values_list = explode("	",$data[$row_index+1]);
			
			$result[] = array('date_entered'=>in_ivr_datise($date),'option'=>$option,'pass'=>intval(trim($values_list[1])),'fail'=>intval(trim($values_list[2])));
			unset($data[$row_index]);
		}else{
			unset($data[$row_index]);
		}
	}
	
	if(count($result) == 0) return FALSE;
	
	return $result;
}

function in_ivr_datise($inivr_date){
	return substr($inivr_date,0,4)."-".substr($inivr_date,4,2)."-".substr($inivr_date,-2);
}
?>