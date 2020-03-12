<?php
//TO BE EXECUTED ON CCBA01
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
error_reporting(E_PARSE | E_ERROR);

echo "INITIATING SERVICE RECOVERY AT ".date("Y-m-d H:i:s")."\n";

$use_date = date("Y-m-d", strtotime("-1 days"));

$to_list = 'Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>, Victor Ndugga/CC/Kampala <victor.ndugga@waridtel.co.ug>, Macdavid Mugga/CC/Kampala <macdavid.muggaa@waridtel.co.ug>, Viola Natukunda/CC/Kampala <Viola.Natukunda@waridtel.co.ug>';

$cc_list = 'Brenda Ninsima Mugerwa/CC/Kampala <Brenda.NinsimaByaruhanga@waridtel.co.ug>, Robert Walakira/CC/Kampala <robert.walakira@waridtel.co.ug>, Moses Iga/CC/Kampala <moses.iga@waridtel.co.ug>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Vincent Lukyamuzi/CC/Kampala <vincent.lukyamuzi@waridtel.co.ug>,Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';

//$to_list = 'Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>';
//$to_list = 'vincent.lukyamuzi@waridtel.co.ug, Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>';
//$cc_list = '';


$body = generate_service_recovery_report($use_date);
$body[html] = attach_html_container($title='',$body[html]);
$body[attach] = attach_html_container($title='',$body[attach]);

my_mail(
	$to = $to_list,
	$cc = $cc_list,
	$bcc = '',
	$message = $body[html],
	$subject = 'Service Recovery Report - '.date('F Y',strtotime("-1 days")),
	$from = "CC SERVICE RECOVERY<ccnotify@waridtel.co.ug>",
	$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
);

echo "END OF SERVICE RECOVERY EXECUTION AT ".date("Y-m-d H:i:s")."\n";	
?>