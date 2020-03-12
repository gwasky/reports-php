<?
require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
LOAD_RESOURCE('SMS');
LOAD_RESOURCE('FTP');
LOAD_RESOURCE('EXCEL');

require_once('/srv/www/htdocs/reports/web/includes/includes.php');
require_once('/srv/www/htdocs/reports/cron/lib.html.php');

function require_dir_nest($directory,$except){
	if(!$except){$except = array();}
	$dir = opendir($directory);
	
	//echo "Dir is ".$directory." and Dir result is "; print_r($dir); echo "<br>";
	while(($file = readdir($dir)) !== false){
		$path = $directory."/{$file}"; 
		//echo  "Path is ".$path." <br>";
		if(!in_array($file,$except)){
			if(!is_dir($path) && (strlen($file) > 2) && (substr(strtolower($file), strlen($file) - 4) === '.php')){
				if(!include_once $path){
					echo "Failed to include ".$path." <br>";
				}
			}elseif(is_dir($path)){
				if(($file != '.') && ($file != '..') && ($file != '_notes')){
					//echo "entering level 2 for [".$path."] <br>";
					require_dir_nest($path,'');
				}
			}
		}
	}
	closedir($dir);
}

function show_mem_usage($unit='M'){
	
	switch($unit){
		case 'k':
		case 'K':
			return number_format(memory_get_usage(TRUE)/(1024),0)." KB";
			break;
		case 'g':
		case 'G':
			return number_format(memory_get_usage(TRUE)/(1024*1024*1024),6)." GB";
			break;
		case 'm':
		case 'M':
		default:
			return number_format(memory_get_usage(TRUE)/(1024*1024),3)." MB";
			break;
	}
}

require_dir_nest('/srv/www/htdocs/reports/cron/modules','');

function my_strtotime($duration){
	return (strtotime($duration) - strtotime('00:00:00'));
}

function timetostr($seconds){
    /*** return value ***/
    $ret = "";

    /*** get the hours ***/
    $hours = intval(intval($seconds) / 3600);
    if($hours > 0)
    {
        $ret .= "$hours:";
    }else{
		$ret .= "00:";
	}
    /*** get the minutes ***/
    $minutes = bcmod((intval($seconds) / 60),60);
    if($hours > 0 || $minutes > 0)
    {
		if($minutes < 10){
			$ret .= "0";
		}
        $ret .= "$minutes:";
    }else{
		$ret .= "00:";
	}
  
    /*** get the seconds ***/
    $seconds = bcmod(intval($seconds),60);
	if($seconds < 10){ $ret .= "0";}
    $ret .= $seconds;

    return $ret;
}

function generate_cases_count(){
	$myquerys = new custom_query();
	custom_query::select_db('reportscrm');
	
	$query_cases_count = "select
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 1 DAY),'%')) as yesterday,
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 2 DAY),'%')) as before_yesterday,
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 3 DAY),'%')) as b_before_yesterday,
		(SELECT  count(*) AS rows   FROM reportscrm   WHERE YEARweek(createdon) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY)) as last_week,
		(SELECT count(*) AS rows   FROM reportscrm   WHERE YEARweek(createdon) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY)) as before_last_week,
		(SELECT count(*) AS rows   FROM reportscrm   WHERE YEARweek(createdon) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY)) as b_before_last_week,
		(SELECT   COUNT(*) AS rows   FROM reportscrm  WHERE  SUBSTRING(createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7)) as last_month,
		(SELECT   COUNT(*) AS rows   FROM reportscrm  WHERE  SUBSTRING(createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7)) as before_last_month,
		(SELECT   COUNT(*) AS rows   FROM reportscrm  WHERE  SUBSTRING(createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7)) as b_before_last_month,
		(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
		(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
		(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
	";
	$case_count_array = $myquerys->multiple($query_cases_count);
	//print_r($case_count_array);
	return 	 display_cases_count($case_count_array);
}

function generate_closed_cases_count(){
	$myquerys = new custom_query();
	custom_query::select_db('reportscrm');
	
	$query_closed_cases_count = "select
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 1 DAY),'%') and status = 'Closed') as yesterday,
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 2 DAY),'%') and status = 'Closed') as before_yesterday,
		(SELECT  count(*) AS rows   FROM reportscrm WHERE createdon LIKE concat(DATE_SUB(CURDATE(),INTERVAL 3 DAY),'%') and status = 'Closed') as b_before_yesterday,
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE YEARweek(caseresolution.actualend) = YEARweek(CURRENT_DATE) AND YEARweek(reportscrm.createdon) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY)) as last_week,
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE YEARweek(caseresolution.actualend) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY)) as before_last_week, 
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE YEARweek(caseresolution.actualend) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY)) as b_before_last_week, 
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE  SUBSTRING(caseresolution.actualend FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND  SUBSTRING(reportscrm.createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7)) as last_month,
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE  SUBSTRING(caseresolution.actualend FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND  SUBSTRING(reportscrm.createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7)) as before_last_month,
		(select count(*) FROM reportscrm Inner Join caseresolution ON reportscrm.casenum = caseresolution.casenum WHERE  SUBSTRING(caseresolution.actualend FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND  SUBSTRING(reportscrm.createdon FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) ) as b_before_last_month,
		(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
		(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
		(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num;";
	$closed_case_count_array = $myquerys->multiple($query_closed_cases_count);
	//print_r($closed_case_count_array);
	return 	 display_closed_cases_count($closed_case_count_array);
}

function generate_offered_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_offered_ivr_count = "select
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND status = 'Received') as yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND status = 'Received') as before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND status = 'Received') as b_before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND status = 'Received') as last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND status = 'Received') as before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND status = 'Received') as b_before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND status = 'Received') as last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND status = 'Received') as before_last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND status = 'Received') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$ivr_offered_count_array = $myquerys->multiple($query_offered_ivr_count);
	print_r($ivr_offered_count_array);
	return 	 display_offered_ivr_count($ivr_offered_count_array);
}

function generate_answered_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_answered_ivr_count = "select
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND status = 'Handled') as yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND status = 'Handled') as before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND status = 'Handled') as b_before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND status = 'Handled') as last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND status = 'Handled') as before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND status = 'Handled') as b_before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND status = 'Handled') as last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND status = 'Handled') as before_last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND status = 'Handled') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$ivr_answered_count_array = $myquerys->multiple($query_answered_ivr_count);
	print_r($ivr_answered_count_array);
	return 	 display_answered_ivr_count($ivr_answered_count_array);
}

