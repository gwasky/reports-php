<?php
//THIS FILE TRANSFERS WRAP UPS FROM CCBA01 TO CCBA02 AND DELETES THE RECORD FROM CCBA01
error_reporting(E_PARSE|E_ERROR);

function show_mem_usage($unit='M'){
	
	switch($unit){
		case 'k':
		case 'K':
			return number_format(memory_get_usage(TRUE)/(1024),0)." KB";
			break;
		case 'g':
		case 'G':
			return number_format(memory_get_usage(TRUE)/(1024*1024*1024),6)." GB";
			break;
		case 'm':
		case 'M':
		default:
			return number_format(memory_get_usage(TRUE)/(1024*1024),3)." MB";
			break;
	}
}

require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');

$custom_object = new custom_query();

$fetch_record_count = 1500;		/* Number of Records per Interval */
$processed_record_count = 0;	/* Number of Processed Records */
$sleeping_time = 3;				/* Number of Seconds for resting */

$count_db_records = "
	SELECT
		count(*) as count_db_records
	FROM
		reportsphonecalls
	WHERE
		reportsphonecalls.createdon <= '2011-12-31 23:59:59'
";

$db_records_count = $custom_object->single($count_db_records,'ccba01.reportscrm');

$full_count = $db_records_count['count_db_records'];

echo date('Y-m-d H:i:s')." : Attempting to transfer [".number_format($full_count,0)."] records .. \r\n"; 

do{
	$select_data_query = "
		SELECT
			*
		FROM 
			`reportscrm`.`reportsphonecalls`
		WHERE
			reportsphonecalls.createdon <= '2011-12-31 23:59:59'
		ORDER BY
			reportsphonecalls.createdon ASC
		LIMIT 
			".$fetch_record_count.";
	";
	$selected_data_list = $custom_object->multiple($select_data_query,'ccba01.reportscrm');
	echo date('Y-m-d H:i:s')." : Got [".count($selected_data_list)."] rows. MEM_USAGE : ".show_mem_usage().". TXind & Deleting ... ";
	$start_del = date('Y-m-d H:i:s');
	if(!empty($selected_data_list)){
		foreach($selected_data_list as $insert_selected_data_key){
			$insert_selected_data_query = '
				INSERT INTO `reportscrm`.`reportsphonecalls`(
					`id`,
					`activity_source`,
					`createdon`,
					`subject`,
					`activitystatus`,
					`createdby`,
					`regarding`,
					`description`,
					`phonenumber`,
					`statusreason`,
					`source_name`,
					`wrapupcat`,
					`wrapupsubcat`,
					`month`,
					`week`,
					`up_cross_sell`,
					`product_sold`,
					`customername`,
					`fno`,
					`crbt_requested`,
					`language`,
					`ftype`,
					`floc`,
					`custloc`,
					`district`,
					`crbt_available`,
					`sms_count`,
					`chap_call_dest`,
					`chap_caller_type`,
					`wrapupcall_type`
				)VALUES(
					"'.$insert_selected_data_key['id'].'",
					"'.$insert_selected_data_key['activity_source'].'",
					"'.$insert_selected_data_key['createdon'].'",
					"'.$insert_selected_data_key['subject'].'",
					"'.$insert_selected_data_key['activitystatus'].'",
					"'.$insert_selected_data_key['createdby'].'",
					"'.$insert_selected_data_key['regarding'].'",
					"'.mysql_real_escape_string($insert_selected_data_key['description']).'",
					"'.$insert_selected_data_key['phonenumber'].'",
					"'.$insert_selected_data_key['statusreason'].'",
					"'.$insert_selected_data_key['source_name'].'",
					"'.$insert_selected_data_key['wrapupcat'].'",
					"'.$insert_selected_data_key['wrapupsubcat'].'",
					"'.$insert_selected_data_key['month'].'",
					"'.$insert_selected_data_key['week'].'",
					"'.$insert_selected_data_key['up_cross_sell'].'",
					"'.mysql_real_escape_string($insert_selected_data_key['product_sold']).'",
					"'.mysql_real_escape_string($insert_selected_data_key['customername']).'",
					"'.$insert_selected_data_key['fno'].'",
					"'.mysql_real_escape_string($insert_selected_data_key['crbt_requested']).'",
					"'.$insert_selected_data_key['language'].'",
					"'.$insert_selected_data_key['ftype'].'",
					"'.$insert_selected_data_key['floc'].'",
					"'.$insert_selected_data_key['custloc'].'",
					"'.$insert_selected_data_key['district'].'",
					"'.mysql_real_escape_string($insert_selected_data_key['crbt_available']).'",
					"'.$insert_selected_data_key['sms_countINT'].'",
					"'.$insert_selected_data_key['chap_call_dest'].'",
					"'.$insert_selected_data_key['chap_caller_type'].'",
					"'.$insert_selected_data_key['wrapupcall_type'].'"
				);		
			';
			$insert_selected_data_status = $custom_object->addit($insert_selected_data_query,'ccba02.reportscrm');
			if($insert_selected_data_status == 0 or mysql_error()){
				echo date('Y-m-d H:i:s')." : ERROR: ".mysql_error()." Record Not Inserted id:".$insert_data_key['id']." name:".$insert_data_key['name']." comment:".$insert_data_key['comment']." Query => [".$insert_selected_data_query."]";
				die("Dying ...\n");
				exit("Exiting ...\n");
			}else{
				$delete_selected_data_query = "
					DELETE FROM 
						`reportscrm`.`reportsphonecalls`
					WHERE 
						reportsphonecalls.id = '".$insert_selected_data_key['id']."';		
				";
				$delete_selected_data_query_status = $custom_object->no_row($delete_selected_data_query,'ccba01.reportscrm');
				
				if($delete_selected_data_query_status == false or mysql_error()){
					echo date('Y-m-d H:i:s')." : ERROR: ".mysql_error()." Record Not Deleted id:".$delete_data_key['id']." name:".$delete_data_key['name']." comment:".$delete_data_key['comment']." Query => [".$delete_selected_data_query."]";
					die("Dying ...\n");
					exit("Exiting ...\n");		
				}
			}
		}
	}else{
		echo date('Y-m-d H:i:s')." : The Table Is Already Empty. exiting ...\n";
		die("Dying ...\n");
		exit("Exiting ...\n");
	}
	$end_del = date('Y-m-d H:i:s');
	$del_time = strtotime($end_del) - strtotime($start_del);
	
	$processed_record_count += $fetch_record_count;	
	$current_db_count = $full_count	- $processed_record_count;
	$percent = number_format(($processed_record_count*100/$full_count),3);
	echo $percent."% Done Rows : ".number_format($processed_record_count,0)." in ".$del_time."s, Left : ".number_format($current_db_count,0)." Sleeping For : ".$sleeping_time."s ...";		
	sleep($sleeping_time);
	echo " Resuming \n";
	
}while(count($selected_data_list) > 0);

echo "Processed ".$full_count." Records";

?>