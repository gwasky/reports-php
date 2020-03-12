<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function retention_simreg_feedback_report($from, $to, $report_type){
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
				sr1_sim_registration.`name`,
				sr1_sim_registration.date_entered,
				sr1_sim_registration.date_modified,
				sr1_sim_registration.simreg_call_status,
				sr1_sim_registration.simreg_feedback,
				sr1_sim_registration.simreg_callback_date,
				sr1_sim_registration.simreg_callback_time,
				sr1_sim_registration.simreg_visit_date,
				sr1_sim_registration.simreg_visit_time,
				sr1_sim_registration.simreg_visit_location,
				sr1_sim_registration.simreg_district,
				sr1_sim_registration.simreg_town,
				sr1_sim_registration.simreg_franchise,
				sr1_sim_registration.msisdn_group,
				sr1_sim_registration.customer_name,
				sr1_sim_registration.customer_email
			FROM
				sr1_sim_registration
			INNER JOIN users ON users.id = sr1_sim_registration.assigned_user_id
			WHERE
				sr1_sim_registration.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			-- OR
				-- sr1_sim_registration.date_entered BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				sr1_sim_registration.simreg_call_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row[msisdn_group] == '') $row[msisdn_group] = 'BLANK';
		if($row['simreg_call_status'] == '') $row['simreg_call_status'] = 'Not Called';
		
		if($row['simreg_call_status'] == 'Not Called'){
			$row[answer_group] = $row[simreg_call_status];
			$row[call_result] = $row[simreg_call_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[simreg_call_status]));
		}
	}
	
	unset($row);
	
	function summarise($rows){
		foreach($rows as $rowdata){
			++$summary['Call attempts By MSISDN Group By Answer State'][$rowdata[msisdn_group]." >> ".$rowdata[answer_group]];
			
			if($rowdata[simreg_call_status] != 'Not Called'){
				++$summary['Call attempts By Month'][date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Date'][substr($rowdata[date_modified],0,10)];
				++$summary['Call attempts By Agent'][$rowdata[Agent]];
			}
			
			++$summary['Call attempts By MSISDN Group By Call Result'][$rowdata[msisdn_group]." >> ".$rowdata['call_result']];
			++$summary['Call attempts By Month By Answer Group By MSISDN Group By Call Result'][date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[msisdn_group]." >> ".$rowdata[call_result]];
			++$summary['Call attempts By MSISDN Group By Agent By Answer State'][$rowdata[msisdn_group]." >> ".$rowdata[Agent]." >> ".$rowdata[answer_group]];
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
	return display_retention_simreg_feedback_report($report);
}


function display_retention_simreg_feedback_report($report){
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
					<th>Date</th>
					<th>Call Status</th>
					<th>Call Result</th>
					<th>Callback Date Time</th>
					<th>Visit Date Time</th>
					<th>Visit Location</th>
					<th>District</th>
					<th>Town</th>
					<th>Franchise</th>
					<th>Feedback</th>
					<th>Customer Name</th>
					<th>Customer Email</th>
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
					<td class="text_values">'.$row[simreg_callback_date].' '.$row[simreg_callback_time].'</td>
					<td class="text_values">'.$row[simreg_visit_date].' '.$row[simreg_visit_time].'</td>
					<td class="text_values">'.$row[simreg_visit_location].'</td>
					<td class="text_values">'.$row[simreg_district].'</td>
					<td class="text_values">'.$row[simreg_town].'</td>
					<td class="text_values">'.$row[simreg_franchise].'</td>
					<td class="text_values">'.$row[simreg_feedback].'</td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="text_values">'.$row[customer_email].'</td>
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