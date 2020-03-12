<?php
set_time_limit(0);

function generate_gsm_activities_report($use_date){
	
	if(trim($use_date) == ''){
		$use_date = date('Y-m-d',strtotime("-1 days"));
	}
	
	echo date('Y-m-d H:i:s')." : [".$use_date."] START RUNNING GSM ACTIVITIES REPORT FOR ".$use_date."... \n\n";
	
	$_REQUEST[use_date] = $use_date;
	
	$html = '
		<div class="section_head">CALL HANDLING</div>
	';
	
	echo date('Y-m-d H:i:s')." : [".$use_date."] Generating Call Handling summary ... \n";
	
	$html .= generate_gsm_ivr_summary($_REQUEST[use_date]);
	
	echo date('Y-m-d H:i:s')." : [".$use_date."] Generating Wrap up summary ... \n";
	
	$html .= generate_gsm_wrapup_summary($_REQUEST[use_date]);
	
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">SUBSCRIBER MANAGEMENT - BACK OFFICE</div>
	';
	
	echo date('Y-m-d H:i:s')." : [".$use_date."] Generating back office summary ... \n";
	
	$html .= generate_gsm_back_office_summary($_REQUEST[use_date]);
	
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">SUBSCRIBER MANAGEMENT - SERVICE CENTRES/FRANCHISES</div>
	';
	
	echo date('Y-m-d H:i:s')." : [".$use_date."] Generating front office summary ... \n";
	
	$html .= generate_front_office_summary($_REQUEST[use_date]);
	
	/*
	$html .= '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="section_head">AUTOMATED HELP</div>
	';
		
	echo date('Y-m-d H:i:s')." : [".$use_date."] Generating USSD summary ... \n";
	
	$html .= generate_ussd_summary($_REQUEST[use_date]);
	*/
	
	echo "\n".date('Y-m-d H:i:s')." : [".$use_date."] END RUNNING GSM ACTIVITIES REPORT ... \n\n";
	
	//$end_time = date('Y-m-d H:i:s');
	//$html .= "<br><br>GSM report run on ".$_REQUEST[use_date].";<br>Duration of report run is ".(strtotime($end_time) - strtotime($start_time))." seconds"; 
	return attach_html_container2($title='GSM ACTIVITIES REPORT '.$_REQUEST[use_date],$body=$html);
}

?>
