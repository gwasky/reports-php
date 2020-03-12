<?php

function generate_backoffice_flash($use_date){
	
	if($use_date == '') $use_date = date('Y-m-d',strtotime("-1 days"));
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$to = $use_date." 23:59:59";
	$query = "select date_sub('".$use_date."', interval 30 day) as `from`";
	$result = $myquerys->single($query,'wimax');
	$from = $result[from]." 00:00:00";
	
	$report[Resolution_trend][period] = substr($from,0,10).' to '.substr($to,0,10);
	
	$query = "
		SELECT
			left(cases.date_entered,10) as the_date,
			count(*) as num_created
		FROM 
			cases 
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) 
			INNER JOIN accounts ON (cases.account_id=accounts.id) 
		WHERE 
			cases.deleted = '0' AND 
			accounts.deleted = '0' AND 
			cases.date_entered BETWEEN DATE_SUB('".$from."', INTERVAL 3 HOUR) and  DATE_SUB('".$to."', INTERVAL 3 HOUR)
		GROUP BY
			the_date
	";
	
	$lists[wimax_created] = $myquerys->multiple($query,'wimax','the_date');
	
	//echo $query."\n";
	
	$query = "
		SELECT
			left(cases_audit.date_created ,10) as the_date,
			count(*) AS num_resolved,
			avg((UNIX_TIMESTAMP(cases_audit.date_created) - UNIX_TIMESTAMP(cases.date_entered))/3600) as average_resolution_Hrs
		FROM
			cases 
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) 
			INNER JOIN accounts ON (cases.account_id=accounts.id) 
			LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') 
		WHERE 
			cases.deleted = '0' AND 
			accounts.deleted = '0' AND 
			cases_audit.date_created BETWEEN DATE_SUB('".$from."', INTERVAL 3 HOUR) AND DATE_SUB('".$to."', INTERVAL 3 HOUR)
		GROUP BY
			the_date
	";
	
	$lists[wimax_resolved] = $myquerys->multiple($query,'wimax','the_date');
	
	//echo $query."\n";
	
	$query = "
		select
			LEFT(createdon,10) AS the_date,
			count(*) as num_created
		FROM 
			reportscrm 
		WHERE
			reportscrm.troubleticket NOT LIKE 'WPesa : %' AND
			createdon between '".$from."' and '".$to."'
		GROUP BY
			the_date
	";
	
	$lists[gsm_created] = $myquerys->multiple($query,'reportscrm','the_date');
	
	$query = "
		SELECT
			left(caseresolution.actualend,10) as the_date,
			count(*) as num_resolved,
	AVG((UNIX_TIMESTAMP(caseresolution.actualend) - UNIX_TIMESTAMP(reportscrm.createdon))/3600) AS average_resolution_Hrs
		FROM
			caseresolution
			INNER JOIN reportscrm ON caseresolution.casenum = reportscrm.casenum
		WHERE
			reportscrm.troubleticket NOT LIKE 'WPesa : %' AND
			caseresolution.actualend between '".$from."' and '".$to."'
		GROUP BY
			the_date
	";
	
	$lists[gsm_resolved] = $myquerys->multiple($query,'reportscrm','the_date');
	
	//echo $query."\n";
	
	$query = "select date_sub('".substr($use_date,0,7)."-01', interval 6 month) as `from`";
	$result = $myquerys->single($query,'reportscrm');
	$from = $result[from]." 00:00:00";
	$query = "
		SELECT
			COUNT(*) AS NUM
		FROM
			reportscrm
			left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
		WHERE
			reportscrm.troubleticket NOT LIKE 'WPesa : %' AND
			(createdon between '".$from."' AND '".$to."') AND
			(caseresolution.casenum IS NULL OR caseresolution.actualend > '".$to."')
	";
	
	$result = $myquerys->single($query,'reportscrm');
	$report[Open_cases][period] = substr($from,0,10).' to '.substr($to,0,10);
	$report[Open_cases][data][gsm] = $result[NUM];
	$report[Open_cases][data][total] += $result[NUM];
	
	$query = "
		SELECT
			COUNT(status) AS NUM
		FROM
			cases
			INNER JOIN accounts ON (cases.account_id=accounts.id)
			LEFT OUTER JOIN cases_audit ON ( cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') 
		WHERE
			(cases.deleted = '0' AND	accounts.deleted = '0') AND
			(
				cases.date_entered BETWEEN DATE_SUB('".$from."', INTERVAL 3 HOUR) AND  DATE_SUB('".$to."', INTERVAL 3 HOUR) OR
				cases_audit.date_created >  DATE_SUB('".$to."', INTERVAL 3 HOUR)
			)AND
			cases.status != 'Closed'
	";
	
	$result = $myquerys->single($query,'wimax');
	$report[Open_cases][data][wimax] = $result[NUM];
	$report[Open_cases][data][total] += $result[NUM];
	
	/*foreach($lists as $name=>$list){
		echo $name." => ".count($list)."\n";
	}*/
	
	foreach($lists as $casetype_group=>$list){
		$casetype_group_array = explode("_",$casetype_group);
		$casetype = $casetype_group_array[0]; unset($casetype_group_array);
		foreach($list as $date=>$row){
			$dates[strtotime($date)] = $date;
			foreach($row as $key=>$value){
				if($key != 'the_date'){
					$data[$date][$casetype][$key] = $value;
				}
			}
			
			$data[$date][$casetype][total_resolution_hrs] = intval($row[num_resolved]) * intval($row[average_resolution_Hrs]);
		}
	}
	
    /*
	[2012-03-13] => Array
        (
            [wimax] => Array
                (
                    [num_created] => 22
                    [num_resolved] => 16
                    [average_resolution_Hrs] => 12.96823750
					[total_resolution_hrs] => 12.96823750
                )

            [gsm] => Array
                (
                    [num_created] => 184
                    [num_resolved] => 155
                    [average_resolution_Hrs] => 99.43377032
					[total_resolution_hrs] => 12.96823750
                )

        )
	*/
	
	//Order dates from oldest to newest
	asort($dates);
	
	foreach($dates as $date){
		foreach($data[$date] as $case_type=>$case_type_data){
			$graphs["Number of cases by date"]["Cases created"][$date] += intval($case_type_data[num_created]);
			$graphs["Number of cases by date"]["Cases resolved"][$date] += intval($case_type_data[num_resolved]);
			$totals[$date][total_resolution_hrs] += intval($case_type_data[total_resolution_hrs]);
			$graphs["Resolution time by date"]["Avg resolution time Hrs"][$date] = number_format($totals[$date][total_resolution_hrs]/$graphs["Number of cases by date"]["Cases resolved"][$date],1,'.','');
		}
	}
	
	$graph_detail[data]=$graphs["Number of cases by date"];
	$graph_detail[title]='Case Created and Resolved Trend by Day';
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=900;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue', 'purple','yellow','navy','lime');
	$period = $report[Resolution_trend][period];
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graphs][] = array('id'=>$my_graph->Save(),'title'=>$graph_detail[title]." From ".$period);
	
	$graph_detail[data]=$graphs["Resolution time by date"];
	$graph_detail[title]='Case resolution time Trend by Day';
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=900;
	$graph_detail[height]=300;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue', 'purple','yellow','navy','lime');
	$period = $report[Resolution_trend][period];
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graphs][] = array('id'=>$my_graph->Save(),'title'=>$graph_detail[title]." From ".$period);
	
	//ATTACH ANOTHER SET OF DATA
	generate_case_nos_by_day($use_date, $report);

	return display_backoffice_flash($report);
}

