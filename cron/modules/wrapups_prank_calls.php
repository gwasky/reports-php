<?php
//TO BE RUN ON CCBA01
function generate_prank_call_wrapups($upto,$from=''){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	if($upto == '') {
		$upto = date('Y-m-d',strtotime("-1 days"));
	}
	
	if($from == ''){
		$from = substr($upto,0,8)."01";
	}
	
	$report[usedate] = $upto;
	
	$query = "
		SELECT
			DAY('".$upto."') AS expected_days,
			count(DISTINCT queue.entrydate) AS data_days
		FROM
			queue INNER JOIN calldetail on queue.id = calldetail.id_c
		WHERE
			queue.entrydate BETWEEN '".substr($upto,0,8)."01' AND '".$upto."'
	";
	$check_calls = $myquery->single($query,'ccba02.ivrperformance');
	
	if($check_calls[expected_days] != $check_calls[data_days]){
		$report[error] = "MISSING DATA";
		$report[error_details] = PrintR($check_calls);
		$report[check_query] = $query;
		
		return display_prank_call_wrapups_report($report);
	}
	
	$query = "
		SELECT
			queue.entrydate AS the_date,
			sum(calldetail.calls) AS answered_calls
		FROM
			queue INNER JOIN calldetail on queue.id = calldetail.id_c
		WHERE
			queue.entrydate BETWEEN '".$from."' AND '".$upto."' AND
			calldetail.`status` = 'Handled'
		GROUP BY
			queue.entrydate
		ORDER BY
			queue.entrydate ASC
	";
	
	$handled_calls = $myquery->multiple($query, 'ccba02.ivrperformance');
	
	$query = "
		SELECT
			left(reportsphonecalls.createdon,10) AS the_date,
			count(*) as no_of_tags
		FROM
			`reportsphonecalls`
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$upto." 23:59:59'
		GROUP BY
			the_date
		ORDER BY
			the_date ASC
	";
	$wrapups_by_day = $myquery->multiple($query, 'ccba02.reportscrm');
	
	$query = "
		SELECT
			left(reportsphonecalls.createdon,10) as the_date,
			reportsphonecalls.`subject`,
			count(*) as no_of_tags
		FROM
			`reportsphonecalls`
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$upto." 23:59:59' AND
			reportsphonecalls.`wrapupcat` = 'Unclassified' AND
			reportsphonecalls.wrapupsubcat = 'Prank Calls' AND
			reportsphonecalls.`subject` IN ('Dropped Call','Silent Customer')
		GROUP BY
			the_date, reportsphonecalls.wrapupsubcat,reportsphonecalls.`subject`
		ORDER BY
			the_date, reportsphonecalls.`subject` ASC;
	";
	$prank_wrapups = $myquery->multiple($query, 'ccba02.reportscrm');
	
	foreach($handled_calls as $row){
		$report[dates][$row[the_date]] = $row[the_date];
		
		$data['Answered Calls'][$row[the_date]] = $row[answered_calls];
		$report[MTD]['Answered Calls'] += $row[answered_calls];
	}
	
	foreach($wrapups_by_day as $row){
		$report[dates][$row[the_date]] = $row[the_date];
		
		$data['Total Tagging'][$row[the_date]] = $row[no_of_tags];
		$report[MTD]['Total Tagging'] += $row[no_of_tags];
	}
	
	foreach($prank_wrapups as $row){
		$report[dates][$row[the_date]] = $row[the_date];
		
		$data[$row[subject]][$row[the_date]] = $row[no_of_tags];
		$report[MTD][$row[subject]] += $row[no_of_tags];
	}
	
	$report[MTD]['Silent + Dropped Calls'] = $report[MTD]['Silent Customer'] + $report[MTD]['Dropped Call'];
	$report[MTD]['%age of Total Tags'] = $report[MTD]['Silent + Dropped Calls'] * 100 / $report[MTD]['Total Tagging'];
	
	foreach($report[MTD] as $parameter=>$param_data){
		$report[parameters][] = $parameter;
	}
	
	asort($report[dates]);
	
	foreach($data as $group_name=>$group_name_data){
		foreach($report[dates] as $date){
			$report[data][$group_name][$date] = intval($group_name_data[$date]);
			
			$report[data]['Silent + Dropped Calls'][$date] = $report[data]['Silent Customer'][$date] + $report[data]['Dropped Call'][$date];
			$report[data]['%age of Total Tags'][$date] = $report[data]['Silent + Dropped Calls'][$date] * 100 /  $report[data]['Total Tagging'][$date];
			
			unset($data[$group_name]);
		}
	}
	
	return display_prank_call_wrapups_report($report);
}


function display_prank_call_wrapups_report($report){
	
	if($report[error] == "MISSING DATA"){
		
		$html = 'Some dates do not have call data uploaded<br>Error Details are : '.PrintR($report).'<hr>
		If you are done uploading the data, click <a href="http://ccba01.waridtel.co.ug/reports/cron/wrapups_prank_calls.php?upto='.$report[usedate].'">here</a> to rerun the report.';
		
		my_mail(
			$to = 'Vincent Lukyamuzi/CC/Kampala <vincent.lukyamuzi@waridtel.co.ug>, Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>',
			$cc = '',
			$bcc = '',
			$message = $html,
			$subject = 'WTU Silent and Dropped calls MTD '.date("F Y", strtotime("-1 days")).' - ERROR',
			$from = "CCREPORTS <ccnotify@waridtel.co.ug>"
		);
		
		exit("No data, sending error mail and exiting .... \n");
	}
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<th colspan="3">'.date('F Y',strtotime($report[usedate])).' MTD</th>
			</tr>
			<tr>
				<th class="text_values">#</th>
				<th class="text_values">PARAMETER</th>
				<th class="text_values">VALUE</th>
			</tr>';
		foreach($report[MTD] as $parameter => $value){
			$html .= '
			<tr>
				<td class="text_values">'.++$i.'.</td>
				<td class="text_values">'.$parameter.'</td>
				<td class="values">'.number_format($value,0).'</td>
			</tr>
			';
		}
		unset($i);
	$html .= '
			<tr>
				<td colspan="3" height="20"> </th>
			</tr>
		</table>
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<th>Parameter</th>
	';
	
	foreach($report[dates] as $date){
		$html .= '
				<th>'.date('d-M',strtotime($date)).'</th>
		';
	}
	
	$html .= '
			</tr>
	';
	foreach($report[parameters] as $parameter){
		$html .= '
			<tr>
				<td class="text_values">'.$parameter.'</td>
		';
		
		foreach($report[dates] as $date){
			$html .= '
				<td class="values">'.number_format($report[data][$parameter][$date],0).'</td>
			';
		}		
		
		$html .= '
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}

?>