<?php

//error_reporting(E_ALL);
//error_reporting(E_PARSE |  E_WARNING | E_ERROR);
//$yesterday = '2010-08-15';
require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
$root_dir = '/srv/www/htdocs/reports/';
require_once('lib.html.php');


function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if($from==''){
		$from = 'CC REPORTS <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	if($bcc){
		$headers .= "BCC: ".$bcc." \r\n";
	}
    
	mail($to,$subject,$message,$headers);
}

function generate_conversion_rate_report()
{
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "SELECT lead_conversion.month as months,lead_conversion.leads_created AS created,
					lead_conversion.leads_converted AS converted,
					lead_conversion.conversion_rate AS conversion_rate
					FROM lead_conversion
					ORDER BY lead_conversion.month ASC";
	custom_query::select_db('trend_analysis');
	$input_list = $myquerys->multiple($query);
	foreach($input_list as $row)
	{
		$Info[$row[months]]['Leads Created'] = $row[created];
		$Info[$row[months]]['Leads Converted'] = $row[converted];
		$Info[$row[months]]['Conversion Rate'] = $row[conversion_rate];
	}
	foreach($Info as $month=>$counts)
	{
		//echo $month.'----------'.var_dump($counts).'<br>';
		$report[data]['Leads Created'][$month] = $counts['Leads Created'] ;
		$report[data]['Leads Coverted'][$month] = $counts['Leads Converted'] ;
		$report[data]['Conversion Rate'][$month] = $counts['Conversion Rate'] ;
		
	}	
	//print_r($report[data]);
	$graph_detail[data]=$report[data];
	$graph_detail[title]='Lead Conversion Rate trend '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	//print_r($graph_detail);
	$current_month = date('m'); 
	$current_year = date('Y');
	$period=$current_year.'-0'.($current_month-6).'-'.'01'.' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	return $report;
	
}

function generate_accounts_cases_report()
{
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "SELECT
    accounts_cases.DAY as an_date
    , accounts_cases.accounts as accounts
    , accounts_cases.cases as cases,
	accounts_cases.accounts/accounts_cases.cases as ratio
	FROM
    trend_analysis.accounts_cases
	WHERE date_sub(date(now()), interval 6 day) AND date_sub(date(now()), interval 1 day) 
	ORDER BY accounts_cases.DAY ASC";
	custom_query::select_db('trend_analysis');
	$input_list = $myquerys->multiple($query);
	foreach($input_list as $row)
	{
		$AccountInfo[$row[an_date]]['Accounts'] = $row[accounts];
		$AccountInfo[$row[an_date]]['Cases'] = $row[cases];
		$AccountInfo[$row[an_date]]['ratio'] = $row[ratio];
	}
	foreach($AccountInfo as $date=>$counts)
	{
		//echo $month.'----------'.var_dump($counts).'<br>';
		$report[data]['Accounts'][$date] = $counts['Accounts'] ;
		$report[data]['Cases'][$date] = $counts['Cases'] ;
		$report[data]['Ratio'][$date] = round($counts['ratio'],2) ;
		
	}	
	print_r($report[data]);
	$graph_detail[data]=$report[data];
	$graph_detail[title]='Accounts to Cases Ratio trends '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	print_r($graph_detail);
	$period= date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	return $report;
	
}


function generate_pakalast__report()
{
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "SELECT
			left(reportsphonecalls.createdon,10) as created_on,
			count(case when subsubcategory.subject_type = 'Negative Feedback' then 1 else null end) as Complaints,
			count(case when subsubcategory.subject_type = 'Inquiry' || subsubcategory.subject_type = '' then 1 else null end) as Inquiry
			FROM
			reportsphonecalls
 			INNER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) 
			AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
			subsubcategory.subject_status = 'active' and reportsphonecalls.wrapupsubcat = 'pakalast' AND 
			left(reportsphonecalls.createdon,10) between date_sub(date(now()), interval 6 day) AND date_sub(date(now()), interval 1 day) 
			group by created_on ASC";
	custom_query::select_db('reportscrm');
	$input_list = $myquerys->multiple($query);
	foreach($input_list as $row)
	{
		$AccountInfo[$row[created_on]]['Complaints'] = $row[Complaints];
		$AccountInfo[$row[created_on]]['Inquiries'] = $row[Inquiry];
	}
	foreach($AccountInfo as $date=>$counts)
	{
		$report[data]['Complaints'][$date] = $counts['Complaints'] ;
		$report[data]['Inquiries'][$date] = $counts['Complaints'] ;
		
	}	
	$graph_detail[data]=$report[data];
	$graph_detail[title]='Pakalast Trends Graph Report '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	
	$graph_detail[line_colors]=array('red','black','green','blue');
	$period= date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	return $report;
	
}


