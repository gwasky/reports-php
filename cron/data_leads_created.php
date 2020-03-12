<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
echo date('Y-m-d H:i:s')." - Started Leads created \n";

exit("This report has not been commissioned");
require_once('/srv/www/htdocs/reports/cron/lib.php');

$sales_list = 'Rogers Byamukama <Rogers.Byamukama@ug.airtel.com>, Deogratias Biwaga <Deogratias.Biwaga@ug.airtel.com>';

$core_list = 'Gaudy Baine/Service Experience/Uganda <gaudy.baine@ug.airtel.com>, George A. Waigumbulizi <George.Waigumbulizi@ug.airtel.com>, Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>, Yvonne Wekesa <Yvonne.Wekesa@ug.airtel.com>';

$to_list = $sales_list.','.$core_list;
$to_list = $core_list;
//$to_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>,Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>,DATATEAM@waridtel.co.ug';

$queue_cs_list = "Invalid Prospect";

sendHTMLemail(
	$to=$to_list,
	$bcc=$bcc_list,
	$message=attach_html_container(
				$title='Leads created this month up to '.date('l jS F Y',strtotime("-3 days")),
				$body = generate_sales_lead_report($from,$to,$queues,$status,$platform)
			),
	$subject="Leads created this month up to ".date('l jS F Y',strtotime("-3 days")),
	$from="Data Reporting <ccnotify@waridtel.co.ug>"
);

echo date('Y-m-d H:i:s')." - Stopped Leads created execution \n";

?>