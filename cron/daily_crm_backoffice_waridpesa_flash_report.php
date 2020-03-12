<?
echo date('Y-m-d H:i:s')." - Started Back Office - Warid Pesa Flash execution \n";
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$use_date = date("Y-m-d", strtotime("-1 days"));

$recon_list = 'Viola Natukunda/CC/Kampala <Viola.Natukunda@waridtel.co.ug>,Victor Ndugga/CC/Kampala <victor.Ndugga@waridtel.co.ug>,Macdavid Mugga/CC/Kampala <macdavid.mugga@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Sandra Nabakooza/CC/Kampala <Sandra.Nabakooza@waridtel.co.ug>, Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>';
//$recon_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$html = generate_backoffice_waridpesa_flash($use_date);

sendHTMLemail($to=$recon_list, $bcc = $bcc_list, $message=attach_html_container($title='',$body=$html), $subject ='CRM Back Office (WARID PESA) Flash Report', $from="WARID PESA CUSTOMER CARE BACK OFFICE <ccnotify@waridtel.co.ug>");

echo date('Y-m-d H:i:s')." - Ended Back Office - Warid Pesa Flash execution \n";

?>
