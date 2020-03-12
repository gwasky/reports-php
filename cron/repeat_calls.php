<?php
//error_reporting(E_ALL);
error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);


require_once('/srv/www/htdocs/reports/cron/lib_gib.php');

$recon_list = 'gibson.wasukira@waridtel.co.ug';

sendHTMLemail($to=$recon_list,$bcc,$message=generate_repeat_calls_call_status(),$subject='Repeat Calls Report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

?>