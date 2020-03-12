<?php

//error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

echo "INITIATING MOBILE DATA WRAPUP EXECUTION AT ".date("Y-m-d H:i:s")."</br>";

$use_date = date("Y-m-d", strtotime("-1 days"));

$recon_list = 'ronald.bogere@waridtel.co.ug,moses.wamono@waridtel.co.ug,vincent.lukyamuzi@waridtel.co.ug,david.daka@waridtel.co.ug,David.Nsubuga@waridtel.co.ug,david.kakembo@waridtel.co.ug,Moses.Mulambaazi@waridtel.co.ug,george.lule@waridtel.co.ug,gerald.magembe@waridtel.co.ug,joseph.kibuuka@waridtel.co.ug,Joseph.Semakula@waridtel.co.ug,josephine.tumwesige@waridtel.co.ug,musa.musazi@waridtel.co.ug,samuel.wanyama@waridtel.co.ug,Victor.SSebugwawo@waridtel.co.ug, Kenneth Muzahura/Customer Service/Uganda <Kenneth.Muzahura@ug.airtel.com>,Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>,Jackie Rozario/Customer Service/Airtel Ug <Jackie.Rozario@ug.airtel.com>';

//$recon_list = 'steven.ntambi@waridtel.co.ug';

$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$html = attach_html_container($title='',$body=generate_yesterday_mobile_data_top_wrapups());

//exit($html.'<hr> EXITING!');

sendHTMLemail(
	$to = $recon_list,
	$bcc = $bcc_list,
	$message = $html,
	$subject ='Mobile Data Wrapups For '.$use_date, $from="CCREPORTS <ccnotify@waridtel.co.ug>");
	

echo "END OF MOBILE DATA WRAPUP EXECUTION AT ".date("Y-m-d H:i:s");	

?>