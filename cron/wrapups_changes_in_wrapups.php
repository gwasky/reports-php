<?
error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';

//$recon_list .= 'ccbusinessanalysis@waridtel.co.ug,moses.wamono@waridtel.co.ug,herbert.luyinda@waridtel.co.ug,david.daka@waridtel.co.ug,james.ddungu@waridtel.co.ug,robert.walakira@waridtel.co.ug,james.busulwa@waridtel.co.ug,mathias.nazyo@waridtel.co.ug,cccontactcentremanagementteam@waridtel.co.ug';
//$recon_list = 'gibson.wasukira@waridtel.co.ug';
$recon_list = 'steven.ntambi@waridtel.co.ug';

$html = generate_changes_in_wrapups($usedate=date('Y-m-d'),$number_of_subjects=4,$period_grouping='day');

sendHTMLemail($to=$recon_list,$bcc,$message=$html,$subject='Changes in Wrap ups upt to '.date("Y-m-d", strtotime("-1 days")),$from="CCREPORTS <ccnotify@waridtel.co.ug>");
?>
