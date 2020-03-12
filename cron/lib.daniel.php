<?
require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
require_once('/srv/www/htdocs/reports/web/includes/includes.php');

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'Task Manager <no-reply@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	$headers .= "BCC: ".$bcc." \r\n";
    mail($to,$subject,$message,$headers);
}

function display_value_trend($current_value,$value_before){
	if($current_value < $value_before){
		$html .= ' Down ';
	}else if ($current_value == 0 || $value_before == 0){
		$html .= ' - ';
	}else{
		$html .= ' Up ';
	}
	
	return $html;
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

function display_cases_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
	
	
					<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Total Cases (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Total Cases</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>';
	return $HTML;
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
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$closed_case_count_array = $myquerys->multiple($query_closed_cases_count);
	print_r($closed_case_count_array);
	return 	 display_closed_cases_count($closed_case_count_array);
}

function display_closed_cases_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Closed Cases (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Closed Cases</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}


function generate_offered_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_offered_ivr_count = "select
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND calldetail.status = 'Received' ) as yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND calldetail.status = 'Received') as before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate= DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND calldetail.status = 'Received') as b_before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND calldetail.status = 'Received') as last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND calldetail.status = 'Received') as before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND calldetail.status = 'Received') as b_before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND calldetail.status = 'Received') as last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND calldetail.status = 'Received') as before_last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND calldetail.status = 'Received') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;
"
 ;
	$ivr_offered_count_array = $myquerys->multiple($query_offered_ivr_count);
	print_r($ivr_offered_count_array);
	return 	 display_offered_ivr_count($ivr_offered_count_array);
}

function display_offered_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Offered Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Offered Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function generate_answered_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_answered_ivr_count = "select
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND calldetail.status = 'Handled' ) as yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND calldetail.status = 'Handled') as before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate= DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND calldetail.status = 'Handled') as b_before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND calldetail.status = 'Handled') as last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND calldetail.status = 'Handled') as before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND calldetail.status = 'Handled') as b_before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND calldetail.status = 'Handled') as last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND calldetail.status = 'Handled') as before_last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND calldetail.status = 'Handled') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;
"
 ;
	$ivr_answered_count_array = $myquerys->multiple($query_answered_ivr_count);
	print_r($ivr_answered_count_array);
	return 	 display_answered_ivr_count($ivr_answered_count_array);
}

function display_answered_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Answered Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Answered Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function generate_abandon_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_abandon_ivr_count = "select
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND calldetail.status = 'Abandon' ) as yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND calldetail.status = 'Abandon') as before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate= DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND calldetail.status = 'Abandon') as b_before_yesterday,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND calldetail.status = 'Abandon') as last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND calldetail.status = 'Abandon') as before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND calldetail.status = 'Abandon') as b_before_last_week,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND calldetail.status = 'Abandon') as last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND calldetail.status = 'Abandon') as before_last_month,
(SELECT
sum(calldetail.calls) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND calldetail.status = 'Abandon') as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;
"
 ;
	$ivr_abandon_count_array = $myquerys->multiple($query_abandon_ivr_count);
	print_r($ivr_abandon_count_array);
	return 	 display_abandon_ivr_count($ivr_abandon_count_array);
}

function display_abandon_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Abandon Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Abandoned Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function generate_prepaid_servicelevel_ivr_count(){
	$myquerys = new custom_query();
	custom_query::select_db('ivr');
	
	$query_prepaid_servicelevel_ivr_count = "select
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND queue.que = 'Prepaid' ) as yesterday,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate = DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND queue.que = 'Prepaid' ) as before_yesterday,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE queue.entrydate= DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND queue.que = 'Prepaid' ) as b_before_yesterday,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 7 DAY) AND queue.que = 'Prepaid' ) as last_week,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 14 DAY) AND queue.que = 'Prepaid' ) as before_last_week,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE YEARweek(queue.entrydate) = YEARweek(CURRENT_DATE - INTERVAL 21 DAY) AND queue.que = 'Prepaid' ) as b_before_last_week,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 1 MONTH FROM 1 FOR 7) AND queue.que = 'Prepaid' ) as last_month,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 2 MONTH FROM 1 FOR 7) AND queue.que = 'Prepaid' ) as before_last_month,
(SELECT
avg(queue.servicelevel) as total
FROM
calldetail
Inner Join queue ON queue.id = calldetail.id_c WHERE  SUBSTRING(queue.entrydate FROM 1 FOR 7) =   SUBSTRING(CURRENT_DATE - INTERVAL 3 MONTH FROM 1 FOR 7) AND queue.que = 'Prepaid' ) as b_before_last_month,
(select week(CURRENT_DATE - INTERVAL 7 DAY) as last_week) as last_week_num,
(select week(CURRENT_DATE - INTERVAL 14 DAY) as last_week) as before_last_week_num,
(select week(CURRENT_DATE - INTERVAL 21 DAY) as last_week) as b_before_last_week_num
;"
 ;
	$ivr_prepaid_servicelevel_count_array = $myquerys->multiple($query_prepaid_servicelevel_ivr_count);
	print_r($ivr_prepaid_servicelevel_count_array);
	return 	 display_prepaid_servicelevel_ivr_count($ivr_prepaid_servicelevel_count_array);
}

function display_prepaid_servicelevel_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Prepaid Service Levels (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Prepaid Service Levels</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}


function display_cc_flash_report($report){
	
	//print_r($report['Prepaid service level']); echo "<br>";
	
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	/*font:calibri;*/
	font-family:Verdana, Geneva, sans-serif;
	font-size:11px;
	/*font-family:Tahoma, Geneva, sans-serif;*/
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
			<table width="100%" border="0" cellpadding="1" cellspacing="0">
	';
	
	//Getting the date as tiltes
	$dates = array_keys($report[data]['Prepaid service level']);
	$html .= '
		<tr>
			<th scope="col" class="row_title">Contact Center Daily Flash Report</th>
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
			<tr>
		';

	$html .= '
			<td>
			<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Prepaid service level'].'" /></td>
			</tr>
		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact cc business analysis.</p>
</body>
</html>
	';
	
	return $html;
}

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
    $ret .= "$seconds";

    return $ret;
}

/*function display_trend_graph($width,$height,$data,$title,$graph_type){

	///set default graph type
	if($graph_type == ''){
		$graph_type = 'line';
	}
	
	$graph=new PHPGraphLib($width,$height);

	$graph->addData($data);
	$graph->setTitle($title);
	$graph->setGradient("lime", "green");
	$graph->setBarOutlineColor("black");
	if($graph_type == 'bar'){
		$graph->setBars(true);
	}else{
		$graph->setBars(false);
	}
	if($graph_type == 'line'){
		$graph->setLine(true);
	}else{
		$graph->setLine(false);
	}

	$graph->createGraph();
	
}*/

?>