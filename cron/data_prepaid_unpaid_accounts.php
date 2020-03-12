<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
echo date('Y-m-d H:i:s')." - Started : Prepaid unpaid accounts \n";

require_once('/srv/www/htdocs/reports/cron/lib.php');

$sales_list = '';

$core_list = 'Kenneth Muzahura/Customer Service/Uganda <Kenneth.Muzahura@ug.airtel.com>, Isaac Kiyingi/Customer Service/Uganda <Isaac.Kiyingi@ug.airtel.com>, Jennifer Nakaddu/Customer Service/Uganda <Jennifer.Nakaddu@ug.airtel.com>, Robert Walakira/Customer Service/Uganda <Robert.Walakira@ug.airtel.com>, Lynda Nabayiinda/Customer Service/uganda <Lynda.Nabayiinda@ug.airtel.com>, Leonard Kibuuka/Enterprise/Uganda <Leonard.Kibuuka@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>';

$to_list = $sales_list.','.$core_list;
$to_list = $core_list;
//$to_list = 'steven.ntambi@waridtel.co.ug';
$bcc_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>,Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>,DATATEAM@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$body = generate_prepaid_unpaid_accounts(date('Y-m-d',strtotime("-5 days")));

if($body == "NO DATA"){
	//Cases where there is no data to report
	$body = "There are no prepaid accounts with uncleared balancess for more than 5 days. ";
	$to_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>,Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>,DATATEAM@waridtel.co.ug';
	$bcc_list = '';
	
	echo "No data ... sending notification to CCBA \n";
}else{
	echo "Data there ... sending notification DATA team \n";
}

sendHTMLemail(
	$to=$to_list,
	$bcc=$bcc_list,
	$message=attach_html_container(
				$title='Unpaid prepaid data accounts ',
				$body
			),
	$subject="Unpaid prepaid data accounts older than 5 days",
	$from="Data Reporting <ccnotify@waridtel.co.ug>"
);

echo date('Y-m-d H:i:s')." - Stopped : Prepaid unpaid accounts \n";
?>