function generate_abandon_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_abandon_ivr_count = "select
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND status = 'Abandon') as yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND status = 'Abandon') as before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND status = 'Handled') as b_before_yesterday,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND status = 'Abandon') as last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND status = 'Abandon') as before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND status = 'Abandon') as b_before_last_week,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND status = 'Abandon') as last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND status = 'Abandon') as before_last_month,
(SELECT SUM(calls) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND status = 'Abandon') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$ivr_abandon_count_array = $myquerys->multiple($query_abandon_ivr_count);
	print_r($ivr_abandon_count_array);
	return 	 display_abandon_ivr_count($ivr_abandon_count_array);
}

function generate_prepaid_servicelevel_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_prepaid_servicelevel_ivr_count = "select
(SELECT avg(servicelevel) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND que = 'Prepaid') as yesterday,
(SELECT avg(servicelevel) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND que = 'Prepaid') as before_yesterday,
(SELECT avg(servicelevel) FROM analysistable WHERE datecall = DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND que = 'Prepaid') as b_before_yesterday,
(SELECT avg(servicelevel) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND que = 'Prepaid') as last_week,
(SELECT avg(servicelevel) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND que = 'Prepaid') as before_last_week,
(SELECT avg(servicelevel) FROM analysistable WHERE YEARweek(datecall) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND que = 'Prepaid') as b_before_last_week,
(SELECT avg(servicelevel) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND que = 'Prepaid') as last_month,
(SELECT avg(servicelevel) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND que = 'Prepaid') as before_last_month,
(SELECT avg(servicelevel) FROM analysistable WHERE  SUBSTRING(datecall FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND que = 'Prepaid') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$ivr_prepaid_servicelevel_count_array = $myquerys->multiple($query_prepaid_servicelevel_ivr_count);
	print_r($ivr_prepaid_servicelevel_count_array);
	return 	 display_prepaid_servicelevel_ivr_count($ivr_prepaid_servicelevel_count_array);
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
	//print_r($report[data]);
	$graph_detail[data]=$report[data];
	$graph_detail[title]='Accounts to Cases Ratio trends '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	//print_r($graph_detail);
	$period= date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	return $report;
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

function generate_cc_ivr_calls_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			date_format(queue.`entrydate`,'%D-%b') as full_date,
			queue.que,
			queue.servicelevel,
			queue.avgcallduration,
			calldetail.status,
			calldetail.calls
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between date_sub(date(now()), interval 30 day) AND date_sub(date(now()), interval 1 day) and
			calldetail.status != 'Abandon'
	";

	custom_query::select_db('ivrperformance');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
		$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
		$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
		
		$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
		$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
		$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
		$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
	}
	
	foreach($wb as $date=>$date_data){
		$report[data]['Prepaid-RCD'][$date] = $date_data[Prepaid]['Received Calls'];
		$report[unit]['Prepaid-RCD'] = '';
		
		$report[data]['Prepaid-HDL'][$date] = $date_data[Prepaid]['Handled Calls'];
		$report[unit]['Prepaid-HDL'] = '';
		
		$report[data]['Total RCD'][$date] = $date_data['All Queues']['Total Calls Received'];
		$report[unit]['Total RCD'] = '';
		
		$report[data]['Total HDL'][$date] = $date_data['All Queues']['Total Calls Handled'];
		$report[unit]['Total HDL'] = '';
		
	}
	
	$graph_detail[data]=$report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='IVR Daily Calls Trend '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['IVR Daily Calls Trend'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
}

function generate_cc_ivr_monthly_calls_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
	SELECT
			date_format(queue.`entrydate`,'%b') as full_date,
			date_format(queue.`entrydate`,'%Y%m') as normal_date,
			queue.que,
			avg(queue.servicelevel) as servicelevel,
			calldetail.status,
			sum(calldetail.calls) as calls
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between date_format(date_sub(date(now()), interval 180 day) ,'%Y-%m-01') AND date_sub(date(now()), interval 1 day) and
			calldetail.status != 'Abandon' group by full_date,queue.que,calldetail.status order by normal_date
	";

	custom_query::select_db('ivrperformance');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
		$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
		$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
		
		$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
		$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
		$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
		$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
	}
	
	foreach($wb as $date=>$date_data){
	
		
		$report[data]['Prepaid-RCD'][$date] = $date_data[Prepaid]['Received Calls'];
		$report[unit]['Prepaid-RCD'] = '';
		
		$report[data]['Prepaid-HDL'][$date] = $date_data[Prepaid]['Handled Calls'];
		$report[unit]['Prepaid-HDL'] = '';
		
		$report[data]['Total RCD'][$date] = $date_data['All Queues']['Total Calls Received'];
		$report[unit]['Total RCD'] = '';
		
		$report[data]['Total HDL'][$date] = $date_data['All Queues']['Total Calls Handled'];
		$report[unit]['Total HDL'] = '';
		
	}
	
	$graph_detail[data]=$report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='IVR Monthly Calls Trend '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['IVR Monthly Calls Trend'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
	//return sendHTMLemail($to='daniel.katatumba@waridtel.co.ug',$bcc='',$message='<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Graphical Presentation'].'" /><br>'.generate_offered_ivr_count().'<br>'.generate_answered_ivr_count().'',$subject='Testing Array Graph',$from='CCREPORTS@waridtel.co.ug') ;
}

function generate_cc_ivr_svl_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			date_format(queue.`entrydate`,'%D-%b') as full_date,
			queue.que,
			queue.servicelevel
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between date_sub(date(now()), interval 30 day) AND date_sub(date(now()), interval 1 day) and
			calldetail.status != 'Abandon'
	";

	custom_query::select_db('ivrperformance');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
		$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
		$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
		
		$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
		$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
		$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
		$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
	}
	
	foreach($wb as $date=>$date_data){
	
		
		$report[data]['Prepaid-SLV'][$date] = $date_data[Prepaid]['Service Level'];
		$report[unit]['Prepaid-SLV'] = '';
		
	}
	
	$graph_detail[data]=$report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='Daily IVR Service Level Trend '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Daily IVR Service Level Trend'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
	//return sendHTMLemail($to='daniel.katatumba@waridtel.co.ug',$bcc='',$message='<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Graphical Presentation Service Level'].'" /><br>'.generate_prepaid_servicelevel_ivr_count().'',$subject='Testing Array Graph Service Level',$from='CCREPORTS@waridtel.co.ug') ;
}

