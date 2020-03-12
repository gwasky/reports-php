<?php
function generate_sms_manager_report_update($date = '',$period='month'){
	
	if($date == ''){$date = date('Y-m-d',strtotime("-1 days"));}
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			LEFT(feedback_cstm.modified_on, 10) AS `modified_date`,
			LEFT(feedback.datesent, 10) AS `the_date`,
			IF(feedback_cstm.`status` IS NULL,feedback.`status`,feedback_cstm.`status`) AS `the_status`,
			IF(feedback_cstm.last_modified_by IS NOT NULL,feedback.last_modified_by,'None') AS `modified_by`,
			IF(activities.activity_name IS NULL, 'None', activities.activity_name) as `activity`,
			count(feedback.id) as number
		FROM 
			feedback
			LEFT OUTER JOIN feedback_cstm ON feedback.id = feedback_cstm.id_c
			LEFT OUTER JOIN activities ON activities.id = feedback_cstm.activity_id
		WHERE
			(
			 	feedback.datesent BETWEEN date_format('".$date."','%Y-%m-01 00:00:00')	AND '".$date." 23:59:59' OR
				feedback_cstm.modified_on BETWEEN date_format('".$date."','%Y-%m-01 00:00:00')	AND '".$date." 23:59:59'
			)
		GROUP BY 
			`the_date`,
			`modified_date`,
			modified_by,
			`the_status`,
			activity
		ORDER BY
			`modified_date` DESC
	";
	
	//echo $query."\n";
	
	custom_query::select_db('ccba01.smsfeedback');
	$result = $myquerys->no_row("delete from feedback where sender = ''");
	$sms_data = $myquerys->multiple($query);
	
	if(count($sms_data) == 0){ exit("Exiting due to zero sized query ... \n"); }
	
	$report[run_date] = $date;
	$report[run_month] = substr($date,0,7);
	foreach($sms_data as $row){
		$report[statuses][$row[the_status]] = $row[the_status];
		
		//STREAMLINE THE USERNAMES
		$row[last_modified_by_key] = str_replace(array(" "),"",strtolower($row[modified_by]));
		if($agent_list[$row[last_modified_by_key]] == '' and $row[modified_by] != ''){
			$agent_list[$row[last_modified_by_key]] = $row[modified_by];
		}
		
		if($row[the_date] == $report[run_date] or $row[modified_date] == $report[run_date]){
			//RUN FOR FILLED IN DATE
			if($row[the_date] == $report[run_date]){
				$report[totals][day]['No of SMS Entered'] += $row[number];
			}
			
			$report[data][day]['No of SMS entered or Worked on by Status'][$row[the_status]] += $row[number];
			$report[data][day]['No of SMS entered or Worked on by User by Status'][$agent_list[$row[last_modified_by_key]]][$row[the_status]] += $row[number];
			$report[data][day]['No of SMS entered or Worked on by User'][$agent_list[$row[last_modified_by_key]]] += $row[number];
			
			if($row[modified_date] == $report[run_date]){
				$report[totals][day]['No of SMS Worked on'] += $row[number];
			}
		}
		
		if($period == 'month' and (substr($row[the_date],0,7) == $report[run_month] or substr($row[modified_date],0,7) == $report[run_month])){
			//RUN FOR MONTH OF FILLED IN DATE
			if(substr($row[the_date],0,7) == $report[run_month]){
				$report[dates][str_replace('-','',$row[the_date])] = $row[the_date];
				
				$report[totals][month]['No of SMS Entered'] += $row[number];
				//POPULATING GRAPH DATA
				$graph_data[resolution]['SMS Entered'][$row[the_date]] += $row[number];
			}
			
			$report[data][month]['No of SMS entered or Worked on by Status'][$row[the_status]] += $row[number];
			$report[data][month]['No of SMS entered or Worked on by User by Status'][$agent_list[$row[last_modified_by_key]]][$row[the_status]] += $row[number];
			$report[data][month]['No of SMS entered or Worked on by User'][$agent_list[$row[last_modified_by_key]]] += $row[number];
			
			if(substr($row[modified_date],0,7) == $report[run_month]){
				$report[dates][str_replace('-','',$row[modified_date])] = $row[modified_date];
				
				$report[totals][month]['No of SMS Worked on'] += $row[number];
				
				//POPULATING GRAPH DATA
				$graph_data[resolution]['SMS Worked on'][$row[modified_date]] += $row[number];
			}
		}
	}
	
	//SORT DATES IN ASCENDING ORDER
	ksort($report[dates]);
	
	//SORT 2 DIMENTIONAL TABULA IN DESCENDING ORDER 
	arsort($report[data][day]['No of SMS entered or Worked on by Status']);
	arsort($report[data][month]['No of SMS entered or Worked on by Status']);
	arsort($report[data][day]['No of SMS entered or Worked on by User']);
	arsort($report[data][month]['No of SMS entered or Worked on by User']);
	
	foreach($report[dates] as $this_date){
		foreach($graph_data as $graph_kind=>$graph_kind_data){
			foreach($graph_kind_data as $legend=>$legend_data){
				$report[graph_data][$graph_kind][$legend][$this_date] = intval($legend_data[$this_date]);
			}
		}
	}
	
	$graph_detail[data]=$report[graph_data][resolution];
	unset($report[graph_data]);
	$graph_detail[title]='SMS Feedback Entered Vs Worked : '.date_format(date_create($date),'l jS F Y');
	$graph_detail[display_title]=true;
	$graph_detail[legend]=true;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=false;
	$graph_detail[width]=850;
	$graph_detail[height]=600;
	
	$my_graph->graph($graph_detail[title],substr($date,0,7)."-01 - ".$date, $graph_detail);
	custom_query::select_db('graphing');
	$report[graph_id] = $my_graph->Save();
		
	//exit("Count Day is [".count($report[totals][day][status])."] and count Month is [".count($report[totals][month][status])."]\n");
	
	return display_sms_manager_report_update($report);
}

