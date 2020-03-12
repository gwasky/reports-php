<?php
echo date('Y-m-d H:i:s')." - Start TREND Report Execution \n";
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$recon_list = 'david.daka@waridtel.co.ug,moses.iga@waridtel.co.ug,robert.walakira@waridtel.co.ug,steven.ntambi@waridtel.co.ug,macdavid.mugga@waridtel.co.ug,moses.wamono@waridtel.co.ug, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>';
//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';
//$recon_list = 'STEVEN.NTAMBI@waridtel.co.ug';
$bcc = 'ccbusinessanalysis@waridtel.co.ug';

sendHTMLemail($to=$recon_list,$bcc,$message=display_trends_by_day(),$subject='Customer Care Trends by Day',$from="DO NOT REPLY<ccnotify@waridtel.co.ug>");
//sendHTMLemail($to=$recon_list,$bcc,$message=display_analysis_report_T(),$subject='Customer Care Trends by Day '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

echo date('Y-m-d H:i:s')." - End TREND Report Execution \n";
?>