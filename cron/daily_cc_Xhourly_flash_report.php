<?
//TO BE RUN ON CCBAO2
//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

//Sriram Yarlagadda <sriram.yarlagadda@waridtel.co.ug>, Manish Dashputre/Finance/Kampala <manish.dashputre@waridtel.co.ug>, Shailendra Naidu/Commercial/Kampala <shailendra.naidu@waridtel.co.ug>, Shaikh Waris/Commercial/Kampala <shaikh.waris@waridtel.co.ug>

$to_list = 'Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan Guatam <vipang@spancobpo.com>, Pavan B<pavanb@spancobpo.com>, Isaac Kyohere/Commercial/Kampala <isaac.kyohere@waridtel.co.ug>, Bryan Muwonge/Marketing/Kampala <bryan.muwonge@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>,Nellie Mwandha/Commercial/Kampala <nellie.mwandha@waridtel.co.ug>, David Daka/CC/Kampala <david.daka@waridtel.co.ug>, Christine Aanyu/CC/Kampala <Christine.Aanyu@waridtel.co.ug>, Mike M. Muhumuza/CC/Kampala <Mike.Muhumuza@waridtel.co.ug>, Mildred Nakalema/Products & Services/Kampala <mildred.nakalema@waridtel.co.ug>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>';

//$to_list = 'steven.ntambi@waridtel.co.ug';

$bcc_list = 'ccbusinessanalysis@waridtel.co.ug, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>';

$date = date('Y-m-d H:i:s');
$html = generate_cc_xhourly_report($input_date=$date);

//echo $html;

sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=attach_html_container($title='',$body=$html),$subject='Top Call Drivers comparison '.substr($date,0,10),$from="CALL CENTRE PERFORMANCE<ccnotify@waridtel.co.ug>");

?>
