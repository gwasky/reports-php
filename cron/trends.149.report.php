<?
echo date("Y-m-d H:i:s")." : starting 149 service trending ....\n";
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$recon_list = 'david.daka@waridtel.co.ug,moses.iga@waridtel.co.ug,robert.walakira@waridtel.co.ug,steven.ntambi@waridtel.co.ug,macdavid.mugga@waridtel.co.ug,moses.wamono@waridtel.co.ug,john.aogon@waridtel.co.ug,Jamil Kireri/IT/Kampala <jamil.kireri@waridtel.co.ug>,Moses Iga/CC/Kampala <moses.iga@waridtel.co.ug>,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>';
//, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>

//$recon_list = 'steven.ntambi@waridtel.co.ug';

$bcc = 'ccbusinessanalysis@waridtel.co.ug';

$date=date('Y-m-d');
sendHTMLemail($to=$recon_list,$bcc,$message=display_149_trend_report(generate_149_trend_report()),$subject='Pakalast/Kankolera/Pepe and Pakachini/Ovanite/Beera Ko Trends '.date('Y-m-d'),$from="CCREPORTS <ccnotify@waridtel.co.ug>");

echo date("Y-m-d H:i:s")." : stopping 149 service trending ....\n";
?>