<?php
function generate_monthly_csat($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")).' 23:59:59'; } else { $to .= " 23:59:59";}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.smsfeedback');

	$querymonth = "
		SELECT
			LEFT(smsfeedback.sms_evaluation.date_entered,7) AS period,
			IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
			COUNT(smsfeedback.sms_evaluation.text) as number
		FROM
			smsfeedback.sms_evaluation
		WHERE
			smsfeedback.sms_evaluation.date_entered BETWEEN date_format('".$to."' ,'%Y-%m-01 00:00:00') AND '".$to."'
		GROUP BY
			period,
			evaluation_answer
	";
	$rows = $myquery->multiple($querymonth);
	
	foreach($rows as &$row){
		$report[data][$row[period]][$row[evaluation_answer]] += $row[number];
		if($evaluation_answer == ''){
			$report[data][$row[period]][ALL] += $row[number];
			$report[data][$row[period]][score] = ($report[data][$row[period]][Yes]/$report[data][$row[period]][ALL]) * 100;
		}
	}
	$MonthlyScore = $report[data][$row[period]][score];
	
	return number_format($MonthlyScore); 
}


function get_number_of_cases_created($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")).' 23:59:59'; } else { $to .= " 23:59:59";}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba01.reportscrm');
	
	$query = "
			SELECT
				Count(reportscrm.casenum) AS numcases
			FROM
				reportscrm
			WHERE
				reportscrm.createdon BETWEEN date_format('".$to."' ,'%Y-%m-01 00:00:00') AND '".$to."'
			";
	$rows = $myquery->single($query);
	return $rows[numcases];
}

function get_wraups_and_repeats_totals($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")); }
		
	$myquery = new custom_query();
	custom_query::select_db('ccba02.ivrperformance');
	
	$query = "
			SELECT
				SUM(wrapup_repeats.total_wrapups) as total_wp,
				SUM(wrapup_repeats.repeat_wrapups) as repeat_wp
			FROM
				wrapup_repeats
			WHERE
				wrapup_repeats.date_entered BETWEEN date_format('".$to."' ,'%Y-%m-01') AND '".$to."'
	";
	$rows = $myquery->single($query);
	return $rows;
}


function get_cc_quality_score($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")).' 23:59:59'; } else { $to .= " 23:59:59";}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba01.reportscrm');
	
	$query = "
			SELECT
				avg(call_evaluation_scores.final_score_display_fatal) as overallscore
			FROM
				call_evaluation_scores
			WHERE
				call_evaluation_scores.datecreated BETWEEN date_format('".$to."' ,'%Y-%m-01 00:00:00') AND '".$to." 23:59:59'
			";
	$rows = $myquery->single($query);
	
	$returnscore = number_format($rows['overallscore'],2).'%';
	return $returnscore;
}


function get_ccprojections($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")); }
	$projectiondate = date('Y-m',strtotime($to));

	$myquery = new custom_query();
	custom_query::select_db('ccba02.ivrperformance');
	
	$query = "
			SELECT
				callcenter_projections.projection
			FROM
				callcenter_projections
			WHERE
				callcenter_projections.projection_date = '".$projectiondate."-01'
			";
	$rows = $myquery->single($query);

	return $rows['projection'];
}


