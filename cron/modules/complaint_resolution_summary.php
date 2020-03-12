<?php

function generate_complaint_resolution_summary($use_date){
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			left(reportscrm.createdon,7) as month_created,
			date(reportscrm.createdon) as date_created,
			if(caseresolution.casenum is not null,left(caseresolution.actualend,7),'') as month_resolved,
			if(caseresolution.casenum is not null,date(caseresolution.actualend),'') as date_resolved,
			reportscrm.casenum,
			reportscrm.status,
			if(caseresolution.casenum is null,'Open','Closed') as resolved_status,
			if(caseresolution.casenum is not null,(UNIX_TIMESTAMP(caseresolution.actualend) - UNIX_TIMESTAMP(reportscrm.createdon))/3600,'') as resolution_hours
		FROM
			reportscrm
			left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
		WHERE
			(reportscrm.createdon between DATE_FORMAT('".$use_date."','%Y-%m-01') AND '".$use_date."') or
			(caseresolution.actualend between DATE_FORMAT('".$use_date."','%Y-%m-01') AND '".$use_date."')
	";
	
	custom_query::select_db('reportscrm');
	$gsm_list = $myquerys->multiple($query);
	$report[case_counts][gsm] = count($gsm_list);
	//echo $query."\n";
	
	$query = "
		SELECT
			left(date_add(cases.date_entered, interval 3 hour),7) as month_created,
			date(date_add(cases.date_entered, interval 3 hour)) as date_created,
			left(date_add(cases_audit.date_created, interval 3 hour),7) as month_resolved,
			date(date_add(cases_audit.date_created, interval 3 hour)) as date_resolved,
			cases.case_number as casenum,
			cases.status,
			cases.status as resolved_status,
			(UNIX_TIMESTAMP(cases_audit.date_created)- UNIX_TIMESTAMP(cases.date_entered))/3600 as resolution_hours
		FROM
 			cases
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
			INNER JOIN accounts ON (cases.account_id=accounts.id)
			LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status')
		WHERE 
			accounts.deleted = '0' AND
			cases.deleted = '0' AND
			(
				(cases.date_entered between DATE_FORMAT('".$use_date."','%Y-%m-01') AND '".$use_date."') or
				(cases_audit.date_created between DATE_FORMAT('".$use_date."','%Y-%m-01') AND '".$use_date."')
			)
	";
	
	//echo $query."\n";
	
	custom_query::select_db('wimax');
	$wmx_list = $myquerys->multiple($query);
	$report[case_counts][wimax] = count($wmx_list);
	
	$case_list = $gsm_list;
	foreach($wmx_list as &$row){
		$case_list[] = $row;
		unset($row);
	}
	unset($wmx_list);
	
	if(count($case_list) == 0){ $report[NO_DATA] = TRUE; return display_complaint_resolution_summary($report); }else{ echo "Working on ".count($case_list)." rows \n";}
	
	$report[periods][day] = $use_date;
	$report[periods][month] = substr($use_date,0,7);
	$report[periods][last_month] = date_time_add($date=substr($use_date,0,7)."-01",$value=-1,$mysql_interval='DAY');
	
	//Open Wimax cases
	$query = "
		select
			count(*) as open_cases
		FROM
			cases
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
			INNER JOIN accounts ON (cases.account_id=accounts.id)
			LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status')
		WHERE
			accounts.deleted = '0' and
			cases.date_entered < '".$report[periods][month]."-01' and
			(
				(cases.status = 'Closed' and cases_audit.date_created is not null) or
				(cases.status != 'Closed' and cases_audit.date_created is null)
			) and
			(cases_audit.date_created is null or cases_audit.date_created >= '".$report[periods][month]."-01')
	";
	custom_query::select_db('wimax');
	$result = $myquerys->single($query);
	$report[open_cases][wimax] = $result[open_cases];
	
	//Open GSM cases
	$query = "
		SELECT
			count(*) as open_cases
		FROM
			reportscrm
			left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
		WHERE
			reportscrm.createdon < '".$report[periods][month]."-01' and
			(caseresolution.actualend is null or caseresolution.actualend >= '".$report[periods][month]."-01')
	";
	custom_query::select_db('reportscrm');
	$result = $myquerys->single($query);
	$report[open_cases][gsm] = $result[open_cases];
	
	foreach($case_list as &$case){
		if(substr($case[date_created],0,7) == $report[periods][month]){
			++$report[data][days]['Cases Created'][$case[date_created]];
			if($case[resolved_status] =='Closed'){
				++$report[data][days]['Cases Closed'][$case[date_resolved]];
			}
			++$report[data][summary][substr($use_date,0,7)]['Cases Created'];
		}else{
			++$report[data][days]['Cases Created'][$report[periods][last_month]];
			++$report[data][days]['Cases Closed'][$case[date_resolved]];
		}
	}
	
	//to reorder Cases closed and cacl open cases ...
	$closed_cases_summary = $report[data][days]['Cases Closed']; unset($report[data][days]['Cases Closed']);
	$running_sum = array_sum($report[open_cases]);
	foreach($report[data][days]['Cases Created'] as $date=>$value){
		$report[data][days]['Cases Closed'][$date] = number_format($closed_cases_summary[$date],0,'.',''); unset($closed_cases_summary[$date]);
		$running_sum += ($report[data][days]['Cases Created'][$date] - $report[data][days]['Cases Closed'][$date]);
		$report[Other_data][days]['Cases Open'][$date] = $running_sum;
	}
	unset($closed_cases_summary,$running_sum);
	
	$graph_detail[data]=$report[data][days];
	$graph_detail[title]='Case resolution Trend up to: '.$use_date;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=700;
	$graph_detail[height]=600;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue', 'purple','yellow','navy','lime');
	$period= 'Up to '.$use_date;
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	
	//print_r($report[data][days]);

	return $report;
}

function display_complaint_resolution_summary($report){
	
	if($report[NO_DATA]) exit("No DATA!!! \n");
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
			<tr>
				<th style="background-color:#009; font-size:16px; color:#FFF;" valign="middle">Case resolution Trends from the start of the month to '.$report[periods][day].'</th>
			</tr>
			<tr>
	';
	
	$html .= display_generic_graph($graph_id = $report[graph],$with_td=TRUE);
	
	$html .= '
			</tr>
	';
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>