function generate_repeat_calls_call_status($yesterday)
{
	//$yesterday = $enddate = date("Y-m-d", strtotime("-1 days"));
		$myquerys = new custom_query();
		$query = "SELECT
					sv_repeat_wraups.name,
					sv_repeat_wraups.call_status as call_status,
					sv_repeat_wraups.dob as dob,
					sv_repeat_wraups.customer_name as customer_name,
					sv_repeat_wraups.customer_satisfaction as customer_sat,
					sv_repeat_wraups.repeat_call_date as call_date,
					sv_repeat_wraups.number_of_repeat_calls_per_cal,
					sv_repeat_wraups.repeat_wrapup_subject as wrapup_subject,
					sv_repeat_wraups.activated_activities as profiles,
					sv_repeat_wraups.agent as agent,
					sv_repeat_wraups.number_of_repeat_calls_per_cal as agent_repeat_calls,
					sv_repeat_wraups.compliments as compliment,
					sv_repeat_wraups.reason_for_repeat_call as reason_for_repeat_call
					FROM
					sv_repeat_wraups 
					WHERE sv_repeat_wraups.call_status != 'Not Yet Called' and 
					sv_repeat_wraups.repeat_call_date >= '$yesterday' and sv_repeat_wraups.repeat_call_date <= '$yesterday'";
					echo $query;
		custom_query::select_db('survey');
		$repeat_data = $myquerys->multiple($query);
		foreach($repeat_data as $row){
		$row[profiles] = str_replace('^','',trim($row[profiles]));
		++$data[callstatus][$row[call_status]];
		++$data[satisfaction][$row[customer_sat]];
		++$data[wrapupsubject][$row[wrapup_subject]];
		++$data[wrapupprofiles][$row[profiles]];
		++$data[Compliments][$row[compliment]];
		++$data[ReasonForRepeatCall][$row[reason_for_repeat_call]];
		$data[agent_repeat_calls][$row[agent]] += $row[agent_repeat_calls];
		}
		arsort($data[agent_repeat_calls]);
		//print_r($data);
		return display_repeat_calls_call_status($data);
		}
	
function generate_callstatus_pie($yesterday){
	$my_graph = new dbgraph();
	$myquerys = new custom_query();
		$query = "SELECT
					sv_repeat_wraups.name,
					sv_repeat_wraups.call_status as call_status,
					sv_repeat_wraups.dob as dob,
					sv_repeat_wraups.customer_name as customer_name,
					sv_repeat_wraups.customer_satisfaction as customer_sat,
					sv_repeat_wraups.repeat_call_date as call_date,
					sv_repeat_wraups.number_of_repeat_calls_per_cal,
					sv_repeat_wraups.repeat_wrapup_subject as wrapup_subject
					FROM
					sv_repeat_wraups where sv_repeat_wraups.call_status != 'Not Yet Called' and 
					sv_repeat_wraups.repeat_call_date >= '$yesterday' and sv_repeat_wraups.repeat_call_date <= '$yesterday'";
		custom_query::select_db('survey');
		$repeat_data = $myquerys->multiple($query);
		foreach($repeat_data as $row){
		++$data[callstatus][$row[call_status]];
		}
		$graph_detail[data]=$data;
		$graph_detail[title]='Call Status '.$yesterday;
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period= $yesterday;
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;
}

