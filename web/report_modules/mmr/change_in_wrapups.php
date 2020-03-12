<?php

function top_decreasing_increasing_wrapups($period, $number = 10){
	
	$to = last_day($period);
	$from = substr(date_time_add($to,$value = -1,$mysql_interval = 'month'),0,8)."01";
	
	$report[titles][past_month] = date_reformat($date=$from,$format='%b - %y');
	$report[titles][this_month] = date_reformat($date=$to,$format='%b - %y');
	$report[titles][parameter] = 'Wrap up';

	$myquery = new custom_query();
	//custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			CONCAT(sub_category,' >> ',subject) AS `".$report[titles][parameter]."`,
			`".$report[titles][past_month]."`,
			`".$report[titles][this_month]."`,
			(`".$report[titles][this_month]."` - `".$report[titles][past_month]."`) as Difference
		FROM
			(
			SELECT
				reportsphonecalls.wrapupsubcat as sub_category,
				reportsphonecalls.subject,
				count(if(left(reportsphonecalls.createdon,7) = '".date_reformat($from,'%Y-%m')."',1,NULL)) as '".$report[titles][past_month]."',
				count(if(left(reportsphonecalls.createdon,7) = '".date_reformat($to,'%Y-%m')."',1,NULL)) as '".$report[titles][this_month]."'
			FROM
				reportsphonecalls
			WHERE
				reportsphonecalls.createdon between '".$from." 00:00:00' and '".$to." 23:59:59'
			group by
				sub_category,subject
			) AS WRAPUPS
		ORDER BY
			difference DESC
	";
	
	//echo $query."<hr>";

	$rows = $myquery->multiple($query,'ccba02.reportscrm');
	
	$report[data]['Top Increasing wrap ups (Complaints and Inquiries)'] = array();
	$counter = 0;
	while(count($report[data]['Top Increasing wrap ups (Complaints and Inquiries)']) < 10){
		$report[data]['Top Increasing wrap ups (Complaints and Inquiries)'][] = $rows[$counter];
		++$counter;
	}
	
	$report[data]['Top Decreasing wrap ups (Complaints and Inquiries)'] = array();
	$counter = count($rows);
	while(count($report[data]['Top Decreasing wrap ups (Complaints and Inquiries)']) < 10){
		$report[data]['Top Decreasing wrap ups (Complaints and Inquiries)'][] = $rows[$counter-1];
		--$counter;
	}
	
	unset($counter,$rows);

	return show_top_decreasing_increasing_wrapups($report);
}

function show_top_decreasing_increasing_wrapups($report){
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="60%">
	';
	
	foreach($report[data] as $title=>$table_data){
		$html .= '
		<tr>
			<th>'.$title.'</th>
		</tr>
		<tr>
			<td>
				<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
					<tr>
						<th></th>
						<th>Wrap up</th>
						<th>'.$report[titles][past_month].'</th>
						<th>'.$report[titles][this_month].'</th>
						<th>Difference</th>
					</tr>
		';
		unset($i);
		foreach($table_data as $row){
			$html .= '
					<tr>
						<td class="text_values">'.++$i.'</td>
						<td class="text_values">'.$row[$report[titles][parameter]].'</td>
						<td class="values">'.number_format($row[$report[titles][past_month]]).'</td>
						<td class="values">'.number_format($row[$report[titles][this_month]]).'</td>
						<td class="values">'.number_format($row[Difference]).'</td>
					</tr>
			';
		}
		
		$html .= '
				</table>
			</td>
		</tr>
		<tr>
			<td style="height:20px;"></td>
		</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>