<?
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$use_date = date("Y-m-d", strtotime("-1 days"));

//$recon_list = 'gibson.wasukira@waridtel.co.ug';
$recon_list  = 'faridah.namutebi@waridtel.co.ug,moses.wamono@waridtel.co.ug,moses.iga@waridtel.co.ug,macdavid.mugga@waridtel.co.ug,robert.walakira@waridtel.co.ug,david.nsiyona@waridtel.co.ug,Louis Pereira <louisp@spancobpo.com>,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>';

$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

sendHTMLemail($to=$recon_list, $bcc = $bcc_list, $message=display_chap_chap_report(generate_chapchap($use_date)), $subject ='Chap Chap Inquiries for '.$use_date, $from="CCREPORTS <ccnotify@waridtel.co.ug>");

//sendHTMLemail($to=$recon_list, $bcc = $bcc_list, $message=display_chap_chap_report(generate_chapchap('2011-01-31')).display_chap_chap_report(generate_chapchap('2011-02-14')), $subject ='Chap Chap Inquiries for '.$use_date, $from="CCREPORTS <ccnotify@waridtel.co.ug>");

?>
