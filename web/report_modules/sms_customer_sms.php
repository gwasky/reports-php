<?
ini_set('memory_limit','1024M');

function generate_sms_feedback_report($from,$to,$msisdn,$status,$last_modified_by,$report_type){
	
	custom_query::select_db('smsfeedback');
	$myquery = new custom_query();
	
	function summarise($details,$agent_list){
		
		foreach($details as $row){
			++$summary['Numbers Created by Date'][substr($row[created_date],0,10)];
			++$summary['Numbers Created by Month'][substr($row[created_date],0,7)];
			++$summary['Numbers Entered or Worked on by Status'][$row[the_state]];
			
			++$summary['Numbers Entered or Worked on by Users by Status'][$agent_list[$row[last_modified_by_key]]."~".$row[the_state]];
			
			if($row[modified_on] != ''){
				++$summary['Numbers Worked on by Month'][substr($row[modified_on],0,7)];
				++$summary['Numbers Worked on by Month by Status'][substr($row[modified_on],0,7)."~".$row[the_state]];
				++$summary['Numbers by User'][$agent_list[$row[last_modified_by_key]]];
				++$summary['Numbers Worked on by Month by User'][substr($row[modified_on],0,7)."~".$agent_list[$row[last_modified_by_key]]];
				++$summary['Numbers Worked on by Date'][substr($row[modified_on],0,10)];
				++$summary['Numbers Worked on by Date by User'][substr($row[modified_on],0,10)."~".$agent_list[$row[last_modified_by_key]]];
			}
			
			/*
			++$summary['Numbers by Date by Status'][$row[the_date]."~".$row[status]];
			++$summary['Numbers by Date by User'][$row[the_date]."~".$agent_list[$row[last_modified_by_key]]];
			*/
		}
		
		return $summary;
	}
	
	function hourlize($details,$agent_list){
		
		foreach($details as $row){
			++$hourly_summary['Numbers by Hour'][substr($row[the_date],0,13)];
		}
		
		return $hourly_summary;
	}
	
	if($from == '') { $from = date('Y-m-d'); $_POST[from] = $from;}
	if($to == '') { $to = date('Y-m-d'); $_POST[to] = $to; }
	
	if(count($last_modified_by) > 0 and is_array($last_modified_by)){
		//$modified_query = " AND feedback.last_modified_by IN (";
		$modified_query = " AND feedback_cstm.last_modified_by IN (";
		foreach($last_modified_by as $user){
			++$II;
			$modified_query .= "'".$user."'";
			if($II < count($last_modified_by)) { $modified_query .= ","; }
		}
	  	$modified_query .= ") ";
	}
	
	if($status != ''){
		/*$status_query = "
			AND feedback.status = '".$status."'
		";*/
		if($status_query != 'Unread'){
			$status_query = "
				AND feedback_cstm.`status` = '".$status."'
			";
		}else{
			$status_query = "
				AND (feedback_cstm.`status` = '".$status."' OR feedback_cstm.`status` IS NULL)
			";
		}
	}
	
	if($msisdn != ''){
		$msisdn_query = "
			AND feedback.sender = '".$msisdn."'
		";
	}
	
	$query = "
		SELECT
			feedback.datesent as created_date,
			feedback.sender AS msisdn,
			feedback.message,
			feedback_cstm.modified_on,
			IF(feedback_cstm.`status` IS NULL,feedback.`status`,feedback_cstm.`status`) AS the_state,
			IF(feedback_cstm.last_modified_by IS NOT NULL,feedback.last_modified_by,'None') as last_modified_by,
			IF(activities.activity_name IS NULL, 'None', activities.activity_name) as the_activity
		FROM
			feedback
			LEFT OUTER JOIN feedback_cstm ON feedback.id = feedback_cstm.id_c
			LEFT OUTER JOIN activities ON activities.id = feedback_cstm.activity_id
		WHERE
			( feedback.datesent BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59' OR feedback_cstm.modified_on BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59' )"
			.$modified_query
			.$status_query
			.$msisdn_query
	;
	
	//echo "<pre>".$query."</pre>";
	
	$entries = $myquery->multiple($query); if(count($entries) == 0) { return display_sms_feedback_report("NO DATA"); }
	
	foreach($entries as &$entry){
		if(str_replace(array(" "),"",$entry[last_modified_by]) == '') { $entry[last_modified_by] = 'None'; }
		$entry[last_modified_by_key] = str_replace(array(" "),"",strtolower($entry[last_modified_by]));
		
		if($report[agent_list][$entry[last_modified_by_key]] == ''){
			$report[agent_list][$entry[last_modified_by_key]] = $entry[last_modified_by];
		}
	}
	
	switch($report_type){
		case 'detail':
			$report[detail] = $entries;
			break;
		case 'hourly_summary':
			$report[summary_title] = 'SMS Entered or Worked on in the selected period';
			$report[summary] = hourlize($entries,$report[agent_list]);
			break;
		case 'both':
			$report[detail] = $entries;
			$report[summary_title] = 'SMS Entered or Worked on in the selected period';
			$report[summary] = summarise($entries,$report[agent_list]);
			break;
		case 'summary':
		default:
		$report[summary_title] = 'SMS Entered or Worked on in the selected period';
			$report[summary] = summarise($entries,$report[agent_list]);
			break;
	}
	
	return display_sms_feedback_report($report);
}