function generate_cc_monthly_ivr_svl_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			date_format(queue.`entrydate`,'%b') as full_date,
			queue.que,
			avg(queue.servicelevel) as servicelevel
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between date_sub(date(now()), interval 180 day) AND date_sub(date(now()), interval 1 day) and
			calldetail.status != 'Abandon' group by queue.que,full_date order by queue.entrydate
	";

	custom_query::select_db('ivrperformance');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$wb[$row[full_date]][$row[que]]['Service Level'] = number_format($row[servicelevel],2,".","");
		$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
		$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
		
		$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
		$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
		$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
		$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
	}
	
	foreach($wb as $date=>$date_data){
	
		
		$report[data]['Prepaid-SLV'][$date] = $date_data[Prepaid]['Service Level'];
		$report[unit]['Prepaid-SLV'] = '';
		
	}
	
	$graph_detail[data]=$report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='Monthly IVR Service Level Trend '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Monthly IVR Service Level Trend'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
	//return sendHTMLemail($to='daniel.katatumba@waridtel.co.ug',$bcc='',$message='<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Graphical Presentation Service Level'].'" /><br>'.generate_prepaid_servicelevel_ivr_count().'',$subject='Testing Array Graph Service Level',$from='CCREPORTS@waridtel.co.ug') ;
}

function generate_cc_cases_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT date_format(createdon,'%D-%b') as full_date, count(createdon) as `total cases` FROM reportscrm WHERE createdon between date_sub(date(now()), interval 30 day) AND date_sub(date(now()), interval 1 day) group by full_date order by createdon
	";

	custom_query::select_db('reportscrm');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Cases Created'][$row[full_date]] = $row['total cases'];
	}
	
	$query = "
		SELECT 
			date_format(caseresolution.actualend,'%D-%b') as full_date, count(caseresolution.actualend) as `created and closed`
FROM
caseresolution
Inner Join reportscrm ON reportscrm.casenum = caseresolution.casenum AND date_format(reportscrm.createdon,'%D-%b') = date_format(caseresolution.actualend,'%D-%b')
WHERE
caseresolution.actualend between date_sub(date(now()), interval 30 day) AND date_sub(date(now()), interval 1 day)
group by full_date order by caseresolution.actualend";

	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Created & Closed'][$row[full_date]] = $row['created and closed'];
	}
	
		$query = "
		SELECT 
			date_format(caseresolution.actualend,'%D-%b') as full_date, count(caseresolution.actualend) as `total closed`
FROM
caseresolution
Inner Join reportscrm ON reportscrm.casenum = caseresolution.casenum
WHERE
caseresolution.actualend between date_sub(date(now()), interval 30 day) AND date_sub(date(now()), interval 1 day)
group by full_date order by caseresolution.actualend";

	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Closed'][$row[full_date]] = $row['total closed'];
	}
	
	//print_r($input_list);
	
	$graph_detail[data] = $report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='Cases Trend By Day '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Cases Trend By Day'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
	//return sendHTMLemail($to='daniel.katatumba@waridtel.co.ug',$bcc='',$message='<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Cases Trend By Day'].'" />',$subject= $graph_detail[title],$from='CCREPORTS@waridtel.co.ug') ;
}

function generate_cc_monthly_cases_trend_report(){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT date_format(createdon,'%b') as full_date,date_format(createdon,'%Y%m') as normal_date, count(createdon) as `monthly number of cases` FROM reportscrm WHERE createdon between date_sub(date(now()), interval 180 day) AND date_sub(date(now()), interval 1 day) group by full_date order by normal_date
	";

	custom_query::select_db('reportscrm');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Cases Created'][$row[full_date]] = $row['monthly number of cases'];
	}
	
	$query = "
		SELECT 
			date_format(caseresolution.actualend,'%b') as full_date,date_format(caseresolution.actualend,'%Y%m') as normal_date, count(caseresolution.actualend) as `mth total num of closed cases`
FROM
caseresolution
Inner Join reportscrm ON reportscrm.casenum = caseresolution.casenum AND date_format(reportscrm.createdon,'%b') = date_format(caseresolution.actualend,'%b')
WHERE
caseresolution.actualend between date_sub(date(now()), interval 180 day) AND date_sub(date(now()), interval 1 day)
group by full_date order by normal_date";

	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Created & Closed'][$row[full_date]] = $row['mth total num of closed cases'];
	}
	
		$query = "
		SELECT 
			date_format(caseresolution.actualend,'%b') as full_date,date_format(caseresolution.actualend,'%Y%m') as normal_date, count(caseresolution.actualend) as `total closed cases created this month`
FROM
caseresolution
Inner Join reportscrm ON reportscrm.casenum = caseresolution.casenum
WHERE
caseresolution.actualend between date_sub(date(now()), interval 180 day) AND date_sub(date(now()), interval 1 day)
group by full_date order by normal_date";

	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$report[data]['Closed'][$row[full_date]] = $row['total closed cases created this month'];
	}
	
	//print_r($input_list);
	
	$graph_detail[data] = $report[data];
	//$graph_detail[data][]=$report[data]['Prepaid - Calls Recieved'];
	$graph_detail[title]='Cases Trend By Month '.date('F Y');
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Cases Trend By Month'] = $my_graph->Save();
	//print_r($graph_detail[data]);
	return $report;
	//return sendHTMLemail($to='daniel.katatumba@waridtel.co.ug',$bcc='',$message='<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Cases Trend By Month'].'" />',$subject= $graph_detail[title],$from='CCREPORTS@waridtel.co.ug') ;
}

