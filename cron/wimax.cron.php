<?php
error_reporting(E_ERROR);
//error_reporting(E_ALL);

$root_dir = '/srv/www/htdocs/reports/';
require_once($root_dir.'cron/lib.php');


$airtel_ccreports = 'David Daka <David.Daka@ug.airtel.com>, Jackie Rozario/Customer Service/Uganda <Jackie.Rozario@ug.airtel.com>, Jael C. Wawulira/Customer Service/Uganda <Jael.wawulira@ug.airtel.com>, kyeyune dorothy/Customer Service/Uganda <dorothy.kyeyune@ug.Airtel.com>, Lynda Nabayiinda/Customer Service/uganda <Lynda.Nabayiinda@ug.airtel.com>, Macdavid Mugga/Customer Service/Uganda <Macdavid.Mugga@ug.airtel.com>, Nelson Mugisha/Customer Service/uganda <Nelson.Mugisha@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>, Steven Ntambi/Customer Service/Uganda <Steven.Ntambi@ug.airtel.com>,Abhishek Mudgal/Customer Service/Airtel Africa <abhishek.mudgal@africa.airtel.com>, Arindam Chakrabarty/Airtel Ug <Arindam.Chakrabarty@ug.airtel.com>, Somasekhar VG /Airtel UG <Somasekhar.VG@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>,Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>, Kenneth Muzahura/Customer Service/Uganda <Kenneth.Muzahura@ug.airtel.com>';

$gsm_to_list = $airtel_ccreports.", Pavan Boyina/ISONBPO/Uganda <pavan.boyina@isonbpo.com>, Lucy Komakech/ISONBPO/Uganda <lucy.komakech@isonbpo.com>, Vipan G <vipang@spancobpo.com>,  Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Amit Mehta <amitmehta@spancobpo.com>";
//$gsm_to_list = "ccbusinessanalysis@waridtel.co.ug";

/*
if(date('D') == 'Mon'){
	echo date('Y-m-d H:i:s')." : Initiating DATA report run for the past 3 days Saturday (Fri), Sunday (Sat) then Monday (Sun) ... \n";
	$days = array(date('Y-m-d',strtotime("-3 days")),date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
}else {
	echo date('Y-m-d H:i:s')." : Initiating DATA report run for yesterday ... \n";
	$days = array(date('Y-m-d',strtotime("-1 days")));
}
*/

echo date('Y-m-d H:i:s')." : Initiating DATA report run for yesterday ... \n";
$days = array(date('Y-m-d',strtotime("-1 days")));

foreach($days as $key=>$day){

	$WIMAX_ACTIVITIES_HTML = generate_wimax_activities_report($day);
	sendHTMLemail(
				  $to=$gsm_to_list,
				  $bcc=$gsm_to_bcc,
				  $message=$WIMAX_ACTIVITIES_HTML,
				  $subject='Customer Care DATA Activities report for '.date_format(date_create($day),'D jS M Y'),
				  $from='DO NOT REPLY <ccnotify@waridtel.co.ug>'
	);
}
?>