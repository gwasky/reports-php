<?php
//TO BE RUN FROM CCBAO1

function generate_simlock_trends($date){
	
	if($date == ''){
		$date = date('Y-m-d');
	}
	
	$my_graph = new dbgraph();
	$myquerys = new custom_query();

	$result = $myquerys->single("SELECT HOUR(NOW()) as this_hour",'ccba01.reportscrm');
	$this_hour = $result[this_hour];
	
	$hour = 0;
	while($hour <= $this_hour){
		$even_hours = 2 * round($hour/2);
		$hours[$even_hours] = $even_hours;
		++$hour;
	}

	$result = $myquerys->single("SELECT DATE_SUB('".$date."', INTERVAL 1 DAY) as yesterday",'ccba01.reportscrm');
	$yesterday = $result[yesterday];
	$dates = array($date,$yesterday);

	$query = "
		SELECT
			round(hour(reportsphonecalls.createdon)/2)*2 as hrs,
			count(reportsphonecalls.subject) as counts
		FROM
			reportsphonecalls
		WHERE 
			reportsphonecalls.subject = 'My simcard is locked because of recharging' AND
			reportsphonecalls.createdon between '".$date." 00:00:00' AND NOW()
		GROUP BY
			hrs
	";
	
	
	$scrap[$date] = $myquerys->multiple($query,'ccba01.reportscrm','hrs');
	
	//echo my_print_r($scrap); exit();
	
	$query = "
		SELECT
			round(hour(reportsphonecalls.createdon)/2)*2 as hrs,
			count(reportsphonecalls.subject) as counts
		FROM
			reportsphonecalls
		WHERE 
			reportsphonecalls.subject = 'My simcard is locked because of recharging' AND
			reportsphonecalls.createdon between DATE_SUB('".$date." 00:00:00', INTERVAL 1 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY)
		GROUP BY
			hrs
	";
	
	$scrap[$yesterday] = $myquerys->multiple($query,'ccba02.reportscrm','hrs');
	
	foreach($hours as $hour){
		foreach($dates as $this_date){
			/*if($this_date == $date){
				$cumulative_total += $scrap[$this_date][$hour][counts];
				$data[$this_date." cumulative"][$hour] = $cumulative_total;
			}*/
			$data[$this_date][$hour] = intval($scrap[$this_date][$hour][counts]);
		}
	}
	
	//echo my_print_r($data); exit();
	
	$graph_detail[data]=$data;
	$graph_detail[title]='Sim Lock Wrap up Trends By hour for '.$date.' and '.$dates[1];
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=600;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue');
	$period = $hours[0].' to '.$hours[count($hours) - 1];
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graphs][] = array('id'=>$my_graph->Save(),'title'=>$graph_detail[title]);

	unset($data,$my_graph->id);
	
	$query = "
		SELECT
			date_format(reportsphonecalls.createdon,'%Y-%m-%d') as created_on,
			count(reportsphonecalls.subject) as counts
		FROM
			reportsphonecalls
		WHERE 
			reportsphonecalls.subject = 'My simcard is locked because of recharging' AND
			reportsphonecalls.createdon between date_sub('".$date." 00:00:00', interval 8 day) AND
			date_sub('".$date." 23:59:59', interval 1 day)
		GROUP BY
			date_format(reportsphonecalls.createdon,'%Y-%m-%d')
	";

	$input_list = $myquerys->multiple($query,'ccba02.reportscrm');
	
	foreach($input_list as $row){
		$data['Sim Lock Counts'][$row[created_on]] = $row[counts];		
	}
	
	//echo my_print_r($data); exit();
				
	$graph_detail[data]=$data;
	$graph_detail[title]='Sim Lock Wrap up Trends Graph Report for the period '.date("Y-m-d", strtotime("-8 days")).' to '.date('Y-m-d', strtotime("-1 days"));
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=600;
	//$graph_detail[type] = 'pie_chart';
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue');
	$period = date("Y-m-d", strtotime("-8 days")).' to '.date('Y-m-d',strtotime("-1 days"));
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graphs][] = array('id'=>$my_graph->Save(),'title'=>$graph_detail[title]);
	
	return display_simlock_trends_report($report);		
}

function display_simlock_trends_report($report){
	
	foreach($report[graphs] as $graph){
		$html .= '
			<!--<tr>
				<th>'.$graph[title].'</th>
			</tr>-->
			<tr>
				<td>'.display_generic_graph($graph[id],$with_td=false).'</td>
			</tr>
			<tr>
				<td height="20"></td>
			</tr>
		';
	}
	
	return $html;
}
?>