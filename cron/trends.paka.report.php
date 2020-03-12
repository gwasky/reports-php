<?
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);
exit("Exiting ... run trends.149.report.php instead ... \n");
require_once('/srv/www/htdocs/reports/cron/lib.php');

//$recon_list = 'gibson.wasukira@waridtel.co.ug,steven.ntambi@waridtel.co.ug';
$recon_list = 'moses.wamono@waridtel.co.ug';
$bcc = 'ccbusinessanalysis@waridtel.co.ug';


$date=date('Y-m-d');
sendHTMLemail($to=$recon_list,$bcc,$message=display_pakalast_trend_report(generate_pakalast__report()),$subject='Pakalast Trends Report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");
?>