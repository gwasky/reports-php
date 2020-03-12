<?php
//BEING RUN ON CCBA 02
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

$root_dir = '/srv/www/htdocs/reports/';
require_once($root_dir.'cron/lib.php');

//$SPECIAL_RUN = 1;

if($SPECIAL_RUN == 1){
	//$days = array('2012-10-27','2012-10-28','2012-10-29','2012-10-30');
	//$days = array('2012-08-26','2012-08-25','2012-08-24');
	$days = array('2013-09-01','2013-09-02','2013-09-03','2013-09-04','2013-09-05','2013-09-06','2013-09-07','2013-09-08','2013-09-09','2013-09-10','2013-09-11');
	//$days = array('2013-09-06','2013-09-07','2013-09-08');
	//$days = array(date('Y-m-d',strtotime("-2 days")));
	echo "!!!! COMMENT OUT SPECIAL RUN ON ROW 10 IN THE SOURCE CODE NOW !!!!!\n";
}elseif(date('D') == 'Mon'){
	$days = array(date('Y-m-d',strtotime("-2 days")),date('Y-m-d',strtotime("-1 days")));
}else{
	$days = array(date('Y-m-d',strtotime("-1 days")));
}

$html = '';
foreach($days as $date){
	echo date('Y-m-d H:i:s')." : Running BC traffic fetch for ".$date." \n";
	$result = get_business_centre_walkins($date);
	if($result[error] != ''){
		$subject_suffix = ' - ERROR';
	}
	$html .= str_replace(array('\n'),'<br>',print_r($result,true))."<hr><br><hr>";
};

$to_list = 'steven.ntambi@ug.airtel.com';
//$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$recon_list = 'steven.ntambi@waridtel.co.ug';

sendHTMLemail($to=$to_list,$bcc,$message=nl2br($html),$subject='Camera Data Transfer'.$subject_suffix,$from='Camera Transfer Extract<ccnotify@waridtel.co.ug>');

?>
