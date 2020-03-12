<?
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$to_list = 'Lumonya Nangwala/Procurement & Logistics/Kampala <Lumonya.nangwala@waridtel.co.ug>,Victor Ndugga/CC/Kampala <victor.Ndugga@waridtel.co.ug>,Faridah Namutebi/Commercial/Kampala <faridah.namutebi@waridtel.co.ug>,Brian Kimathi/Sales/Kampala <brian.kimathi@waridtel.co.ug>,David C. Nsiyona/Sales/Kampala <David.Nsiyona@waridtel.co.ug>,moses.wamono@waridtel.co.ug,Macdavid Mugga/CC/Kampala <macdavid.mugga@waridtel.co.ug>,Joseph Babeiha/CC/Kampala <joseph.babeiha@waridtel.co.ug>,faridah Namuleme/CC/Kampala <faridah.namuleme@waridtel.co.ug>,Immaculate Kiconco/Sales/Kampala <immaculate.kiconco@waridtel.co.ug>,Ian Kateregga/Sales/Kampala <ian.kateregga@waridtel.co.ug>,Moses Baguma/Finance/Kampala <moses.baguma@waridtel.co.ug>,Jauharah Nakiberu <Jauharah.nakiberu@waridtel.co.ug>, dorothy.kyeyune@ug.airtel.com';

//COMMENT OUT THIS TO LIST SO THAT NORMAL OPERATIONS CAN PROCEED
//$to_list = 'vincent.lukyamuzi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';


//$use_date = date('2012-05-12');

if(date('D')=='Mon'){
	$use_date = date('Y-m-d', strtotime("-2 days"));
}else{
	$use_date = date('Y-m-d', strtotime("-1 days"));
}

$html = generate_wpesacourier_report($use_date);

sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=attach_html_container($title='',$body=$html),$subject='Warid Pesa Courier Report '.$use_date,$from="DO NOT REPLY <ccnotify@waridtel.co.ug>");
?>
