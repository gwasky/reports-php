<?php
function generate_helpline_traffic_flash($date){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	custom_query::select_db('ivrperformance');
	
	if(($date=='') or ($date == date('Y-m-d'))){
		$date = date('Y-m-d',strtotime("-1 days"));
	}

	$query = "
		SELECT
			'total_in_ivr' as `row_type`,
			in_ivr_stats.date_entered AS `date_of_call`,
			IF(in_ivr_stats.`option` = 'Contact the Customer Care Agent','Redirect to CC IVR','WITHIN IN IVR') AS custom_group,
			SUM(in_ivr_stats.pass) AS `number_of_calls_pass`,
			SUM(in_ivr_stats.pass + in_ivr_stats.fail) AS `number_of_calls_total`
		FROM
			in_ivr_stats
		WHERE
			in_ivr_stats.date_entered BETWEEN DATE_FORMAT(DATE_SUB('".$date."', INTERVAL 20 DAY),'%Y-%m-%d 00:00:00') AND '".$date." 23:59:59'
		GROUP BY
		 `date_of_call`,custom_group;
	";
	
	$calls[total_in_ivr] = $myquerys->multiple($query,'ivrperformance');
	
	$query = "
		SELECT
			'total_it_ivr' as `row_type`,
			left(asterisk_cdrs.date_entered,10) as `date_of_call`,
			asterisk_cdrs.last_option_group,
			IF(
				asterisk_cdrs.last_option_group IN ('CC IVR','Agent'),
				SUBSTRING_INDEX(asterisk_translations.name,' ',1),
				asterisk_cdrs.last_option_group
			) AS custom_group,
			count(asterisk_cdrs.last_option_group) as `number_of_calls`
		FROM
			asterisk_cdrs
			LEFT OUTER Join asterisk_translations ON asterisk_translations.option_value = asterisk_cdrs.last_option_value
		WHERE
			asterisk_cdrs.date_entered BETWEEN DATE_FORMAT(DATE_SUB('".$date."', INTERVAL 20 DAY),'%Y-%m-%d 00:00:00') AND '".$date." 23:59:59'
		GROUP BY
		 `date_of_call`,asterisk_cdrs.last_option_group,custom_group
	";
	
	$calls[total_it_ivr] = $myquerys->multiple($query,'ivrperformance');
	
	$query = "
		SELECT
			'total_queued' as `row_type`,
			queue.entrydate  as `date_of_call`,
			calldetail.status as custom_group,
			sum(calldetail.calls) as `number_of_calls`
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c
		WHERE
			calldetail.status != 'Received' AND
			queue.entrydate between DATE_SUB('".$date."', INTERVAL 20 DAY) and '".$date."'
		group by
			entrydate,
			custom_group
	";
	
	$calls[total_queued] = $myquerys->multiple($query,'ivrperformance');
	
	if(count($calls[total_queued]) == 0 or count($calls[total_it_ivr]) == 0){
		//DO NOT SEND BLANK NUMBERS
		$text = '
Either Total in ['.count($calls[total_it_ivr]).'] or Total Queued ['.count($calls[total_queued]).'] is ZEOR/does not have data.\n\n Check to ensure that IVR data was transfered from IT to ccba02 and that AVAYA BCMS data has been uploaded into CCBA reporting DBs.\n\nThen log into CCBA02 AND RUN "php /www/reports/cron/daily_helpline_traffic_flash_report.php" \n\n
		';
		$list = 'ccbusinessanalysis@waridtel.co.ug';
		sendHTMLemail($to=$list, $bcc='', $message=nl2br($text), $subject ='Helpline Call traffic - Error', $from="DO NOT REPLY <ccnotify@waridtel.co.ug>");
		
		exit($text);
	}
	
	function translate($input){
		
		$report[table_translations]['Agent'] = 'TXD to Call Centre';
		$report[table_translations]['IVR'] = 'Stayed in CC IVR';
		$report[table_translations]['Abandon'] = 'Abandoned in Call Centre';
		$report[table_translations]['Handled'] = 'Handled in Call Centre';
		
		$output = $report[table_translations][$input] != ''? $report[table_translations][$input] : $input;

		return $output;
	}
		
	foreach($calls as &$list){
		foreach($list as &$row){
			$report[dates][str_replace(array('-'),'',$row[date_of_call])] = $row[date_of_call];
			
			switch($row[row_type]){
				case 'total_in_ivr':
					$report[table]['Total RXD by IN IVR'][$row[date_of_call]] += $row[number_of_calls_total];
					$report[table]['Total Failed within IN IVR'][$row[date_of_call]] += ($row[number_of_calls_total] - $row[number_of_calls_pass]);
					$report[table][translate($row[custom_group]).' Pass'][$row[date_of_call]] += $row[number_of_calls_pass];
					$report[table][translate($row[custom_group]).' Fail'][$row[date_of_call]] += ($row[number_of_calls_total] - $row[number_of_calls_pass]);
				break;
				case 'total_it_ivr':
					if($row[last_option_group] != 'Agent' and $row[last_option_group] != 'IVR'){
						$row[last_option_group] = 'Helpline - Other';
						$row[custom_group] = 'Other';
					}
					
					$report[table]['Total RXD by CC IVR'][$row[date_of_call]] += $row[number_of_calls];
					$report[table][translate($row[last_option_group])][$row[date_of_call]] += $row[number_of_calls];
					//$report[table][translate($row[last_option_group]).' '.$row[custom_group]][$row[date_of_call]] += $row[number_of_calls];
				break;
				case 'total_queued':
					$report[table]['Total RXD by Call Centre'][$row[date_of_call]] += $row[number_of_calls];
					$report[table][translate($row[custom_group])][$row[date_of_call]] += $row[number_of_calls];
				break;
			}
			
			unset($row);
		}
		
		unset($list);
	}
	
	ksort($report[dates]);
	
	foreach($report[dates] as $date){
		$report[graph_data]['IN to CC IVR Pass'][$date] = number_format($report[table]['Redirect to CC IVR Pass'][$date],0,'.','');
		$report[graph_data]['IN to CC IVR Fail'][$date] = number_format($report[table]['Redirect to CC IVR Fail'][$date],0,'.','');
		
		$report[graph_data]['CC IVR IN'][$date] = number_format($report[table]['Total RXD by CC IVR'][$date],0,'.','');
		$report[graph_data]['CC IVR STAY'][$date] = number_format($report[table]['Stayed in CC IVR'][$date],0,'.','');
		$report[graph_data]['CC IVR TO AGENTS'][$date] = number_format($report[table]['TXD to Call Centre'][$date],0,'.','');
		//$report[graph_data]['Helpline - Other'][$date] = number_format($report[table][$date]['Helpline - Other'][$date],0,'.','');
		
		$report[graph_data]['RXD - AGENTS'][$date] = number_format($report[table]['Total RXD by Call Centre'][$date],0,'.','');
		$report[graph_data]['Handled - AGENTS'][$date] = number_format($report[table]['Handled in Call Centre'][$date],0,'.','');
		//$report[graph_data]['Abandoned in Call Centre'][$date] = number_format($report[table]['Abandoned in Call Centre'][$date],0,'.','');
	}
	
	$graph_detail[data] = $report[graph_data];
	
	//print_r($report[graph_data]); exit();
	
	$graph_detail[title]='Helpline call traffic 20 days from '.date_format(date_create($date),'F Y');
	$graph_detail[display_title]=false;
	$graph_detail[legend]=true;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=false;
	$graph_detail[width]=1000;
	$graph_detail[height]=700;
	
	$my_graph->graph($title=$graph_detail[title], $period="20 days before ".$date, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Helpline call traffic 20 days before '.$date] = $my_graph->Save();
	
	return display_helpline_traffic_flash($report);
}

function display_helpline_traffic_flash($report){
	
	if(count($report[dates]) == 0){ return "No Data"; }
	
	$html = '
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<tr>
		<td><table width="100%" border="0" cellpadding="1" cellspacing="0">
			<tr>
				<th>Parameter</th>
	';
	
	foreach($report[dates] as $date){
		$html .= '
			<th>'.substr($date,5,5).'</th>
		';
	}
	
	$html .= '
			</tr>
	';
	
	foreach($report[table] as $parameter=>$parameter_data){
		$html .= '
			<tr>
				<td class="text_values">'.$parameter.'</td>
		';
		
		foreach($report[dates] as $date){
			$html .= '
				<td class="values">'.number_format($report[table][$parameter][$date],0).'</td>
			';
		}
		
		$html .= '
			</tr>
		';
	}
	
	$html .= '
			</table></td>
		</tr>
		<tr>
			<td style="height:10px; verticle-align:middle;"></td>
		</tr>
		<!--<tr>
			<td>
				Please be advised that this IVR source does not include IVR calls received for 129. As such Total Calls transfered to Call centre can be less than total calls received by Call centre systems.
			</td>
		</tr>-->
		<tr>
			<td style="height:10px; verticle-align:middle;"></td>
		</tr>
	';
	
	foreach($report[graph] as $graph_tile=>$graph_id){
		$html .= '
			<tr>
				<th style="height:20px; verticle-align:middle;">'.$graph_tile.'</th>
			</tr>
			<tr>
				<td>
					<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$graph_id.'.jpg" />
				</td>
			</tr>
			<tr>
				<td style="height:15px;"></td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}

?>