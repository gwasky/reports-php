<?
//TO BE EXECUTED ON CCBA01
//error_reporting(E_WARNING | E_PARSE | E_ERROR);\
error_reporting(E_ERROR);

$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

$myquery = new custom_query();

//GET THE MSISDSN WHOSE BIRTHDAY IS TODAY ....
custom_query::select_db('customer_birthdays');
$customer_msisdn_query = "
	SELECT
		autobirth.msisdn,
		autobirth.customer_name
	FROM
		autobirth
	WHERE
		right(autobirth.birthdate,5) = right(date(now()),5)
";
$customer_msisdnlist = $myquery->multiple($customer_msisdn_query);
//GET THE TOP MOST ACTIVE MESSAGE
$birthday_text_query = "
	SELECT
		sms_text.text
	FROM
		sms_text
	where
		sms_text.category='birthdays' and
		sms_text.status='active'
	order by
		sms_text.id desc
	limit 1
";
$birthday_text_result = $myquery->single($birthday_text_query);

echo "BDAY Text is -> `".str_replace(array("\r\n\r\n"),array(" "),$birthday_text_result[text])."`\n";

//GET THE NUMBER OF RECORDS
$no_of_records_query = "
	SELECT
		count(autobirth.msisdn) as number
	FROM
		autobirth
	WHERE
		autobirth.birthdate != '0000-00-00'
";
$no_of_records_result = $myquery->single($no_of_records_query);

if(strlen($birthday_text_result[text]) == 0){
	//send an email ccba
	sendHTMLemail($to='CCBUSINESSANALYSIS@waridtel.co.ug',$bcc,$message='THERE IS NO BIRTHDAT TEXT',$subject='BIRTHDAY SMS - ERROR',$from='CC BIRTHDAY SMS<ccnotify@waridtel.co.ug>');
	exit($message);
}

//SEND THE SMS TO THE CUSTOMERS
//$customer_msisdnlist = array(array('msisdn'=>'256704008736','customer_name'=>'Steven Ntambi'),array('msisdn'=>'customer_name','customer_name'=>'Faith Bugonzi'));
//$customer_msisdnlist = array(array('msisdn'=>'customer_name','customer_name'=>'Faith Bugonzi'),array('msisdn'=>'256704008736','customer_name'=>'23946293864'));
//$customer_msisdnlist[0][customer_name] = '9678423842';

//array_push($customer_msisdnlist,array('msisdn'=>'256704008736','customer_name'=>'Steven Ntambi'));
//array_push($customer_msisdnlist,array('msisdn'=>'256752600229','customer_name'=>'Faith Bugonzi'));

foreach($customer_msisdnlist as $row){
	
	if(intval($row[customer_name]) == 0 and trim($row[customer_name]) != ''){
		$sms_text = str_replace(array("Customer"),ucwords(strtolower($row[customer_name])),$birthday_text_result[text]);
	}else{
		$sms_text = $birthday_text_result[text];
	}
	
	$send_result = send_this_sms($msisdn=$row[msisdn],$message=$sms_text,'SMS','Birthday');
	//log[0] = SUCCESS
	//log[1] = FAILURE
	++$log[$send_result[status]];
	
	//$clean_text = str_replace(array("\r\n\r\n"),array(" "),$sms_text);
	//echo "[".$row[msisdn]."] - [".$send_result[status]."] >> [".$clean_text."] to \n";
	
	if(++$no_sent == 500){
		$sleep_time = 2;
		sleep($sleep_time);
		unset($no_sent);
		//echo "Sleeping for ".$sleep_time."\n";;
	}
}
unset($no_sent);

print_r($log);

//GET THE CC STAKE HOLDERS FOR NOTIFICATION
$stake_holder_query = "
	SELECT
		broadcastlist.msisdn
	FROM
		broadcastlist
	WHERE
		broadcastlist.status = 'active'
";
$stake_holder_list = $myquery->multiple($stake_holder_query);

//TEST
//$stake_holder_list = array(array('msisdn'=>'256704008736'),array('msisdn'=>'256752600229'));

//SEND THE SMS TO THE STAKEHOLDERS
foreach($stake_holder_list as $row){
	$sms_text = "Date: ".date('l, jS F Y')."\n\n";
	$sms_text .= number_format($log[0],0)." birthday messages sent.\n";
	if($log[1] > 0){
		$sms_text .= number_format($log[1],0)." birthday messages failed.\n";
	}
	$sms_text .= "\nTotal records with birthdates : ".number_format($no_of_records_result[number],0);
	
	$result = send_this_sms($msisdn=$row[msisdn],$message=$sms_text);
	//$clean_text = str_replace(array("\r\n\r\n"),array(" "),$message);
	//echo "Sending [".$row[msisdn]."] - [".$result[status]."] :> [".$clean_text."]\n";
	
	if(++$no_sent == 5){
		$sleep_time = 2;
		sleep($sleep_time);
		unset($no_sent);
		//echo "Sleeping for ".$sleep_time."\n";;
	}
}

//SEND THE LOG MESSAGE TO STAKEHOLDER EMAILS
$receipients = 'david.daka@ug.waridtel.co.ug,robert.walakira@ug.waridtel.co.ug,steven.ntambi@ug.waridtel.co.ug,macdavid.mugga@ug.waridtel.co.ug';
$receipients = 'CCBUSINESSANALYSIS@waridtel.co.ug';
//$receipients = 'steven.ntambi@waridtel.co.ug';
sendHTMLemail($to=$receipients,$bcc='',$message=nl2br($sms_text)."<hr>".nl2br($birthday_text_result[text]),$subject='Daily Birthday Greetings',$from='CC BIRTHDAY SMS<ccnotify@waridtel.co.ug>');

?>