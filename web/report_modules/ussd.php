<?
function generate_ussd_report($from,$to,$ussd_service_code='ALL',$complete_state,$end_state,$period_grouping){

	$myquery = new custom_query();
	
	switch($period_grouping){
		case 'daily':
			$left_value = 10;
			$period_grouping = 'daily';
			$report[titles][period] = 'Date';
			break;
		case 'monthly':
		default:
			$left_value = 7;
			$period_grouping = 'monthly';
			$_POST[period_grouping] = $period_grouping;
			$report[titles][period] = 'Month';
			break;
	}
	
	//blank service code means all
	if(trim($ussd_service_code) == ""){$ussd_service_code = "ALL"; $_POST[ussd_service_code]='';}
	if($ussd_service_code != 'ALL'){
		$service_code_query = " and ussd_log.service_code = '".$ussd_service_code."' ";
	}

	if($complete_state){
		if($complete_state == 'PROCESSED'){
			$complete_state_query = " and ussd_log.end_event = '".$complete_state."' ";
		}else{
			$complete_state_query = " and ussd_log.end_event != 'PROCESSED' ";
		}
	}
	
	if(!$from){
		$from = date('Y-m-',strtotime("-1 days"))."01";
		$_POST[from] = $from;
	}
	
	if(!$to){
		$to = date('Y-m-d',strtotime("-1 days"));
		$_POST[to] = $to;
	}
	
	$query = "
		SELECT
			ussd_log.service_code,
			left(ussd_log.start_time,".$left_value.") AS period_label,
			if(ussd_log.end_event='PROCESSED','COMPLETE','INCOMPLETE') AS status,
			count(if(ussd_log.end_event='PROCESSED','COMPLETE','INCOMPLETE')) AS number_of_sessions,
			sum(ussd_log.number_of_tx) AS number_of_hits
		FROM
			ussd_log
		WHERE
			ussd_log.start_time between '".$from." 00:00:00' and '".$to." 23:59:59'
			".$service_code_query."
			".$complete_state_query."
		GROUP BY
			service_code,period_label,status
	";
	
	//echo $query."<br><br>";
	
	custom_query::select_db('reportsussd');
	$entries = $myquery->multiple($query);
	
	if(count($entries) == 0 ){ $report[NO_DATA] = TRUE; return display_ussd_report($report);}
	
	//if(!$complete_state_query) $report[titles][statuses] = array('All Statuses'=>'All Statuses');
	//if(!$service_code_query) $report[titles][service_codes] = array('All Service codes'=>'All Service codes');
	
	foreach($entries as $row){
		//populating the titleslist
		$report[titles][statuses][$row[status]] = $row[status];
		$report[titles][service_codes][$row[service_code]] = $row[service_code];

		$report[rows][$row[period_label]][sessions][$row[service_code]][$row[status]] += $row[number_of_sessions];
		$report[rows][$row[period_label]][hits][$row[service_code]][$row[status]] += $row[number_of_hits];
		
		$graph_detail[data][$row[service_code].' Sessions'][$row[period_label]] += $row[number_of_sessions]/1000;
		//$graph_detail[data][$row[service_code].' Hits'][$row[period_label]] += $row[number_of_hits]/1000;
	}
		
	custom_query::select_db('reporting');
	$myreport = new report();
	$list = $myreport->GetList(array(array('reportname','=',$_GET[report])));
	$myreport = $list[0];

	$graph_detail[title]=$myreport->name." : Trends in '000";
	/*$graph_detail[line_graph]=true;*/
	/*$graph_detail[bar_graph]=false;*/
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=1024;
	$graph_detail[height]=800;
	$graph_detail[legend]=true;
	/*$graph_detail[line_colors]=array('red','blue','green');*/
	$period = $_POST[from].' to '.$_POST[to];
	
	$my_graph = new dbgraph();
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type='line');
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	
	//echo "<br><br>"; print_r($report[titles]);
	
	return display_ussd_report($report);
}

function display_ussd_report($report){
	
	if($report[NO_DATA]){return "No DATA";} 
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%"> 
		<tr> 
			<th>#</th>
			<th>'.$report[titles][period].'</th>
	';
	
	foreach($report[titles][service_codes] as $service_code){
		foreach($report[titles][statuses] as $status){
			$html .= '
				<th>'.$service_code.' '.$status.' (Sessions)</th>
				<th>'.$service_code.' '.$status.' (Hits)</th>
			';
		}
	}
	
	$html .='
		</tr>
	';
	foreach($report[rows] as $period=>$period_data){
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="values">'.$period.'</td>
		';
		foreach($report[titles][service_codes] as $service_code){
			foreach($report[titles][statuses] as $status){
				$html .= '
					<td class="values">'.number_format($period_data[sessions][$service_code][$status],0).'</td>
					<td class="values">'.number_format($period_data[hits][$service_code][$status],0).'</td>
				';
			}
		}
		
		$html .= '
			</tr>
		';
	}
	
	$html .= '
		</table>
		<table border="0" cellpadding="2" cellspacing="0" width="100%"> 
			<tr>
				<td><img class="graph" src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /></td>
			</tr>
		</table>
	';
	return $html;
}
?>