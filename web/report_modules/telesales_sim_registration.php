<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function telesales_sim_registration_feedback_report($from, $to, $report_type, $segment){
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	if(!$to){
		$to = date('Y-m-d');
	}
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	//echo $_POST[segment];
	if($segment != ''){ $conditions = " AND segment = '".$segment."'"; }
	
	custom_query::select_db('telesales');
	$myquery = new custom_query();
	$query = "
			SELECT
				concat(users.last_name,' ',users.first_name) AS Agent,
				tsr_telesales_sim_registration.id,
				tsr_telesales_sim_registration.`name`,
				tsr_telesales_sim_registration.date_entered,
				tsr_telesales_sim_registration.date_modified,
				tsr_telesales_sim_registration.modified_user_id,
				tsr_telesales_sim_registration.created_by,
				tsr_telesales_sim_registration.description,
				tsr_telesales_sim_registration.deleted,
				tsr_telesales_sim_registration.assigned_user_id,
				tsr_telesales_sim_registration.alternate_number,
				tsr_telesales_sim_registration.first_name,
				tsr_telesales_sim_registration.last_name,
				tsr_telesales_sim_registration.date_of_birth,
				tsr_telesales_sim_registration.gender,
				tsr_telesales_sim_registration.district,
				tsr_telesales_sim_registration.village,
				tsr_telesales_sim_registration.zone,
				tsr_telesales_sim_registration.street_name,
				tsr_telesales_sim_registration.po_box,
				tsr_telesales_sim_registration.business_address,
				tsr_telesales_sim_registration.email_address,
				tsr_telesales_sim_registration.call_status,
				tsr_telesales_sim_registration.segment,
				tsr_telesales_sim_registration.landmark
				FROM
				tsr_telesales_sim_registration
			INNER JOIN users ON users.id = tsr_telesales_sim_registration.assigned_user_id
			WHERE
				tsr_telesales_sim_registration.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
				".$conditions." AND
				tsr_telesales_sim_registration.call_status <> 'Not Called'
			ORDER BY
				tsr_telesales_sim_registration.call_status
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'telesales');
	//var_dump($rows);
	//echo '<pre>'.print_r($rows,true).'</pre>';
	//exit();
	
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
			if(substr($rowdata[name],0,5) == '25670'){
				$rowdata[network] = 'Warid';
			}elseif(substr($rowdata[name],0,5) == '25675'){
				$rowdata[network] = 'Airtel';
			}else{
				$rowdata[network] = 'Unspecified';
			}
			
			if($rowdata[Agent] == ''){
				++$summary['Unassigned Numbers By Segment By Network'][$rowdata[segment]." >> ".$rowdata[network]];
			}else{
				++$summary['Call attempts By Segment By Network By Answer State'][$rowdata[segment]." >> ".$rowdata[network]." >> ".$rowdata[answer_group]];
				
				if($rowdata[call_status] != 'Not Called'){
					++$summary['Call attempts By Segment By Network By Month'][$rowdata[segment]." >> ".$rowdata[network]." >> ".date('M-Y',strtotime($rowdata[date_modified]))];
					++$summary['Call attempts By Segment By Network By Date'][$rowdata[segment]." >> ".$rowdata[network]." >> ".substr($rowdata[date_modified],0,10)];
					
					++$summary['Call attempts By Agent'][$rowdata[Agent]];
				}
				
				++$summary['Call attempts By Segment By Network By Call Result'][$rowdata[segment]." >> ".$rowdata[network]." >> ".$rowdata['call_result']];
				++$summary['Call attempts By Segment By Month By Answer Group By Call Result'][$rowdata[segment]." >> ".date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
				++$summary['Call attempts By Agent By Answer State'][$rowdata[Agent]." >> ".$rowdata[answer_group]];
				++$summary['Call attempts By Agent By Date By Answer State'][$rowdata[Agent]." >> ".substr($rowdata[date_modified],0,10)." >> ".$rowdata[answer_group]];
				++$summary['Call attempts By Month By Call Result'][date('M-Y',strtotime($rowdata[date_modified]))." >> ".$rowdata[call_result]];
				
			}
				
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
	
	return display_telesales_sim_registration_feedback_report($report);
}


function display_telesales_sim_registration_feedback_report($report){
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
					<th>Alt Number</th>
					<th>Customer Name</th>
					<th>Date Of Birth</th>
					<th>Gender</th>
					<th>District</th>
					<th>Village</th>
					<th>Zone</th>
					<th>Street_name</th>
					<th>P.O.Box</th>
					<th>Business Address</th>
					<th>Email Address</th>
					<th>Call Status</th>
					<th>Segment</th>
					<th>Land Mark</th>
					<th>Comment</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$row_count.'</td>
					<td class="text_values">'.$row[segment].'</td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[date_modified].'</td>
					<td class="text_values">'.$row[answer_group].'</td>	
					<td class="text_values">'.$row[call_result].'</td>
					<td class="text_values">'.$row[alternate_number].'</td>
					<td class="text_values">'.$row[first_name].' '.$row[last_name].'</td>
					<td class="text_values">'.$row[date_of_birth].'</td>
					<td class="text_values">'.$row[gender].'</td>
					<td class="text_values">'.$row[district].'</td>
					<td class="text_values">'.$row[village].'</td>
					<td class="text_values">'.$row[zone].'</td>
					<td class="text_values">'.$row[street_name].'</td>
					<td class="text_values">'.$row[po_box].'</td>
					<td class="text_values">'.$row[business_address].'</td>
					<td class="text_values">'.$row[email_address].'</td>
					<td class="text_values">'.$row[call_status].'</td>
					<td class="text_values">'.$row[segment].'</td>
					<td class="text_values">'.$row[landmark].'</td>
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