function generate_customer_satisfaction_pie($yesterday){
//$yesterday = $enddate = date("Y-m-d", strtotime("-1 days"));
	$my_graph = new dbgraph();
	$myquerys = new custom_query();
		$query = "SELECT
					sv_repeat_wraups.name,
					sv_repeat_wraups.call_status as call_status,
					sv_repeat_wraups.dob as dob,
					sv_repeat_wraups.customer_name as customer_name,
					sv_repeat_wraups.customer_satisfaction as customer_sat,
					sv_repeat_wraups.repeat_call_date as call_date,
					sv_repeat_wraups.number_of_repeat_calls_per_cal,
					sv_repeat_wraups.repeat_wrapup_subject as wrapup_subject
					FROM
					sv_repeat_wraups where sv_repeat_wraups.call_status != 'Not Yet Called' and 
					sv_repeat_wraups.repeat_call_date >= '$yesterday' and sv_repeat_wraups.repeat_call_date <= '$yesterday'";
		custom_query::select_db('survey');
		$repeat_data = $myquerys->multiple($query);
		foreach($repeat_data as $row){
		++$data[satisfaction][$row[customer_sat]];
		}
		$graph_detail[width]=1000;
		$graph_detail[height]=700;
		$graph_detail[data]=$data;
		$graph_detail[title]='Customer Satisfaction '.$yesterday;
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period= $yesterday;
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;
}

function generate_customer_profile_pie($yesterday){
	$my_graph = new dbgraph();
	$myquerys = new custom_query();
		$query = "SELECT
					sv_repeat_wraups.name,
					sv_repeat_wraups.call_status as call_status,
					sv_repeat_wraups.dob as dob,
					sv_repeat_wraups.customer_name as customer_name,
					sv_repeat_wraups.customer_satisfaction as customer_sat,
					sv_repeat_wraups.repeat_call_date as call_date,
					sv_repeat_wraups.number_of_repeat_calls_per_cal,
					sv_repeat_wraups.repeat_wrapup_subject as wrapup_subject,
					sv_repeat_wraups.activated_activities as profiles
					FROM
					sv_repeat_wraups 
					WHERE sv_repeat_wraups.call_status != 'Not Yet Called' and 
					sv_repeat_wraups.repeat_call_date >= '$yesterday' and sv_repeat_wraups.repeat_call_date <= '$yesterday'";
		custom_query::select_db('survey');
		$repeat_data = $myquerys->multiple($query);
		foreach($repeat_data as $row){
		$row[profiles] = str_replace('^','',trim($row[profiles]));
		++$data[wrapupprofiles][$row[profiles]];
		}
		$graph_detail[width]=1000;
		$graph_detail[height]=700;
		$graph_detail[data]=$data;
		$graph_detail[title]='Customer Profile '.$yesterday;
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period= $yesterday;
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;
}
//echo generate_repeat_calls_call_status($yesterday);
//generate_customer_profile_pie($yesterday);

