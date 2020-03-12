<?php

function get_network_wrapups_for_last_30_days(){
	$myquery = new custom_query();
	
	$query = "
		SELECT
			reportsphonecalls.createdon,
			reportsphonecalls.subject,
			reportsphonecalls.phonenumber,
			reportsphonecalls.wrapupsubcat,
			reportsphonecalls.district,
			REPLACE(LOWER(reportsphonecalls.custloc),'~nil','') AS custloc
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon BETWEEN DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL 30 DAY) AND DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59'), INTERVAL 1 DAY) AND
			reportsphonecalls.wrapupcat = 'Network'
	";
	
	$wrapups_list = $myquery->multiple($query, 'ccba02.reportscrm');
	
	return $wrapups_list;	}

function generate_network_repeat_calls_per_24hrs($use_date){
	$records = get_network_wrapups_for_last_30_days();
	if(count($records) == 0){ exit('NO DATA'); }
	
	//Get dates for trending in the graph
	$trending_dates = dateRange($first_date = date("Y-m-d"), $last_date = date("Y-m-d", strtotime("-30 days")));
	
	//Create dummy array
	$dummy_records = $records;
	
	/*
	Loop through the $dummy_array
		Loop through the $testArray
			Store $dummy_array values that appear in $testArray and also whose count is greater than 1.
	*/
	foreach($dummy_records as $dummy_records_key => $dummy_records_value){
		foreach($records as $records_key => $records_value){
			if(
				$dummy_records_value['phonenumber'] == $records_value['phonenumber']&&
				$dummy_records_value['subject'] == $records_value['subject']				
			){
				if(
					!in_array(
						$records_value['createdon'],
						$repeat_records[$dummy_records_value['subject']][$dummy_records_value['phonenumber']]
					)
				){
					$repeat_records[$dummy_records_value['subject']][$dummy_records_value['phonenumber']][] = $records_value['createdon'];				
				}
			}
		}
		//Remove phone numbers that only appear once
		if(count($repeat_records[$dummy_records_value['subject']][$dummy_records_value['phonenumber']]) < 2){
			unset($repeat_records[$dummy_records_value['subject']][$dummy_records_value['phonenumber']]);
		}	
		//Remove subjects that dont have entries
		if(count($repeat_records[$dummy_records_value['subject']]) == 0){
			unset($repeat_records[$dummy_records_value['subject']]);
		}
	}
	//Sort the dates in ascending order	
	foreach($repeat_records as $repeat_records_key => $repeat_records_value){
		foreach($repeat_records_value as $key => $value){
			if(asort($value) != 1){
				$sorted_repeat_records[$repeat_records_key][$key] = asort($value);
			}
			else{
				$sorted_repeat_records[$repeat_records_key][$key] = $value;
			}
		}
	}
		
	//Check the number of hours between the dates.
	foreach($sorted_repeat_records as $sorted_repeat_records_key => $sorted_repeat_records_value){
		foreach($sorted_repeat_records_value as $key => $value){
			//Variables to control the while loops
			$loop_state = true;
			$last_element = end($value);
			
			//Move array pointer back to the start
			reset($value);
			
			//This loop will keep comparing 2 dates unitl the last row is reached.
			while($loop_state == true){
				$time1 = current($value); 
				$time2 = next($value);
				$counted_date = date("Y-m-d", strtotime($time2));
				
				$date_difference = date_difference($time1, $time2);
				
				if($date_difference <= 24){
					++$repeat_wrapups[$sorted_repeat_records_key][$counted_date];
				}
				if($time2 == $last_element){/* Checks for the last element */
					$loop_state = false;
				}
			}
		}
	}
	
	//Get repeat subjects and assign them keys
	$keyed_subjects[] = 0;
	foreach($repeat_wrapups as $key => $value){
		$keyed_subjects[] = $key;
	}
	unset($keyed_subjects[0]);/* Making the first element start from 1. */
	$report['unique_subs'] = $keyed_subjects;
	
	//Replacing the subject keys and also synchronizing the dates and counts
	foreach($keyed_subjects as $keyed_subjects_key => $keyed_subjects_value){
		foreach($repeat_wrapups as $repeat_wrapups_key => $repeat_wrapups_value){
			if($keyed_subjects_value == $repeat_wrapups_key){
				$report['subject_count'][$keyed_subjects_key] = synchronize_dates_in_arrays($trending_dates, $repeat_wrapups_value);
			}
		}
	}
	
	/* Graph Data */
	$graph_detail['data'] = $report['subject_count'];
	$graph_detail['title'] = '24hr Network Repeat Wrapups';
	$graph_detail['display_title'] = false;
	$graph_detail['legend'] = true;
	$graph_detail['line_graph'] = true;
	$graph_detail['bar_graph'] = false;  
	$graph_detail['set_data_points'] = true;
	$graph_detail['set_data_values'] = false;
	$graph_detail['width'] = 850;
	$graph_detail['height'] = 600;
	
	/* Graph Data For Complaints */
	$my_graph = new dbgraph();
	$my_graph->graph($graph_detail['title'],"30 days before ".$use_date, $graph_detail);
	custom_query::select_db('graphing');
	$report['graph_id']['24hr_repeat_network_wrapups'] = $my_graph->Save();
	
	// EXCEL ATTACHMENT
	/* Get Districts */
	foreach($records as $records_key => $records_value){
		$districts[] = $records_value['district'];
	}
	$unique_districts = array_values(array_unique($districts));
	
	/* TO EXCEL::: Get District Count */
	$district_counts = array_count_values($districts);
	foreach($district_counts as $key => $value){
		$excel['district_count'][] = array('district' => $key, 'count' => $value);
	}
	
	/* Get Districts And Towns */
	foreach($unique_districts as $unique_districts_key => $unique_districts_value){
		$town_count = 0;
		foreach($records as $records_key => $records_value){
			if($unique_districts_value == $records_value['district']){
				$districts_towns[$unique_districts_value][] = $records_value['custloc'];
			}
		}
	}	
	/* Get Districts And Towns Counts */
	foreach($districts_towns as $key => $value){
		$district_town_count[$key] = array_count_values($value);
	}
	/* TO EXCEL::: Get Districts And Towns Counts */
	foreach($district_town_count as $district_town_count_key => $district_town_count_value){
		foreach($district_town_count_value as $key => $value){
			$excel['district_town_count'][] = array(
				'district' => $district_town_count_key,
				'town' => $key,
				'count' => $value
			);
		}
	}
	
	/* Get District And Town */
	foreach($districts_towns as $districts_towns_key => $districts_towns_value){
		foreach($districts_towns_value as $key => $value){
			$district_town[] = array(
				'district' => $districts_towns_key,
				'town' => $value
			);
		}
	}	
	/* TO EXCEL::: Get District And Town */
	$testing_array = array();
	foreach($district_town as $district_town_key => $district_town_value){
		if(!in_array($district_town_value, $testing_array)){
			$excel['district_town'][] = $district_town_value;
		}
		$testing_array[] = $district_town_value;
	}
	
	/* Creating an excel file with locations */
	$objPHPExcel = new PHPExcel();	
	$objPHPExcel->getProperties()->setCreator('CCBA TEAM')
								 ->setLastModifiedBy('CCBA TEAM')
								 ->setTitle('Network Wrapups Locations')
								 ->setSubject('Network Wrapups Locations')
								 ->setDescription('Locations From Repeat Network Calls')
								 ->setKeywords('office PHPExcel php')
								 ->setCategory('Test result file');
	/* Setting up districts count. Filter functionality is also added */
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'District')
								  ->setCellValue('B1', 'Call Count');								  
	$dataArray = $excel['district_count'];
	$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');/* Passing data on cell A2 */	
	$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);/* Set title row bold */	
	$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());/* Set filter */	
	$objPHPExcel->getActiveSheet()->setTitle('District Count');/* Rename worksheet */
	
	/* Setting up districts and town counts. Filter functionality is also added */
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'District')
								  ->setCellValue('B1', 'Customer Location')
								  ->setCellValue('C1', 'Call Count');								  
	$dataArray = $excel['district_town_count'];
	$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');/* Passing data on cell A2 */	
	$objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);/* Set title row bold */	
	$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());/* Set filter */	
	$objPHPExcel->getActiveSheet()->setTitle('District Town Count');/* Rename worksheet */
	
	/* Setting up districts and towns. Filter functionality is also added */
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(2);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'District')
								  ->setCellValue('B1', 'Customer Location');								  
	$dataArray = $excel['district_town'];
	$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');/* Passing data on cell A2 */	
	$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);/* Set title row bold */	
	$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());/* Set filter */	
	$objPHPExcel->getActiveSheet()->setTitle('Districts And Towns');/* Rename worksheet */
	$objPHPExcel->setActiveSheetIndex(0);/* Setting first sheet as default for openning */
		
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');/* Select file format */
	$objWriter->save('/srv/www/htdocs/resources/EXCEL/PHPExcel/temp_Excel_Files/'.$use_date.'.xlsx');
			
		
	return display_24hr_network_repeat_wrapups_report($report, $use_date);}

