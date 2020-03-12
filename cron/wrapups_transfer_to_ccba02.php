<?php
//TO BE RUN ON CCBA02
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

$from = date('Y-m-d',strtotime("-1 days")); $to = $from;

//BACK LOG
//$from = '2012-10-03'; $to = '2012-11-18';

$subject = 'Wrapups and CSAT Transfer';
$result = transfer_wrapups_to_ccba02($from,$to);
if($result[not_transfered] > 0 or $result[not_cleaned] > 0){
	$subject .= ' ERROR';
}

$mail = print_r($result,true)."<br><hr>";

$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$recon_list = 'steven.ntambi@waridtel.co.ug';

sendHTMLemail($to=$to_list,$bcc,$message=nl2br($mail),$subject,$from = 'Wrapups and CSAT Transfer <ccnotify@waridtel.co.ug>');

?>