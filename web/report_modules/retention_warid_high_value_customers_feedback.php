<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function warid_high_value_customers_feedback_report($from, $to, $report_type){
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
				whvc_warid_high_value_customers.`name`,
				whvc_warid_high_value_customers.date_entered,
				whvc_warid_high_value_customers.date_modified,
				whvc_warid_high_value_customers.modified_user_id,
				whvc_warid_high_value_customers.created_by,
				whvc_warid_high_value_customers.description,
				whvc_warid_high_value_customers.deleted,
				whvc_warid_high_value_customers.assigned_user_id,
				whvc_warid_high_value_customers.customer_name,
				whvc_warid_high_value_customers.email_address,
				whvc_warid_high_value_customers.alternate_contact,
				whvc_warid_high_value_customers.prefered_pick_up_date,
				whvc_warid_high_value_customers.time,
				whvc_warid_high_value_customers.whvc_location,
				whvc_warid_high_value_customers.whvc_call_status,
				whvc_warid_high_value_customers.whvc_overall_feedback,
				whvc_warid_high_value_customers.whvc_delievry_location
			FROM
				whvc_warid_high_value_customers
				INNER JOIN users ON users.id = whvc_warid_high_value_customers.assigned_user_id
			WHERE
				whvc_warid_high_value_customers.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				whvc_warid_high_value_customers.whvc_call_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['whvc_call_status'] == '') $row['whvc_call_status'] = 'Not Called';
		
		if($row['whvc_call_status'] == 'Not Called'){
			$row[answer_group] = $row[whvc_call_status];
			$row[call_result] = $row[whvc_call_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[whvc_call_status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			++$summary['Call attempts By Answer State'][$rowdata[answer_group]];
			
			if($rowdata[whvc_call_status] != 'Not Called'){
				++$summary['Call attempts By Month'][date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Date'][substr($rowdata[date_modified],0,10)];
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
	return display_warid_high_value_customers_feedback_report($report);
}


function display_warid_high_value_customers_feedback_report($report){
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
					<th>Date Modified</th>
					<th>Grouping</th>
					<th>Call Result</th>
					<th>Date Time</th>
					<th>Customer Name</th>
					<th>Email Address</th>
					<th>Contact</th>
					<th>Pick Up Loaction</th>
					<th>Delivery Loaction</th>
					<th>Feedback</th>
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
					<td class="text_values">'.$row[whvc_call_status].'</td>
					<td class="text_values">'.$row[prefered_pick_up_date].' '.$row['time'].'</td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="text_values">'.$row[email_address].'</td>
					<td class="text_values">'.$row[alternate_contact].'</td>
					<td class="text_values">'.$row[whvc_location].'</td>
					<td class="text_values">'.$row[whvc_delievry_location].'</td>
					<td class="text_values">'.$row[whvc_overall_feedback].'</td>
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