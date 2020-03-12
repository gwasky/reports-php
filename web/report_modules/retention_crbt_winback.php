<?php

function generate_crbt_winback($from,$to,$reporttype){
//echo $reporttype;
	
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	
 	function summarise($entries){
		
		foreach($entries as $row){
			
			++$summary['Numbers by Call status'][$row[call_status]];
			
			if($row[date_called] != ''){
				++$summary['Numbers by Date Called'][substr($row[date_called],0,10)];
				++$summary['Numbers Called by Student'][$row[student_assigned]];
				++$summary['Numbers Called by Rejection reason'][$row[crbt_rejection_reason]];

			}else{
				++$summary['Un called Numbers by Date Entered'][$row[date_entered]];
			}
		}
		
		return $summary;
	}
 
	$myquery = new custom_query();
	$query = "
		SELECT
			sv_crbt_winback.name as msisdn,
			if(sv_crbt_winback.call_status != 'Not Called', date_add(sv_crbt_winback.date_modified, interval 3 hour), '') AS date_called,
			concat(assigned_users.first_name,' ',assigned_users.last_name) as student_assigned,
			sv_crbt_winback.crbt_rejection_reason,
			sv_crbt_winback.crbt_tone_id,
			left(sv_crbt_winback.date_entered,10) as date_entered,
			sv_crbt_winback.call_status,
			sv_crbt_winback.crbt_activation_date,
			sv_crbt_winback.customer_name,
			sv_crbt_winback.description
		FROM
			sv_crbt_winback
			Inner Join users assigned_users ON sv_crbt_winback.assigned_user_id = assigned_users.id
		WHERE
			sv_crbt_winback.deleted = 0 AND
			assigned_users.status = 'active' AND 
			sv_crbt_winback.assigned_user_id != '' AND 
			sv_crbt_winback.assigned_user_id != '1' AND
	";
	
	if(!$from){
		$_POST[from] = date('Y-m-d',strtotime("-1 days"));
		$from = $_POST[from];
	}
	if(!$to){
		$_POST[to] = date('Y-m-d',strtotime("-1 days"));
		$from = $_POST[to];
	}
	$query .= " sv_crbt_winback.date_modified between date_sub('".$from." 00:00:00', interval 3 hour) and date_sub('".$to." 23:59:59', interval 3 hour) ";
	
	if($reporttype == 'detail'){
		$query .= " AND sv_crbt_winback.call_status != 'Not Called' ";
	}
	
	$query .= "
		ORDER BY 
			-- sv_crbt_winback.date_modified
			if(sv_crbt_winback.call_status != 'Not Called', date_add(sv_crbt_winback.date_modified, interval 3 hour), '')
			ASC
	";
	
	//echo nl2br($query);
	
	$entries = $myquery->multiple($query,'ccba02.survey');
	
	if(count($entries) == 0){ return display_crbt_winback("NO DATA"); }

	switch($reporttype){
		case 'both':
			$report[rows] = $entries;
			$report[summary] = summarise($entries);
			//echo "<hr> running $reporttype";
			break;
		case 'detail':
			$report[rows] = $entries;
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($entries);
			break;
	}
	
	$report[stop] = strtotime(date('Y-m-d H:i:s'));

	return display_crbt_winback($report);
}
 
 
 function display_crbt_winback($report){
	
	if($report == 'NO DATA') { return "Selected filters return no results."; };
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="80%">
			<tr>
				<td>Report took ['.($report[stop] - $report[start]).'] seconds to run</td>
			</tr>
			<!--class="sortable"-->
	';
	
	if($report[summary] != ''){
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
			';
			
			//Titles
			$columns = explode(" by ",$summary_heading);
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
	
	if(($report[rows] != '') and ($report[summary] != '')){
		$html = '
		<tr>
			<th style="height:30px;"></th>
		</tr>
		';
	}
	
	if(count($report[rows]) > 0){
		$html = '
		<tr>
			<th style="height:30px;">DETAILS</th>
		</tr>
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th width="5px"></th>
				<th width="20px">Call Status</th>
				<th width="20px">Date Called</th>
				<!--<th>Time</th>-->
				<th width="20px">MSISDN</th>
				<th width="30px">Customer Name</th>
				<th width="30px">Student Assigned</th>
				<th width="20px">CRBT Tone ID</th>
				<th width="20px">CRBT Tone Activation Date</th>
				<th width="60px">CRBT Rejection Reason</th>
				<th width="40%">General Description</th>
			</tr>
		';
		
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$row[call_status].'</td>
					<td class="values">'.substr($row[date_called],0,10).'</td>
					<!--<td class="values">'.substr($row[date_called],-8).'</td>-->
					<td class="values">'.$row[msisdn].'</td>
					<td class="text_values">'.ucfirst($row[customer_name]).'</td>
					<td class="text_values">'.ucfirst($row[student_assigned]).'</td>
					<td class="values">'.$row[crbt_tone_id].'</td>
					<td class="values">'.$row[crbt_activation_date].'</td>
					<td class="text_values">'.$row[crbt_rejection_reason].'</td>
					<td class="wrap_text">'.$row[description].'</td>
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