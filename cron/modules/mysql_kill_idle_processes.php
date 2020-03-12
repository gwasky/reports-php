<?php
function ccba01_kill_idle_processes(){
	custom_query::select_db('mysql');
	$myquerys = new custom_query();
	
	do{
		unset($max,$processes);
		$query = "show processlist";
		echo date('Y-m-d H:i:s')." : CCBA01 Counting the number of processes running "; //sleep(2);
		$rows = $myquerys->multiple($query);

		$no_of_rows = count($rows);
		
		echo " : ".$no_of_rows." processes found .. \n"; //sleep(2);
		
		foreach($rows as $row){
			++$processes[$row[Command]];
			
			list($Host,$port) = explode(":",$row[Host]);
			
			if($Host != 'devfe02.waridtel.co.ug' and ($row[Command] == 'Sleep' || $row[Command] == 'NULL')){
				$query = "kill ".$row[Id];
				$myquerys->no_row($query);
				
				echo date('Y-m-d H:i:s')." : CCBA01 Killed process ".str_replace(array("\n"," "),array("; ",""),print_r($row,true))." .. \n";
				//sleep(1);
				
				++$processes[killed];
			}else{
				if(!isset($max)){ $max = $row; }
				
				if($row[Time] > intval($max[Time])){
					$max = $row;
				}
			}
		}
		
		echo date('Y-m-d H:i:s')." : CCBA01 Process break down ".str_replace(array("\n"," "),array("; ",""),print_r($processes,true))." .. \n"; sleep(1);
		echo date('Y-m-d H:i:s')." : CCBA01 Max process ".str_replace(array("\n"," "),array("; ",""),print_r($max,true))." .. \n"; sleep(1);
		echo date('Y-m-d H:i:s')." : CCBA01 Taking a break before I check again .. \n";
		sleep(10);
	}while($no_of_rows > 5 and $processes[killed] > 0);
	
	echo date('Y-m-d H:i:s')." : CCBA01 Done .. \n";
}

function ccba02_kill_idle_processes(){
	custom_query::select_db('ccba02.mysql');
	$myquerys = new custom_query();
	
	do{
		unset($max,$processes);
		$query = "show processlist";
		echo date('Y-m-d H:i:s')." : CCBA02 Counting the number of processes running "; //sleep(2);
		$rows = $myquerys->multiple($query);

		$no_of_rows = count($rows);
		
		echo " : ".$no_of_rows." processes found .. \n"; //sleep(2);
		
		foreach($rows as $row){
			++$processes[$row[Command]];
			
			list($Host,$port) = explode(":",$row[Host]);
			
			if($Host != 'devfe02.waridtel.co.ug' and ($row[Command] == 'Sleep' || $row[Command] == 'NULL')){
				$query = "kill ".$row[Id];
				$myquerys->no_row($query);
				
				echo date('Y-m-d H:i:s')." : CCBA02 Killed process ".str_replace(array("\n"," "),array("; ",""),print_r($row,true))." .. \n";
				//sleep(1);
				
				++$processes[killed];
			}else{
				if(!isset($max)){ $max = $row; }
				
				if($row[Time] > intval($max[Time])){
					$max = $row;
				}
			}
		}
		
		echo date('Y-m-d H:i:s')." : CCBA02 Process break down ".str_replace(array("\n"," "),array("; ",""),print_r($processes,true))." .. \n"; sleep(1);
		echo date('Y-m-d H:i:s')." : CCBA02 Max process ".str_replace(array("\n"," "),array("; ",""),print_r($max,true))." .. \n"; sleep(1);
		echo date('Y-m-d H:i:s')." : CCBA02 Taking a break before I check again .. \n";
		sleep(10);
	}while($no_of_rows > 5 and $processes[killed] > 0);
	
	echo date('Y-m-d H:i:s')." : CCBA02 Done .. \n";
}
?>