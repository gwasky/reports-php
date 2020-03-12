<?
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

$report = generate_paka_care_report();
$recon_list = 'robert.walakira@waridtel.co.ug,gaudy.baine@waridtel.co.ug,yvonne.kabataizibwa@waridtel.co.ug,ccbusinessanalysis@waridtel.co.ug';
//sendHTMLemail($to,$bcc,$message,$subject,$from)
 sendHTMLemail($to=$recon_list,
			$bcc = $bcc_list,
			$message=display_paka_care_report($report)
			.display_pakacenter_report(generate_pakacenter_pie())
			.display_warid_cs_rate_report(generate_warid_cs_pie())
			.display_paka_rate_report(generate_paka_rate_pie())
			.display_recommend_report(generate_recommendation_pie())
			.display_period_network_pie(generate_period_on_network_pie()),
			$subject ='Voice OF Customer Report 2010-10-27 -'.date('Y-m-d'),
			$from="CCREPORTS <ccnotify@waridtel.co.ug>");

?>
