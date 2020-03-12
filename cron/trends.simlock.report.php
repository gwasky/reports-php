<?
//TO BE RUN FROM CCBAO1
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$recon_list = 'robert.walakira@waridtel.co.ug,moses.wamono@waridtel.co.ug,
CONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,moses.wamono@waridtel.co.ug,moses.iga@waridtel.co.ug,joseph.kibuuka@waridtel.co.ug,george.lule@waridtel.co.ug,victor.ssebugwawo@waridtel.co.ug,macdavid.mugga@waridtel.co.ug,caroline.abigaba@waridtel.co.ug,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>';

//$recon_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$date=date('Y-m-d');
$message = generate_simlock_trends();

//echo $message;
sendHTMLemail($to=$recon_list,$bcc=$bcc_list,$message,$subject='Sim Lock Trends Report '.date('Y-m-d'),$from="NO-REPLY <ccnotify@waridtel.co.ug>");
?>