function generate_case_nos_by_day($use_date, &$report){
	
	if($use_date == '') $use_date = date('Y-m-d',strtotime("-1 days"));
	
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	$tt_order_query = "
		SELECT 
			reportscrm.troubleticket,
			count(*) as number
		FROM
			reportscrm
		WHERE
			reportscrm.createdon BETWEEN '".substr($use_date,0,7)."-01 00:00:00' AND  '".$use_date." 23:59:59' AND
			reportscrm.troubleticket NOT LIKE 'WPESA%'
		GROUP BY
			troubleticket
		ORDER BY
			number DESC
	";
	
	$list = $myquery->multiple($tt_order_query,'ccba01.reportscrm');
	foreach($list as $row_no=>$row){
		$ordered_tickets[$row_no] = $row[troubleticket];
		if(($row_no + 1) <= 10){
			$report[ticket_translation][$row[troubleticket]] = $row_no+1;
		}
	}
	
	$query = "
		SELECT 
			left(reportscrm.createdon,10) as date_created,
			reportscrm.troubleticket,
			count(*) as number
		FROM
			reportscrm
		WHERE
			reportscrm.createdon BETWEEN '".substr($use_date,0,7)."-01 00:00:00' AND  '".$use_date." 23:59:59' AND
			reportscrm.troubleticket NOT LIKE 'WPESA%'
		GROUP BY
			date_created,troubleticket
	";
	
	$list = $myquery->multiple($query,'ccba01.reportscrm');
	
	foreach($list as $row){
		$report[Cases_created_dates][$row[date_created]] = $row[date_created];
		
		$initial_array[cases_created][$row[troubleticket]][$row[date_created]] = $row[number];
	}
	
	foreach($report[Cases_created_dates] as $date){
		foreach($ordered_tickets as $ttiecket_id=>$tticket){
			if(($ttiecket_id + 1) <= 10){
				$graph["Cases created by Date"][$report[ticket_translation][$tticket]][$date] = intval($initial_array[cases_created][$tticket][$date]);
			}
			
			$report[Cases_created][$tticket][$date] = $initial_array[cases_created][$tticket][$date];
		}
	}
	
	//print_r($graph["Cases created by Date"]); echo "\n\n\n\n";
	//print_r($report[Cases_created]); exit();
	
	$graph_detail[data]=$graph["Cases created by Date"];
	$graph_detail[title]='Top Trouble Tickets created Month to date '.$use_date;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=900;
	$graph_detail[height]=600;
	$graph_detail[legend]=true;
	$period = substr($use_date,0,7)."-01 - ".$use_date;
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graphs][] = array('id'=>$my_graph->Save(),'title'=>$graph_detail[title]);
}

