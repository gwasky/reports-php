<?
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');


$recon_list = 'gibson.wasukira@waridtel.co.ug';
//$recon_list = 'ccreports@waridtel.co.ug,products&services@waridtel.co.ug,robert.walakira@waridtel.co.ug,moses.wamono@waridtel.co.ug,raymond.baziwane@waridtel.co.ug,julia.rweju@waridtel.co.ug,john.aogon@waridtel.co.ug';
$bcc = 'ccbusinessanalysis@waridtel.co.ug';

$date=date('Y-m-d');
sendHTMLemail($to=$recon_list,$bcc,$message=display_selfcare_wrapup_report(generate_self_care_report()),$subject='CALL CENTER INQUIRIES ON USSD SELF CARE MENU Report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");
?>
