<?
echo date('Y-m-d H:i:s')." - Started Helpline Traffic Flash execution \n";
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$use_date = date("Y-m-d", strtotime("-1 days"));

$recon_list = 'Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>,Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Henry Sempa/IT/Kampala <Henry.Sempa@waridtel.co.ug>, ccbusinessanalysis@waridtel.co.ug, Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Pavan <pavanb@spancobpo.com>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>, Amit Mehta <amitmehta@spancobpo.com>';
//$recon_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$html = generate_helpline_traffic_flash($use_date);

//echo $html;

sendHTMLemail($to=$recon_list, $bcc = $bcc_list, $message=attach_html_container($title='',$body=$html), $subject ='Helpline Call traffic', $from="DO NOT REPLY <ccnotify@waridtel.co.ug>");

echo date('Y-m-d H:i:s')." - Ended Helpline Traffic Flash execution \n";

?>