<?php
//TO BE EXECUTED ON CCBA01
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
error_reporting(E_PARSE | E_ERROR);

echo "INITIATING PROJECT INVITICUS/VOC TOP X WRAP UPS AT ".date("Y-m-d H:i:s")."\n";

$use_date = date("Y-m-d", strtotime("-1 days"));

$to_list = 'Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>';

$cc_list = 'Brenda Ninsima Mugerwa/CC/Kampala <Brenda.NinsimaByaruhanga@waridtel.co.ug>, Robert Walakira/CC/Kampala <robert.walakira@waridtel.co.ug>, Moses Iga/CC/Kampala <moses.iga@waridtel.co.ug>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Vincent Lukyamuzi/CC/Kampala <vincent.lukyamuzi@waridtel.co.ug>,Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';

//$to_list = 'Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>';
//$to_list = 'vincent.lukyamuzi@waridtel.co.ug, Steven Ntambi/CC/Kampala <Steven.Ntambi@waridtel.co.ug>';
//$cc_list = '';

$week_dates = array(date('Y-m'.'-01'),date('Y-m'.'-08'),date('Y-m'.'-15'),date('Y-m'.'-22'),date('Y-m'.'-29'));

if(in_array(date('Y-m-d'), $week_dates)){
	$body = generate_inviticus_voc_topX_wrapups_week();
	$body[html] = attach_html_container($title='',$body[html]);
	$body[attach] = attach_html_container($title='',$body[attach]);
	
	my_mail(
	$to = $to_list,
	$cc = $cc_list,
	$bcc = '',
	$message = $body[html],
	$subject = 'Top Call Drivers Weekly - '.date('F Y',strtotime("-1 days")),
	$from = "CC WRAPUPS<ccnotify@waridtel.co.ug>",
	$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
);
}

$body = generate_inviticus_voc_topX_wrapups($use_date,5);
$body[html] = attach_html_container($title='',$body[html]);
$body[attach] = attach_html_container($title='',$body[attach]);

my_mail(
	$to = $to_list,
	$cc = $cc_list,
	$bcc = '',
	$message = $body[html],
	$subject = 'Top Call Drivers Daily - '.date('F Y',strtotime("-1 days")),
	$from = "CC WRAPUPS<ccnotify@waridtel.co.ug>",
	$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
);

echo "END OF PROJECT INVITICUS/VOC TOP X WRAP UPS EXECUTION AT ".date("Y-m-d H:i:s")."\n";	



echo "INITIATING VAS TOP X WRAP UPS AT ".date("Y-m-d H:i:s")."\n";

$body = generate_vas_call_drivers($use_date,5);
$body[html] = attach_html_container($title='',$body[html]);
$body[attach] = attach_html_container($title='',$body[attach]);

my_mail(
	$to = $to_list,
	$cc = $cc_list,
	$bcc = '',
	$message = $body[html],
	$subject = 'VAS Top Call Drivers - '.date('F Y',strtotime("-1 days")),
	$from = "CC WRAPUPS<ccnotify@waridtel.co.ug>",
	$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
);

echo "END OF VAS TOP X WRAP UPS EXECUTION AT ".date("Y-m-d H:i:s")."\n";	
?>