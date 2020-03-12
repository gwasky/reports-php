<?
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = 'Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>, David Daka <David.Daka@ug.airtel.com>, Geoffrey Ashaba/Sales/Uganda <Geoffrey.Ashaba@ug.Airtel.com>, Maria Nalukwago S/Customer Service/uganda <Maria.Nalukwagos@ug.airtel.com>, kyeyune dorothy/Customer Service/Uganda <dorothy.kyeyune@ug.Airtel.com>, Edward Atuhe/Sales/Uganda <Edward.Atuhe@ug.Airtel.com>, Faridah Namutebi/Customer Service/Uganda <Faridah.Namutebi@ug.airtel.com>';

//$to_list = 'steven.ntambi@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$html = display_pick_up_request_wrapups(get_pick_up_request_wrapups());

sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=attach_html_container($title='',$body=$html),$subject='Warid pesa courier pick up requests',$from="DO NOT REPLY <ccnotify@waridtel.co.ug>");
?>
