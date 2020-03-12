<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function hvild_hv_ild_upsell_feedback_report($from, $to, $report_type){
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
				hvild_hv_ild_upsell.id,
				hvild_hv_ild_upsell.`name`,
				hvild_hv_ild_upsell.date_entered,
				hvild_hv_ild_upsell.date_modified,
				hvild_hv_ild_upsell.modified_user_id,
				hvild_hv_ild_upsell.created_by,
				hvild_hv_ild_upsell.description,
				hvild_hv_ild_upsell.deleted,
				hvild_hv_ild_upsell.assigned_user_id,
				hvild_hv_ild_upsell.hvild_segment,
				hvild_hv_ild_upsell.hvild_status,
				hvild_hv_ild_upsell.hvild_customer_name,
				hvild_hv_ild_upsell.hvild_email,
				hvild_hv_ild_upsell.hvild_why_sub_doesnt_call,
				hvild_hv_ild_upsell.hvild_about_ild_bundles,
				hvild_hv_ild_upsell.hvild_areas_of_improvement,
				hvild_hv_ild_upsell.hvild_cc_medium,
				hvild_hv_ild_upsell.hvild_sub_pain,
				hvild_hv_ild_upsell.hvild_product_name,
				hvild_hv_ild_upsell.hvild_network_problem,
				hvild_hv_ild_upsell.district,
				hvild_hv_ild_upsell.hvild_town,
				hvild_hv_ild_upsell.hvild_landmark,
				hvild_hv_ild_upsell.call_center_sub_pain,
				hvild_hv_ild_upsell.hvild_district
			FROM
				hvild_hv_ild_upsell

			INNER JOIN users ON users.id = hvild_hv_ild_upsell.assigned_user_id
			WHERE
				hvild_hv_ild_upsell.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				hvild_hv_ild_upsell.hvild_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['hvild_status'] == '') $row['hvild_status'] = 'Not Called';
		
		if($row['hvild_status'] == 'Not Called'){
			$row[answer_group] = $row[hvild_status];
			$row[call_result] = $row[hvild_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[hvild_status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			++$summary['Call attempts By Segment By Answer State'][$rowdata[hvild_segment]." >> ".$rowdata[answer_group]];
			
			if($rowdata[hvild_status] != 'Not Called'){
				++$summary['Call attempts By Month'][date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Date'][substr($rowdata[date_modified],0,10)];
				
				if($rowdata[hvild_why_sub_doesnt_call] != ''){
					++$summary['Call attempts By Question By Answer']["Why customer doesn't make international calls? >> ".$rowdata[hvild_why_sub_doesnt_call]];
				}
				
				if($rowdata[hvild_about_ild_bundles] != ''){
					++$summary['Call attempts By Question By Answer']["Do you know about Airtel's discount international calll Bundles? >> ".$rowdata[hvild_about_ild_bundles]];
				}
				
				if($rowdata[hvild_areas_of_improvement] != ''){
					++$summary['Call attempts By Question By Answer']["Are there any areas you would like Airtel to improve? >> ".$rowdata[hvild_areas_of_improvement]];
				}
				
				if($rowdata[hvild_cc_medium] != ''){
					++$summary['Call attempts By Question By Answer']["Customer Care Medium >> ".$rowdata[hvild_cc_medium]];
				}
				
				if($rowdata[hvild_sub_pain] != ''){
					++$summary['Call attempts By Question By Answer']["Specific Customer Pain >> ".$rowdata[hvild_sub_pain]];
				}
				
				if($rowdata[hvild_product_name] != ''){
					++$summary['Call attempts By Question By Answer']["Product Name >> ".$rowdata[hvild_product_name]];
				}
				
				if($rowdata[hvild_network_problem] != ''){
					++$summary['Call attempts By Question By Answer']["Specific Network Problem >> ".$rowdata[hvild_network_problem]];
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
	return display_hvild_hv_ild_upsell_feedback_report($report);
}


function display_hvild_hv_ild_upsell_feedback_report($report){
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
					<th>Segment</th>
					<th>Customer Name</th>
					<th>Email</th>
					<th>Why customer doesnt make international calls?</th>
					<th>Do you know about Airtels discount international calll Bundles?</th>
					<th>Are there any areas you would like Airtel to improve?</th>
					<th>Customer Care Medium</th>
					<th>Specific Customer Pain</th>
					<th>Product Name</th>
					<th>Specific Network Problem</th>
					<th>District</th>
					<th>Town</th>
					<th>Landmark</th>
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
					<td class="text_values">'.$row[hvild_segment].'</td>
					<td class="text_values">'.$row[hvild_customer_name].'</td>
					<td class="text_values">'.$row[hvild_email].'</td>
					<td class="text_values">'.$row[hvild_why_sub_doesnt_call].'</td>
					<td class="text_values">'.$row[hvild_about_ild_bundles].'</td>
					<td class="text_values">'.$row[hvild_areas_of_improvement].'</td>
					<td class="text_values">'.$row[hvild_cc_medium].'</td>
					<td class="text_values">'.$row[hvild_sub_pain].'</td>
					<td class="text_values">'.$row[hvild_product_name].'</td>
					<td class="text_values">'.$row[hvild_network_problem].'</td>
					<td class="text_values">'.$row[hvild_district].'</td>
					<td class="text_values">'.$row[hvild_town].'</td>
					<td class="text_values">'.$row[hvild_landmark].'</td>
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