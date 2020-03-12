<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function warid_and_airtel_sim_holder_feedback_report($from, $to, $report_type, $wash_segment){
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	if(!$to){
		$to = date('Y-m-d');
	}
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	//echo $_POST[wash_segment];
	if($wash_segment != ''){ $conditions = " AND wash_segment = '".$wash_segment."'"; }
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.survey');
	$query = "
			SELECT
				concat(users.last_name,' ',users.first_name) AS Agent,
				wash_warid_and_airtel_sim_holder.id,
				wash_warid_and_airtel_sim_holder.`name`,
				wash_warid_and_airtel_sim_holder.date_entered,
				wash_warid_and_airtel_sim_holder.date_modified,
				wash_warid_and_airtel_sim_holder.modified_user_id,
				wash_warid_and_airtel_sim_holder.created_by,
				wash_warid_and_airtel_sim_holder.description,
				wash_warid_and_airtel_sim_holder.deleted,
				wash_warid_and_airtel_sim_holder.assigned_user_id,
				wash_warid_and_airtel_sim_holder.wash_call_status,
				wash_warid_and_airtel_sim_holder.wash_merger_awareness,
				wash_warid_and_airtel_sim_holder.wash_have_airtel_and_warid_sim,
				wash_warid_and_airtel_sim_holder.wash_sim_usage,
				wash_warid_and_airtel_sim_holder.wash_prefered_network,
				wash_warid_and_airtel_sim_holder.wash_prefrence_reason,
				wash_warid_and_airtel_sim_holder.wash_one_network_sim,
				wash_warid_and_airtel_sim_holder.wash_post_merger_sim_reason,
				wash_warid_and_airtel_sim_holder.wash_what_happens_to_other_sim,
				wash_warid_and_airtel_sim_holder.wash_occupation,
				wash_warid_and_airtel_sim_holder.wash_comment,
				wash_warid_and_airtel_sim_holder.wash_segment
				FROM
				wash_warid_and_airtel_sim_holder
			INNER JOIN users ON users.id = wash_warid_and_airtel_sim_holder.assigned_user_id
			WHERE
				wash_warid_and_airtel_sim_holder.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
				".$conditions."
			ORDER BY
				wash_warid_and_airtel_sim_holder.wash_call_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'survey');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['wash_call_status'] == '') $row['wash_call_status'] = 'Not Called';
		
		if($row['wash_call_status'] == 'Not Called'){
			$row[answer_group] = $row[wash_call_status];
			$row[call_result] = $row[wash_call_status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[wash_call_status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			++$summary['Call attempts By Segment By Answer State'][$rowdata[wash_segment]." >> ".$rowdata[answer_group]];
			
			if($rowdata[whvc_call_status] != 'Not Called'){
				++$summary['Call attempts By Segment By Month'][$rowdata[wash_segment]." >> ".date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Segment By Date'][$rowdata[wash_segment]." >> ".substr($rowdata[date_modified],0,10)];
				if($rowdata[wash_merger_awareness] != ''){
					++$summary['Call attempts By Question By Answer']['Are you aware of Warid and Airtel Merger?'." >> ".$rowdata[wash_merger_awareness]];
				}
				
				if($rowdata[wash_have_airtel_and_warid_sim] != ''){
					++$summary['Call attempts By Segment By Question By Answer'][$rowdata[wash_segment]." >> Do you have both an Airtel & Warid Sim Card? >> ".$rowdata[wash_have_airtel_and_warid_sim]];
				}
				
				if($rowdata[wash_sim_usage] != ''){
					++$summary['Call attempts By Segment By Question By Answer'][$rowdata[wash_segment]." >> Do you use both Warid and Airtel Sims? >> ".$rowdata[wash_sim_usage]];
				}
				
				if($rowdata[wash_prefered_network] != ''){
					++$summary['Call attempts By Segment By Question By Answer'][$rowdata[wash_segment]." >> Which one is your preferred network? >> ".$rowdata[wash_prefered_network]];
				}
				
				if($rowdata[wash_one_network_sim] != ''){
					++$summary['Call attempts By Segment By Question By Answer'][$rowdata[wash_segment]." >> After the merger which SIM card will you continue to use? >> ".$rowdata[wash_one_network_sim]];
				}
				++$summary['Call attempts By Agent'][$rowdata[Agent]];
			}
			
			++$summary['Call attempts By Segment By Call Result'][$rowdata[wash_segment]." >> ".$rowdata['call_result']];
			++$summary['Call attempts By Segment By Month By Answer Group By Call Result'][$rowdata[wash_segment]." >> ".date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
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
	return display_warid_and_airtel_sim_holder_feedback_report($report);
}


function display_warid_and_airtel_sim_holder_feedback_report($report){
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
					<th>Segmentation</th>
					<th>Agent</th>
					<th>MSISDN</th>
					<th>Date Modified</th>
					<th>Grouping</th>
					<th>Call Result</th>
					<th>Are you aware of Warid and Airtel Merger?</th>
					<th>Do you have both an Airtel & Warid Sim Card?</th>
					<th>Do you use both Warid and Airtel Sims?</th>
					<th>Which one is your preferred network?</th>
					<th>Why?</th>
					<th>After the merger which SIM card will you continue to use?</th>
					<th>Why?</th>
					<th>What will you do with the other SIM?</th>
					<th>Occupation</th>
					<th>Comment</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$row_count.'</td>
					<td class="text_values">'.$row[wash_segment].'</td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[date_modified].'</td>
					<td class="text_values">'.$row[answer_group].'</td>	
					<td class="text_values">'.$row[call_result].'</td>
					<td class="text_values">'.$row[wash_merger_awareness].'</td>
					<td class="text_values">'.$row[wash_have_airtel_and_warid_sim].'</td>
					<td class="text_values">'.$row[wash_sim_usage].'</td>
					<td class="text_values">'.$row[wash_prefered_network].'</td>
					<td class="text_values">'.$row[wash_prefrence_reason].'</td>
					<td class="text_values">'.$row[wash_one_network_sim].'</td>
					<td class="text_values">'.$row[wash_post_merger_sim_reason].'</td>
					<td class="text_values">'.$row[wash_what_happens_to_other_sim].'</td>
					<td class="text_values">'.$row[wash_occupation].'</td>
					<td class="text_values">'.$row[wash_comment].'</td>
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