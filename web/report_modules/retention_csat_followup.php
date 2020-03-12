<?php


function generate_csat_followup($from,$to,$reporttype){
//echo $reporttype;

	function summarise($rows){
		foreach($rows as $row){
			
			if($row[why_no] == '' and $row[call_status] == 'Answered'){
				$row[call_status] = 'Not Called';
			}
			
			++$summary['Number of calls by Follow up date'][substr($row[date_modified],0,10)];
			++$summary['Number of calls by Evaluation date'][substr($row[date_entered],0,10)];
			
			if($row[call_status] == ''){ $row[call_status] = 'Not Called'; }
			++$summary['Number of calls by Call status'][$row[call_status]];
			
			if($row[call_status] == 'Answered'){
				++$summary['Number of calls by Negative Evaluation reason'][$row[why_no]];
				++$summary['Number of calls by Recording Evaluation activity status'][$row[recording_evaluation]];
			}
			
			++$summary['Number of calls by Caller'][ucwords(strtolower($row[assigned_to]))];
			
			//++$summary['Number of calls by Evaluated agent'][$row[evaluated_agent]];
			
			//++$summary['Number of calls by Wrap up category'][$row[category]];
			//++$summary['Number of calls by Wrap up subject'][str_replace("~"," >> ",$row[subject])];
			
			//++$summary['Number of calls by Student by Call status'][ucwords(strtolower($row[student_name]))][$row[call_status]];
			//++$summary['Number of calls by Follow up date by Call status'][substr($row[date_modified],0,10)][$row[call_status]];
			
			++$summary['Number of calls by Student by Call status'][ucwords(strtolower($row[assigned_to]))." ".$row[call_status]];
			
			//++$summary['Number of calls by Follow up date by Call status'][substr($row[date_modified],0,10)." ".$row[call_status]];
			
			if($row[recording_evaluation] == 'done'){
				++$summary['Number of Calls Recording Evaluation Reason'][$row[recording_evaluation_comment]];
				++$summary['Number of calls by Negative Evaluation reason'][$row[why_no]." => ".$row[recording_evaluation_comment]];
			}
		}
		
		foreach($summary as $table_title => $table_date){
			arsort($summary[$table_title]);
		}
		
		return $summary;
	}
 
	$myquery = new custom_query();

	if($from){
		$from = $from." 00:00:00";
	}else{
		$from = date('Y-m-d')." 00:00:00";
	}
	
	$_POST[from] =  substr($from,0,10);
	if($to){
		$to = $to." 23:59:59";
	}else{
		$to = date('Y-m-d')." 23:59:59";
	}
	
	$_POST[to] = substr($to,0,10);
	
	$query = "
		SELECT
			csat_csat_evaluation_follow_up.no_of_evaluations,
			csat_csat_evaluation_follow_up.call_status,
			csat_csat_evaluation_follow_up.why_no,
			csat_csat_evaluation_follow_up.date_called,
			date_add(csat_csat_evaluation_follow_up.date_entered, interval 3 hour) as date_entered,
			date_add(csat_csat_evaluation_follow_up.date_modified, interval 3 hour) as date_modified,
			csat_csat_evaluation_follow_up.category,
			csat_csat_evaluation_follow_up.subject,
			csat_csat_evaluation_follow_up.name as MSISDN,
			CONCAT(modified_users.first_name,' ',modified_users.last_name) AS modified_by,
			CONCAT(assigned_users.first_name,' ',assigned_users.last_name) AS assigned_to,
			csat_csat_evaluation_follow_up.customer_wrap_up_category,
			csat_csat_evaluation_follow_up.customer_wrap_up_subject,
			csat_csat_evaluation_follow_up.call_wrap_up_category,
			csat_csat_evaluation_follow_up.call_wrap_up_subject,
			csat_csat_evaluation_follow_up.evaluated_agent,
			csat_csat_evaluation_follow_up.recording_evaluation,
			csat_csat_evaluation_follow_up.recording_evaluation_comment
		FROM
			csat_csat_evaluation_follow_up
			LEFT OUTER JOIN users assigned_users ON assigned_users.id = csat_csat_evaluation_follow_up.assigned_user_id
			LEFT OUTER JOIN users modified_users ON modified_users.id = csat_csat_evaluation_follow_up.modified_user_id
		WHERE
			csat_csat_evaluation_follow_up.deleted = 0 AND
			csat_csat_evaluation_follow_up.date_modified BETWEEN date_sub('".$from."', interval 3 hour) AND date_sub('".$to."', interval 3 hour) 
	";
	
			/*
			LEFT OUTER JOIN users ON users.id = csat_csat_evaluation_follow_up.modified_user_id
			(
			 	csat_csat_evaluation_follow_up.date_modified BETWEEN date_sub('".$from."', interval 3 hour) AND date_sub('".$to."', interval 3 hour) 
				OR
				csat_csat_evaluation_follow_up.date_modified IS NULL
			)*/
		
	//echo $query;
	custom_query::select_db('survey');
	$entries = $myquery->multiple($query);
	
	iF(count($entries) == 0){ $report[NO_DATA] = "No data matches the input filters ..."; return display_csat_followup($report); }
	
	switch($reporttype){
		case 'both':
			$report[rows] = $entries;
			$report[summary] = summarise($entries);
			//print_r($report[leads]);
			break;
		case 'detail':
			$report[rows] = $entries;
			//print_r($report[leads]);
			break;
		case 'summary':
		default:
			$_POST[reportype] = 'summary';
			$report[summary] = summarise($entries);
			break;
	}

	return display_csat_followup($report);
}
 
 
function display_csat_followup($report){
	 
	if($report[NO_DATA] != '') {
		return $report[NO_DATA];
	}
	
	function decide_user($assigned,$modified){
		if($assigned == ''){
			return $modified;
		}else{
			return $assigned;
		}
	}
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
	';
	
	if($report[summary]){
		$html .= '
			<tr>
				<td>
					<table border="0" cellpadding="2" cellspacing="0">
		';
		
		foreach($report[summary] as $summary_title=>$summary_data){
			
			$html .= '
						<tr>
							<th>'.$summary_title.'</th>
						</tr>
						<tr>
							<td>
								<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
									<tr>
										<th>Parameter</th>
										<th>Values</th>
									</tr>
			';
			
			foreach($summary_data as $parameter=>$value){
				$html .= '
									<tr>
										<td class="text_values">'.ucfirst($parameter).'</td>
										<td class="values">'.$value.'</td>
									</tr>
				';
			}
			
			$html .= '
									<tr id="totals">
										<td class="text_values">TOTALS</td>
										<td class="values">'.number_format(array_sum($summary_data),0).'</td>
									</tr>
								</table>
							</td>
						</tr>
			';
		}
		
		$html .= '
					</table>
				</td>
			</tr>
		';
	}
	
	if($report[summary] and $report[rows]){
		$html .= '
			<tr>
				<td height="20px"></td>
			</tr>
		';
	}
	
	if($report[rows]){
		$html .= '
			<tr>
				<td>
					<table border="0" cellpadding="2" cellspacing="0" class="sortable">
						<tr>
							<th>#</th>
							<th>CALL STATUS</th>
							<th>MSISDN</th>
							<th>FOLLOW UP DATE</th>
							<th>EVALUATION DATE</th>
							<th>NEGATIVE EVALUATION REASON</th>
							<th>WRAP UP DATE</th>
							<th>EVALUATED AGENT</th>
							<th>WRAP UP CATEGORY</th>
							<th>WRAP UP SUBJECT</th>
							<th>CUSTOMER WRAP UP CATEGORY</th>
							<th>CUSTOMER WRAP UP SUBJECT</th>
							<th>RECORDING WRAP UP CATEGORY</th>
							<th>RECORDING WRAP UP SUBJECT</th>
							<th>CALLED BY</th>
							<th>NO OF EVALUATIONS</th>
							<th>RECORDING EVALUATION</th>
							<th>RECORDING EVALUATION REASON</th>
						</tr>
		';
		
		$RR = 0;
		foreach($report[rows] as $row){
			
			$html .= '
						<tr>
							<td class="values">'.++$RR.'</td>
							<td class="text_values">'.$row[call_status].'</td>
							<td class="values">'.$row[MSISDN].'</td>
							<td class="values">'.substr($row[date_modified],0,10).'</td>
							<td class="values">'.substr($row[date_entered],0,10).'</td>
							<td class="text_values">'.$row[why_no].'</td>
							<td class="values">'.$row[date_called].'</td>
							<td class="text_values">'.$row[evaluated_agent].'</td>
							<td class="text_values">'.$row[category].'</td>
							<td class="text_values">'.$row[subject].'</td>
							<td class="text_values">'.$row[customer_wrap_up_category].'</td>
							<td class="text_values">'.$row[customer_wrap_up_subject].'</td>
							<td class="text_values">'.$row[call_wrap_up_category].'</td>
							<td class="text_values">'.$row[call_wrap_up_subject].'</td>
							<td class="text_values">'.decide_user($row[assigned_to],$row[modified_by]).'</td>
							<td class="values">'.$row[no_of_evaluations].'</td>
							<td class="text_values">'.$row[recording_evaluation].'</td>
							<td class="text_values">'.$row[recording_evaluation_comment].'</td>
						</tr>
			';
		}
		
		$html .= '
					</table>
				</td>
			</tr>
		';
	}
	
	
	$html .= '
	</table>
	';
	
	return $html;
}
		


?>