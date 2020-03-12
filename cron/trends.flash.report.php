<?
error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);


require_once('/srv/www/htdocs/reports/cron/lib_gib.php');

//$recon_list = 'danrweki@yahoo.com';
//$recon_list = 'daniel.katatumba@waridtel.co.ug';
//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';


//$recon_list .= 'ccbusinessanalysis@waridtel.co.ug,moses.wamono@waridtel.co.ug,herbert.luyinda@waridtel.co.ug,david.daka@waridtel.co.ug,james.ddungu@waridtel.co.ug,robert.walakira@waridtel.co.ug,james.busulwa@waridtel.co.ug,mathias.nazyo@waridtel.co.ug,cccontactcentremanagementteam@waridtel.co.ug';
$recon_list = 'gibson.wasukira@waridtel.co.ug';

//sendHTMLemail($to=$recon_list,$bcc,$message=display_cc_flash_report(generate_cc_flash_report()),$subject='Contact Centre Flash report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

//sendHTMLemail($to=$recon_list,$bcc,$message=display_cc_flash_report(generate_cc_flash_report()),$subject='Contact Centre Flash report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

sendHTMLemail($to=$recon_list,$bcc,$message=display_pakalast_trend_report(generate_pakalast__report()),$subject='Pakalast Trends Report '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

//sendHTMLemail($to=$recon_list,$bcc,$message=generate_cases_count()."\n".generate_closed_cases_count()."\n".generate_offered_ivr_count()."\n".generate_answered_ivr_count()."\n".generate_abandon_ivr_count()."\n".generate_prepaid_servicelevel_ivr_count(),$subject='PEAK DROP ANALYSIS '.date_reformat(date('Y-m-d'),'%D %M %Y').' '.date('H:i:s'),$from='PEAK DROP ANALYSIS <ccnotify@waridtel.co.ug>');

?>