function display_backoffice_flash($report){
	
	if($report[NO_DATA]) exit("No DATA!!! \n");
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
			<tr>
				<th>Open cases from '.$report[Open_cases][period].'</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
						<tr>
							<td class="text_values">GSM Cases</td>
							<td class="values">'.number_format($report[Open_cases][data][gsm],0).'</td>
						</tr>
						<tr>
							<td class="text_values">DATA Cases</td>
							<td class="values">'.number_format($report[Open_cases][data][wimax],0).'</td>
						</tr>
						<tr id="totals">
							<td class="text_values">Total Cases</td>
							<td class="values">'.number_format($report[Open_cases][data][total],0).'</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="height:30px" valign="middle"></td>
			</tr>
	';
	
	foreach($report[graphs] as $graph){
		$html .= '
			<tr>
				<th>'.$graph[title].'</th>
			</tr>
			<tr>'.
				display_generic_graph($graph_id = $graph[id],$with_td=TRUE).'
			</tr>
			<tr>
				<td style="height:30px" valign="middle"></td>
			</tr>
		';
	}
	
	if(count($report[Cases_created]) > 0){
		$html .= '
			<tr>
				<th>Graph Key : Top Trouble Tickets created Month to date</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
						<tr>
							<th class="text_values">Trouble Ticket</th>
							<th class="values">Number on Graph</th>
						</tr>
		';
		
		foreach($report[ticket_translation] as $tticket=>$tticket_no){
			$html .= '
						<tr class="'.row_style(++$i).'">
							<td class="text_values">'.$tticket.'</td>
							<td class="values">'.$tticket_no.'</td>
						</tr>
			';
		}
		
		unset($tticket,$i);
		
		$html .= '
					</table>
				</td>
			</tr>
			<tr>
				<td height="20"></td>
			</tr>
			<tr>
				<th>Number of Cases Created by Trouble Ticket by Date</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
					<tr>
						<th width="18">#</th>
						<th>Trouble Ticket</th>
		';
		
		foreach($report[Cases_created_dates] as $heading_date){
			$html .= '
						<th>'.$heading_date.'</th>
			';
		}
		
		$html .= '
					</tr>
		';
		
		foreach($report[Cases_created] as $tticket=>$tticket_row){
			$html .= '
					<tr class="'.row_style(++$i).'">
						<td class="values">'.$i.'</td>
						<td class="text_values">'.$tticket.'</td>
			';
			
			foreach($tticket_row as $tticket_value){
				$html .= '
						<td class="values">'.number_format($tticket_value,0).'</td>
					';
			}
			
			$html .= '
					</tr>
			';
		}
		
		$html .= '
					</table>
				</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>