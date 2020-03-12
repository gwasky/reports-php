<?
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

//$recon_list = 'CCCONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,robert.walakira@waridtel.co.ug,gaudy.baine@waridtel.co.ug,moses.wamono@waridtel.co.ug,james.ddungu@waridtel.co.ug';
$recon_list = 'gibson.wasukira@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';


$date=date('Y-m-d');
$from = '2010-08-01';
$yesterday = date("Y-m-d", strtotime("-2 days"));
//sendHTMLemail($to,$bcc,$message,$subject,$from)
sendHTMLemail($to=$recon_list,
			$bcc = $bcc_list,
			$message=generate_repeat_calls_call_status($from,$yesterday)
			.display_repeat_wrap_callstatus_report(generate_callstatus_pie($from,$yesterday))
			.display_repeat_wrap_customer_satisfaction_report(generate_customer_satisfaction_pie($from,$yesterday))
			.display_repeat_wrap_customer_profile_report(generate_customer_profile_pie($from,$yesterday)),
			$subject ='Repeat Wrapup Customer Feedback Report '.date('Y-m-d'),
			$from="CCREPORTS <ccnotify@waridtel.co.ug>");
//sendHTMLemail($to=$recon_list,$bcc,$message= generate_repeat_calls_call_status($yesterday),$subject='Repeat Wrapup Customer Feedback Report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");
?>
