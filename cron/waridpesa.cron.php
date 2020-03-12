<?
//exit("Script NOT IN USE. IT HAS BEEN INTEGRATED IN THE GSM ACTIVITIES CALLER!");
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

//if($DATA_INPUT_CHECKS_C0MPLETE != FALSE) { exit("Please use the crm_auto_inserts.php file instead of running this file directly. Exiting ...\n\n");}

$root_dir = '/srv/www/htdocs/reports/';
require_once($root_dir.'cron/lib.php');

//Date reformat back ups
//PHP 5 standard -> date_format(date_create('2011-04-01'),'D jS M Y')
//PHP custom using mysql -> date_reformat(date('Y-m-d',strtotime("-1 days"))

$warid_pesa_to_list = "CCREPORTS@waridtel.co.ug,abhinav.jha@waridtel.co.ug, Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>";
$warid_pesa_to_bcc = "complaintsupport@waridtel.co.ug,customercare@waridtel.co.ug,CCBUSINESSANALYSIS@waridtel.co.ug";

$warid_pesa_to_list = "steven.ntambi@waridtel.co.ug";
$warid_pesa_to_bcc = "";

$warid_pesa_ivrflash_to_list = 'moses.wamono@waridtel.co.ug,herbert.luyinda@waridtel.co.ug,david.daka@waridtel.co.ug,moses.iga@waridtel.co.ug,robert.walakira@waridtel.co.ug,cccontactcentremanagementteam@waridtel.co.ug,ritah.nakafero@waridtel.co.ug,Louis Pereira <louisp@spancobpo.com>,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>';
$warid_pesa_ivrflash_bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

//$ccba_list = "ccbusinessanalysis@waridtel.co.ug";

$warid_pesa_ivrflash_to_list =  "steven.ntambi@waridtel.co.ug";
$warid_pesa_ivrflash_bcc_list = '';

//SPECIAL RUNS LIKE EASTER BREAK ....
//$special_run = 1; //THIS SHOULD BE UNSET AFTER THE SCRIPT HAS BEEN INITIATED

if(date('D') == 'Mon' and $special_run != 1){
	//Running the reports for Saturday (Fri) and Sunday (Sat) then Monday (Sun)
	echo date('Y-m-d H:i:s')." : Initiating WARID PESA report run for the past 3 days Saturday (Fri), Sunday (Sat) then Monday (Sun) ... \n";
	$days = array(date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
}elseif($special_run == 1){
	echo "UN SET special_run variable  [".$special_run."] !!!!!! AFTER RUNNING THIS SCRIPT !!!!!!!! \n\n";
	//Running the reports for Saturday (Fri) and Sunday (Sat) then Monday (Sun)
	//echo date('Y-m-d H:i:s')." : Initiating WARID PESA report run for the special run ... \n";
	//PUBLIC HOLIDAY DURING THE WEEK
	//$days = array(date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
	//3 DAY WEEKEND
	//$days = array(date('Y-m-d',strtotime("-4 days")),date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
}else{
	//Running the reports for other days
	echo date('Y-m-d H:i:s')." : Initiating WARID PESA report run for yesterday ... \n";
	$days = array(date('Y-m-d',strtotime("-1 days")));
}

foreach($days as $key=>$day){	
	

	$WARID_PESA_ACTIVITIES_HTML = generate_warid_pesa_activities_report($day);
	
	sendHTMLemail(
				  $to=$warid_pesa_to_list,
				  $bcc=$warid_pesa_to_bcc,
				  $message=$WARID_PESA_ACTIVITIES_HTML,
				  $subject='Customer care WARID PESA activities report for '.date_format(date_create($day),'D jS M Y'),
				  $from='DO NOT REPLY <ccnotify@waridtel.co.ug>'
	);
	
	/*echo date('Y-m-d H:i:s')." : Running IVR Flash report ... \n";
	
	$WARID_PESA_IVR_FLASH_HTML = display_waridpesa_sl_trends(generate_waridpesa_sl_trends($day));
	sendHTMLemail(
				  $to=$warid_pesa_ivrflash_to_list,
				  $bcc=$warid_pesa_ivrflash_bcc_list,
				  $message=$WARID_PESA_IVR_FLASH_HTML,
				  $subject='Contact Centre Warid pesal Flash report for '.date_format(date_create($day),'D jS M Y'),
				  $from="DO NOT REPLY<ccnotify@waridtel.co.ug>"
	);*/
}

?>