function generate_cc_monthly_flash_report($input_date){
	
	if($input_date){
		$input_date = "'".$input_date."'";
	}else{
		$input_date = "date_sub(date(now()), interval 1 day)";
	}
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			date_format(queue.`entrydate`,'%D-%b') as full_date,
			queue.que,
			queue.servicelevel,
			queue.avgcallduration,
			calldetail.status,
			calldetail.calls
		FROM 
			calldetail 
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE 
			queue.entrydate between date_format(".$input_date.", interval 1 day) ,'%Y-%m-01') AND ".$input_date." and
			calldetail.status != 'Abandon'
	";

	custom_query::select_db('ivrperformance');
	$input_list = $myquerys->multiple($query);
	
	foreach($input_list as $row){
		$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
		$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
		$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
		
		$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
		$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
		$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
		$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
	}
	
	foreach($wb as $date=>$date_data){
	
		$report[data]['Prepaid service level'][$date] = number_format($date_data[Prepaid]['Service Level'],2);
		$report[unit]['Prepaid service level'] = '%';
		
		$report[data]['Service level - Overall'][$date] = number_format(($date_data['All Queues']['service_level_call_index']/$date_data['All Queues']['Total Calls']),2);
		$report[unit]['Service level - Overall'] = '%';
		
		$report[data]['Prepaid - Calls Recieved'][$date] = number_format($date_data[Prepaid]['Received Calls'],0);
		$report[unit]['Prepaid - Calls Recieved'] = '';
		
		$report[data]['Prepaid - Calls Handled'][$date] = number_format($date_data[Prepaid]['Handled Calls'],0);
		$report[unit]['Prepaid - Calls Handled'] = '';
		
		$report[data]['Prepaid - Average call duration'][$date] = $date_data[Prepaid]['Average Call Duration'];
		$report[unit]['Prepaid - Average call duration'] = '';
		
		$report[data]['Total Calls Recieved'][$date] = number_format($date_data['All Queues']['Total Calls Received'],0);
		$report[unit]['Total Calls Recieved'] = '';
		
		$report[data]['Total Calls Handled'][$date] = number_format($date_data['All Queues']['Total Calls Handled'],0);
		$report[unit]['Total Calls Handled'] = '';
		
		$report[data]['Average call duration - Overall'][$date] = timetostr($date_data['All Queues']['avg_call_duration_call_index']/$date_data['All Queues']['Total Calls']);
		$report[unit]['Average call duration - Overall'] = '';
	}
	
	$graph_detail[data]['Prepaid service level']=$report[data]['Prepaid service level'];
	//$graph_detail[title]='Prepaid Service level trend '.date('F Y');
	$graph_detail[title]='Monthly Prepaid Service level trend';
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=true;
	$graph_detail[width]=750;
	$graph_detail[height]=470;
	
	//$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	$my_graph->graph($title=$graph_detail[title], $period=date('Y-m-')."01 to ".date('Y-m-d'), $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph]['Prepaid service level'] = $my_graph->Save();
	
	return $report;
}


