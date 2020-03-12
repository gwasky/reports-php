<?
set_time_limit(0);
ini_set('memory_limit','1400M');
$mins = 0;
//echo date('Y-m-d H:i:s')." : sleeping for ".$mins." minutes ...\n";
//sleep($mins*60);
//echo date('Y-m-d H:i:s')." : Resuming ... \n";

//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);

//if($DATA_INPUT_CHECKS_C0MPLETE != FALSE) { exit("Please use the crm_auto_inserts.php file instead of running this file directly. Exiting ...\n\n");}

$root_dir = '/srv/www/htdocs/reports/';
require_once($root_dir.'cron/lib.php');
error_reporting(E_ERROR | E_PARSE);

//Date reformat back ups
//PHP 5 standard -> date_format(date_create('2011-04-01'),'D jS M Y')
//PHP custom using mysql -> date_reformat(date('Y-m-d',strtotime("-1 days"))

$airtel_ccreports = 'David Daka <David.Daka@ug.airtel.com>, Jackie Rozario/Customer Service/Uganda <Jackie.Rozario@ug.airtel.com>, Jael C. Wawulira/Customer Service/Uganda <Jael.wawulira@ug.airtel.com>, kyeyune dorothy/Customer Service/Uganda <dorothy.kyeyune@ug.Airtel.com>, Lynda Nabayiinda/Customer Service/uganda <Lynda.Nabayiinda@ug.airtel.com>, Macdavid Mugga/Customer Service/Uganda <Macdavid.Mugga@ug.airtel.com>, Nelson Mugisha/Customer Service/uganda <Nelson.Mugisha@ug.airtel.com>, Steven Ntambi/Customer Service/Uganda <Steven.Ntambi@ug.airtel.com>,Abhishek Mudgal/Customer Service/Airtel Africa <abhishek.mudgal@africa.airtel.com>, Arindam Chakrabarty/Airtel Ug <Arindam.Chakrabarty@ug.airtel.com>, Somasekhar VG /Airtel UG <Somasekhar.VG@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';

$ccba_list = "Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.waridtel.co.ug>, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>";

$gsm_to_list = $airtel_ccreports.", Pavan Boyina/ISONBPO/Uganda <pavan.boyina@isonbpo.com>, Lucy Komakech/ISONBPO/Uganda <lucy.komakech@isonbpo.com>, Vipan G <vipang@spancobpo.com>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Amit Mehta <amitmehta@spancobpo.com>";
$gsm_to_bcc = "Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.waridtel.co.ug>,Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>";

//$gsm_to_list = "Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.waridtel.co.ug>,Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>";
//$gsm_to_bcc = "";

$warid_pesa_to_list = $gsm_to_list;
$warid_pesa_to_bcc = $gsm_to_bcc;

$ivrflash_to_list = 'david.daka@ug.airtel.com,ritah.nakafero@waridtel.co.ug,Vipan G <vipang@spancobpo.com>, Pavan Boyina/ISONBPO/Uganda <pavan.boyina@isonbpo.com>, Lucy Komakech/ISONBPO/Uganda <lucy.komakech@isonbpo.com>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>, Abhishek Mudgal/Customer Service/Airtel Africa <abhishek.mudgal@africa.airtel.com>, Amit Mehta <amitmehta@spancobpo.com>';
$ivrflash_bcc_list = $ccba_list;

$warid_pesa_ivrflash_to_list = $ivrflash_to_list.',Faridah Namutebi/Commercial/Kampala <faridah.namutebi@ug.airtel.com>';
$warid_pesa_ivrflash_bcc_list = $ccba_list;

$crm_backoffice_flash_to_list = 'Isaac Kiyingi/Customer Service/Uganda <Isaac.Kiyingi@ug.airtel.com>,Jennifer Nakaddu/Customer Service/Uganda <jennifer.nakaddu@ug.airtel.com>,David Daka/Customer Service/Uganda <david.daka@ug.airtel.com>, Mike Muhangi/Customer Service/Uganda <Mike.Muhangi@ug.airtel.com>, Christine Aanyu/Customer Service/Uganda <Christine.Aanyu@ug.airtel.com>,Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>';
$crm_backoffice_flash_bcc_list = $ccba_list;

