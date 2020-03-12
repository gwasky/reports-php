<?php

// This algorithm is for repeat wrapups in the same day
// The script - wrapups_network_repeat_calls_per_24hrs.php - has the algorithm for repeat wrapups within 24hrs

function generate_network_wrapups_for_last_30_days(){
	$myquery = new custom_query();
	
	$query = "
		SELECT
			date(reportsphonecalls.createdon) AS createdon,
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

function generate_network_repeat_wrapups_per_day($use_date){
	$wrapups = generate_network_wrapups_for_last_30_days();
	if(count($wrapups) == 0){ exit('NO DATA'); }
	
	/* Creating dates as headers */
	foreach($wrapups as $wrapups_key){
		$heads[] = $wrapups_key['createdon'];
	}
	/* Removing duplicate dates */
	$date_heads = array_merge(array_unique($heads));
	
	/* Sorting and storing data into respective dates */
	foreach($date_heads as $heads_key => $heads_value){
		foreach($wrapups as $wrapups_list_key){
			if($heads_value == $wrapups_list_key['createdon']){
				$dated_wrapups[$heads_value][] = $wrapups_list_key;
			}
		}
	}
	
	/* Declaring arrays */
    $tested_wrapup_array = array();
    $duplicates = array();
	
	/* Generating repeat wrapup keys */
	foreach($date_heads as $heads_key => $heads_value){
		foreach($dated_wrapups[$heads_value] as $date_key => $date_value){
			foreach($tested_wrapup_array[$heads_value] as $tested_key => $tested_value){
				if(
					in_array($date_value['subject'], $tested_value) &&
					in_array($date_value['phonenumber'], $tested_value) &&
					in_array($date_value['createdon'], $tested_value)
				){
					$duplicates[$heads_value][] = $date_key;
				}
			}
			$tested_wrapup_array[$heads_value][] = $date_value;
		}
	}			
		
	/* GET DISTRICT COUNT */
	foreach($duplicates as $dup_key => $dup_value){
		foreach($dup_value as $key => $value){
			$repeat_districts[] = $dated_wrapups[$dup_key][$value]['district'];
		}
	}
	/* Unique repeat districts */
	$districts = array_values(array_unique($repeat_districts));	
	/* Counting repeat districts */
	foreach($districts as $key => $value){
		foreach($repeat_districts as $repeat_key => $repeat_value){
			if($value == $repeat_value){
				++$district_count[$value];
			}
		}
	}
	/* TO EXCEL:::::District count */
	foreach($district_count as $dist_count_key => $dist_count_value){
		$report['district_count'][] = array('dist' => $dist_count_key, 'count' => $dist_count_value);
	}
	
	/* GET DISTRICTS AND TOWNS COUNT */
	foreach($duplicates as $dup_key => $dup_value){
		foreach($dup_value as $key => $value){
			++$repeat_towns[$dup_key][$value]['custloc'];
		}
	}
	/* TO EXCEL:::::Districts and towns count */
	foreach($repeat_towns as $rep_town_key => $rep_town_value){
		foreach($rep_town_value as $key => $value){
			$report['dist_town_count'][] = array(
												'dist' => $dated_wrapups[$rep_town_key][$key]['district'],
												'town' => $dated_wrapups[$rep_town_key][$key]['custloc'],
												'count' => $value['custloc']
											);
		}
	}
	
	/* GET DISTRICTS AND TOWNS */
	foreach($duplicates as $dup_key => $dup_value){
		foreach($dup_value as $key => $value){
			$district_and_town[] = array($dated_wrapups[$dup_key][$value]['district'], $dated_wrapups[$dup_key][$value]['custloc']);
		}
	}		
	/* TO EXCEL:::::Get Unique districts and towns */
	$unique_dist_town = array();
	foreach($district_and_town as $value){
		if(!in_array($value, $unique_dist_town)){
			$report['unique_dist_town'][] = $value;
		}
		$unique_dist_town[] = $value;
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
	/* Setting up districts and towns. Filter functionality is also added */
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'District')
								  ->setCellValue('B1', 'Call Count');								  
	$dataArray = $report['district_count'];
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
	$dataArray = $report['dist_town_count'];
	$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');/* Passing data on cell A2 */	
	$objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);/* Set title row bold */	
	$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());/* Set filter */	
	$objPHPExcel->getActiveSheet()->setTitle('District Town Count');/* Rename worksheet */
	
	/* Setting up districts and towns. Filter functionality is also added */
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(2);
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'District')
								  ->setCellValue('B1', 'Customer Location');								  
	$dataArray = $report['unique_dist_town'];
	$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');/* Passing data on cell A2 */	
	$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);/* Set title row bold */	
	$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());/* Set filter */	
	$objPHPExcel->getActiveSheet()->setTitle('Districts And Towns');/* Rename worksheet */
	$objPHPExcel->setActiveSheetIndex(0);/* Setting first sheet as default for openning */
		
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');/* Select file format */
	$objWriter->save('/srv/www/htdocs/resources/EXCEL/PHPExcel/temp_Excel_Files/'.$use_date.'.xlsx');
	
	/* GET SUBJECTS */
	foreach($dated_wrapups as $dated_wrapups_key => $dated_wrapups_value){
		foreach($dated_wrapups_value as $key => $value){
			$all_subjects[] = $value['subject'];
			$repeat_subjects[$dated_wrapups_key][] = $value['subject'];
		}
	}
	/* Unique subjects */
	$unique_subs = array_unique($all_subjects);
	/* Changing keys */
	$unique_keyed_subs = array_combine($unique_subs, $unique_subs);
	
	/* Count subjects */
