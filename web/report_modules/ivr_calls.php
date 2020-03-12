<?
function generate_ivr_report($from,$to,$queues,$report_type,$subbase){
	custom_query::select_db('ivrperformance');
	
	$myquery = new custom_query();
	
	if($subbase == ''){
		$subbase = '3448380';
	}
	$_POST[subbase] = $subbase;
	
	$sub_base_query = "
		SELECT
			avg(subscount.active_subs) as average_subs,
			count(subscount.`day`) as no_of_days
		FROM
			subscount
		WHERE
	";
	
	$ivr_query = "
		select
			queue.que as queue,
			queue.entrydate as thedate,
			queue.servicelevel as sl,
			queue.avgcallduration as acd,
			queue.avgabancallwait as aacw,
			queue.avgspeedofans as asos,
			calldetail.status,
			calldetail.calls
		from
			queue
			inner join calldetail on calldetail.id_c = queue.id
		where
	";
	//print $ivr_query;
	
	if($from==''){
		$_POST[from] = date('Y-m-d',strtotime("-1 days"));
		$from = $_POST[from];
	}
	$ivr_query .= " queue.entrydate >= '".$from."' ";
	$sub_base_query .= " subscount.`day` >= '".$from."' ";
	
	if($to==''){
		$_POST[to] = date('Y-m-d',strtotime("-1 days"));
		$to = $_POST[to];
	}
	$ivr_query .= " and queue.entrydate <= '".$to."' ";
	$sub_base_query .= " and subscount.`day` <= '".$to."'";

	if(($queues) && (!in_array('%%',$queues))){
		$ivr_query .= "AND (";
		foreach($queues as $count=>$queue){
			$ivr_query .= "queue.que = '".$queue."'";
			if(count($queues) > $count+1){
				$ivr_query .= " OR ";
			}
		}
		$ivr_query .= ")";
	}
	
	//echo $ivr_query."<br>";
	
	$ivr_data = $myquery->multiple($ivr_query);
	//echo $sub_base_query."<br>";
	$sub_base_result = $myquery->single($sub_base_query);
	if(intval($subbase)==0){
		$subbase = $sub_base_result[average_subs];
		//echo $subbase."<br>";
	}
	
	$report[totals][constants][subbase] = $subbase;
	//$_POST[subbase] = number_format($report[totals][constants][subbase],1);
	
	foreach($ivr_data as $row){
		unset($key);
		foreach($row as $key => $value){
			$row[$key] = trim($value);
		}
		if($report_type == 'summary' or $report_type == ''){
			if($from == $to){
				$period = $from;
			}else{
				$period = $from.' to '.$to;
			}
			$report[summary][period] = $period;
			if($row[status] == 'Received'){
				$report[summary][data][$row[queue]][sl_sum] += ($row[sl] * $row[calls]);
				//echo "$row[queue] =>> $row[sl] X $row[calls] = ".($row[sl] * $row[calls])." [".$report[summary][data][$row[queue]][sl_sum]."]<br>";
			}
			if($row[status] == 'Handled'){
				$report[summary][data][$row[queue]][acd_sum] += (time_to_sec($row[acd]) * $row[calls]);
				$report[summary][data][$row[queue]][asos_sum] += (time_to_sec($row[asos]) * $row[calls]);
			}
			if($row[status] == 'Abandon'){
				$report[summary][data][$row[queue]][aacw_sum] += (time_to_sec($row[aacw]) * $row[calls]);
			}
			$report[summary][data][$row[queue]][status][$row[status]] += $row[calls];
			$report[summary][data][$row[queue]][cpc][$row[status]] = $report[summary][data][$row[queue]][status][$row[status]]/$report[totals][constants][subbase];
			$report[summary][data][$row[queue]][sl] = $row[sl];
		}
		if($report_type == 'monthly summary' or $report_type == 'daily summary'){
			if($report_type == 'monthly summary'){
				//Get the month component
				$row[thedate] = substr($row[thedate],0,7);
			}
			if($row[status] == 'Received'){
				$report[data][rows][$row[thedate]][$row[queue]][sl] = $row[sl];
				$report[data][totals][$row[thedate]][sl_sum] += ($row[sl]*$row[calls]);
			}
			if($row[status] == 'Handled'){
				$report[data][rows][$row[thedate]][$row[queue]][acd] = $row[acd];
				$report[data][rows][$row[thedate]][$row[queue]][asos] = $row[asos];
				
				$report[data][totals][$row[thedate]][acd_sum] += (time_to_sec($row[acd])*$row[calls]);
				$report[data][totals][$row[thedate]][asos_sum] += (time_to_sec($row[asos])*$row[calls]);
			}
			if($row[status] == 'Abandon'){
				$report[data][rows][$row[thedate]][$row[queue]][aacw] = $row[aacw];
				$report[data][totals][$row[thedate]][aacw_sum] += (time_to_sec($row[aacw])*$row[calls]);
			}
			$report[data][rows][$row[thedate]][$row[queue]][status][$row[status]] += $row[calls];
			$report[data][rows][$row[thedate]][$row[queue]][cpc][$row[status]] = $report[data][rows][$row[thedate]][$row[queue]][status][$row[status]]/$report[totals][constants][subbase];
			$report[data][totals][$row[thedate]][$row[status]] += $row[calls];
		}
	}
	
	//Generate graph if you have more than one point on the X axis
	if(count($report[data][rows]) > 1){
		$report[generate_graph] = TRUE;
	}
	
	return display_ivr_report($report);
	
}

