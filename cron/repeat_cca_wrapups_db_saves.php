<?
//EXECUTED ON CCBA02
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
ini_set('memory_limit','2128M');
$root_dir = '/srv/www/htdocs/reports/cron/';
echo date('Y-m-d H:i:s')." - Started Saving Repeat wrap ups by CCA execution \n";
require($root_dir.'lib.php');
 
$list = 'ccbusinessanalysis@waridtel.co.ug';

//$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';
//$list = 'steven.ntambi@waridtel.co.ug';

//$HTML = update_wrapup_repeats($from = '2012-07-01', $to = '2012-09-20');

$HTML = update_wrapup_repeats($from = date('Y-m-d', strtotime("-1 days")), $to = date('Y-m-d', strtotime("-1 days")));

//echo list_array($HTML,'br');

sendHTMLemail(
	$to=$list,
	$bcc='',
	$message=attach_html_container($title='',$body = list_array($HTML,'br')),
	$subject='Repeat Wrap ups DB Saves '.date('F Y',strtotime("-1 days")),
	$from='Wrap Up Database <ccnotify@waridtel.co.ug>'
);

echo date('Y-m-d H:i:s')." - Ended Saving Repeat wrap ups by CCA execution \n";

?>