<?php
function generate_repeat_cca_wrapups_trend_by_day($upto){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	$handled_calls_query = "
		SELECT
			queue.entrydate AS entry_date,
			SUM(calldetail.calls) AS calls_handled
		FROM
			queue
			Inner Join calldetail ON queue.id = calldetail.id_c
		WHERE
			queue.entrydate >= DATE_SUB('".$upto."', interval 30 day) AND
			queue.entrydate <= '".$upto."' AND
			calldetail.status = 'Handled'
		GROUP BY
			queue.entrydate,calldetail.status
	";
	custom_query::select_db('ivrperformance');
	$handled_calls = $myquery->multiple($handled_calls_query);
	//exit($handled_calls_query." \n");
	
	$repeat_wrapup_query = "
		SELECT
			wrapup_repeats.date_entered as entry_date,
			wrapup_repeats.repeat_wrapups
		FROM
			wrapup_repeats
		WHERE
			wrapup_repeats.date_entered BETWEEN DATE_SUB('".$upto." 00:00:00', interval 30 day) AND '".$upto."'
		GROUP BY
			entry_date
	";
	//exit($repeat_wrapup_query." \n");
	
	//custom_query::select_db('reportscrm');
	$repeat_wrapups_by_day = $myquery->multiple($repeat_wrapup_query,'ccba02.ivrperformance');
	
	$total_wrapup_query = "
		SELECT
			LEFT(reportsphonecalls.createdon,10) AS entry_date,
			COUNT(*) as total_wrap_ups
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon >= DATE_SUB('".$upto." 00:00:00', interval 30 day) and
			reportsphonecalls.createdon <= '".$upto." 23:59:59' AND
			reportsphonecalls.wrapupcat != 'Unclassified'
		GROUP BY
			entry_date
	";
	
	//custom_query::select_db('reportscrm');
	$total_wrapups_by_day = $myquery->multiple($total_wrapup_query,'ccba02.reportscrm');
	
	foreach($repeat_wrapups_by_day as $row){
		$report[dates][str_replace('-','',$row[entry_date])] = $row[entry_date];
		$data['Repeat Wrapups'][$row[entry_date]] += $row[repeat_wrapups];
	}
	
	foreach($total_wrapups_by_day as $row){
		$report[dates][str_replace('-','',$row[entry_date])] = $row[entry_date];
		$data['Total Wrapups'][$row[entry_date]] = $row[total_wrap_ups];
	}
	
	foreach($handled_calls as $row){
		$report[dates][str_replace('-','',$row[entry_date])] = $row[entry_date];
		$data['Un wrappedup'][$row[entry_date]] = $row[calls_handled] - $data['Total Wrapups'][$row[entry_date]];
		$data['Handled Calls'][$row[entry_date]] = $row[calls_handled];
	}
	
	sort($report[dates]);
	foreach($data as $data_group_name => $data_group){
		foreach($report[dates] as $date){
			$report[data][$data_group_name][$date] = intval($data_group[$date]);
		}
	}
	
	$graph_detail[data]=$report[data];
	//$graph_detail[title] = '30 day Trend of Repeat and Total wrapups up to '.date_format(date_create($upto),'l jS F Y');
	$report[graph_title] = '30 day Trend of Repeat and Total wrapups up to '.date_format(date_create($upto),'l jS F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=900;
	$graph_detail[height]=650;
	$graph_detail[legend]=true;
	$graph_detail[line_colors] = array('black','blue','red','green');
	$my_graph->graph($title=$report[graph_title], $period = '-30 days to '.$upto, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	
	return display_repeat_cca_wrapups_trend_by_day($report);
}

function display_repeat_cca_wrapups_trend_by_day($report){
	return '
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<th>
					'.$report[graph_title].'
				</th>
			</tr>
			<tr>
				<td>
					<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /></td>
				</td>
			</tr>
		</table>
	';
}

?>