function display_sms_manager_report_update($report){
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0">
	';
	
	if(count($report[totals][day]) != 0){
		$html .= '
			<tr>
				<th style="min-width:500px; font-size:16px;">PERIOD : '.date_format(date_create($report[run_date]),'l jS F Y').'</th>
			</tr>
			<tr>
				<td class="text_values">SMS Entered : '.number_format($report[totals][day]['No of SMS Entered']).'</td>
			</tr>
			<tr>
				<td class="text_values">SMS Worked on : '.number_format($report[totals][day]['No of SMS Worked on']).'</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>No of SMS Worked on by Status</th>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th width="10">#</th>
					<th>Status</th>
					<th>Number</th>
				</tr>
		';
		
		foreach($report[data][day]['No of SMS entered or Worked on by Status'] as $status=>$status_number){
			$html .= '
				<tr class="'.row_style(++$i).'">
					<td class="values">'.$i.'</td>
					<td class="text_values">'.$status.'</td>
					<td class="values">'.number_format($status_number,0).'</td>
				</tr>
			';
		}
		unset($status,$i);
		
		$html .= '
				</table>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>No of SMS Worked on by Resolution</th>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="10">#</th>
						<th>User</th>
		';
		
		foreach($report[statuses] as $status){
			$html .= '
						<th width="90">'.$status.'</th>
			';
		}
		unset($status);
			
		$html .= '
						<th width="90">Total</th>
					</tr>
				
		';
		
		foreach($report[data][day]['No of SMS entered or Worked on by User'] as $user=>$user_total){
			$html .= '
					<tr class="'.row_style(++$i).'">
						<td class="values">'.$i.'</td>
						<td class="text_values">'.$user.'</td>
			';
			foreach($report[statuses] as $status){
				$html .= '
						<td class="values">'.number_format($report[data][day]['No of SMS entered or Worked on by User by Status'][$user][$status],0).'</td>
				';
			}
			$html .= '
						<td class="values">'.number_format($user_total,0).'</td>
					</tr>
			';
		}
		unset($user,$i);
		
		$html .= '
				</table>
				</td>
			</tr>
			<tr>
				<td height="50"></td>
			</tr>
		';
	}else{
		$html .= '
			<tr>
				<th style="min-width:500px;">SMS by Status for '.date_format(date_create($report[run_date]),'l jS F Y').'</th>
			</tr>
			<tr>
				<td class="text_values"><strong style="font-size:15px; color:#FF0000;">There were No SMS Received</strong></td>
			</tr>
			<tr>
				<td height="30"></td>
			</tr>
		';
	}
	
	if(count($report[totals][month]) != 0){
		$html .= '
			<tr>
				<th style="min-width:500px; font-size:16px;">PERIOD : '.date_format(date_create($report[run_date]),'F Y').'</th>
			</tr>
			<tr>
				<td class="text_values">SMS Entered : '.number_format($report[totals][month]['No of SMS Entered']).'</td>
			</tr>
			<tr>
				<td class="text_values">SMS Worked on : '.number_format($report[totals][month]['No of SMS Worked on']).'</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>No of SMS Worked on by Status</th>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th width="10">#</th>
					<th>Status</th>
					<th>Number</th>
				</tr>
		';
		
		foreach($report[data][month]['No of SMS entered or Worked on by Status'] as $status=>$status_number){
			$html .= '
				<tr class="'.row_style(++$i).'">
					<td class="values">'.$i.'</td>
					<td class="text_values">'.$status.'</td>
					<td class="values">'.number_format($status_number).'</td>
				</tr>
			';
		}
		unset($status,$i);
		
		$html .= '
				</table>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>No of SMS Worked on by Resolution</th>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="10">#</th>
						<th>User</th>
		';
		
		foreach($report[statuses] as $status){
			$html .= '
						<th width="90">'.$status.'</th>
			';
		}
		unset($status);
			
		$html .= '
						<th width="90">Total</th>
					</tr>
		';
		
		foreach($report[data][month]['No of SMS entered or Worked on by User'] as $user=>$user_total){
			$html .= '
					<tr class="'.row_style(++$i).'">
						<td class="values">'.$i.'</td>
						<td class="text_values">'.$user.'</td>
			';
			foreach($report[statuses] as $status){
				$html .= '
					<td class="values">'.number_format($report[data][month]['No of SMS entered or Worked on by User by Status'][$user][$status],0).'</td>
				';
			}
			$html .= '
					<td class="values">'.number_format($user_total,0).'</td>
				</tr>
			';
		}
		
		$html .= '
				</table>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				'.display_generic_graph($report[graph_id],TRUE).'
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>