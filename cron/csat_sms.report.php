<?
error_reporting(E_ALL);
//error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');
$use_date = date("Y-m-d", strtotime("-1 days"));

$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$to_list = 'steven.ntambi@waridtel.co.ug';

$use_date = '2013-12-15';

$sms_text = "CSAT : Call Center answered calls.\n";
$sms_text .= date("l, jS F Y", strtotime("-1 days"))." : ".generate_daily($use_date)."%\n";
$sms_text .= "Month of ".date("F Y", strtotime("-1 days"))." : ".generate_monthly($use_date)."%";

/*$numbers = array(
				'Robert W'=>'256704008017',
				'David D'=>'256704008010',
				'Jackie R'=>'256752600995',
				'Nelson M'=>'256752600855',
				'Jael W'=>'256752670621',
				'Shailendra N'=>'256704006979',
				'Christine Aanyu'=>'256704008065',
				'Mike M. Muhumuza'=>'256704008595',
				'Vipan G'=>'256706200200',
				'Pavan G'=>'256700995555',
				'Zulaika Saidi'=>'256704008596',
				'Barbara Nabwire'=>'256704008411',
				'Florence Namubiru'=>'256704008042',
				'Carol Nanyange'=>'256704008587',
				'Monica Kimono'=>'256702373302',
				'Amos Aturo'=>'256701401503',
				'Comfort Turyamureeba'=>'256704008255',
				'Adonia Kibuuka'=>'256704008322',
				'Josephine Naluwu'=>'256704008052',
				'Diana Mwasti'=>'256704008054',
				'Warida Abdalla'=>'256702127837',
				'Cornelius Ochom'=>'256704008051',
				'Harriet Bakanansa'=>'256704008613',
				'Crispus Kiyonga'=>'256700619748',
				'Kate Mitali'=>'0704008044',
				'Sam Kuluby'=>'0701077457',
				'Arindam Chakrabarty/Airtel UG'=>'0700670995',
				'Somasekhar VG/Airtel UG'=>'0700670001',
				'Vincent L'=>'0704008777',
			); */

$numbers = array(
				'Vincent L'=>'256704008777',
				'David D'=>'256704008010',
			);

foreach($numbers as $number){
	$result[$number] = log_sms_send_request($message=$sms_text,$msisdn=$number,$source='notifications_CSAT',$sender_uid='0316');
	sleep(1);
}

unset($number);

//print_r($result); echo "\n\n";

$message = "The following SMS was sent out ...\n\n".$sms_text."\n\n to the following ";
foreach($numbers as $person=>$number){
	$message .= ++$i." ".$person." [".$number."] = ".$result[$number][result];
	if($i < count($numbers)){ $message .= ",<br>";}
}

sendHTMLemail($to=$to_list, $bcc = "", $message=nl2br($message), $subject ='CSAT Summary '.$use_date, $from="DO NOT REPLY<ccnotify@waridtel.co.ug>");
?>