function generate_repeat_calls_call_status($yesterday){
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
					WHERE sv_repeat_wraups.call_status != 'Not Yet Called'";
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

function generate_cpc_monthly_trends(){
		
	$my_graph = new dbgraph();
	$myquerys = new custom_query();
	
	$query = "
		SELECT
			`month`,
			call_group,
			call_nums/active_subs as cpc
		FROM
			(
				SELECT
					date_format(queue.entrydate,'%Y-%m') as month_id,
					date_format(queue.entrydate,'%b %Y') as `month`,
					calldetail.status as call_group, 
					sum(calldetail.calls) as call_nums,
					avg(subscount.active_subs) as active_subs
				FROM calldetail 
					Inner Join queue ON queue.id = calldetail.id_c
					inner Join subscount ON subscount.`day` = queue.entrydate
				WHERE 
					queue.entrydate between date_format(date_sub(date(now()), interval 12 month),'%Y-%m-01') AND last_day(date_sub(date(now()), interval 1 month))
				GROUP BY month_id,calldetail.status
			) AS data_source
	";
	custom_query::select_db('ivrperformance');
	$cpc_data = $myquerys->multiple($query);
	
	foreach($cpc_data as $row){
		$data[CPC][$row[call_group]][$row[month]] = round($row[cpc],7);
	}
	
	$graph_detail[data]=$data[CPC];
	$graph_detail[title]='CPC Trends for Received, Handled and Abandoned for the period '.date("M Y", strtotime("-12 months")).' to '.date('M Y', strtotime("-1 months"));
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=1000;
	$graph_detail[height]=1000;
	$graph_detail[type] = 'line';
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red','black','green','blue');
	$period= date("Y-m-d", strtotime("-13 months")).' to '.date('Y-m-d');
	
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	
	return $report;
}

function generate_self_care_report(){
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "SELECT
		left(reportsphonecalls.createdon,10) as createdon,
		reportsphonecalls.wrapupsubcat as wrap_up_cat,
		COUNT(reportsphonecalls.wrapupsubcat) as wrap_count
		FROM
		reportsphonecalls
		WHERE
		left(reportsphonecalls.createdon,10) between date_sub(date(now()), interval 26 day) AND date_sub(date(now()), interval 1 day) AND
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
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[title] = 'Trends '.date("Y-m-d", strtotime("-40 days")).' to '.date('Y-m-d');
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

function generate_sms_counts($from,$back_period ='30',$interval = 'day'){
	$myquerys = new custom_query();
	
	if($from == ''){ $from = date('Y-m-d'); }
	
	$to_sql = $from.' 23:59:59';
	
	if($interval == 'day'){
		$query = "
			SELECT
				date_format(feedback.datesent,'%Y%m%d') as period,
				date_format(feedback.datesent,'%b-%d') as period_label,";
				
		$from_sql = "'".$from." 00:00:00'";
		$back_period_sql = $back_period + 1; 
	}else{
		$query = "
			SELECT
				date_format(feedback.datesent,'%Y%m') as period,
				date_format(feedback.datesent,'%b %Y') as period_label,";
		
		$from_sql = "date_format('".$from."', '%Y-%m-01 00:00:00')";
		$back_period_sql = $back_period; 
	}
	
	$query .= "
			IF(feedback_cstm.`status` IS NULL,feedback.`status`,feedback_cstm.`status`) as state,
			count(*) as number
		FROM
			feedback
			LEFT OUTER JOIN feedback_cstm ON feedback.id = feedback_cstm.id_c
		where
			feedback.datesent between date_sub(".$from_sql.", interval ".$back_period_sql." ".$interval.") and date_sub('".$to_sql."', interval 1 day)
		group by
			period,
			state
	";
	
	//echo nl2br($query)."<br><hr>";
	
	custom_query::select_db('ccba01.smsfeedback');
	$sms_data = $myquerys->multiple($query);
	
	//echo "Count of results is ".count($sms_data)."<br><hr>";
	
	$data[period][from] = $from;
	$data[period][back_period] = $back_period;
	$data[period][interval] = $interval;
	$data[about] = 'SMS manager counts';
	
	foreach($sms_data as $row){
		$data[intervals][$row[period_label]]=$row[period_label];
		
		$rows[$row[state]][$row[period_label]] = $row[number];
		$rows['All states'][$row[period_label]] += $row[number];
	}
	
	$data[states] = array_keys($rows);
	
	//exit(print_r($data[states]));
	
	//Putting the data in order and putting zeros where they should be ...
	foreach($data[intervals] as $interval){
		foreach($data[states] as $state){
			$data[data][$state][$interval] = number_format($rows[$state][$interval],0,'.','');
		}
	}
	
	//echo my_print_r($data,50,100)."<hr>";
	return $data;
}

function generate_sms_counts_graph_data($sms_data){
	
	$my_graph = new dbgraph();
	
	$graph_detail[data]=$sms_data[data];
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[title] = 'Trends: From '.$sms_data[period][back_period].' '.$sms_data[period][interval].'s before '.$sms_data[period][from];
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=500;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue','yellow','navy','purple');
	//print_r($graph_detail);
	$period = $sms_data[period][back_period].' '.$sms_data[period][interval].'s before '.$sms_data[period][from];
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$graph_id = $my_graph->Save();
	
	return $graph_id;
}

function generate_simswap_counts($from,$back_period ='30',$interval = 'day'){
	$myquerys = new custom_query();
	
	if($from == ''){ $from = date('Y-m-d'); }
	
	if($interval == 'day'){
		$query = "
			SELECT
				left(simswaps.datecreated,10) as period_label,
				left(simswaps.datecreated,10) as period,";
				
		$from_sql = "'".$from."'";
		$back_period_sql = $back_period + 1; 
	}else{
		$query = "
			SELECT
				left(simswaps.datecreated,7) as period,
				date_format(left(simswaps.datecreated,10) ,'%b %Y') as period_label,";
		
		$from_sql = "date_format('".$from."', '%Y-%m-01')";
		$back_period_sql = $back_period; 
	}
	
	$query .= "
			simswaps.status as state,
			count(simswaps.swapid) as number
		FROM
			simswaps
		where
			simswaps.datecreated between date_sub(CONCAT(".$from_sql.",' 00:00:00'), interval ".$back_period_sql." ".$interval.") and date_sub('".$from." 23:59:59', interval 1 day)
		group by
			period,
			state
	";
	
	//echo $query."\n\n";
	
	custom_query::select_db('fci');
	$simswap_data = $myquerys->multiple($query);
	
	$data[period][from] = $from;
	$data[period][back_period] = $back_period;
	$data[period][interval] = $interval;
	$data[about] = 'Simswaps';
	
	foreach($simswap_data as $row){
		$data[intervals][$row[period]]=$row[period_label];
		
		$rows[$row[state]][$row[period_label]] = $row[number];
		$rows['All states'][$row[period_label]] += $row[number];
	}
	
	$data[states] = array_keys($rows);
	
	//Putting the data in order and putting zeros where they should be ...
	foreach($data[intervals] as $interval){
		foreach($data[states] as $state){
			$data[data][$state][$interval] = number_format($rows[$state][$interval],0,'.','');
		}
	}
	
	return $data;
}

function generate_scratchcard_counts($from,$back_period ='30',$interval = 'day'){
	$myquerys = new custom_query();
	
	if($from == ''){ $from = date('Y-m-d'); }
	
	if($interval == 'day'){
		$query = "
			SELECT
				left(scratchcards.datecreated,10) as period_label,
				left(scratchcards.datecreated,10) as period,";
				
		$from_sql = "'".$from."'";
		$back_period_sql = $back_period + 1; 
	}else{
		$query = "
			SELECT
				left( scratchcards.datecreated,7) as period,
				date_format(left( scratchcards.datecreated,10) ,'%b %Y') as period_label,";
		
		$from_sql = "date_format('".$from."', '%Y-%m-01')";
		$back_period_sql = $back_period; 
	}
	
	$query .= "
			count(scratchcards.scratchcardid) AS number,
			scratchcards.status AS state
		FROM
			scratchcards
		where
			scratchcards.datecreated between date_sub(CONCAT(".$from_sql.",' 00:00:00'), interval ".$back_period_sql." ".$interval.") and date_sub('".$from." 23:59:59', interval 1 day)
		group by
			period,
			state
	";
	
	//echo $query."\n\n";
	
	custom_query::select_db('fci');
	$scratchcard_data = $myquerys->multiple($query);
	
	$data[period][from] = $from;
	$data[period][back_period] = $back_period;
	$data[period][interval] = $interval;
	$data[about] = 'Scratch Card';
	
	foreach($scratchcard_data as $row){
		$data[intervals][$row[period]]=$row[period_label];
		
		$rows[$row[state]][$row[period_label]] = $row[number];
		$rows['All states'][$row[period_label]] += $row[number];
	}
	
	$data[states] = array_keys($rows);
	
	//Putting the data in order and putting zeros where they should be ...
	foreach($data[intervals] as $interval){
		foreach($data[states] as $state){
			$data[data][$state][$interval] = number_format($rows[$state][$interval],0,'.','');
		}
	}
	
	return $data;
}

function generate_center_activity_graph_data($activities){
	
	if(count($activities) > 0){
		foreach($activities as $activity){
			$activity_data[period][back_period] = $activity[period][back_period];
			$activity_data[period][interval] = $activity[period][interval];
			$activity_data[period][from] = $activity[period][from];
			
			//Getting the periods in ascending order ...
			foreach($activity[intervals] as $interval=>$interval_label){
				if($activity_data[intervals][$interval] == ''){
					$activity_data[intervals][$interval]=$interval_label;
				}
			}
			
			$rows[$activity[about]] = $activity[data]['All states'];
		}
		
		//Putting data in its respective period which periods we have arranged in order so our graphing software shall not display funny stuff
		foreach($activity_data[intervals] as $interval){
			foreach($rows as $about=>$data){
				$activity_data[data][$about][$interval] = number_format($data[$interval],0,'.','');
			}
		}
		
		$my_graph = new dbgraph();
		
		$graph_detail[data]=$activity_data[data];
		$graph_detail[line_graph]=true;
		$graph_detail[bar_graph]=false;
		$graph_detail[display_title]=false;
		$graph_detail[title] = 'All Pakacare activities: From '.$activity_data[period][back_period].' '.$activity_data[period][interval].'s before '.$activity_data[period][from];
		$graph_detail[set_data_points]=true;
		$graph_detail[width]=800;
		$graph_detail[height]=500;
		$graph_detail[legend]=true;
		//$graph_detail[line_colors]=array('red','black','green','blue','yellow','navy','purple');
		$graph_detail[line_colors]=array('red','black');
		print_r($graph_detail);
		$period = $activity_data[period][back_period].' '.$activity_data[period][interval].'s before '.$activity_data[period][from];
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
		custom_query::select_db('graphing');
		$graph_id = $my_graph->Save();
		
		return $graph_id;
	}else{
		exit("No data to show .... \n");
	}
}

/*function generate_gsm_case_counts($from,$back_period ='30',$interval = 'day'){
	$myquerys = new custom_query();
	
	if($from == ''){ $from = date('Y-m-d'); }
	
	if($interval == 'day'){
		$query = "
			SELECT
				left(reportscrm.createdon,10) as created_period_label,
				left(reportscrm.createdon,10) as created_period,
				if(caseresolution.casenum is not null,left(caseresolution.actualend,10),NULL) as resolved_period_label,
				if(caseresolution.casenum is not null,left(caseresolution.actualend,10),NULL) as resolved_period, ";
				
		$from_sql = "'".$from."'";
		$back_period_sql = $back_period + 1; 
	}else{
		$query = "
			SELECT
				left(reportscrm.createdon,7) as created_period,
				date_format(left(reportscrm.createdon,10) ,'%b %Y') as created_period_label,
				if(caseresolution.casenum is not null,left(caseresolution.actualend,7),NULL) as resolved_period,
				if(caseresolution.casenum is not null,date_format(left(caseresolution.actualend,7) ,'%b %Y'),NULL) as resolved_period_label,";
		
		$from_sql = "date_format('".$from."', '%Y-%m-01')";
		$back_period_sql = $back_period; 
	}
	
	$query .= "
			if(caseresolution.casenum is null,'Open','Closed') as state,
			count(DISTINCT(reportscrm.casenum)) AS number
		FROM
			reportscrm
			left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
		where
			(reportscrm.createdon between date_sub('".$from_sql." 00:00:00', interval ".$back_period_sql." ".$interval.") AND date_sub('".$from_sql." 23:59:59', interval ".$back_period_sql." ".$interval.")) OR
			(caseresolution.actualend date_sub('".$from_sql." 00:00:00', interval ".$back_period_sql." ".$interval.") AND date_sub('".$from_sql." 23:59:59', interval ".$back_period_sql." ".$interval."))
		group by
			period,
			state
	";
	
	//echo $query."\n\n";
	
	custom_query::select_db('fci');
	$simswap_data = $myquerys->multiple($query);
	
	$data[period][from] = $from;
	$data[period][back_period] = $back_period;
	$data[period][interval] = $interval;
	$data[about] = 'Simswaps';
	
	foreach($simswap_data as $row){
		$data[intervals][$row[period]]=$row[period_label];
		
		$rows[$row[state]][$row[period_label]] = $row[number];
		$rows['All states'][$row[period_label]] += $row[number];
	}
	
	$data[states] = array_keys($rows);
	
	//Putting the data in order and putting zeros where they should be ...
	foreach($data[intervals] as $interval){
		foreach($data[states] as $state){
			$data[data][$state][$interval] = number_format($rows[$state][$interval],0,'.','');
		}
	}
	
	return $data;
}*/

function generate_paka_care_report1(){
	$myquery = new custom_query();
	custom_query::select_db('survey');
	$query = "
			SELECT
				sv_paka_care.name AS msisdn,
				sv_paka_care.pakacare_center_code AS paka_code,
				paka_care_codes.paka_name As paka_name,
				sv_paka_care.rate_warid_cs AS warid_cs,
				left( SUBSTRING( replace(sv_paka_care.reason_for_rate_answer,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_for_rate_answer,'^,^','^'),2))-1) AS reason_warid_cs,
				sv_paka_care.rate_service_at_center AS paka_rate,
				left( SUBSTRING( replace(sv_paka_care.reason_center_answer,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_center_answer,'^,^','^'),2))-1) AS paka_reason,
				   sv_paka_care.recommend_warid_services AS services_recommend,
				left( SUBSTRING( replace(sv_paka_care.reason_for_recommendation,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_for_recommendation,'^,^','^'),2))-1) AS reason_recommend,
				sv_paka_care.period_on_network AS period_on_network
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				
				";
	
		$results = $myquery->multiple($query);
		foreach($results as $row){
			++$data[paka_center_count][$row[paka_name]];
			if($row[warid_cs]<=3){
				++$data[warid_cs]['Poor(7-8)'][$row[warid_cs]];
			}
			if($row[warid_cs]==4 || $row[warid_cs]==5 || $row[warid_cs]==6){
				++$data[warid_cs]['Average(4-6)'][$row[warid_cs]];
			}
			if($row[warid_cs]>=7){
				++$data[warid_cs]['Good(9-10)'][$row[warid_cs]];
			}
			$warid_cs_reason_array = explode('^',$row[reason_warid_cs]);
			$warid_cs_reason = $warid_cs_reason_array[0];
			++$data[warid_cs_reason][$warid_cs_reason];
			if($row[paka_rate]<=3){
				++$data[paka_rate]['Poor(1-3)'][$row[paka_rate]];
			}
			if($row[paka_rate]==4 || $row[paka_rate]==5 || $row[paka_rate]==6){
				++$data[paka_rate]['Average(4-6)'][$row[paka_rate]];
			}
			if($row[paka_rate]>=7){
				++$data[paka_rate]['Good(7-10)'][$row[paka_rate]];
			}
			$paka_rate_reason_array = explode('^',$row[paka_reason]);
			$paka_rate_reason = $paka_rate_reason_array[0];
			++$data[paka_rate_reason][$paka_rate_reason];
			if($row[services_recommend]<=6){
				++$data[services_recommend]['Detractors(0-6)'][$row[services_recommend]];
			}
			if($row[services_recommend]==7 || $row[services_recommend]==8){
				++$data[services_recommend]['Passives(7-8)'][$services_recommend[services_recommend]];
			}
			if($row[services_recommend]>=9){
				++$data[services_recommend]['Promoters(9-10)'][$row[services_recommend]];
			}
			$reason_recommend_array = explode('^',$row[reason_recommend]);
			$reason_recommend = $reason_recommend_array[0];
			++$data[reason_recommend][$reason_recommend];
			++$data[period_on_network][$row[period_on_network]];
		}
		//print_r($data[warid_cs]);
	return $data;
}

