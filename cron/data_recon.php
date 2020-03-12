<?
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_PARSE | E_ERROR);

$root_dir = '/srv/www/htdocs/reports/';
require('lib.php');

$recon_list = 'ra@waridtel.co.ug, Revenue Assurance <RevenueAssurance@ug.Airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>, Leonard Kibuuka/Enterprise/Uganda <Leonard.Kibuuka@ug.airtel.com>, Samuel Senkindu/Engineering/Kampala <Samuel.Senkindu@waridtel.co.ug>, Manoj Sheoran/Finance/Kampala <manoj.sheoran@waridtel.co.ug>, Peter Katongole/Finance/Uganda <Peter.Katongole@ug.Airtel.com>';
$bcc_list = 'steven.ntambi@ug.airtel.com';
//$recon_list = 'steven.ntambi@waridtel.co.ug';

sendHTMLemail($to=$recon_list,$bcc=$bcc_list,$message=generate_new_aaa_crm_reconciliation(),$subject='AAA - CRM Recon '.date_reformat(date('Y-m-d'),'%D %M %Y').' '.date('H:i:s'),$from='AAA - CRM Reconciliation <ccnotify@waridtel.co.ug>');

?>