function display_ivr_report($report){

	$html = '
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<th>USING AN AVERAGE SUB BASE OF '.number_format($report[totals][constants][subbase],0).'</th>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
	';
	if($report[summary]){
		$html .= '
			<tr>
				<th>SUMMARY THROUGH PERIOD</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="1" cellspacing="0" class="sortable">
				<tr>
					<th>Period</th>
					<th>Queue</th>
					<th>Calls Received</th>
					<th>Received CPC</th>
					<th>Calls Handled</th>
					<th>Handled CPC</th>
					<th>Average Speed of Answer</th>
					<th>Average Call Duration</th>
					<th>Service Level %</th>
					<th>Calls Abandoned</th>
					<th>Abandoned CPC</th>
					<th>Average Abandon Wait</th>
				</tr>
		';
		foreach($report[summary][data] as $queue=>$queue_data){
			$html .= '
					<tr>
						<td class="text_values">'.$report[summary][period].'</td>
						<td class="text_values">'.$queue.'</td>
						<td class="values">'.number_format($queue_data[status][Received],0).'</td>
						<td class="values">'.number_format($report[summary][data][$queue][cpc][Received],5).'</td>
						<td class="values">'.number_format($queue_data[status][Handled],0).'</td>
						<td class="values">'.number_format($report[summary][data][$queue][cpc][Handled],5).'</td>
						<td class="values">'.sec_to_time(intval($queue_data[asos_sum]/$queue_data[status][Handled])).'</td>
						<td class="values">'.sec_to_time(intval($queue_data[acd_sum]/$queue_data[status][Handled])).'</td>
						<td class="values">'.number_format($queue_data[sl_sum]/$queue_data[status][Received],2).'</td>
						<td class="values">'.number_format($queue_data[status][Abandon],0).'</td>
						<td class="values">'.number_format($report[summary][data][$queue][cpc][Abandon],5).'</td>
						<td class="values">'.sec_to_time(intval($queue_data[aacw_sum]/$queue_data[status][Abandon])).'</td>
					</tr>
			';
			$report[summary][totals][sl_sum] += $queue_data[sl_sum];
			$report[summary][totals][Received] += $queue_data[status][Received];
			
			$report[summary][totals][acd_sum] += $queue_data[acd_sum];
			$report[summary][totals][asos_sum] += $queue_data[asos_sum];
			$report[summary][totals][Handled] += $queue_data[status][Handled];
			
			$report[summary][totals][aacw_sum] += $queue_data[aacw_sum];
			$report[summary][totals][Abandon] += $queue_data[status][Abandon];
			//echo $queue." => ".$queue_data[sl]." X ".$queue_data[status][Received]." = ".$queue_data[sl_sum]." =>".$report[summary][totals][sl_sum]." <br>+".$queue_data[status][Received]." = ".$report[summary][totals][Received]."<br><br>";
		}

			$html .= '
					<tr id="totals">
						<td class="text_values"></td>
						<td class="text_values"></td>
						<td class="values">'.number_format($report[summary][totals][Received],0).'</td>
						<td class="values">'.number_format($report[summary][totals][Received]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.number_format($report[summary][totals][Handled],0).'</td>
						<td class="values">'.number_format($report[summary][totals][Handled]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.sec_to_time($report[summary][totals][asos_sum]/$report[summary][totals][Handled]).'</td>
						<td class="values">'.sec_to_time($report[summary][totals][acd_sum]/$report[summary][totals][Handled]).'</td>
						<td class="values">'.number_format($report[summary][totals][sl_sum]/$report[summary][totals][Received],2).'</td>
						<td class="values">'.number_format($report[summary][totals][Abandon],0).'</td>
						<td class="values">'.number_format($report[summary][totals][Abandon]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.sec_to_time($report[summary][totals][aacw_sum]/$report[summary][totals][Abandon]).'</td>
					</tr>
			';
		
		$html .= '
				</table>
				</td>
				</tr>
		';
	}
	
	if(($report[data]) and ($report[summary])){
		$html .= '
			<tr>
				<td style="height:10px;"></td>
			</tr>
		';
	}
	
	if($report[data]){
		$html .= '
			<tr>
				<th>DETAILS BY DAY</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="1" cellspacing="0" class="sortable" width="100%">
				<tr>
					<th>Period</th>
					<th>Queue</th>
					<th>Calls Received</th>
					<th>Received CPC</th>
					<th>Calls Handled</th>
					<th>Handled CPC</th>
					<th>Average Speed of Answer</th>
					<th>Average Call Duration</th>
					<th>Service Level %</th>
					<th>Calls Abandoned</th>
					<th>Abandoned CPC</th>
					<th>Average Abandon Wait</th>
				</tr>
		';

		foreach($report[data][rows] as $date=>$date_data){
			foreach($date_data as $queue=>$queue_data){
				$html .= '
						<tr>
							<td class="text_values">'.$date.'</td>
							<td class="text_values">'.$queue.'</td>
							<td class="values">'.number_format($queue_data[status][Received],0).'</td>
							<td class="values">'.number_format($report[data][rows][$date][$queue][cpc][Received],5).'</td>
							<td class="values">'.number_format($queue_data[status][Handled],0).'</td>
							<td class="values">'.number_format($report[data][rows][$date][$queue][cpc][Handled],5).'</td>
							<td class="values">'.$queue_data[asos].'</td>
							<td class="values">'.$queue_data[acd].'</td>
							<td class="values">'.round($queue_data[sl],2).'</td>
							<td class="values">'.number_format($queue_data[status][Abandon],0).'</td>
							<td class="values">'.number_format($report[data][rows][$date][$queue][cpc][Abandon],5).'</td>
							<td class="values">'.$queue_data[aacw].'</td>
						</tr>
				';
			}
			
			$html .= '
					<tr id="totals">
						<td class="text_values">'.$date.'</td>
						<td class="text_values">All Queues</td>
						<td class="values">'.number_format($report[data][totals][$date][Received],0).'</td>
						<td class="values">'.number_format($report[data][totals][$date][Received]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.number_format($report[data][totals][$date][Handled],0).'</td>
						<td class="values">'.number_format($report[data][totals][$date][Handled]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.sec_to_time($report[data][totals][$date][asos_sum]/$report[data][totals][$date][Handled]).'</td>
						<td class="values">'.sec_to_time($report[data][totals][$date][acd_sum]/$report[data][totals][$date][Handled]).'</td>
						<td class="values">'.number_format($report[data][totals][$date][sl_sum]/$report[data][totals][$date][Received],2).'</td>
						<td class="values">'.number_format($report[data][totals][$date][Abandon],0).'</td>
						<td class="values">'.number_format($report[data][totals][$date][Abandon]/$report[totals][constants][subbase],5).'</td>
						<td class="values">'.sec_to_time($report[data][totals][$date][aacw_sum]/$report[data][totals][$date][Abandon]).'</td>
					</tr>
			';
			
			
			
			//CPC
			$report[data_sets][cpc]['Received Calls'][$date] = number_format($report[data][totals][$date][Received]/$report[totals][constants][subbase],5);
			$report[data_sets][cpc]['Handled Calls'][$date] = number_format($report[data][totals][$date][Handled]/$report[totals][constants][subbase],5);
			$report[data_sets][cpc]['Abandon Calls'][$date] = number_format($report[data][totals][$date][Abandon]/$report[totals][constants][subbase],5);
			
			//Performance Summary
			$report[data_sets][performance_summary]['Service level'][$date] = number_format($report[data][totals][$date][sl_sum]/$report[data][totals][$date][Received],2,'.','');
			$report[data_sets][performance_summary]['%age Calls Handled'][$date] = number_format(100 * $report[data][totals][$date][Handled]/$report[data][totals][$date][Received],2,'.','');
			$report[data_sets][performance_summary]['%age Calls Abandoned'][$date] = number_format(100 * $report[data][totals][$date][Abandon]/$report[data][totals][$date][Received],2,'.','');
		}
		
		$html .= '
				</table>
				</td>
			</tr>
		';
		
		if($report[generate_graph]){
			//CPC Graph
			$graph_detail[data]=$report[data_sets][cpc];
			
			custom_query::select_db('reporting');
			$myreport = new report();
			$list = $myreport->GetList(array(array('reportname','=',$_GET[report])));
			$myreport = $list[0];
		
			$graph_detail[title]=$myreport->name.' : CPC trends by day ';
			//$graph_detail[line_graph]=true;
			//$graph_detail[bar_graph]=false;
			$graph_detail[set_data_points]=true;
			$graph_detail[width]=800;
			$graph_detail[height]=600;
			$graph_detail[legend]=true;
			$graph_detail[line_colors]=array('red','blue','green');
			$period = $_POST[from].' to '.$_POST[to];
			
			$my_graph = new dbgraph();
			$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type='line');
			custom_query::select_db('graphing');
			$report[graph][cpc] = $my_graph->Save();
	
			$html .= '
				<tr>
					<td><img class="graph" src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph][cpc].'.jpg" /></td>
				</tr>
				<tr>
					<td style="height:10px;"></td>
				</tr>
			';
			
			//Performance Summary Graph
			$graph_detail[data]=$report[data_sets][performance_summary];
			$graph_detail[title]=$myreport->name.' : Service level compared to the % of calls handled and abandoned ';
			$graph_detail[line_colors]=array('blue','green','red');
			$graph_detail[display_title]=FALSE;
			
			$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type='line');
			custom_query::select_db('graphing');
			$report[graph][performance_summary] = $my_graph->Save();
			
			$html .= '
				<tr>
					<th style="height:10px; background:#009; color:#FFF;" align="centre">Call Centre: Service level compared to the % of calls handled and abandoned</th>
				</tr>
				<tr>
					<td><img class="graph" src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph][performance_summary].'.jpg" /></td>
				</tr>
			';
		}
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>