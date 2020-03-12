<?php
//TO BE RUN ON CCBA02

//error_reporting(E_WARNING|E_PARSE|E_ERROR);
error_reporting(E_PARSE|E_ERROR);

$log[start_time] = date('Y-m-d H:i:s');

//LARGER MEM REQUISITION
ini_set('memory_limit','1024M');

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = "ccbusinessanalysis@waridtel.co.ug";
//$to_list = 'steven.ntambi@waridtel.co.ug';
$debug_mode = TRUE;
//$debug_mode = FALSE;

$mylog = new ussd_log();
$myquery = new custom_query();

custom_query::select_db('ccba02.reportscrm');

if($debug_mode){ echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : Getting the no of ids to DELETE ... \n";}
//db ids with accepted .....
$query = " select count(id) as no_of_rows from reportsphonecalls where createdon <= '2011-12-31 23:59:59'; ";
$result = $myquery->single($query);

$log[total_ids_in] = $result[no_of_rows];

if($debug_mode){ echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : Got ".number_format($log[total_ids_in],0)."... \n"; }

do{
	if((0 <= intval(date('H'))) and (intval(date('H')) <= 1)){
		$sleep_time = (strtotime(date('Y-m-d')." 01:20:59") - strtotime(date('Y-m-d H:i:s')));
		echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : Sleeping for ".sec_to_time($sleep_time)." until 01:20:59  ... ";
		sleep($sleep_time);
		echo " Resuming ... \n";
	}elseif((9 <= intval(date('H'))) and (intval(date('H')) <= 17)){
		
		$sleep_time = (strtotime(date('Y-m-d')." 17:59:59") - strtotime(date('Y-m-d H:i:s')));
		$sleep_time = 70;
		echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : Sleeping for ".sec_to_time($sleep_time)." ... ";
		sleep($sleep_time);
		echo " Resuming ... \n";
		
	}else{
		//echo "Hour is ".intval(date('H'))." ... \n";
	}
	
	if(intval($used_limit) <= 0){ $used_limit = 70000; }
	
	$get_result = get_ids_to_delete($used_limit);
	$deleted_total += $get_result[row_count];
	$percentage = number_format((100 * ($deleted_total/$log[total_ids_in])),5);
	
	if($debug_mode){ echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : %age [".$percentage."] : ".show_mem_usage($unit='M')."; Deleted ".number_format($get_result[row_count],0)." in ".sec_to_time($get_result[duration])." ".number_format($deleted_total,0)."/".number_format($log[total_ids_in],0) ." :-> "; }
	
	if(intval($this_duration) > 0){
		$fraction = $this_duration/$get_result[duration];
		if($fraction > 1.11){
			if(($used_limit + 6000) < 72000) { $used_limit += 6000; }
			echo "HI ".$used_limit." - [".round($fraction,2)."] \n";
		}elseif($fraction < 0.89){
			$used_limit -= 4000;
			echo "LO ".$used_limit." - [".round($fraction,2)."] \n";
		}else{
			if(($used_limit + 1500) <= 72000) { $used_limit += 1500; }
			echo "== ".$used_limit." - [".round($fraction,2)."] \n";
		}
	}else{
		if(($used_limit + 1000) <= 72000) { $used_limit += 1000; }
		echo "~~ ".$used_limit." - [".round($fraction,2)."] \n";
	}
	
	$this_duration = $get_result[duration];
	/*if((strtotime(date('Y-m-d H:i:s')) - strtotime($log[start_time])) > 2){
		exit();
	}*/
}while($get_result[row_count] > 0);

$log[end_time] = date('Y-m-d H:i:s');

$log[duration] = sec_to_time(strtotime($log[end_time]) - strtotime($log[start_time]));
if($debug_mode){ echo date('Y-m-d H:i:s')." MEM USAGE [".show_mem_usage('M')."] : Done [".$log[duration]."] ... \n"; }

sendHTMLemail($to=$to_list,$bcc='',$message=explain($log),$subject="Old Wrap ups cleaning ",$from="ccnotify@waridtel.co.ug");

function get_ids_to_delete($limit = 1000){
	
	if(intval($limit) == 0) { $limit = 1000;}
	
	$start_time = date('Y-m-d H:i:s');
	$myquery = new custom_query();
	
	$query = "select id from reportsphonecalls where createdon <= '2011-12-31 23:59:59' limit ".$limit.";";
	//order by start_time asc 
	$ids = $myquery->multiple($query);
	$no_of_rows = count($ids);
	
	foreach($ids as &$row){
		$in_list .= "'".$row[id]."'";
		if(++$counter < $no_of_rows) { $in_list .= ","; }
		
		unset($row);
	}
	
	//echo date('Y-m-d H:i:s')." Got sub count as ".$no_of_rows.". Going to delete them now [".$query."] ... \n";
	if($no_of_rows > 0){
		$query = "delete from reportsphonecalls where id in (".$in_list.") ;";
		unset($in_list);
		//echo date('Y-m-d H:i:s')." Updating ".$no_of_rows." ids using [".$query."] ... \n";
		$result = $myquery->no_row($query);
		$error = mysql_error();
		if($error){
			echo date('Y-m-d H:i:s')." MYSQL ERROR [".$error."]... \n";
		}
		unset($query);
	}else{
		$result = 0;
	}
	
	$end_time = date('Y-m-d H:i:s');
	//echo date('Y-m-d H:i:s')." Started at [".$start_time."] ended at [".$end_time."] ... \n";
	return array('row_count'=>$no_of_rows,'duration'=>(strtotime($end_time) - strtotime($start_time)),'result'=>$result);
}


function explain($array,$prepend=""){
	
	$indent = $prepend.'------------------->';
	
	$text = $prepend.'{<br>';
	
	foreach($array as $key=>$data){
		
		$text .= $prepend." [".$key."] =>> ";
		
		if(!is_array($data) and !is_object($data)){
			print_r($data); echo "\n";
			$text .= $data."<br>";
		}else{
			$text .= "<br>".explain($data,$indent.'---------------');
		}
	}
	
	$text .= $prepend.'}<br>';
	
	return $text;
}

function to_array($input){
	if(is_object($input)){
		$class = get_class($input);
		foreach($input as $key=>$value){
			$output[$class][$key]=$value;
		}
	}
	
	return $output;
}
?>