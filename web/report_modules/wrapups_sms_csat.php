<?
function generate_wrapups_sms_csat_report($from,$to,$evaluation_answer,$period_grouping,$show_msisdn_list=FALSE){
	
	custom_query::select_db($_POST[csat_data_source]);
	
	$myquery = new custom_query();
	
	if($evaluation_answer != ''){
		$evaluation_answer_query = " AND 
			smsfeedback.sms_evaluation.text LIKE '".$evaluation_answer."'
		";
	}
	
	if(!$to){ $_POST[to] = date('Y-m-d'); }
	$to = $_POST[to]." 23:59:59";
	
	switch($period_grouping){
		case 'daily':
			$sql_left_value_for_period = "10";
			if(!$from){ $_POST[from] = date('Y-m-d'); }
			break;
		case 'agent_perfomance':
			$data = agent_csat_perfomance($from,$to);
			//print_r($data);
			break;
		case 'monthly':
		default:
			$sql_left_value_for_period = "7";
			if(!$from){ $_POST[from] = date('Y-m-')."01"; }
			break;	
	}
	
	$from = $_POST[from]." 00:00:00";

	$query = "
		SELECT
			LEFT(IF(reportscrm.reportsphonecalls.createdon IS NULL, smsfeedback.sms_evaluation.date_entered,reportscrm.reportsphonecalls.createdon),".$sql_left_value_for_period.") AS period,
			IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
			COUNT(smsfeedback.sms_evaluation.text) as number
		FROM
			smsfeedback.sms_evaluation
			LEFT OUTER JOIN reportscrm.reportsphonecalls ON smsfeedback.sms_evaluation.wrapup_id = reportscrm.reportsphonecalls.id
		WHERE
			IF(
			   	reportscrm.reportsphonecalls.createdon IS NULL,
				smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' AND '".$to."',
				reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
			)
			".$evaluation_answer_query."
		GROUP BY
			period,
			evaluation_answer
	";
	
	/*
	smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' AND '".$to."'
	*/
	
	//echo nl2br($query)."<br><br>";
	
	$evaluations_rows = $myquery->multiple($query);
	
	//echo "This is ->> ".count($evaluations_rows);
	
	if(count($evaluations_rows) == 0){
		$report[NO_DATA] = TRUE; return display_wrapups_sms_csat_report($report);
	}
	
	if($show_msisdn_list == TRUE){
		$list_query = "
			SELECT
				smsfeedback.sms_evaluation.msisdn,
				LEFT(smsfeedback.sms_evaluation.date_entered,".$sql_left_value_for_period.") AS evaluation_period,
				reportscrm.reportsphonecalls.createdby AS agent,
				LEFT(reportscrm.reportsphonecalls.createdon,".$sql_left_value_for_period.") AS interaction_period,
				IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
				smsfeedback.sms_evaluation.text
			FROM
				smsfeedback.sms_evaluation
				LEFT OUTER JOIN reportscrm.reportsphonecalls ON smsfeedback.sms_evaluation.wrapup_id = reportscrm.reportsphonecalls.id
			WHERE
				IF(
					reportscrm.reportsphonecalls.createdon IS NULL,
					smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' AND '".$to."',
					reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
				)
				".$evaluation_answer_query."
		";
		
		//echo nl2br($list_query)."<br><br>";
		
		$report[msisdn_list] = $myquery->multiple($list_query);
	}
	
	foreach($evaluations_rows as &$row){
		$report[data][$row[period]][$row[evaluation_answer]] += $row[number];
		$report[graph][data][$row[evaluation_answer]][$row[period]] += $row[number];
		if($evaluation_answer == ''){
			$report[data][$row[period]][ALL] += $row[number];
			$report[graph][data][ALL][$row[period]] += $row[number];
			$report[data][$row[period]][score] = ($report[data][$row[period]][Yes]/$report[data][$row[period]][ALL]) * 100;
		}
		$report[answers][$row[evaluation_answer]] = $row[evaluation_answer];
		
		unset($row);
	}

	$query = "
		SELECT
			LEFT(reportscrm.reportsphonecalls.createdon,".$sql_left_value_for_period.") AS period,
			COUNT(reportscrm.reportsphonecalls.id) as Evaluated_wrap_ups
		FROM
			reportscrm.reportsphonecalls
		WHERE
			reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."' AND
			reportscrm.reportsphonecalls.language IN ('English','Luganda') AND
			reportscrm.reportsphonecalls.wrapupsubcat NOT IN ('Silent Customer','Dropped Call')
		GROUP BY
			period
	";
	
	$evaluated_wrapup_rows = $myquery->multiple($query);
	
	//print nl2br($query).'<hr>'.count($evaluated_wrapup_rows);
	
	foreach($evaluated_wrapup_rows as $row){
		$report[data][$row[period]]['No of Evaluated Calls'] += $row['Evaluated_wrap_ups'];
	}
	
	$query = "
		SELECT
			LEFT(reportscrm.reportsphonecalls.createdon,".$sql_left_value_for_period.") AS period,
			COUNT(reportscrm.reportsphonecalls.id) as Total_wrap_ups
		FROM
			reportscrm.reportsphonecalls
		WHERE
			reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
		GROUP BY
			period
	";
	
	$wrapup_rows = $myquery->multiple($query);
	
	foreach($wrapup_rows as $row){
		$report[data][$row[period]]['No of Calls'] += $row['Total_wrap_ups'];
	}
	
	if($evaluation_answer == ''){ $report[answers][ALL] = 'ALL'; }
	
	if(count($report[graph][data]) > 0){
		$graph_detail[data] = $report[graph][data];
				
		custom_query::select_db('reporting');
		$myreport = new report();
		$list = $myreport->GetList(array(array('reportname','=',$_GET[report])));
		$myreport = $list[0];
	
		$graph_detail[title]=$myreport->name.' : '.$period_grouping;
		//$graph_detail[line_graph]=true;
		//$graph_detail[bar_graph]=false;
		$graph_detail[set_data_points]=true;
		$graph_detail[width]=800;
		$graph_detail[height]=600;
		$graph_detail[legend]=true;
		//$graph_detail[line_colors]=array('red','blue','green');
		$period = $_POST[from].' to '.$_POST[to];
		
		$my_graph = new dbgraph();
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type='line');
		custom_query::select_db('graphing');
		$report[graph_ids] = $my_graph->Save();
		unset($report[graph]);
	}
	return display_wrapups_sms_csat_report($report);
}

