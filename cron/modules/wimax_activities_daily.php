<?php

set_time_limit(0);

function generate_wimax_activities_report($use_date){
	
	if(trim($use_date) == ''){
		$use_date = date('Y-m-d',strtotime("-1 days"));
	}
	
	echo date('Y-m-d H:i:s')." : START RUNNING WIMAX ACTIVITIES REPORT FOR ".$use_date."... \n\n";
	
	$_REQUEST[use_date] = $use_date;
	
	$html = '
		<div class="section_head">CALL CENTRE</div>
	';
	
	echo date('Y-m-d H:i:s')." : Generating wrap up summary ... \n";
	
	$html .= generate_wimax_wrapup_summary($_REQUEST[use_date]);
	
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">SUBSCRIBER MANAGEMENT - BACK OFFICE</div>
	';
	
	echo date('Y-m-d H:i:s')." : Generating back office summary ... \n";
	
	$html .= generate_wimax_back_office_summary($_REQUEST[use_date]);
	
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">LEADS</div>
	';
	
	echo date('Y-m-d H:i:s')." : Generating Leads Statistics Summary ... \n";
	
	$html .= generate_leads_summary($_REQUEST[use_date]);
	
	
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">ACCOUNTS</div>
	';
	
	echo date('Y-m-d H:i:s')." : Generating Accounts Statistics Summary ... \n";
	
	$html .= generate_accounts_summary($_REQUEST[use_date]);
	
	
	/*//USSD IS NOT THERE IN DATA!!
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">AUTOMATED HELP</div>
	';
	
	echo date('Y-m-d H:i:s')." : Generating USSD summary ... \n";
	
	$html .= generate_ussd_summary($_REQUEST[use_date]);
	*/
	
	echo "\n".date('Y-m-d H:i:s')." : END RUNNING WIMAX ACTIVITIES REPORT ... \n\n";
	
	//$end_time = date('Y-m-d H:i:s');
	//$html .= "<br><br>GSM report run on ".$_REQUEST[use_date].";<br>Duration of report run is ".(strtotime($end_time) - strtotime($start_time))." seconds"; 
	return attach_html_container2($title='WIMAX ACTIVITIES REPORT '.$_REQUEST[use_date],$body=$html);
}

?>