function generate_cc_flash_report($date_input){
		
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	custom_query::select_db('ivrperformance'); 
	
	if(($date_input=='') or ($date_input == date('Y-m-d'))){
		$date_input = date('Y-m-d',strtotime("-1 days"));
	}
	
	$check_query = "
		SELECT
			date_format(queue.`entrydate`,'%D-%b') as full_date,
			queue.que,
			queue.servicelevel,
			queue.avgcallduration,
			queue.avgspeedofans,
			queue.avgabancallwait,
			calldetail.status,
			calldetail.calls
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between '".$date_input."' AND '".$date_input."'
	";
	
	//echo $check_query." \n\n";
	
	if($myquerys->single($check_query,'')){
		$query = "
			SELECT
				date_format(queue.`entrydate`,'%D-%b') as full_date,
				queue.que,
				queue.servicelevel,
				queue.avgcallduration,
				queue.avgspeedofans,
				queue.avgabancallwait,
				calldetail.status,
				calldetail.calls
			FROM 
				calldetail 
				Inner Join queue ON queue.id = calldetail.id_c 
			WHERE 
				queue.entrydate between date_format('".$date_input."' ,'%Y-%m-01') AND '".$date_input."'
		";
		
		//queue.entrydate between date_format(date_sub(date(now()), interval 1 day) ,'%Y-%m-01') AND date_sub(date(now()), interval 1 day) and
	
		$input_list = $myquerys->multiple($query);
		
		foreach($input_list as $row){
			$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
			$wb[$row[full_date]][$row[que]]['Average Talk Time'] = $row[avgcallduration];
			$wb[$row[full_date]][$row[que]]['Average Answer Speed'] = $row[avgspeedofans];
			$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
			
			$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
			$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
			$wb[$row[full_date]]['All Queues']['avg_ans_speed_call_index'] += ($row[calls] * my_strtotime($row[avgspeedofans]));
			$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
			$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
		}
		
		foreach($wb as $date=>$date_data){
			$report[data]['Prepaid service level'][$date] = number_format($date_data[Prepaid]['Service Level'],2);
			$report[unit]['Prepaid service level'] = '%';
			$report[data]['Prepaid - Calls Recieved'][$date] = number_format($date_data[Prepaid]['Received Calls'],0,'.','');
			$report[unit]['Prepaid - Calls Recieved'] = '';
			$report[data]['Prepaid - Calls Handled'][$date] = number_format($date_data[Prepaid]['Handled Calls'],0,'.','');
			$report[unit]['Prepaid - Calls Handled'] = '';
			$report[data]['Prepaid - Calls Abandoned'][$date] = number_format($date_data[Prepaid]['Abandon Calls'],0,'.','');
			$report[unit]['Prepaid - Calls Abandoned'] = '';
			$report[data]['Prepaid - Average Talk Time'][$date] = $date_data[Prepaid]['Average Talk Time'];
			$report[unit]['Prepaid - Talk Time'] = '';
			$report[data]['Prepaid - Average Answer Speed'][$date] = $date_data[Prepaid]['Average Answer Speed'];
			$report[unit]['Prepaid - Average Answer Speed'] = '';
			
			$report[data]['Franchise service level'][$date] = number_format($date_data[Franchise]['Service Level'],2);
			$report[unit]['Franchise service level'] = '%';
			$report[data]['Franchise - Calls Recieved'][$date] = number_format($date_data[Franchise]['Received Calls'],0,'.','');
			$report[unit]['Franchise - Calls Recieved'] = '';
			$report[data]['Franchise - Calls Handled'][$date] = number_format($date_data[Franchise]['Handled Calls'],0,'.','');
			$report[unit]['Franchise - Calls Handled'] = '';
			$report[data]['Franchise - Calls Abandoned'][$date] = number_format($date_data[Franchise]['Abandon Calls'],0,'.','');
			$report[unit]['Franchise - Calls Abandoned'] = '';
			$report[data]['Franchise - Average Talk Time'][$date] = $date_data[Franchise]['Average Talk Time'];
			$report[unit]['Franchise - Talk Time'] = '';
			$report[data]['Franchise - Average Answer Speed'][$date] = $date_data[Franchise]['Average Answer Speed'];
			$report[unit]['Franchise - Average Answer Speed'] = '';
			
			$report[data]['Wimax service level'][$date] = number_format($date_data[Wimax]['Service Level'],2);
			$report[unit]['Wimax service level'] = '%';
			$report[data]['Wimax - Calls Recieved'][$date] = number_format($date_data[Wimax]['Received Calls'],0,'.','');
			$report[unit]['Wimax - Calls Recieved'] = '';
			$report[data]['Wimax - Calls Handled'][$date] = number_format($date_data[Wimax]['Handled Calls'],0,'.','');
			$report[unit]['Wimax - Calls Handled'] = '';
			$report[data]['Wimax - Calls Abandoned'][$date] = number_format($date_data[Wimax]['Abandon Calls'],0,'.','');
			$report[unit]['Wimax - Calls Abandoned'] = '';
			$report[data]['Wimax - Average Talk Time'][$date] = $date_data[Wimax]['Average Talk Time'];
			$report[unit]['Wimax - Talk Time'] = '';
			$report[data]['Wimax - Average Answer Speed'][$date] = $date_data[Wimax]['Average Answer Speed'];
			$report[unit]['Wimax - Average Answer Speed'] = '';
			
			$report[data]['Non Warid service level'][$date] = number_format($date_data[NonWarid]['Service Level'],2);
			$report[unit]['Non Warid service level'] = '%';
			$report[data]['Non Warid - Calls Recieved'][$date] = number_format($date_data[NonWarid]['Received Calls'],0,'.','');
			$report[unit]['Non Warid - Calls Recieved'] = '';
			$report[data]['Non Warid - Calls Handled'][$date] = number_format($date_data[NonWarid]['Handled Calls'],0,'.','');
			$report[unit]['Non Warid - Calls Handled'] = '';
			$report[data]['Non Warid - Calls Abandoned'][$date] = number_format($date_data[NonWarid]['Abandon Calls'],0,'.','');
			$report[unit]['Non Warid - Calls Abandoned'] = '';
			$report[data]['Non Warid - Average Talk Time'][$date] = $date_data[NonWarid]['Average Talk Time'];
			$report[unit]['Non Warid - Talk Time'] = '';
			$report[data]['Non Warid - Average Answer Speed'][$date] = $date_data[NonWarid]['Average Answer Speed'];
			$report[unit]['Non Warid - Average Answer Speed'] = '';
			
			$report[data]['Warid Pesa service level'][$date] = number_format($date_data[MobileMoney]['Service Level'],2);
			$report[unit]['Warid Pesa service level'] = '%';
			$report[data]['Warid Pesa - Calls Recieved'][$date] = number_format($date_data[MobileMoney]['Received Calls'],0,'.','');
			$report[unit]['Warid Pesa - Calls Recieved'] = '';
			$report[data]['Warid Pesa - Calls Handled'][$date] = number_format($date_data[MobileMoney]['Handled Calls'],0,'.','');
			$report[unit]['Warid Pesa - Calls Handled'] = '';
			$report[data]['Warid Pesa - Calls Abandoned'][$date] = number_format($date_data[MobileMoney]['Abandon Calls'],0,'.','');
			$report[unit]['Warid Pesa - Calls Abandoned'] = '';
			$report[data]['Warid Pesa - Average Talk Time'][$date] = $date_data[MobileMoney]['Average Talk Time'];
			$report[unit]['Warid Pesa - Talk Time'] = '';
			$report[data]['Warid Pesa - Average Answer Speed'][$date] = $date_data[MobileMoney]['Average Answer Speed'];
			$report[unit]['Warid Pesa - Average Answer Speed'] = '';
			
			$report[data]['Postpaid service level'][$date] = number_format($date_data[Postpaid]['Service Level'],2);
			$report[unit]['Postpaid service level'] = '%';
			$report[data]['Postpaid - Calls Recieved'][$date] = number_format($date_data[Postpaid]['Received Calls'],0,'.','');
			$report[unit]['Postpaid - Calls Recieved'] = '';
			$report[data]['Postpaid - Calls Handled'][$date] = number_format($date_data[Postpaid]['Handled Calls'],0,'.','');
			$report[unit]['Postpaid - Calls Handled'] = '';
			$report[data]['Postpaid - Calls Abandoned'][$date] = number_format($date_data[Postpaid]['Abandon Calls'],0,'.','');
			$report[unit]['Postpaid - Calls Abandoned'] = '';
			$report[data]['Postpaid - Average Talk Time'][$date] = $date_data[Postpaid]['Average Talk Time'];
			$report[unit]['Postpaid - Talk Time'] = '';
			$report[data]['Postpaid - Average Answer Speed'][$date] = $date_data[Postpaid]['Average Answer Speed'];
			$report[unit]['Postpaid - Average Answer Speed'] = '';
			
			$report[data]['Overall Service level'][$date] = number_format(($date_data['All Queues']['service_level_call_index']/$date_data['All Queues']['Total Calls']),2);
			$report[unit]['Overall Service level'] = '%';
			$report[data]['Overall Calls Recieved'][$date] = number_format($date_data['All Queues']['Total Calls Received'],0,'.','');
			$report[unit]['Overall Calls Recieved'] = '';
			$report[data]['Overall Calls Handled'][$date] = number_format($date_data['All Queues']['Total Calls Handled'],0,'.','');
			$report[unit]['Overall Calls Handled'] = '';
			$report[data]['Overall Calls Abandoned'][$date] = number_format($date_data['All Queues']['Total Calls Abandon'],0,'.','');
			$report[unit]['Overall Calls Abandoned'] = '';
			$report[data]['Overall Average Talk Time'][$date] = timetostr($date_data['All Queues']['avg_call_duration_call_index']/$date_data['All Queues']['Total Calls']);
			$report[unit]['Overall Average Talk Time'] = '';
			$report[data]['Overall Average Answer Speed'][$date] = timetostr($date_data['All Queues']['avg_ans_speed_call_index']/$date_data['All Queues']['Total Calls']);
			$report[unit]['Overall Average Answer Speed'] = '';
		
			$report[mtd]['Overall Service level'][] = number_format(($date_data['All Queues']['service_level_call_index']/$date_data['All Queues']['Total Calls']),0);
			$report[mtd]['Calls Received'] += number_format($date_data['All Queues']['Total Calls Received'],0,'.','');
			$report[mtd]['Calls Abandon'] += number_format($date_data['All Queues']['Total Calls Abandon'],0,'.','');
		}
		
		$report[mtd]['SL'] = number_format((array_sum($report[mtd]['Overall Service level'])/count($report[mtd]['Overall Service level'])),2).'%';
		$report[mtd]['PER_AC'] = number_format((($report[mtd]['Calls Abandon']/$report[mtd]['Calls Received'])*100),2).'%';
		$report[mtd]['CSAT'] = number_format(generate_monthly_csat($date_input),2).'%';
		$wrapup_totals = get_wraups_and_repeats_totals($date_input);
		$report[mtd]['FIRSTCALLRES'] = number_format(((($wrapup_totals['total_wp'] - $wrapup_totals['repeat_wp']) - get_number_of_cases_created($date_input))/$wrapup_totals['total_wp']*100),2).'%';
		//print get_cc_quality_score($date_input);
		$report[mtd]['QLT'] = get_cc_quality_score($date_input);
		
		$number_of_days_mtd = ((((strtotime($date_input) - strtotime(date('Y-m',strtotime($date_input)).'-01'))/60)/60)/24)+1;
		$report[mtd]['projected_calls'] = get_ccprojections($date_input);
		$report[mtd]['projected_calls_perday'] = $report[mtd]['projected_calls']/date('t',strtotime($date_input));
		$report[mtd]['Calls_Received_calls_perday'] = number_format($report[mtd]['Calls Received']/$number_of_days_mtd,0);
		$calls_percentage = ($report[mtd]['Calls Received']/$report[mtd]['projected_calls'])*100;
		$calls_percentage_perday = (round((int)str_replace(',','',$report[mtd]['Calls_Received_calls_perday']))/round($report[mtd]['projected_calls_perday']))*100;
		
		$report[mtd]['calls_percentage'] = number_format($calls_percentage,2).'%';		
		$report[mtd]['calls_percentage_perday'] = number_format($calls_percentage_perday,2).'%';
		
		//------------------------------------------------------------------------------------------------------
		//Percentage Calls
		if($report[mtd]['projected_calls'] >= $report[mtd]['Calls Received']){
			$diff = $report[mtd]['projected_calls'] - $report[mtd]['Calls Received'];
			$calls_percentage_diff = ($diff/$report[mtd]['Calls Received'])*100;
			
			if($calls_percentage_diff > 10){
				$report[mtd]['calls_percentage_diff'] = '<span style="color:red; font-weight:bold;">&#9660;'.number_format($calls_percentage_diff,2).'%</span>';
			}else{
				$report[mtd]['calls_percentage_diff'] = '<span style="color:green; font-weight:bold;">&#9660;'.number_format($calls_percentage_diff,2).'%</span>';
			}

		}else{
			$diff = $report[mtd]['Calls Received'] - $report[mtd]['projected_calls'];
			$calls_percentage_diff = ($diff/$report[mtd]['projected_calls'])*100;
			
			if($calls_percentage_diff > 10){
				$report[mtd]['calls_percentage_diff'] = '<span style="color:red; font-weight:bold;">&#9650;'.number_format($calls_percentage_diff,2).'%</span>';
			}else{
				$report[mtd]['calls_percentage_diff'] = '<span style="color:green; font-weight:bold;">&#9650;'.number_format($calls_percentage_diff,2).'%</span>';
			}

		}
		//------------------------------------------------------------------------------------------------------
		
		//------------------------------------------------------------------------------------------------------
		//Percentage Calls Per Day
		$Calls_Received_calls_perday = $report[mtd]['Calls Received']/$number_of_days_mtd;
		if($report[mtd]['projected_calls_perday'] >= $Calls_Received_calls_perday){
			//echo 'Check 1 -> ';
			$diff = $report[mtd]['projected_calls_perday'] - $Calls_Received_calls_perday;
			$calls_percentage_diff_perday = ($diff/$Calls_Received_calls_perday)*100;
			
			if($calls_percentage_diff_perday > 10){
				$report[mtd]['calls_percentage_diff_perday'] = '<span style="color:red; font-weight:bold;">&#9660;'.number_format($calls_percentage_diff_perday,2).'%</span>';
				//echo 'Check 1:1 -> ';
			}else{
				$report[mtd]['calls_percentage_diff_perday'] = '<span style="color:green; font-weight:bold;">&#9660;'.number_format($calls_percentage_diff_perday,2).'%</span>';
				//echo 'Check 1:2 -> ';
			}
			
		}else{
			//echo 'Check 2 -> ';
			$diff = $Calls_Received_calls_perday - $report[mtd]['projected_calls_perday'];
			$calls_percentage_diff_perday = ($diff/$report[mtd]['projected_calls_perday'])*100;
			
			if($calls_percentage_diff_perday > 10){
				$report[mtd]['calls_percentage_diff_perday'] = '<span style="color:red; font-weight:bold;">&#9650;'.number_format($calls_percentage_diff_perday,2).'%</span>';
				//echo 'Check 2:1 -> ';
			}else{
				$report[mtd]['calls_percentage_diff_perday'] = '<span style="color:green; font-weight:bold;">&#9650;'.number_format($calls_percentage_diff_perday,2).'%</span>';
				//echo 'Check 2:2 -> ';
			}
		}

		//------------------------------------------------------------------------------------------------------
		
		
		//$report[mtd]['Calls Received']/$number_of_days_mtd;
		foreach($report[data]['Overall Calls Recieved'] as $dates => $calls_received){
			$projected_perday = round($report[mtd]['projected_calls']/date('t',strtotime($date_input)));
			$calls_percentage_day = ($calls_received/$projected_perday)*100;
			$report[data]['projected_calls_perday'][$dates] = $report[mtd]['projected_calls_perday'];
			
			$report[mtd]['calls_percentage_day'][$dates] = number_format($calls_percentage_day ,2).'%';

			if($calls_received >= $projected_perday){
				//echo 'check 1 -> ';
				$diff = $calls_received - round($projected_perday);
				$calls_percentage_day_diff = ($diff/$projected_perday)*100;
				if($calls_percentage_day_diff > 10){
					$report[mtd]['calls_percentage_day_diff'][$dates] = '<span style="color:red; font-weight:bold;">&#9650;'.number_format($calls_percentage_day_diff ,2).'%</span>';
					//echo 'check 1:1 -> ';
				}else{
					$report[mtd]['calls_percentage_day_diff'][$dates] = '<span style="color:green; font-weight:bold;">&#9650;'.number_format($calls_percentage_day_diff ,2).'%</span>';
					//echo 'check 1:2 -> ';
				}
				
			}else{
				//echo 'check 2 -> ';
				$diff = $projected_perday - $calls_received;
				$calls_percentage_day_diff = ($diff/$calls_received)*100;
				
				if($calls_percentage_day_diff > 10){
					$report[mtd]['calls_percentage_day_diff'][$dates] = '<span style="color:red; font-weight:bold;">&#9660;'.number_format($calls_percentage_day_diff ,2).'%</span>';
					//echo 'check 2:1 -> ';
				}else{
					$report[mtd]['calls_percentage_day_diff'][$dates] = '<span style="color:green; font-weight:bold;">&#9660;'.number_format($calls_percentage_day_diff ,2).'%</span>';
					//echo 'check 2:2 -> ';
				}
				
			}
		}
		
		
		
		$graph_detail[data]['Actual Call Per Day']=$report[data]['Overall Calls Recieved'];
		$graph_detail[data]['Projected calls Per Day']=$report[data]['projected_calls_perday'];
		
		$graph_detail[title]='Number of Calls Per Day Trend '.date_format(date_create($date_input),'F Y');
		$graph_detail[display_title]=false;
		$graph_detail[legend]=true;
		$graph_detail[line_graph]=true;
		$graph_detail[bar_graph]=false;
		$graph_detail[set_data_points]=true;
		$graph_detail[set_data_values]=false;
		$graph_detail[width]=850;
		$graph_detail[height]=450;
		
		$my_graph->graph($title=$graph_detail[title], $period=date_format(date_create($date_input),'Y-m-')."01 to ".$date_input, $data=$graph_detail);
		custom_query::select_db('graphing');
		$report[graph]['Number of Calls Per Day Trend'] = $my_graph->Save();
		
		
		
		
		unset($my_graph->id,$graph_detail);
		
		$graph_detail[data]['Prepaid']=$report[data]['Prepaid service level'];
		$graph_detail[data]['Franchise']=$report[data]['Franchise service level'];
		$graph_detail[data]['Wimax']=$report[data]['Wimax service level'];
		$graph_detail[data]['Non Warid']=$report[data]['Non Warid service level'];
		$graph_detail[data]['Warid Pesa']=$report[data]['Warid Pesa service level'];
		$graph_detail[data]['Postpaid']=$report[data]['Postpaid service level'];
		
		$graph_detail[data]['Overall Service level']=$report[data]['Overall Service level'];
		$graph_detail[title]='Call Centre Service level trend '.date_format(date_create($date_input),'F Y');
		$graph_detail[display_title]=false;
		$graph_detail[legend]=true;
		$graph_detail[line_graph]=true;
		$graph_detail[bar_graph]=false;
		$graph_detail[set_data_points]=true;
		$graph_detail[set_data_values]=false;
		$graph_detail[width]=850;
		$graph_detail[height]=470;
		
		$my_graph->graph($title=$graph_detail[title], $period=date_format(date_create($date_input),'Y-m-')."01 to ".$date_input, $data=$graph_detail);
		custom_query::select_db('graphing');
		$report[graph]['Call Centre Service Level Trend'] = $my_graph->Save();
		
		unset($my_graph->id,$graph_detail);
		$graph_detail[data]['Prepaid']=$report[data]['Prepaid - Calls Handled'];
		$graph_detail[data]['Franchise']=$report[data]['Franchise - Calls Handled'];
		$graph_detail[data]['Wimax']=$report[data]['Wimax - Calls Handled'];
		$graph_detail[data]['Non Warid']=$report[data]['Non Warid - Calls Handled'];
		$graph_detail[data]['Warid Pesa']=$report[data]['Warid Pesa - Calls Handled'];
		$graph_detail[data]['Postpaid']=$report[data]['Postpaid - Calls Handled'];
		$graph_detail[data]['Overall']=$report[data]['Overall Calls Handled'];
		
		$graph_detail[title]='Call Centre Handled calls trend '.date_format(date_create($date_input),'F Y');
		$graph_detail[display_title]=false;
		$graph_detail[legend]=true;
		$graph_detail[line_graph]=true;
		$graph_detail[bar_graph]=false;
		$graph_detail[set_data_points]=true;
		$graph_detail[set_data_values]=false;
		$graph_detail[width]=850;
		$graph_detail[height]=470;
		
		$my_graph->graph($title=$graph_detail[title], $period=date_format(date_create($date_input),'Y-m-')."01 to ".$date_input, $data=$graph_detail);
		custom_query::select_db('graphing');
		$report[graph]['Call Centre Handled Calls Trend'] = $my_graph->Save();
		
		return $report;
	}else{
		sendHTMLemail($to='ccbusinessanalysis@waridtel.co.ug',$bcc,$message='There is no data on this date ['.$date_input.'].<br> Report run has been cancelled ...',$subject='Contact Centre Flash report - ERROR',$from='CCREPORTS <ccnotify@waridtel.co.ug>');
		exit('Exiting due to no data on date ['.$date_input.']');
	}
}

