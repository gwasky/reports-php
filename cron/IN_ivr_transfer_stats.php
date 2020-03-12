<?php
//TO BE RUN ON CCBA02
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

$array_output = transfer_in_ivr_stats();

$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$to_list = 'ccbusinessanalysis@waridtel.co.ug';
//$recon_list = 'steven.ntambi@waridtel.co.ug';

if($array_output) sendHTMLemail($to=$to_list,$bcc,$message=my_print_r($array_output),$subject='IN IVR Statistics Transfer',$from='IN IVR Stats tx<ccnotify@waridtel.co.ug>');

?>