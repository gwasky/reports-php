<?php
function display_trends_by_day(){
			
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	font-family:Verdana, Geneva, sans-serif;
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
	background-color:#FF0000;color:#FFFFFF; font-size:100%;
}

.row {
	color:#000000; border-top:#333333 1px solid; border-left:#333333 1px solid; border-right:#333333 1px solid;
}

.row_title {
	color:#FFFFFF; background-color:#00F;font-weight:bold; font-size:100%;
}

.value{
	text-align:right;	
}

</style>
</head>
		<body>
			<table width="100%" border="0" cellpadding="1" cellspacing="0">

		<tr>
			<th scope="col" class="row_title">Day By Day Trend</th>

			<th scope="col" class="top_th">Monthly Trend</th>
		</tr>
		<tr>
			<th scope="col" colspan="2">Call Centre Incoming Calls</th>
		</tr>
		<tr class="row">
			<th scope="col">'.display_cc_ivr_calls_trend_report(generate_cc_ivr_calls_trend_report()).'</th>

			<td class="value">'.display_cc_ivr_monthly_calls_trend_report(generate_cc_ivr_monthly_calls_trend_report()).'</td>
		</tr>
		<tr>
			<th scope="col" colspan="2">Call Centre Service Levels</th>
		</tr>
		<tr class="row">
			<th scope="col">'.display_cc_ivr_svl_trend_report(generate_cc_ivr_svl_trend_report()).'</th>

			<td class="value">'.display_cc_monthly_ivr_svl_trend_report(generate_cc_monthly_ivr_svl_trend_report()).'</td>

		</tr>
		<tr>
			<th scope="col" colspan="2">GSM Cases</th>
		</tr>
		<tr class="row">
			<th scope="col">'.display_cc_cases_trend_report(generate_cc_cases_trend_report()).'</th>

			<td class="value">'.display_cc_monthly_cases_trend_report(generate_cc_monthly_cases_trend_report()).'</td>

		</tr>
		<tr>
			<th scope="col" colspan="2">SMS to the SMS Manager</th>
		</tr>
		<tr class="row">
			<td scope="col">';
				$html .= display_generic_graph(generate_sms_counts_graph_data(generate_sms_counts($from=date('Y-m-d'),$back_period ='30',$interval = 'day'))); $html .= '
			</th>
		
			<td class="value">';
				$html .= display_generic_graph(generate_sms_counts_graph_data(generate_sms_counts($from=date('Y-m-d'),$back_period ='6',$interval = 'month'))); $html .= '
				</td>
		</tr>
		
		<!--
		<tr>
			<th scope="col" colspan="2">Data lead conversion and Account Cases</th>
		</tr>
		<tr class="row">
			<th scope="col">';
				//$html .= display_conversion_rate_trend_report(generate_conversion_rate_report()); 
				$html .= '
			</th>
			<td class="value">';
				//$html .= display_account_cases_trend_report(generate_accounts_cases_report()); 
				$html .= '
			</td>
		</tr>
		-->
		
		<tr>
			<th scope="col" colspan="2">Paka Care centre Activities in numbers</th>
		</tr>
		<tr class="row">
			<td scope="col">';
				$daily_activities[] = generate_simswap_counts($from=date('Y-m-d'),$back_period ='30',$interval = 'day');
				$daily_activities[] = generate_scratchcard_counts($from=date('Y-m-d'),$back_period ='30',$interval = 'day');
				
				$html.= display_generic_graph(generate_center_activity_graph_data($daily_activities)); unset($daily_activities); $html.= '
			</th>

			<td class="value">';
				$monthly_activities[] = generate_simswap_counts($from=date('Y-m-d'),$back_period = '6',$interval = 'month');
				$monthly_activities[] = generate_scratchcard_counts($from=date('Y-m-d'),$back_period = '6',$interval = 'month');
				
				$html.= display_generic_graph(generate_center_activity_graph_data($monthly_activities)); unset($monthly_activities); $html.= '
			</td>
		</tr>

		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact cc business analysis.</p>
</body>
</html>
';
	return $html;
}
?>