<?
//TO BE EXECUTED ON CCBA02
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
$root_dir = '/srv/www/htdocs/reports/cron/';
require_once($root_dir.'lib.php');
 
$list = 'Charles.Masiga@ug.airtel.com, Charles.Masiga@isonbpo.com, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>, Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Trina Mirembe/CC/Kampala <Trina.Mirembe@waridtel.co.ug>, Asha Namukasa/CC/Kampala <Asha.Namukasa@waridtel.co.ug>, Rachel Atuhaire/CC/Kampala <rachel.atuhaire@waridtel.co.ug>, Robert Walakira/CC/Kampala <robert.walakira@waridtel.co.ug>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>';
//$list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug,Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>';

$use_date = date('Y-m-d',strtotime("-1 days"));
echo date('Y-m-d H:i:s')." - Start Social Media Wrap up execution for [".$use_date."]\n";

$html = attach_html_container($title='',$body=generate_socialmedia_correspondence_report($date = $use_date,$period='month'));

sendHTMLemail(
			  $to=$list,
			  $bcc=$bcc_list,
			  $message=$html,
			  $subject='Social Media Wrap ups as at '.date('l jS F Y',strtotime($use_date)),
			  $from='Social Media Reporting <ccnotify@waridtel.co.ug>'
			  );

echo date('Y-m-d H:i:s')." - Ended Social Media Wrap up execution for [".$use_date."]\n";

?>