function get_color_code($val, $con_target, $condition){
$val = (int) rtrim($val,'%');
if($condition == '=='){
	if($val == $con_target){ $color = '#00FF00'; }else{ $color = '#FF0000'; }
}elseif($condition == '<='){
	if($val <= $con_target){ $color = '#00FF00'; }else{ $color = '#FF0000'; }
}elseif($condition == '>='){
	if($val >= $con_target){ $color = '#00FF00'; }else{ $color = '#FF0000'; }
}elseif($condition == '>'){
	if($val > $con_target){ $color = '#00FF00'; }else{ $color = '#FF0000'; }
}elseif($condition == '<'){
	if($val < $con_target){ $color = '#00FF00'; }else{ $color = '#FF0000'; }
}
	
	return $color;
}

function display_cc_flash_report($report){
	
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	font-family:Calibri, Verdana, Geneva, sans-serif;
	font-size:11px;
}

th,td{
	border-bottom:#333333 1px solid; border-right:#333333 1px solid; padding:2px;
}

td{
	font-size:100%;
}

th{
	white-space:nowrap;
	font-size:100%;
	vertical-align:middle;
	text-align:left;
	font-weight:bold;
}
	
.top_th{
	background-color:#FF0000;color:#FFFFFF;
}

.row {
	color:#000000; border-top:#333333 1px solid; border-left:#333333 1px solid; border-right:#333333 1px solid;
}

