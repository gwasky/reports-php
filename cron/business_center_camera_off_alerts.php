<?php

/*
	Incase of Troubleshooting:-
		1.	Comment all functions on this page.
		2.	Uncomment the one you want to troubleshoot.
		3. Set the varible/parameter called $testing_mode to TRUE.
		4. Set the varible/parameter called $admin_number/$admin_email to your own and then execute.
		5. On completion, please set the $testing_mode varible/parameter back to FALSE.
*/

//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(0);
$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

/*
	THIS INCLUDES THE 'business_centre_walkins_fetch_info.php' SCRIPT THE FIRST TIME IT'S BEING RUN.
	ONLY EXCLUCES THE CODE LINE THE SECOND TIME.
*/
if(date("H:i:s") < date("H:i:s", strtotime("08:45:00"))){
	//require('business_centre_walkins_fetch_info.php');
}

echo "Initializing Business Center Walking Count Execution at ".date("Y-m-d H:i:s")."\n";

/*
	Since this script is executed twice a day, the $testing_time varible/parameter specifies the time you wish to check while
	troubleshooting. 08:00:00 shoot be the minimum time.
*/
$executing_time = get_bottom_of_the_hour($current_time = date("H:i:s"));/* Time when this script is executed */
$recipients = generate_business_centre_camera_alerts($executing_time, $testing_mode = FALSE, $testing_time = "07:00:00");

$formatted_data = format_mail_and_sms($recipients);

if(count($recipients['mail']) > 0 && count($recipients['sms']) > 0){/* Send notifications when camera(s) are not counting */
	send_SMS_to_CCBA($executing_time, $formatted_data, $testing_mode = FALSE, $admin_number = '256704008736');
	send_Mail_to_CCBA($executing_time, $formatted_data, $testing_mode = FALSE, $admin_email = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>');
	send_SMS_to_Center_Managers($executing_time, $formatted_data, $testing_mode = FALSE, $admin_number = '256704008736');
	send_Mail_to_Center_Managers($executing_time, $formatted_data, $testing_mode = FALSE, $admin_email = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>');
	send_SMS_to_Regional_Managers($executing_time, $formatted_data, $testing_mode = FALSE, $admin_number = '256704008736');
	send_Mail_to_Regional_Managers($executing_time, $formatted_data, $testing_mode = FALSE, $admin_email = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>');
}

if(count($recipients['mail']) == 0 && count($recipients['sms']) == 0){/* Send a notification when all cameras are counting */
	send_SMS_to_Admin($executing_time, $admin_number = '256704008736');
}

if(!empty($recipients['camera'])){/* Send a notification about which cameras are on and counting */
	send_Mail_on_camera_status_to_CCBA($executing_time, $recipients, $testing_mode = FALSE, $admin_email = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>');
}
	
if(!empty($recipients['open_failed'])){/* Send a notification when a camera link has failed to open */
	send_Mail_on_failure_to_open_camera_link_to_CCBA($executing_time, $recipients);
}	

echo "End of Business Center Walking Count Execution at ".date("Y-m-d H:i:s")."\n";

?>