function generate_paka_care_report(){
	$myquery = new custom_query();
	custom_query::select_db('survey');
	$query = "
			SELECT
				sv_paka_care.name AS msisdn,
				sv_paka_care.pakacare_center_code AS paka_code,
				paka_care_codes.paka_name As paka_name,
				sv_paka_care.rate_warid_cs AS warid_cs,
				left( SUBSTRING( replace(sv_paka_care.reason_for_rate_answer,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_for_rate_answer,'^,^','^'),2))-1) AS reason_warid_cs,
				sv_paka_care.rate_service_at_center AS paka_rate,
				left( SUBSTRING( replace(sv_paka_care.reason_center_answer,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_center_answer,'^,^','^'),2))-1) AS paka_reason,
				   sv_paka_care.recommend_warid_services AS services_recommend,
				left( SUBSTRING( replace(sv_paka_care.reason_for_recommendation,'^,^','^'),2),length(SUBSTRING( replace(sv_paka_care.reason_for_recommendation,'^,^','^'),2))-1) AS reason_recommend,
				sv_paka_care.period_on_network AS period_on_network
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				
				";
	
		$results = $myquery->multiple($query);
		foreach($results as $row){
			++$data[paka_center_count][$row[paka_name]];
			if($row[warid_cs]<=3){
				++$data[warid_cs]['Poor(1-3)'][$row[warid_cs]];
				$warid_cs_reason_array = explode('^',$row[reason_warid_cs]);
				$warid_cs_reason = $warid_cs_reason_array[0];
				++$data[warid_cs_reason][poor][$warid_cs_reason];
				++$data[network_age][POOR][$row[period_on_network]];
			}
			if($row[warid_cs]==4 || $row[warid_cs]==5 || $row[warid_cs]==6){
				++$data[warid_cs]['Average(4-6)'][$row[warid_cs]];
				$warid_cs_reason_array = explode('^',$row[reason_warid_cs]);
				$warid_cs_reason = $warid_cs_reason_array[0];
				++$data[warid_cs_reason][average][$warid_cs_reason];
				++$data[network_age][AVERAGE][$row[period_on_network]];
			}
			if($row[warid_cs] ==7 || $row[warid_cs] ==8 || $row[warid_cs] ==9){
				++$data[warid_cs]['Good(7-9)'][$row[warid_cs]];
				$warid_cs_reason_array = explode('^',$row[reason_warid_cs]);
				$warid_cs_reason = $warid_cs_reason_array[0];
				++$data[warid_cs_reason][good][$warid_cs_reason];
				++$data[network_age][GOOD][$row[period_on_network]];
			}
				if($row[warid_cs] == 10){
				++$data[warid_cs]['Excellent(10)'][$row[warid_cs]];
				$warid_cs_reason_array = explode('^',$row[reason_warid_cs]);
				$warid_cs_reason = $warid_cs_reason_array[0];
				++$data[warid_cs_reason][excellent_warid_cs_reason][$warid_cs_reason];
				++$data[network_age][EXCELLENT][$row[period_on_network]];
			}

			if($row[paka_rate]<=3){
				++$data[paka_rate]['Poor(1-3)'][$row[paka_rate]];
				$paka_rate_reason_array = explode('^',$row[paka_reason]);
				$paka_rate_reason = $paka_rate_reason_array[0];
				++$data[paka_rate_reason][poor_pakarate_reason][$paka_rate_reason];
			}
			if($row[paka_rate]==4 || $row[paka_rate]==5 || $row[paka_rate]==6){
				++$data[paka_rate]['Average(4-6)'][$row[paka_rate]];
				$paka_rate_reason_array = explode('^',$row[paka_reason]);
				$paka_rate_reason = $paka_rate_reason_array[0];
				++$data[paka_rate_reason][average_pakarate_reason][$paka_rate_reason];
			}
			if($row[paka_rate] == 7 || $row[paka_rate] == 8 || $row[paka_rate] == 9){
				++$data[paka_rate]['Good(7-9)'][$row[paka_rate]];
				$paka_rate_reason_array = explode('^',$row[paka_reason]);
				$paka_rate_reason = $paka_rate_reason_array[0];
				++$data[paka_rate_reason][good_pakarate_reason][$paka_rate_reason];
			}
			if($row[paka_rate] == 10){
				++$data[paka_rate]['Excellent(10)'][$row[paka_rate]];
				$paka_rate_reason_array = explode('^',$row[paka_reason]);
				$paka_rate_reason = $paka_rate_reason_array[0];
				++$data[paka_rate_reason][excellent_pakarate_reason][$paka_rate_reason];
			}
			$paka_rate_reason_array = explode('^',$row[paka_reason]);
			$paka_rate_reason = $paka_rate_reason_array[0];
			++$data[paka_rate_reason][$paka_rate_reason];
			if($row[services_recommend]<=6){
				++$data[services_recommend]['Detractors(0-6)'][$row[services_recommend]];
				$reason_recommend_array = explode('^',$row[reason_recommend]);
				$reason_recommend = $reason_recommend_array[0];
				++$data[reason_recommend][detractors][$reason_recommend];
			}
			if($row[services_recommend]==7 || $row[services_recommend]==8){
				++$data[services_recommend]['Passives(7-8)'][$services_recommend[services_recommend]];
				$reason_recommend_array = explode('^',$row[reason_recommend]);
				$reason_recommend = $reason_recommend_array[0];
				++$data[reason_recommend][passives][$reason_recommend];
			}
			if($row[services_recommend]>=9){
				++$data[services_recommend]['Promoters(9-10)'][$row[services_recommend]];
				$reason_recommend_array = explode('^',$row[reason_recommend]);
				$reason_recommend = $reason_recommend_array[0];
				++$data[reason_recommend][promoters][$reason_recommend];
			}
			//$reason_recommend_array = explode('^',$row[reason_recommend]);
			//$reason_recommend = $reason_recommend_array[0];
			//++$data[reason_recommend][$reason_recommend];
			++$data[period_on_network][$row[period_on_network]];
		}
		//print_r($data[network_age]);
	return $data;
}