.row_title {
	color:#FFFFFF; background-color:#00F;font-weight:bold;
}

.value{
	text-align:right;	
}

</style>
</head>
<body>
';
		
	$html .= '<table border="0" cellspacing="0" cellpadding="0" width="499">
			  <tr class="row">
				<td width="20" bgcolor="#FF0000">&nbsp;</td>
				<td>Below Contractual Target</td>
			  </tr>
			  <tr class="row">
				<td bgcolor="#00FF00">&nbsp;</td>
				<td>Above Or Meets Contractual Target</td>
			  </tr>
			</table>
			<br>';
		
	$html .= '<table border="0" cellspacing="0" cellpadding="0" width="499">
			  <tr>
				<td colspan="2"  class="row_title" align="center"><strong>Contractual Targets</strong></td>
				<td width="59"  class="row_title" align="center"><strong>Status</strong></td>
				<td width="100"  class="row_title" align="right"><strong>MTD</strong></td>
			  </tr>
			  <tr class="row">
				<th width="186"><strong>Service Level </strong></th>
				<td width="154">80% in 20s</td>
				<td bgcolor="'.get_color_code($report[mtd]['SL'], 80, '>=').'"></td>
				<td width="159" class="value">'.$report[mtd]['SL'].'</td>
			  </tr>
			  <tr class="row" bgcolor="#CCCCCC">
				<th width="186"><strong>Abandon Level </strong></th>
				<td width="154">< 7%</td>
				<td bgcolor="'.get_color_code($report[mtd]['PER_AC'], 7, '<').'"></td>
				<td width="159" class="value">'.$report[mtd]['PER_AC'].'</td>
			  </tr>
			  <tr class="row">
				<th width="186"><strong>First Call Resolution </strong></th>
				<td width="154">> 95%</td>
				<td bgcolor="'.get_color_code($report[mtd]['FIRSTCALLRES'], 95, '>').'"></td>
				<td width="159" class="value">'.$report[mtd]['FIRSTCALLRES'].'</td>
			  </tr>
			  <tr class="row" bgcolor="#CCCCCC">
				<th width="186"><strong>CSAT</strong></th>
				<td width="154">80%</td>
				<td bgcolor="'.get_color_code($report[mtd]['CSAT'], 80, '>=').'"></td>
				<td width="159" class="value">'.$report[mtd]['CSAT'].'</td>
			  </tr>
			  <tr class="row">
				<th width="186"><strong>Call Quality </strong></th>
				<td width="154">>= 85%</td>
				<td bgcolor="'.get_color_code($report[mtd]['QLT'], 85, '>=').'"></td>
				<td width="159" class="value">'.$report[mtd]['QLT'].'</td>
			  </tr>
			</table>
			<hr>';
	
	
	$html .= '<table width="499" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="row_title" align="center" colspan="3"><strong>Number of calls MTD</strong></td>
			  </tr>
			  <tr>
				<th width="165"></th>
				<th scope="col" class="top_th"><strong>No. of Calls</strong></th>
				<th scope="col" class="top_th"><strong>No. of per day</strong></th>
			  </tr>
			  <tr>
				<th><strong>Projected No. of Calls:</strong></th>
				<td class="value">'.number_format($report[mtd]['projected_calls'],0).'</td>
				<td class="value">'.number_format($report[mtd]['projected_calls_perday'],0).'</td>
			  </tr>
			  <tr class="row" bgcolor="#CCCCCC">
				<th><strong>Actual No. of Calls:</strong></th>
				<td class="value">'.number_format($report[mtd]['Calls Received'],0).'</td>
				<td class="value">'.$report[mtd]['Calls_Received_calls_perday'].'</td>
			  </tr>
			  <tr>
				<th><strong>% Actaul of Projection:</strong></th>
				<td class="value">'.$report[mtd]['calls_percentage'].'</td>
				<td class="value">'.$report[mtd]['calls_percentage_perday'].'</td>
			  </tr>
			  <tr>
				<th><strong>% Over/Below Projection:</strong></th>
				<td class="value">'.$report[mtd]['calls_percentage_diff'].'</td>
				<td class="value">'.$report[mtd]['calls_percentage_diff_perday'].'</td>
			  </tr>
			</table><br>';
			
		
	//Getting the date as tiltes
	$dates = array_keys($report[data]['Prepaid service level']);
		
	$html .= '<table border="0" cellpadding="1" cellspacing="0">
				<tr>
				<th scope="col" class="row_title" width="165"></th>
				';
	foreach($dates as $date){
		$html .= '<th scope="col" class="top_th">'.$date.'</th>
					';
	}
	
	$html .= '</tr>
				<tr class="row">
				<th><strong>Projected No. of Calls:</strong></th>
				';
	foreach($dates as $date){
		$html .= '<td class="value">'.number_format($report[mtd]['projected_calls_perday'],0).'</td>
				';
	}
	$html .= '</tr>
				<tr class="row" bgcolor="#CCCCCC">
				<th><strong>Actual No. of Calls:</strong></th>
				';
	foreach($dates as $date){
		$html .= '<td class="value">'.number_format($report[data]['Overall Calls Recieved'][$date],0).'</td>
				';
	}
	$html .= '</tr class="row">
				<tr>
				<th><strong>% Actaul of Projection:</strong></th>
				';
	foreach($dates as $date){
		$html .= '<td class="value">'.$report[mtd]['calls_percentage_day'][$date].'</td>';
	}
	$html .= '</tr class="row">
				<tr>
				<th><strong>% Over/Below projection:</strong></th>
				';
	foreach($dates as $date){
		$html .= '<td class="value">'.$report[mtd]['calls_percentage_day_diff'][$date].'</td>';
	}
	$html .= '</tr class="row">
				<tr>
				<td height="8" style="border-right:#fff;"></td>
				';
	foreach($dates as $date){
		$html .= '<td class="value" style="border-right:#fff;"></td>';
	}
	$html .= '</tr>
			<!--</table>
			<hr>-->';
	//$html .= '<table width="100%" border="0" cellpadding="1" cellspacing="0">';
	
	//Getting the date as tiltes
	//$dates = array_keys($report[data]['Prepaid service level']);
	$html .= '
		<tr>
			<th scope="col" class="row_title">Call Centre Daily Flash Report</th>
	';
	
	foreach($dates as $date){
		$html .= '
			<th scope="col" class="top_th">'.$date.'</th>
		';
	}
	
	$html .= '
		</tr>
	';

	foreach($report[data] as $row_title=>$row_data){
		$color[0] = '';
		$color[1] = '#CCCCCC';
		
		$html .= '
		  <tr class="row" bgcolor="'.$color[(++$countnum-1)%2].'">
			<th scope="col">'.$row_title.'</th>
		';
		
		foreach($dates as $date){
			if(intval($row_data[$date]) > 0){ $row_data[$date] = number_format($row_data[$date],0); }
			$html .= '
			<td class="value">'.$row_data[$date].''.$report[unit][$row_title].'</td>
			';
		}
		
		$html .= '
		  </tr>
		';
	}
	
	$html .= '
		</table>
		<table>
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
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact ccbusinessanalysis@waridtel.co.ug.</p>
</body>
</html>
	';
	
	return $html;
}

?>