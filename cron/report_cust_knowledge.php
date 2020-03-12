<?
//error_reporting(E_ALL);
error_reporting(0);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$use_date = date("Y-m-d", strtotime("-1 days"));

//$recon_list  = 'retention@waridtel.co.ug,moses.wamono@waridtel.co.ug,ccresourcedevelopment@waridtel.co.ug';
$recon_list = 'gibson.wasukira@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';
sendHTMLemail($to=$recon_list, $bcc = $bcc_list, $message= generate_cust_knowledge($use_date), $subject ='Students Engagement  Program for '.$use_date, $from="CCREPORTS <ccnotify@waridtel.co.ug>");

?>
