<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function phc_phc_premier_health_Check_feedback_report($from, $to, $report_type){
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	if(!$to){
		$to = date('Y-m-d');
	}
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.survey');
	$query = "
			SELECT
				concat(users.last_name,' ',users.first_name) AS Agent,
				phc_phc_premier_health_check.id,
				phc_phc_premier_health_check.`name`,
				phc_phc_premier_health_check.date_entered,
				phc_phc_premier_health_check.date_modified,
				phc_phc_premier_health_check.modified_user_id,
				phc_phc_premier_health_check.created_by,
				phc_phc_premier_health_check.description,
				phc_phc_premier_health_check.deleted,
				phc_phc_premier_health_check.assigned_user_id,
				phc_phc_premier_health_check.phc_customer_name,
				phc_phc_premier_health_check.phc_occupation,
				phc_phc_premier_health_check.phc_email_address,
				phc_phc_premier_health_check.phc_alternate_phone_no,
				phc_phc_premier_health_check.phc_service_satisfaction,
				phc_phc_premier_health_check.phc_what_you_like_about_us,
				phc_phc_premier_health_check.phc_what_you_like_us_to_improv,
				phc_phc_premier_health_check.phc_how_you_like_to_be_rewarde,
				phc_phc_premier_health_check.phc_recommend_us,
				phc_phc_premier_health_check.phc_prefered_comm_method,
				phc_phc_premier_health_check.phc_marketing,
				phc_phc_premier_health_check.phc_customer_care,
				phc_phc_premier_health_check.phc_promotions,
				phc_phc_premier_health_check.phc_district_town,
				phc_phc_premier_health_check.phc_prefered_product,
				phc_phc_premier_health_check.phc_accessibility,
				phc_phc_premier_health_check.phc_call_status,
				phc_phc_premier_health_check.phc_simreg_status,
				phc_phc_premier_health_check.phc_callback_date,
				phc_phc_premier_health_check.phc_callback_time,
				phc_phc_premier_health_check.phc_feedback_location
			FROM
				phc_phc_premier_health_check
			INNER JOIN users ON users.id = phc_phc_premier_health_check.assigned_user_id
			WHERE
				phc_phc_premier_health_check.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				phc_phc_premier_health_check.phc_call_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['phc_call_status'] == '') $row['phc_call_status'] = 'Not Called';
		
		if($row['phc_call_status'] == 'Not Called'){
			$row[answer_group] = $row[phc_call_status];
			$row[call_result] = $row[phc_call_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[phc_call_status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			++$summary['Call attempts By Segment By Answer State'][$rowdata[wash_segment]." >> ".$rowdata[answer_group]];
			
			if($rowdata[phc_call_status] != 'Not Called'){
				++$summary['Call attempts By Month'][date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Date'][substr($rowdata[date_modified],0,10)];
				
				if($rowdata[phc_service_satisfaction] != ''){
					++$summary['Call attempts By Question By Answer']["As our premium customer are you satisfied with our services? >> ".$rowdata[phc_service_satisfaction]];
				}
				
				if($rowdata[phc_what_you_like_about_us] != ''){
					++$summary['Call attempts By Question By Answer']["If Yes, what do you like most about us? (".$rowdata[phc_service_satisfaction].") >> ".$rowdata[phc_what_you_like_about_us]];
				}
				
				if($rowdata[phc_what_you_like_us_to_improv] != ''){
					++$summary['Call attempts By Question By Answer']["If NO, what do you like us to improve? (".$rowdata[phc_service_satisfaction].") >> ".$rowdata[phc_what_you_like_us_to_improv]];
				}
				
				if($rowdata[phc_how_you_like_to_be_rewarde] != ''){
					++$summary['Call attempts By Question By Answer']["As our Highly Valued friend, How would you like to be rewarded? >> ".$rowdata[phc_how_you_like_to_be_rewarde]];
				}
				
				if($rowdata[phc_prefered_comm_method] != ''){
					++$summary['Call attempts By Question By Answer']["When being contacted, what method of communication would you prefer? >> ".$rowdata[phc_prefered_comm_method]];
				}
				
				if($rowdata[phc_recommend_us] != ''){
					++$summary['Call attempts By Question By Answer']["How likely is it you would recommend us to a friend? >> ".$rowdata[phc_recommend_us]];
				}
				
				++$summary['Call attempts By Agent'][$rowdata[Agent]];
			}
			
			++$summary['Call attempts By Call Result'][$rowdata['call_result']];
			++$summary['Call attempts By Month By Answer Group By Call Result'][date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
			++$summary['Call attempts By Agent By Answer State'][$rowdata[Agent]." >> ".$rowdata[answer_group]];
			++$summary['Call attempts By Agent By Date By Answer State'][$rowdata[Agent]." >> ".substr($rowdata[date_modified],0,10)." >> ".$rowdata[answer_group]];
			//++$summary['Call attempts By Month By Call Result'][date('M-Y',strtotime($rowdata[date_modified]))." >> ".$rowdata[call_result]];
				
		}
		
		return $summary;
	}
	
	switch($report_type){
		case 'summary_brief':
			$report[summary] = summarise($rows);
			break;
		case 'summary':
			$report[summary] = summarise($rows);
			break;
		case 'both':
			$report[rows] = $rows;
			$report[summary] = summarise($rows);
			break;
		case 'detail':
		default:
			$_POST[report_type] = 'summary';
			$report[rows] = $rows;
	}
	
	$report[stop] = strtotime(date('Y-m-d H:i:s'));
	return display_phc_phc_premier_health_Check_feedback_report($report);
}


function display_phc_phc_premier_health_Check_feedback_report($report){
	//echo '<pre>'.print_r($report[summary],true).'</pre>';
	
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
					<th>Agent</th>
					<th>MSISDN</th>
					<th>Date Modified</th>
					<th>Grouping</th>
					<th>Call Result</th>
					<th>Customer Name</th>
					<th>Occupation</th>
					<th>Email Address</th>
					<th>Alternate Number</th>
					<th>As our premium customer are you satisfied with our services?</th>
					<th>If Yes, what do you like most about us?</th>
					<th>If NO, what do you like us to improve?</th>
					<th>As our Highly Valued friend, How would you like to be rewarded?</th>
					<th>How likely is it you would recommend us to a friend?</th>
					<th>When being contacted, what method of communication would you prefer?</th>
					<th>SIM Registration Status</th>
					<th>Callback Date &amp; Time</th>
					<th>Location</th>
					<th>Comment</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$row_count.'</td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[date_modified].'</td>
					<td class="text_values">'.$row[answer_group].'</td>	
					<td class="text_values">'.$row[call_result].'</td>
					<td class="text_values">'.$row[phc_customer_name].'</td>
					<td class="text_values">'.$row[phc_occupation].'</td>
					<td class="text_values">'.$row[phc_email_address].'</td>
					<td class="text_values">'.$row[phc_alternate_phone_no].'</td>
					<td class="text_values">'.$row[phc_service_satisfaction].'</td>
					<td class="text_values">'.$row[phc_what_you_like_about_us].'</td>
					<td class="text_values">'.$row[phc_what_you_like_us_to_improv].'</td>
					<td class="text_values">'.$row[phc_how_you_like_to_be_rewarde].'</td>
					<td class="text_values">'.$row[phc_recommend_us].'</td>
					<td class="text_values">'.$row[phc_prefered_comm_method].'</td>
					<td class="text_values">'.$row[phc_simreg_status].'</td>
					<td class="text_values">'.$row[phc_callback_date].' '.$row[phc_callback_time].'</td>
					<td class="text_values">'.$row[phc_feedback_location].'</td>
					<td class="text_values">'.$row[description].'</td>
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