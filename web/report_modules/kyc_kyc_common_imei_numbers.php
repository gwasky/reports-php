<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function kyc_common_imei_numbers_feedback_report($from, $to, $report_type){
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	if(!$to){
		$to = date('Y-m-d');
	}
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.telesales');
	$query = "	
			SELECT
				concat(users.last_name,' ',users.first_name) AS Agent,
				cinr_kyc_common_imei_numbers.id,
				cinr_kyc_common_imei_numbers.`name`,
				cinr_kyc_common_imei_numbers.date_entered,
				cinr_kyc_common_imei_numbers.date_modified,
				cinr_kyc_common_imei_numbers.modified_user_id,
				cinr_kyc_common_imei_numbers.created_by,
				cinr_kyc_common_imei_numbers.description,
				cinr_kyc_common_imei_numbers.deleted,
				cinr_kyc_common_imei_numbers.assigned_user_id,
				cinr_kyc_common_imei_numbers.unregistered_msisdn,
				cinr_kyc_common_imei_numbers.additional_personal_line,
				cinr_kyc_common_imei_numbers.`status`
			FROM
				cinr_kyc_common_imei_numbers

			INNER JOIN users ON users.id = cinr_kyc_common_imei_numbers.assigned_user_id
			WHERE
				cinr_kyc_common_imei_numbers.date_modified BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				cinr_kyc_common_imei_numbers.`status`
			ASC
			";
	//print nl2br($query);
	//exit();
	
	$rows = $myquery->multiple($query,'telesales');
	
	//REMOVING A Not called rows
	foreach($rows as &$row){
		if($row['status'] == '') $row['status'] = 'Not Called';
		
		if($row['status'] == 'Not Called'){
			$row[answer_group] = $row[status];
			$row[call_result] = $row[status];
		} else { 
			list($row[answer_group],$row[call_result]) = explode(" :: ",trim($row[status]));
		}
	}
	
	unset($row);

	function summarise($rows){
		foreach($rows as $rowdata){
			
			if($rowdata[status] != 'Not Called'){
				++$summary['Call attempts By Month'][date('M-Y',strtotime($rowdata[date_modified]))];
				++$summary['Call attempts By Date'][substr($rowdata[date_modified],0,10)];
				
				if($rowdata[additional_personal_line] != ''){
					++$summary['Call attempts By Question By Answer']["Is this your additional personal line? >> ".$rowdata[additional_personal_line]];
				}
				
				++$summary['Call attempts By Agent'][$rowdata[Agent]];
			}
			
			++$summary['Call attempts By Call Result'][$rowdata['call_result']];
			++$summary['Call attempts By Month By Answer Group By Call Result'][date('M-Y',strtotime($rowdata['date_modified']))." >> ".$rowdata[answer_group]." >> ".$rowdata[call_result]];
			
			if($rowdata[Agent] == '' && $rowdata[status] != 'Not Called'){
				$myquery = new custom_query();
				custom_query::select_db('ccba02.telesales');
				$query = "	
						SELECT
							kyc_manual_recon.agent
						FROM
							kyc_manual_recon
						WHERE
							kyc_manual_recon.record_id = '".$rowdata[id]."'
						";
				$rowagent = $myquery->single($query,'telesales');
				$rowdata[Agent] = $rowagent['agent'];
			}
			
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
	return display_kyc_common_imei_numbers_feedback_report($report);
}


function display_kyc_common_imei_numbers_feedback_report($report){
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
					<th>Uregistered MSISDN</th>
					<th>Is this your additional personal line?</th>
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
					<td class="text_values">'.$row[unregistered_msisdn].'</td>
					<td class="text_values">'.$row[additional_personal_line].'</td>
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