function agent_csat_perfomance($from,$to){
	
	custom_query::select_db($_POST[csat_data_source]);
	
	$myquery = new custom_query();
	$agent_query= "
		select 
			left(reportscrm.reportsphonecalls.createdon,10) as created_on,
			reportscrm.reportsphonecalls.createdby,
			reportscrm.reportsphonecalls.wrapupcat,
			reportscrm.reportsphonecalls.subject,
			smsfeedback.sms_evaluation.msisdn,
			IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer 
		from
			reportscrm.reportsphonecalls 
		inner join 
			smsfeedback.sms_evaluation on reportscrm.reportsphonecalls.phonenumber  = smsfeedback.sms_evaluation.msisdn and
			reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."' limit 10
	";
				
	//$agent_rows = $myquery->multiple($query);
	//print_r($agent_rows);
	//return $agent_rows;
		
}

function display_wrapups_sms_csat_report($report){
	
	if($report[NO_DATA] == TRUE){return "NO DATA";}

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr>
			<th>SUMMARY</th>
		</tr>
		<tr>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="40%">
				<tr>
					<th ></th>
					<th>Period</th>
	';
	
	foreach($report[answers] as $answer){
		$html .= '
					<th>'.$answer.'</th>
		';
	}
	
	$html .= '
					<th>Score %age</th>
					<th>No of Evaluated Calls</th>
					<th>Calls Handled</th>
				</tr>
	';

	foreach($report[data] as $period=>$period_data){
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$period.'</td>
	';
	
		foreach($report[answers] as $answer){
			$html .= '
				<td class="values">'.number_format($period_data[$answer],0).'</td>
			';
		}
	
		$html .= '
				<td class="values">'.number_format($period_data[score],2).' %</td>
				<td class="values">'.number_format($period_data['No of Evaluated Calls'],0).'</td>
				<td class="values">'.number_format($period_data['No of Calls'],0).'</td>
			</tr>
		';
	}
	
	$html .= '
			</table>
		</td></tr>
	';
	
	if(count($report[msisdn_list]) > 0){
		$i = '';
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
			<tr>
				<th>MSISDN LIST</th>
			</tr>
			<tr><td>
				<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="40%">
					<tr>
						<th></th>
						<th>MSISDN</th>
						<th>AGENT</th>
						<th>EVALUATION DATE</th>
						<th>INTERACTION DATE</th>
						<th>EVALUATION</th>
						<th>SUBMITTED TEXT</th>
					</tr>
		';
		
		foreach($report[msisdn_list] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$row[msisdn].'</td>
					<td class="text_values">'.ucwords(strtolower($row[agent])).'</td>
					<td class="values">'.$row[evaluation_period].'</td>
					<td class="values">'.$row[interaction_period].'</td>
					<td class="values">'.$row[evaluation_answer].'</td>
					<td class="wrap_text">'.$row[text].'</td>
				</tr>
			';
		}
		
		$html .= '
				</table>
			</td></tr>
		';
	}
	
	if($report[graph_ids] != ''){
		$html .= '
			<tr>
				<th>GRAPH</th>
			</tr>
			<tr>
				<td><img class="graph" src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph_ids].'.jpg" /></td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>