function generate_pakacenter_pie(){
		$my_graph = new dbgraph();
		$myquery = new custom_query();
		$query = "SELECT
				paka_care_codes.paka_name As paka_name
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				";
		custom_query::select_db('survey');
		$repeat_data = $myquery->multiple($query);
		$results = $myquery->multiple($query);
		foreach($results as $row){
			++$data['Paka Center Count'][$row[paka_name]];
		}
		$graph_detail[width]=700;
		$graph_detail[height]=1200;
		$graph_detail[data]=$data;
		$graph_detail[title]='Survey Participation by Centers';
		$graph_detail[legend]=true;
		$graph_detail[type] = 'bar';
		$graph_detail[bar_graph] = true;
		$period= '2010-10-27 -'.date('Y-m-d');
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;		
}

function generate_warid_cs_pie(){
		$my_graph = new dbgraph();
		$myquery = new custom_query();
		$query = "SELECT
				sv_paka_care.rate_warid_cs AS warid_cs
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				";
		custom_query::select_db('survey');
		$repeat_data = $myquery->multiple($query);
		$results = $myquery->multiple($query);
		foreach($results as $row){
			if($row[warid_cs]<=3){
				++$data[warid_cs]['Poor(1-3)'][$row[warid_cs]];
			}
			if($row[warid_cs]==4 || $row[warid_cs]==5 || $row[warid_cs]==6){
				++$data[warid_cs]['Average(4-6)'][$row[warid_cs]];
			}
			if($row[warid_cs] ==7 || $row[warid_cs] ==8 || $row[warid_cs] ==9){
				++$data[warid_cs]['Good(7-9)'][$row[warid_cs]];
			}
			if($row[warid_cs] ==10){
				++$data[warid_cs]['Excellent(10)'][$row[warid_cs]];
			}
		}
		$data[warid_cs]['Good(7-9)'][total]= array_sum($data[warid_cs]['Good(7-9)']);
		$data[warid_cs]['Average(4-6)'][total] = array_sum($data[warid_cs]['Average(4-6)']);
		$data[warid_cs]['Poor(1-3)'][total] = array_sum($data[warid_cs]['Poor(1-3)']);
		$data[warid_cs]['Excellent(10)'][total] = array_sum($data[warid_cs]['Excellent(10)']);
		
			foreach($data[warid_cs] as $category=>$row){
				 $data[warid_cs][spitting][categorised][$category] = $row[total];
			}
		$graph_detail[width]=700;
		$graph_detail[height]=700;
		$graph_detail[data]=$data[warid_cs][spitting];
		$graph_detail[title]='Overall Rating of the Customer Service';
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period=  '2010-10-27 -'.date('Y-m-d');
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;		
}


