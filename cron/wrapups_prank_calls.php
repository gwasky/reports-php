<?
//TO BE RUN ON CCBA01
require_once('/srv/www/htdocs/reports/cron/lib.php');
error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';

$to_list .= 'Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>';

//$to_list = 'steven.ntambi@waridtel.co.ug';
$cc_list = 'Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Vincent Lukyamuzi/CC/Kampala <vincent.lukyamuzi@waridtel.co.ug>,Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';
if($_GET[upto] == ''){
	//CRON OPTION "This file is now called from gsm.cron.php and not directly here. Exiting ....\n";
	$upto = date('Y-m-d',strtotime("-1 days"));
	$html = attach_html_container($title='',generate_prank_call_wrapups($upto));
}else{
	//VIA A BROWSER
	$upto = $_GET[upto];
	$html = attach_html_container($title='',generate_prank_call_wrapups($upto));
	echo $html;
}

my_mail(
	$to = $to_list,
	$cc = $cc_list,
	$bcc = '',
	$message = $html,
	$subject = 'WTU Silent and Dropped calls MTD '.date("F Y", strtotime("-1 days")),
	$from = "CCREPORTS<ccnotify@waridtel.co.ug>"
);
?>