function date_difference($time1, $time2){	
	$diff = strtotime($time1) - strtotime($time2);
	$hours = $diff / (60*60);
	
	return abs($hours);}

function get_subject_dates($array, $subfield){
	//Get dates and sort them
	$sortarray = array();
	
	foreach($array as $key => $row){
        $sortarray[$key] = $row[$subfield];
    }
	
	if(is_array(asort($sortarray))){
		return asort($sortarray);
	}
	else{
		return $sortarray;
	}	
    }
	
function display_24hr_network_repeat_wrapups_report($report, $date){	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<tr>
				<th colspan="3">NETWORK REPEAT WRAPUPS FOR THE LAST 30 DAYS</th>
			</tr>			
			<tr>
				<td>'.display_generic_graph($report['graph_id']['24hr_repeat_network_wrapups'], TRUE).'</td>
				<td colspan="2">&nbsp;</td>
			</tr>		
			<tr>
				<th colspan="3">GRAPH KEY: REPEAT NETWORK SUBJECTS</th>
			</tr>	
			<tr>
				<th>Subject</th>
				<th class="values">Key</th>
				<th>&nbsp;</th>
			</tr>';		
	foreach($report['unique_subs'] as $key => $value){		
		$html .= '		
			<tr class="'.row_style(++$i).'">
				<td class="text_values">'.$value.'</td>
				<td class="values">'.$key.'</td>
				<td>&nbsp;</td>
			</tr>';
		}
	$html .= '		
		</table>
	';
	
	return $html;}	
	
