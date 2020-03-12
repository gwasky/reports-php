<?
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

//exit("ALERT!!! ->> This report is now called to run within the /srv/www/htdocs/reports/cron/gsm.cron.php file .... \n");

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = 'moses.wamono@waridtel.co.ug,herbert.luyinda@waridtel.co.ug,david.daka@waridtel.co.ug,moses.iga@waridtel.co.ug,robert.walakira@waridtel.co.ug,james.busulwa@waridtel.co.ug,cccontactcentremanagementteam@waridtel.co.ug,ritah.nakafero@waridtel.co.ug';

$to_list = 'steven.ntambi@waridtel.co.ug';

//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$date = date('Y-m-d',strtotime("-1 days"));
sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=display_waridpesa_sl_trends(generate_waridpesa_sl_trends($input_date=$date)),$subject='Contact Centre Warid Pesa Flash report for '.$date,$from="DO NOT REPLY<ccnotify@waridtel.co.ug>");

?>