$crm_backoffice_waridpesa_flash_to_list = 'Mike Muhangi/Customer Service/Uganda <Mike.Muhangi@ug.airtel.com>, Christine Aanyu/Customer Service/Uganda <Christine.Aanyu@ug.airtel.com>, David Daka/Customer Service/Uganda <david.daka@ug.airtel.com>, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>';
$crm_backoffice_waridpesa_flash_bcc_list = $ccba_list;

$dropped_and_silent_calls_to_list .= 'Christine Aanyu/Customer Service/Uganda <Christine.Aanyu@ug.airtel.com>, Mike Muhangi/Customer Service/Uganda <Mike.Muhangi@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, David Daka/Customer Service/Uganda <david.daka@ug.airtel.com>';

$dropped_and_silent_calls_cc_list = 'Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>,Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';

$service_recovery_to_list = 'Isaac Kiyingi/Customer Service/Uganda <Isaac.Kiyingi@ug.airtel.com>, Jennifer Nakaddu/Customer Service/Uganda <jennifer.nakaddu@ug.airtel.com>, Christine Aanyu/Customer Service/Uganda <Christine.Aanyu@ug.airtel.com>, Mike Muhangi/Customer Service/Uganda <Mike.Muhangi@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, David Daka/Customer Service/Uganda <david.daka@ug.airtel.com>';

$service_recovery_cc_list = 'Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>, Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>';

//SPECIAL RUNS LIKE EASTER BREAK ....
//$special_run = 1; //THIS SHOULD BE UNSET AFTER THE SCRIPT HAS BEEN INITIATED