function display_sms_feedback_report($report){
	
	if($report == "NO DATA"){ return "No data"; }
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	
	if($report[summary]){
		$html .= '
			<tr>
				<td width="100%" style="font-size:16px; font-weight:bold;">'.$report[summary_title].'</td>
			</tr>
			<tr>
				<th width="100%" style="font-size:16px; font-weight:bold;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $title=>$title_data){
			$headings = explode(' by ',$title);
			//Remove the first element which is 'Numbers'. It actually goes to the end of the row
			unset($headings[0]);
			$html .= '
			<tr><td>'.$title.'</td></tr>
			<tr><td>
				<table border="0" cellpadding="1" cellspacing="0" width="100%"  class="sortable">
					<tr>
			';
				foreach($headings as $heading){
					$html .= '
						<th>'.strtoupper($heading).'</th>
					';
				}
			$html .= '
						<th width="20%">NUMBER</th>
					</tr>
			';
			
			unset($number_sum);
			
			foreach($title_data as $parameter_string=>$number){
				$parameter_columns = explode('~',$parameter_string);
				$html .= '
					<tr>
				';
				
				foreach($parameter_columns as $column){
					$html .= '
						<td class="text_values">'.$column.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($number,0).'</td>
					</tr>
				';
				
				$number_sum += $number;
			}
			
			$html .= '
					<tr id="totals">
						<td class="text_values" colspan="'.count($parameter_columns).'">TOTAL</td>
						<td class="values">'.number_format($number_sum,0).'</td>
					</tr>
				</table>
			</td></tr>
			';
		}
	}
	
	if(($report[summary]) and ($report[detail])){
		$html .= '
			<tr><td style="height: 20px;"></td></tr>
		';
	}
	
	if($report[detail]){
		$html .= '
			<tr><th width="100%" style="font-size:16px; font-weight:bold;">DETAILS</th></tr>
			<tr><td>
				<table border="0" cellpadding="1" cellspacing="0" class="sortable">
					<tr>
						<th width="5">#</th>
						<th>DATE ENTERED</th>
						<th>MSISDN</th>
						<th>STATUS</th>
						<th>DATE MODIFIED</th>
						<th>MODIFIED BY</th>
						<th width="70%">SMS TEXT</th>
					</tr>
		';
		
		foreach($report[detail] as $row){
			$html .= '
					<tr>
						<td class="values">'.++$ii.'</td>
						<td class="text_values">'.substr($row[created_date],0,10).'</td>
						<td class="text_values">'.$row[msisdn].'</td>
						<td class="text_values">'.$row[status].'</td>
						<td class="text_values">'.substr($row[modified_on],0,10).'</td>
						<td class="text_values">'.$report[agent_list][$row[last_modified_by_key]].'</td>
						<td class="wrap_text">'.nl2br($row[message]).'</td>
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

function display_sms_feedback_report_type_dropdown($selected){
	
	$types = array(
		'summary'=>'summary',
		'detail'=>'detail',
		'both'=>'both',
		'hourly_summary'=>'Hourly'
	);
	
	return dropdown($label='Select Report Type', $name='reporttype', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

?>