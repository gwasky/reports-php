<?
//CANCELLED
echo date('Y-m-d H:i:s')." : Initiating Website content population ... \n";
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

require_once('/srv/www/htdocs/reports/cron/lib.php');

//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';

//$recon_list = 'gibson.wasukira@waridtel.co.ug';
$ccba_list = 'ccbusinessanalysis@waridtel.co.ug';
$itdev_list = 'ITDEV Steven <steven.ntambi@waridtel.co.ug>';

$result = generate_warid_website_howto_txt_file();


echo date('Y-m-d H:i:s')." : Ended Website content population ... \n";
?>
