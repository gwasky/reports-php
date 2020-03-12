<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$list = 'robert.walakira@waridtel.co.ug,moses.wamono@waridtel.co.ug,ccbusinessanalysis@waridtel.co.ug,CCCONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,gaudy.baine@waridtel.co.ug,moses.wamono@waridtel.co.ug,moses.iga@waridtel.co.ug,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>, Abhishek Mudgal/Airtel Africa <abhishek.mudgal@africa.airtel.com>';
//$list = 'steven.ntambi@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$date=date('Y-m-d');
sendHTMLemail($to=$list,$bcc=$bcc_list,$message=display_cpc_month_trend_report(generate_cpc_monthly_trends()),$subject='CPC Trends for Received, Handled and Abandoned. Trends Graph Report for the period '.date("M Y", strtotime("-12 months")).' to '.date('M Y', strtotime("-1 months")),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

?>