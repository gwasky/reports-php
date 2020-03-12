<?
echo date('Y-m-d H:i:s')." - Start SMS Manager execution \n";
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
$root_dir = '/srv/www/htdocs/reports/cron/';
require_once($root_dir.'lib.php');
 
$list = 'David Daka/Customer Service/Uganda <david.daka@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';
//, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>
//$list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'Steven Ntambi/Customer Service/Uganda<steven.ntambi@ug.airtel.com>, Faridah Namutebi/Customer Service/Uganda <Faridah.Namutebi@ug.airtel.com>';

$html = attach_html_container($title='',$body=generate_sms_manager_report_update());

sendHTMLemail(
			  $to=$list,
			  $bcc=$bcc_list,
			  $message=$html,
			  $subject='SMS Manager Summary as at '.date('l jS F Y',strtotime("-1 days")),
			  $from='SMS Manager <ccnotify@waridtel.co.ug>'
			  );

echo date('Y-m-d H:i:s')." - Ended SMS Manager execution \n";

?>