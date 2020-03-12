<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
echo date('Y-m-d H:i:s')." - Started NEWLY ACTIVATED ACCOUNTS execution \n";

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = 'RETENTION1@waridtel.co.ug,CREDIT&COLLECTION@waridtel.co.ug';
//$to_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug,moses.wamono@waridtel.co.ug,robert.walakira@waridtel.co.ug';

$use_date = date('Y-m-d',strtotime("-1 days"));

sendHTMLemail(
	$to=$to_list,
	$bcc=$bcc_list,
	$message=attach_html_container(
				$title='Newly activated DATA CRM accounts on '.date_format(date_create($use_date),'l jS F Y'),
				$body = generate_activated_accounts($from = $use_date,$to = $use_date)
			),
	$subject='Newly activated DATA CRM accounts on '.date_format(date_create($use_date),'l jS F Y'),
	$from="Data Reporting <ccnotify@waridtel.co.ug>"
);

echo date('Y-m-d H:i:s')." - Stopped NEWLY ACTIVATED ACCOUNTS execution \n";

?>