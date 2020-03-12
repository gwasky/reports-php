<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function generate_icr_icr_survey_report($from, $to, $report_type){
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	if(!$to){
		$to = date('Y-m-d');
	}
	$_POST[to] = $to;
	
	if(!$from){
		$from = date('Y-m-').'01';
		$from = date('Y-m-d');
	}
	$_POST[from] = $from;
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.survey');
	$query = "
		SELECT
			icr_icr_survey.id,
			icr_icr_survey.`name` AS msisdn,
			icr_icr_survey.date_modified,
			icr_icr_survey.call_status,
			icr_icr_survey.note_nw_quality_change,
			icr_icr_survey.nw_rating,
			icr_icr_survey.nw_rating_district,
			icr_icr_survey.nw_rating_town,
			icr_icr_survey.nw_subject_issue,
			CONCAT(assigned_user.first_name,' ',assigned_user.last_name) AS assigned_to,
			CONCAT(modified_user.first_name,' ',modified_user.last_name) AS modified_by
		FROM
			icr_icr_survey
			INNER JOIN users assigned_user ON icr_icr_survey.assigned_user_id = assigned_user.id
			INNER JOIN users modified_user ON icr_icr_survey.modified_user_id = modified_user.id
		WHERE
			icr_icr_survey.date_modified BETWEEN DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR)
	";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	if(count($rows) == 0) { return display_icr_icr_survey_report("NO DATA"); }
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['call_status'] == '') $row['call_status'] = 'Not Called';
		
		if($row['call_status'] == 'Not Called'){
			$row[answer_group] = $row[call_status];
			$row[call_result] = $row[call_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[call_status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			
			if($rowdata[answer_group] != 'Not Called'){
				++$summary['Calls By Call Result By Change in Quality'][$rowdata[call_result]." >> ".$rowdata[note_nw_quality_change]];
				++$summary['Calls By Call Result By Rating'][$rowdata[call_result]." >> ".$rowdata[nw_rating]];
				
				++$summary['Calls By Call Result By Change in Quality By Rating'][$rowdata[call_result]." >> ".$rowdata[note_nw_quality_change]." >> ".$rowdata[nw_rating]];
				
				++$summary['Poor Ratings By Call Result By District'][$rowdata[call_result]." >> ".$rowdata[nw_rating_district]];
				++$summary['Poor Ratings By Call Result By Rating By District'][$rowdata[call_result]." >> ".$rowdata[nw_rating]." >> ".$rowdata[nw_rating_district]];
				
				++$summary['Calls By Call Result By Change in Quality By Rating By Network Issue'][$rowdata[call_result]." >> ".$rowdata[nw_rating]." >> ".$rowdata[nw_subject_issue]];
			}
			
			++$summary['Call attempts By Call Result'][$rowdata['call_result']];
			++$summary['Call attempts By Month By Answer Group By Call Result'][date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
			++$summary['Call attempts By Agent By Answer Group By Call Result'][$rowdata[assigned_to]." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
			++$summary['Call attempts By Agent By Date By Answer Group By Call Result'][$rowdata[assigned_to]." >> ".substr($rowdata[date_modified],0,10)." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];

		}
		
		return $summary;
	}
	
	switch($report_type){
		case 'both':
			$_POST[report_type] = $report_type;
			$report[rows] = $rows;
			$report[summary] = summarise($rows);
			break;
		case 'detail':
			$_POST[report_type] = $report_type;
			$report[rows] = $rows;
			break;
		case 'summary':
		default:
			$_POST[report_type] = 'summary';
			$report[summary] = summarise($rows);
	}
	
	$report[stop] = strtotime(date('Y-m-d H:i:s'));
	return display_icr_icr_survey_report($report);
}


function display_icr_icr_survey_report($report){
	//echo '<pre>'.print_r($report[summary],true).'</pre>';
	
	if($report == "NO DATA") { return "No Data matches your filters.<hr>"; }
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="80%">
			<tr>
				<td>Report took ['.($report[stop] - $report[start]).'] seconds to run</td>
			</tr>
	';
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			$html .= '
				<tr>
					<td style="font-size:15px;">'.$summary_heading.'</td>
				</tr>
				<tr>
					<td>
					<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
						<tr>
							<th>#</th>
			';
			//asort($summary_data);
			//echo '<pre>'.print_r($summary_data).'</pre>';
			//Titles
			$columns = explode(" By ",$summary_heading);
			$first_col = array_shift($columns);
			
			foreach($columns as $column){
				$html .= '
							<th>'.$column.'</th>
				';
			}
			
			$html .= '
					<th>'.$first_col.'</th>
			';
			
			$html .= '
						</tr>
			';
			
			//row
			foreach($summary_data as $parameter_string=>$parameter_string_value){
				
				$html .= '
						<tr>
							<td class="values">'.++$row_number.'</td>
				';
				
				$parameters = array();
				$parameters = explode(" >> ",$parameter_string);
				
				foreach($parameters as $parameter){
					$html .= '
							<td class="'; if(!is_numeric($parameter)){ $html .= 'text_'; } $html .= 'values">'; 
								if(!is_numeric($parameter)){ $html .= $parameter; }else{ $html .= number_format($parameter,0); } $html .= '
							</td>
					';
				}
				$html .= '
							<td class="values">'.number_format($parameter_string_value,0).'</td>
						</tr>
				';
			}
			
			unset($row_number);
			
			$html .= '
					</table>
					</td>
				</tr>
				<tr>
					<td style="height:10px;"></td>
				</tr>
			';
		}
	}
	
	//if we have both reports let us space them by a row
	if((count($report[rows]) > 0) &&(count($report[summary]) > 0)){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if(count($report[rows]) > 0){
		$html .= '
			<tr>
				<th style="height:20px;">DETAILS</th>
			</tr>
		';
		$html .= '
			<tr><td>
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable"> 
				<tr>
					<th>#</th>
					<th>Assigned to</th>
					<th>Called by</th>
					<th>MSISDN</th>
					<th>Date Called</th>
					<th>Answer Grouping</th>
					<th>Call Result</th>
					<th>Noticed Change in NW Quality?</th>
					<th>Rate of NW Quality</th>
					<th>NW Issue Experienced</th>
					<th>Rating District</th>
					<th>Rating Town</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$row_count.'</td>
					<td class="text_values">'.$row[assigned_to].'</td>
					<td class="text_values">'.$row[modified_by].'</td>
					<td class="text_values">'.$row[msisdn].'</td>
					<td class="values">'.substr($row[date_modified],0,10).'</td>
					<td class="text_values">'.$row[answer_group].'</td>	
					<td class="text_values">'.$row[call_result].'</td>
					<td class="text_values">'.$row[note_nw_quality_change].'</td>
					<td class="text_values">'.$row[nw_rating].'</td>
					<td class="text_values">'.$row[nw_subject_issue].'</td>
					<td class="text_values">'.$row[nw_rating_district].'</td>
					<td class="text_values">'.$row[nw_rating_town].'</td>
				</tr>
			';
		}
		
		$html .= '
			</table>
			</td></tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
	
}
?>