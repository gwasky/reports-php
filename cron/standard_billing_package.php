<?php
//error_reporting(E_ALL);
error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

$root_dir = '/srv/www/htdocs/reports/';
require('lib.php');

$recon_list = 'creditcollection@waridtel.co.ug';
//$recon_list = 'ccbusinessanalysis@waridtel.co.ug';
$bcc_list = 'ccbusinessanalysis@waridtel.co.ug';

$message=attach_html_container($title='',$body='
		The following accounts are due to have their Monthly Equipment Rental [New] waived.<br><br>'.
		generate_standard_package_billing($measure_options='>=',$target_value='354',$product='Monthly Equipment Rental [New]').'
		<br><br>
		<a href="http://reports.waridtel.co.ug/index.php?report=standard_package_billing" target="_blank">Check out accounts that may be approaching this waiver point</a>');

sendHTMLemail($to=$recon_list,$bcc=$bcc_list,$message,$subject='Data Accounts due for Monthly Equipment Rental [New] Waiver'.date('Y-m-d'),$from="NO-REPLY <ccnotify@waridtel.co.ug>");

?>