function generate_simlock_trends(){
			$my_graph = new dbgraph();
			$myquerys = new custom_query();
			$query = "
						SELECT
						date_format(reportsphonecalls.createdon,'%Y-%m-%d') as created_on,
						count(reportsphonecalls.subject) as counts
						FROM
						reportsphonecalls
						where reportsphonecalls.subject = 'My simcard is locked because of recharging' 
						AND
						left(reportsphonecalls.createdon,10) between date_sub(date(now()), interval 14 day) 
						AND date_sub(date(now()), interval 1 day)
						group by date_format(reportsphonecalls.createdon,'%Y-%m-%d')";
				custom_query::select_db('reportscrm');
				$input_list = $myquerys->multiple($query);
				foreach($input_list as $row){
					$data['Sim Lock Counts'][$row[created_on]] = $row[counts];		
				}
				$graph_detail[data]=$data;
				$graph_detail[title]='Sim Lock Wrap up Trends Graph Report for the period '.date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
					$graph_detail[line_graph]=true;
					$graph_detail[bar_graph]=false;
					$graph_detail[set_data_points]=true;
					$graph_detail[width]=800;
					$graph_detail[height]=600;
					$graph_detail[type] = 'pie_chart';
					$graph_detail[legend]=true;
					$graph_detail[line_colors]=array('red','black','green','blue');
					$period= date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
					$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
					custom_query::select_db('graphing');
					$report[graph] = $my_graph->Save();
					return $report;
			
}
function generate_cpc_trends(){
		$my_graph = new dbgraph();
		$myquerys = new custom_query();
		$query = "
				SELECT 
				date_format(queue.entrydate,'%Y-%m') as month,
				calldetail.status as call_status, 
				sum(calldetail.calls) as total_calls,
				sum(subscount.active_subs) as active_subs
				FROM calldetail 
				Inner Join queue ON queue.id = calldetail.id_c
				inner Join subscount ON subscount.`day` = queue.entrydate
				WHERE 
				left(queue.entrydate,10) between date_sub(date(now()), interval 6 month) 
						AND date_sub(date(now()), interval 1 day)
				group by date_format(queue.entrydate,'%Y-%m'),calldetail.status";
		custom_query::select_db('ivrperformance');
		$cpc_data = $myquerys->multiple($query);
		foreach($cpc_data as $row){
			$query_days = "select count(distinct day) as num_days from subscount where date_format(day,'%Y-%m') = '$row[month]'";
			custom_query::select_db('ivrperformance');
			$days_data = $myquerys->single($query_days);
			$no_days =  $days_data[num_days];
			$avg_active_subs = round($row[active_subs]/$no_days,2);
			$row[cpc] = round($row[total_calls]/$avg_active_subs,7);
			$data[CPC][$row[call_status]][$row[month]] = $row[cpc];
			//$data[]
		}
		$graph_detail[data]=$data[CPC];
				$graph_detail[title]='CPC Trends for Received, Handled and Abandoned. Trends Graph Report for the period '.date("Y-m-d", strtotime("-6 months")).' to '.date('Y-m-d');
					$graph_detail[line_graph]=true;
					$graph_detail[bar_graph]=false;
					$graph_detail[set_data_points]=true;
					$graph_detail[width]=1000;
					$graph_detail[height]=1000;
					$graph_detail[type] = 'line';
					$graph_detail[legend]=true;
					$graph_detail[line_colors]=array('red','black','green','blue');
					$period= date("Y-m-d", strtotime("-6 months")).' to '.date('Y-m-d');
					$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
					custom_query::select_db('graphing');
					$report[graph] = $my_graph->Save();
					return $report;
}

function generate_self_care_report()
{
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "SELECT
		left(reportsphonecalls.createdon,10) as createdon,
		reportsphonecalls.wrapupsubcat as wrap_up_cat,
		COUNT(reportsphonecalls.wrapupsubcat) as wrap_count
		FROM
		reportsphonecalls
		WHERE
		left(reportsphonecalls.createdon,10) between date_sub(date(now()), interval 6 day) AND date_sub(date(now()), interval 1 day) AND
		reportsphonecalls.wrapupsubcat IN ('KAWA','Pakalast','Kankolera','GPRS Data Packages','Mooo SMS','Profile Change','Ringback Tunes')
		group by reportsphonecalls.wrapupsubcat,left(reportsphonecalls.createdon,10) ASC;";
	custom_query::select_db('reportscrm');
	$input_list = $myquerys->multiple($query);
	foreach($input_list as $row)
	{
		$Info[$row[createdon]][$row[wrap_up_cat]] = $row[wrap_count];
		
	}
	foreach($Info as $date=>$wrap_ups){
			foreach($wrap_ups as $k=>$v){
				$data[$k][$date] = $v;
			}
	}

	$graph_detail[data]=$data;
	//$graph_detail[title]='Trend Analysis of Inquiries Made in C Contact Center and are on the Self Care USSD Menu'.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=1000;
	$graph_detail[height]=1000;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue','yellow','navy','maroon');
	//print_r($graph_detail);
	$current_month = date('m'); 
	$current_year = date('Y');
	$period=$current_year.'-0'.($current_month-6).'-'.'01'.' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	return $report;
}
//echo generate_self_care_report();

?>