<?
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);
$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');
 
$list = 'CCCONTACTCENTREMANAGEMENTTEAM@waridtel.co.ug,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Pavan B <pavanb@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>, Amit Mehta <amitmehta@spancobpo.com>';
//$list='ccresourcedevelopment@waridtel.co.ug';
//$list = 'vincent.lukyamuzi@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug,vincent.lukyamuzi@waridtel.co.ug,steven.ntambi@waridtel.co.ug';
//$list = 'steven.ntambi@waridtel.co.ug';

$use_strtotime_days = "-1 days";

//generate_repeat_cca_wrapups_trend_by_day($upto=date('Y-m-d',strtotime($use_strtotime_days)));

sendHTMLemail(
			  $to=$list,
			  $bcc=$bcc_list,
			  $message=attach_html_container(
							$title='',
							$body=generate_agent_wrapup_counts($date=date('Y-m-d',strtotime($use_strtotime_days)),$days_back='',$days_from=0)
						),
			  $subject='Wrap ups by Agent for '.date('l jS F Y',strtotime($use_strtotime_days)),
			  $from='Wrap Up Database <ccnotify@waridtel.co.ug>');

//The weekly update ending Sunday
if(date('D') == 'Mon'){
	sendHTMLemail(
				  $to=$list,
				  $bcc=$bcc_list,
				  $message=attach_html_container(
								$title='',
								$body=generate_agent_wrapup_counts($date=date('Y-m-d',strtotime($use_strtotime_days)),$days_back='',$days_from=7)
							),
				  $subject='Wrap ups by Agent for week ending '.date('l jS F Y',strtotime($use_strtotime_days)),
				  $from='Wrap Up Database <ccnotify@waridtel.co.ug>'
				 );
}else{
	echo "Day is ".date('D').". So I shall not send the weekly summary. Exiting ... \n";
}

?>