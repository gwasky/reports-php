<?
//RUNS ON CCBA01
$root_dir = '/srv/www/htdocs/reports/cron/';
echo date('Y-m-d H:i:s')." - Started Repeat wrap ups by CCA execution \n";
require($root_dir.'lib.php');
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

$execution_date = date('Y-m-d');
//$execution_date = '2013-06-03';
$use_date = date('Y-m-d',strtotime($execution_date) - 86400);

if($use_date == last_day($use_date) or date('D',strtotime($execution_date)) == 'Mon'){
	
	$list = 'CCCONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,robert.walakira@waridtel.co.ug,moses.wamono@waridtel.co.ug,ccresourcedevelopment@waridtel.co.ug,brenda.ninsima@waridtel.co.ug,moses.iga@waridtel.co.ug,macdavid.mugga@waridtel.co.ug,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Pavan <pavanb@spancobpo.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>, Amit Mehta <amitmehta@spancobpo.com>';
	$bcc_list = 'Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, ccbusinessanalysis@waridtel.co.ug';
	//$list = 'steven.ntambi@waridtel.co.ug';
	
	//$body=generate_repeat_cca_wrapups_trend_by_day($upto=date('Y-m-d',strtotime("-1 days"))).generate_repeat_cca_wrapups($to,$interval = 1,$agents,$cat,$sub_cat,$subject))
	
	$HTML = generate_repeat_cca_wrapups_trend_by_day($upto = $use_date);
	$HTML .= generate_repeat_cca_wrapups($to = $use_date,$interval = 1,$agents,$cat,$sub_cat,$subject);
	
	sendHTMLemail(
				  $to=$list,
				  $bcc=$bcc_list,
				  $message=attach_html_container($title='',$body = $HTML),
				  $subject='Repeat Wrap ups '.date('F Y',strtotime($use_date)),
				  $from='Wrap Up Database <ccnotify@waridtel.co.ug>'
				  );
}else{
	echo date('Y-m-d H:i:s')." - Use date [".$use_date."] is not the last date [".last_day($use_date)."] or [".date('D',strtotime($execution_date))."] is not a Monday\n";
}

echo date('Y-m-d H:i:s')." - Ended Repeat wrap ups by CCA execution \n";

?>