/*	foreach($repeat_subjects as $repeat_subjects_key => $repeat_subjects_value){
		$subject_count[$repeat_subjects_key][] = array_count_values($repeat_subjects_value);
	}*/
	/* Repeat subjects */
	foreach($date_heads as $heads_key => $heads_value){
		foreach($duplicates[$heads_value] as $dup_key => $dup_value){
			$repeat_subject[$heads_value][] = $dated_wrapups[$heads_value][$dup_value]['subject'];
		}
	}

	/* Count subjects */
	foreach($repeat_subject as $repeat_subject_key => $repeat_subject_value){
		$subject_count[$repeat_subject_key][] = array_count_values($repeat_subject_value);
	}	
	
	/* Synchronize arrays */
	$dummy_array = array();
	foreach($date_heads as $heads_key => $heads_value){
		if(array_key_exists($heads_value, $subject_count)){
			foreach($subject_count[$heads_value] as $sub_count_key => $sub_count_value){
				$dated_subject_count[$heads_value][] = synchronize_arrays($unique_keyed_subs, $sub_count_value);
			}
		}
		else{
			$dated_subject_count[$heads_value][] = synchronize_arrays($unique_keyed_subs, $dummy_array);
		}
	}
	
	/* TO GRAPH:::::Unique subjects */
	$i = 1;
	foreach($unique_subs as $key => $value){
		$report['unique_subs'][$i] = $value;
		$i++;
	}
	/* TO GRAPH:::::Preparing subjects into dates and subject counts */
	foreach($report['unique_subs'] as $subs_key => $subs_value){
		foreach($dated_subject_count as $dated_key => $dated_value){
			foreach($dated_value as $key => $value){
				$report['subject_count'][$subs_key][$dated_key] = $value[$subs_value];
			}
		}
	}
			
	/* Graph Data */
	$graph_detail['data'] = $report['subject_count'];
	$graph_detail['title'] = 'Network Repeat Wrapups';
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
	$report['graph_id']['repeat_network_wrapups'] = $my_graph->Save();	
		
	return display_network_repeat_wrapups_report($report, $use_date);}
	
function display_network_repeat_wrapups_report($report, $date){	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<tr>
				<th colspan="3">NETWORK REPEAT WRAPUPS FOR THE LAST 30 DAYS</th>
			</tr>			
			<tr>
				<td>'.display_generic_graph($report['graph_id']['repeat_network_wrapups'], TRUE).'</td>
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
	
function synchronize_arrays($array1, $array2){
	// if some elements dont exists, "add" them...
	if(count($array1) != count($array2)){
		foreach($array1 as $key => $value){
			if(!isset($array2[$key])){
				$array2[$key] = 0;
			}
		}
	}
	
	return $array2;}

?>