<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

//$SPECIAL_RUN = 1;

if($SPECIAL_RUN != 1){
	$days = array(date('Y-m-d',strtotime("-1 days")));
}else{
	//$days = array('2012-05-17','2012-05-18','2012-05-19','2012-05-20');
	//$days = array('2012-03-15');
	echo "!!!! COMMENT OUT SPECIAL RUN ON ROW 9 IN THE SOURCE CODE NOW !!!!!";
}

$html = '';
foreach($days as $date){
	$html .= print_r(transfer_ivr_stats($date),true)."<hr><br><hr>";
};

$to_list = 'ccbusinessanalysis@waridtel.co.ug,Henry Sempa/IT/Kampala <Henry.Sempa@waridtel.co.ug>';
//$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$recon_list = 'steven.ntambi@waridtel.co.ug';

sendHTMLemail($to=$to_list,$bcc,$message=nl2br($html),$subject='Asterisk IVR Statistics extraction',$from='IVR Stats extract <ccnotify@waridtel.co.ug>');

?>