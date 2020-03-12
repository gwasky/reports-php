<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_PARSE | E_ERROR);
echo date('Y-m-d H:i:s')." - Started COMPLETE SITE SURVEY LEADS execution \n";

require_once('/srv/www/htdocs/reports/cron/lib.php');

$sales_list = 'Rogers Byamukama <Rogers.Byamukama@ug.airtel.com>,Deogratias Biwaga <Deogratias.Biwaga@ug.airtel.com>';

$core_list = 'PACKETCORENETWORKS@waridtel.co.ug,Gaudy Baine/Service Experience/Uganda <gaudy.baine@ug.airtel.com>, George A. Waigumbulizi <George.Waigumbulizi@ug.airtel.com>, Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>, Yvonne Wekesa <Yvonne.Wekesa@ug.airtel.com>';

$to_list = $sales_list.','.$core_list;
//$to_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Geoffrey Kasibante/Customer Care/Airtel Ug <Geoffrey.Kasibante@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>,DATATEAM@waridtel.co.ug';

$queue_cs_list = "CC Complete Site Surveys";

sendHTMLemail(
	$to=$to_list,
	$bcc=$bcc_list,
	$message=attach_html_container(
				$title='Complete site survey leads in the last 60 days as at '.date('l jS F Y'),
				$body = generate_queue_leads(date('Y-m-d'),$queue_cs_list,$queue_age=" between 2 and 61 ")
			),
	$subject='Leads with complete site surveys in the last 60 days as at '.date('l jS F Y'),
	$from="Data Reporting <ccnotify@waridtel.co.ug>"
);

echo date('Y-m-d H:i:s')." - Stopped COMPLETE SITE SURVEY LEADS execution \n"; 

?>