if(date('D') == 'Mon' and $special_run != 1){
	//Running the reports for Saturday (Fri) and Sunday (Sat) then Monday (Sun)
	echo date('Y-m-d H:i:s')." : Initiating GSM report run for the past 3 days Saturday (Fri), Sunday (Sat) then Monday (Sun) ... \n";
	$days = array(date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
}elseif($special_run == 1){
	echo date('Y-m-d H:i:s')." : UN SET special_run variable  [".$special_run."] !!!!!! AFTER RUNNING THIS SCRIPT !!!!!!!! \n\n";
	//Running the reports for Saturday (Fri) and Sunday (Sat) then Monday (Sun)
	//echo date('Y-m-d H:i:s')." : Initiating GSM report run for the special run ... \n";
	//PUBLIC HOLIDAY DURING THE WEEK
	//$days = array(date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
	//3 DAY WEEKEND
	//$days = array(date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")));
	//SPECIFIC DATE
	//$days = array(date('Y-m-d',strtotime("-1 days")));
	//SPECIAL DAYS
	//$days = array(date('Y-m-d',strtotime("-4 days")),date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
	//$days = array('2013-08-23','2013-08-25','2013-08-27');
}else{
	//Running the reports for other days
	echo date('Y-m-d H:i:s')." : [".date('Y-m-d',strtotime("-1 days"))."] Initiating GSM report run for yesterday ... \n";
	$days = array(date('Y-m-d',strtotime("-1 days")));
}

foreach($days as $key=>$day){
	
	$GSM_ACTIVITIES_HTML = generate_gsm_activities_report($day);
	sendHTMLemail(
				  $to=$gsm_to_list,
				  $bcc=$gsm_to_bcc,
				  $message=$GSM_ACTIVITIES_HTML,
				  $subject='Customer care GSM activities report for '.date_format(date_create($day),'D jS M Y'),
				  $from='CUSTOMER CARE OPERATIONS <ccnotify@waridtel.co.ug>'
	);
	
	$WARID_PESA_ACTIVITIES_HTML = generate_warid_pesa_activities_report($day);
	sendHTMLemail(
				  $to=$warid_pesa_to_list,
				  $bcc=$warid_pesa_to_bcc,
				  $message=$WARID_PESA_ACTIVITIES_HTML,
				  $subject='Customer care WARID PESA activities report for '.date_format(date_create($day),'D jS M Y'),
				  $from='WARID PESA CUSTOMER CARE OPERATIONS <ccnotify@waridtel.co.ug>'
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] Running Call centre IVR Flash report ... \n";
	
	$IVR_FLASH_HTML = display_cc_flash_report(generate_cc_flash_report($day));
	sendHTMLemail(
				 $to=$ivrflash_to_list,
				 $bcc=$ivrflash_bcc_list,
				 $message=$IVR_FLASH_HTML,
				 $subject='Call Centre Flash report for '.date_format(date_create($day),'D jS M Y'),
				 $from="CALL CENTRE PERFORMANCE<ccnotify@waridtel.co.ug>"
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] Running Call centre IVR Flash Warid Pesa report ... \n";
	
	$WARID_PESA_IVR_FLASH_HTML = display_waridpesa_sl_trends(generate_waridpesa_sl_trends($day));
	sendHTMLemail(
				 $to=$warid_pesa_ivrflash_to_list,
				 $bcc=$warid_pesa_ivrflash_bcc_list,
				 $message=$WARID_PESA_IVR_FLASH_HTML,
				 $subject='Call Centre Warid pesa Flash report for '.date_format(date_create($day),'D jS M Y'),
				 $from="CALL CENTRE WARID PESA PERFORMANCE<ccnotify@waridtel.co.ug>"
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] Running Back Office Flash report ... \n";
	
	$CRM_BACKOFFICE_FLASH_HTML = attach_html_container($title='',$body=generate_backoffice_flash($day));
	sendHTMLemail(
				 $to=$crm_backoffice_flash_to_list,
				 $bcc=$crm_backoffice_flash_bcc_list,
				 $message=$CRM_BACKOFFICE_FLASH_HTML,
				 $subject='CRM COMPLAINT RESOLUTION Flash report for '.date_format(date_create($day),'D jS M Y'),
				 $from="CUSTOMER CARE BACK OFFICE <ccnotify@waridtel.co.ug>"	
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] Running Back Office Warid Pesa Flash report ...\n";
	
	$CRM_BACKOFFICE_WARIDPESA_FLASH_HTML = attach_html_container($title='',$body=generate_backoffice_waridpesa_flash($day));
	sendHTMLemail(
				 $to=$crm_backoffice_waridpesa_flash_to_list,
				 $bcc=$crm_backoffice_waridpesa_flash_bcc_list,
				 $message=$CRM_BACKOFFICE_WARIDPESA_FLASH_HTML,
				 $subject='CRM WARID PESA COMPLAINT RESOLUTION Flash report for '.date_format(date_create($day),'D jS M Y'),
				 $from="CUSTOMER CARE WARID PESA COMPLAINT RESOLUTION <ccnotify@waridtel.co.ug>"
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] End Back Office Warid Pesa Flash report ...\n";
	echo date('Y-m-d H:i:s')." : [".$day."] Running Silent and Dropped Calls (PRANK CALLS) Report ... \n";	
	
	$html = attach_html_container($title='',generate_prank_call_wrapups($day));
	my_mail(
		$to = $dropped_and_silent_calls_to_list,
		$cc = $dropped_and_silent_calls_cc_list,
		$bcc = '',
		$message = $html,
		$subject = 'WTU Silent and Dropped calls MTD for '.date_format(date_create($day),'D jS M Y'),
		$from = "CCREPORTS<ccnotify@waridtel.co.ug>"
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] End Silent and Dropped Calls (PRANK CALLS) Report ...\n";
	echo date('Y-m-d H:i:s')." : [".$day."] Running Service Recovery Report ...\n";	
	
	$body = generate_service_recovery_report($day);
	$body[html] = attach_html_container($title='',$body[html]);
	$body[attach] = attach_html_container($title='',$body[attach]);
	
	my_mail(
		$to = $service_recovery_to_list,
		$cc = $service_recovery_cc_list,
		$bcc = '',
		$message = $body[html],
		$subject = 'Service Recovery Report for '.date_format(date_create($day),'D jS M Y'),
		$from = "CC SERVICE RECOVERY<ccnotify@waridtel.co.ug>",
		$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
	);
	
	echo date('Y-m-d H:i:s')." : [".$day."] End Service Recovery Report ...\n";
	echo date('Y-m-d H:i:s')." : [".$day."] Report run done ... \n";

}

?>