function dateRange($first_date, $last_date){
    $dates = array();
    $current = strtotime($last_date);
    $last = strtotime($first_date);
	
    while($current <= $last){
        $dates[] = date("Y-m-d", $current);
        $current = strtotime("+1 day", $current);
    }

    return $dates;}	
	
function synchronize_dates_in_arrays($basis_array, $new_array){
	// if some elements dont exists, "add" them...
	if(count($basis_array) != count($new_array)){
		foreach($basis_array as $key => $value){
			if(!isset($new_array[$value])){
				$new_array[$value] = 0;
			}
		}
	}
	
	return $new_array;}	
	
function mail_attachment($to, $subject, $message, $from, $file){
	// $file should include path and filename
	$filename = basename($file);
	$file_size = filesize($file);
	$content = chunk_split(base64_encode(file_get_contents($file))); 
	$uid = md5(uniqid(time()));
	$from = str_replace(array("\r", "\n"), '', $from); // to prevent email injection
	$header = "From: ".$from."\r\n"
		."MIME-Version: 1.0\r\n"
		."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
		."This is a multi-part message in MIME format.\r\n" 
		."--".$uid."\r\n"
		."Content-type:text/html; charset=iso-8859-1\r\n"
		."Content-Transfer-Encoding: 7bit\r\n\r\n"
		.$message."\r\n\r\n"
		."--".$uid."\r\n"
		."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
		."Content-Transfer-Encoding: base64\r\n"
		."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
		.$content."\r\n\r\n"
		."--".$uid."--"; 
		
	if(@mail($to, $subject, "", $header)){
		echo '<p>File Attachment And Sending Done Successfully!</p>';
	}else{
		echo '<p>File Attachment And Sending Failed </p>';
	}}	

?>