function generate_paka_rate_pie(){
		$my_graph = new dbgraph();
		$myquery = new custom_query();
		$query = "SELECT
				 sv_paka_care.rate_service_at_center AS paka_rate
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				";
		custom_query::select_db('survey');
		$results = $myquery->multiple($query);
		foreach($results as $row){
			if($row[paka_rate]<=3){
				++$data[paka_rate]['Poor(1-3)'][$row[paka_rate]];
			}
			if($row[paka_rate]==4 || $row[paka_rate]==5 || $row[paka_rate]==6){
				++$data[paka_rate]['Average(4-6)'][$row[paka_rate]];
			}if($row[paka_rate] ==7 || $row[paka_rate] ==8 || $row[paka_rate] ==9){
				++$data[paka_rate]['Good(7-9)'][$row[paka_rate]];
			}
			}if($row[paka_rate] == 10){
				++$data[paka_rate]['Excellent(10)'][$row[paka_rate]];
			}
		$data[paka_rate]['Good(7-9)'][total]= array_sum($data[paka_rate]['Good(7-9)']);
		$data[paka_rate]['Average(4-6)'][total] = array_sum($data[paka_rate]['Average(4-6)']);
		$data[paka_rate]['Poor(1-3)'][total] = array_sum($data[paka_rate]['Poor(1-3)']);
		$data[paka_rate]['Excellent(10)'][total] = array_sum($data[paka_rate]['Excellent(10)']);
		
			foreach($data[paka_rate] as $category=>$row){
				 $data[paka_rate][spitting][categorised][$category] = $row[total];
			}
		$graph_detail[width]=700;
		$graph_detail[height]=700;
		$graph_detail[data]=$data[paka_rate][spitting];
		$graph_detail[title]='Rating of the Service at Paka Centers Centers';
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period=  '2010-10-27 -'.date('Y-m-d');
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;		
}


function generate_recommendation_pie(){
		$my_graph = new dbgraph();
		$myquery = new custom_query();
		$query = "SELECT
				replace(sv_paka_care.recommend_warid_services,'^','') AS services_recommend
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
			
				";
		custom_query::select_db('survey');
		$results = $myquery->multiple($query);
		foreach($results as $row){
			if($row[services_recommend]<=6){
				++$data[services_recommend]['Detractors(0-6)'][$row[services_recommend]];
			}
			if($row[services_recommend]==7 || $row[services_recommend]==8){
				++$data[services_recommend]['Passives(7-8)'][$services_recommend[services_recommend]];
			}
			if($row[services_recommend]>=9){
				++$data[services_recommend]['Promoters(9-10)'][$row[services_recommend]];
			}
		}
		$data[services_recommend]['Detractors(0-6)'][total]= array_sum($data[services_recommend]['Detractors(0-6)']);
		$data[services_recommend]['Passives(7-8)'][total] = array_sum($data[services_recommend]['Passives(7-8)']);
		$data[services_recommend]['Promoters(9-10)'][total] = array_sum($data[services_recommend]['Promoters(9-10)']);
		
			foreach($data[services_recommend] as $category=>$row){
				 $data[services_recommend][spitting][categorised][$category] = $row[total];
			}
		$graph_detail[width]=700;
		$graph_detail[height]=700;
		$graph_detail[data]=$data[services_recommend][spitting];
		$graph_detail[title]='Rating of the Service at Warid Centers';
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period=  '2010-10-27 -'.date('Y-m-d');
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;		
}

function generate_period_on_network_pie(){
		$my_graph = new dbgraph();
		$myquery = new custom_query();
		$query = "SELECT
				sv_paka_care.period_on_network AS period_on_network
				FROM
				sv_paka_care
				LEFT OUTER Join paka_care_codes ON paka_care_codes.paka_code = sv_paka_care.pakacare_center_code
				";
		custom_query::select_db('survey');
		$repeat_data = $myquery->multiple($query);
		$results = $myquery->multiple($query);
		foreach($results as $row){
			++$data[period_on_network][$row[period_on_network]];
		}
		$graph_detail[width]=700;
		$graph_detail[height]=700;
		$graph_detail[data]=$data;
		$graph_detail[title]='Rating as Per Lifetime on Network';
		$graph_detail[legend]=true;
		$graph_detail[type] = 'pie_chart';
		$period=  '2010-10-27 -'.date('Y-m-d');
		$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type=$graph_detail[type]);
		custom_query::select_db('graphing');
		$report[graph] = $my_graph->Save();
		return $report;		
}

function get_cc_agent_info(){

	$myquery = new custom_query();
	custom_query::select_db('ccba01.cs');
	$query = "
			SELECT
				*
			FROM
				employees
			WHERE
				employees.emp_POS = 'CSA'
			";
	//print nl2br($query);
	$agent_result = $myquery->multiple($query,'cs');
	
	foreach($agent_result as $employee){
		$agentsdata[$employee[emp_FNAME].' '.$employee[emp_LNAME]] = array(
						'agent_id' => $employee[emp_ID],
						'agent_name' => $employee[emp_FNAME].' '.$employee[emp_LNAME],
						'agent_loginid' => $employee[emp_NUM]
						);
	}
	
	return $agentsdata;
}
?>