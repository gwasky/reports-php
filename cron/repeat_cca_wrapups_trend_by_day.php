<?
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
$root_dir = '/srv/www/htdocs/reports/';
require('lib.php');
 
$list = 'CCCONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,robert.walakira@waridtel.co.ug,gaudy.baine@waridtel.co.ug,moses.wamono@waridtel.co.ug,ccresourcedevelopment@waridtel.co.ug';
//, Nelson Migusha/Airtel UG <Nelson.Mugisha@ug.airtel.com>, Jael Wawulira/Airtel UG <jael.wawulira@ug.airtel.com>
//$list='sntaven@gmail.com';
//$list = 'ccbusinessanalysis@waridtel.co.ug';
//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';
//$list = 'steven.ntambi@waridtel.co.ug';

sendHTMLemail(
			  $to=$list,
			  $bcc=$bcc_list,
			  $message=attach_html_container($title='',$body=generate_repeat_cca_wrapups_trend_by_day($upto=date('Y-m-d',strtotime("-1 days")))),
			  $subject='30 day Trend of Repeat and Total wrapups up to '.date('l jS F Y',strtotime("-1 days")),
			  $from='Wrap Up Database <ccnotify@waridtel.co.ug>'
			  );

?>