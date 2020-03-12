<?
$root_dir = '/srv/www/htdocs/reports/';

//echo "<pre>".print_r($_SERVER,true)."<hr>";

//phpinfo(); 

//echo 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."<hr>";
//echo '1234';
//echo date('Y-m-d H:i:s')." - Started Repeat wrap ups by CCA execution \n";
require_once($root_dir.'cron/lib.php');

LOAD_RESOURCE(PHPMAILER);
LOAD_RESOURCE(SMS);

//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

//$HTML = generate_gsm_activities_report($date='2012-07-31');
//$HTML = generate_front_office_summary($date='2012-07-31');

//$HTML = generate_gsm_back_office_summary($date='2012-09-26');
//$HTML = generate_gsm_wrapup_summary($date='2012-01-31');

//$HTML = generate_repeat_cca_wrapups($to,$interval = 1,$agents,$cat,$sub_cat,$subject);

//$HTML = generate_wimax_activities_report($date='2012-05-21');
//$HTML = generate_warid_pesa_activities_report($date='2012-05-21');

//$HTML = generate_gsm_ivr_summary($date='2012-10-24');

//$HTML = generate_wimax_wrapup_summary($date='2012-10-31');

//$HTML = generate_leads_summary($date='2012-10-13');

//$HTML = generate_warid_pesa_wrapup_summary($date='2012-10-16');

//$HTML = generate_sms_manager_report_update();

//$HTML = display_cc_flash_report(generate_cc_flash_report(date('Y-m-d',strtotime("-4 days"))));


//$HTML = generate_socialmedia_correspondence_report($date = '',$period='month');

//$HTML = generate_inviticus_voc_topX_wrapups();
//$HTML = generate_vas_call_drivers();

//$day = date('Y-m-d',strtotime("-1 days"));
$day = '2013-11-16';
//$HTML = generate_service_recovery_report($day);
//$HTML = generate_business_centre_sales($day,$day);
$HTML = send_this_sms($msisdn='256704008777',$message='Birthday Test','SMS','Birthday');
//$HTML = generate_business_centre_sales('2013-10-10','2013-09-01');
//$use_date = date('Y-m-d', strtotime("-0 days"));
//$HTML = generate_wpesacourier_report($use_date);

//$HTML = generate_gsm_ivr_summary(date('Y-m-d',strtotime("-1 days")));

/*$HTML = '
<div style="font-size:12px; font-family:tahoma;">
Good evening,<br><br>
This is an automatically generated email sent at '.date("l, jS F Y").' - '.date("H:i:s").' HRS to test whether you can recieve automatically generated reports. Please reply to <strong>Fred Katumba/IT/Kampala (fred.katumba@waridtel.co.ug), Steven Ntambi/CC/Kampala (steven.ntambi@waridtel.co.ug)</strong> if you receive it.<br><br>
Steven Ntambi<br>
Business Analysis - Warid Customer Care
</div>
';
*/
$HTML = attach_html_container('',$HTML[attach]);
echo $HTML;
exit();

//$list = 'lucyk@spancobpo.com,pavanb@spancobpo.com,nelson.mugisha@ug.airtel.com,jael.wawulira@ug.airtel.com';
$list = 'steven.ntambi@waridtel.co.ug';
//$list = 'moses.wamono@waridtel.co.ug,david.daka@waridtel.co.ug,moses.iga@waridtel.co.ug,robert.walakira@waridtel.co.ug,cccontactcentremanagementteam@waridtel.co.ug,ritah.nakafero@waridtel.co.ug,Lucy Alal Komakech <lucyk@spancobpo.com>, Vipan G <vipang@spancobpo.com>, Pavan <pavanb@spancobpo.com>';


/*$mail = new PHPMailer;

$mail->IsSMTP();                                    	// Set mailer to use SMTP
$mail->Host = 'ugkpexchcas01.waridtel.co.ug';  			// Specify main and backup server
$mail->SMTPAuth = true;                             	// Enable SMTP authentication
$mail->Username = 'ccnotify';                          	// SMTP username
$mail->Password = 'password00';                        	// SMTP password
$mail->SMTPSecure = 'tls';                          	// Enable encryption, 'ssl' also accepted

$mail->From = 'ccnotify@waridtel.co.ug';
$mail->FromName = 'Automated Mailer';
$mail->AddAddress('steven.ntambi@waridtel.co.ug', 'Steven Ntambi');  // Add a recipient
$mail->AddAddress('derrick.katungi@waridtel.co.ug');               	// Name is optional
$mail->AddReplyTo('steven.ntambi@waridtel.co.ug', 'Steven Ntambi');
$mail->AddCC('vincent.lukyamuzi@waridtel.co.ug','Vincent Lukyamuzi');
$mail->AddBCC('robert.walakira@derrick.katungi@waridtel.co.ug','Robert Walakira');

$mail->IsHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Testing Automated mail';
$mail->Body    = $HTML;
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->Send()) {
   echo 'Message could not be sent.<br>';
   echo 'Mailer Error: ' . $mail->ErrorInfo."<hr>";
   exit;
}else{
	echo 'mail sent<br>';
}*/


/*my_mail(
		$to = $list,
		$cc = '',
		$bcc = '',
		$message = $HTML,
		$subject = 'Testing Automated mail',
		$from = "Automated Mailer <ccnotify@waridtel.co.ug>",
		$fileparams=NULL,
		$reply_to
);*/

echo date('Y-m-d H:i:s')." - Ended Repeat wrap ups by CCA execution \n";

?>