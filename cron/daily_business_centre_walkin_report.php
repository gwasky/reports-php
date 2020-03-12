<?
//RUN ON CCBA02

//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

//exit("ALERT!!! ->> This report is now called to run within the /srv/www/htdocs/ccportal/cron/gsm.cron.php file .... \n");

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = 'Amanda Nanzira/CC/Kampala <amanda.nanzira@waridtel.co.ug>, Peter Angole/CC/Kampala <peter.angole@waridtel.co.ug>, Samuel Mwanje/CC/Kampala <Samuel.Mwanje@waridtel.co.ug>, Joseph Nsamba/CC/Kampala <joseph.nsamba@waridtel.co.ug>, Macdavid Mugga/CC/Kampala <macdavid.mugga@waridtel.co.ug>, Moses Wamono/CC/Kampala <moses.wamono@waridtel.co.ug>, Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>, Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>';

//Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>

//$to_list = 'steven.ntambi@waridtel.co.ug';

$bcc_list = 'ccbusinessanalysis@waridtel.co.ug,Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>';

$date = date('Y-m-d',strtotime("-1 days"));

$html = generate_business_centre_walkins_daily($date);

//echo $html;

sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=attach_html_container($title='',$body=$html),$subject='Business Centre Walkins from 30 days back',$from="Business centre walkins<ccnotify@waridtel.co.ug>");

?>
