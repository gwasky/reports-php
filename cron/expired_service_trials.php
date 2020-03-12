<?php
//MX Widgets3 include
error_reporting(-1);
$hostname_smsconnect = "wimaxcrm.waridtel.co.ug";
$database_smsconnect = "wimax";
$username_smsconnect = "sugarcrm";
$password_smsconnect = "1sugarpass2";
$smsconnect = mysql_pconnect($hostname_smsconnect, $username_smsconnect, $password_smsconnect) or trigger_error(mysql_error(),E_USER_ERROR);
?>
<?php 
 /*  ini_set ("SMTP", "ugkpexch01.waridtel.co.ug");
   ini_set("sendmail_from", CCREPORTS); */
 ?> 
<?php set_time_limit(0); 

$debug = 0;
		
mysql_select_db($database_smsconnect, $smsconnect);
$query_leadsstatus = "
SELECT 
	tr_trials.name,
	tr_trials.`status`,
	tr_trials.testing_bandwidth,
	tr_trials_cstm.trial_start_c,
	tr_trials_cstm.trial_end_c,
	tr_trials_cstm.trial_status_c,
	tr_trials_cstm.username_c,
	leads.first_name,
	leads.last_name
FROM
	tr_trials
	INNER JOIN tr_trials_cstm ON (tr_trials.id=tr_trials_cstm.id_c)
	INNER JOIN leads_tr_trials_c ON (tr_trials.id=leads_tr_trials_c.leads_tr_trstr_trials_idb)
	INNER JOIN leads ON (leads.id=leads_tr_trials_c.leads_tr_trialsleads_ida)
WHERE
	(tr_trials.deleted = '0')
	AND
	(leads.deleted = '0')
	AND
	( leads_tr_trials_c.deleted = '0')
	AND
	DATEDIFF( tr_trials.end_date, CURDATE( ) ) = '-1'
";

echo $query_leadsstatus."\n";
$leadsstatus = mysql_query($query_leadsstatus, $smsconnect) or die(mysql_error());
$row_leadsstatus = mysql_fetch_assoc($leadsstatus);
$totalRows_leadsstatus = mysql_num_rows($leadsstatus);

if($totalRows_leadsstatus ==  0){
	
	$to = 'ccbusinessanalysis@waridtel.co.ug';
	$HTML = "NO expired service trials";
	$subject = $HTML;
	
	if($debug==1){echo "calling the mail function no data /n";}
	sendHTMLemail($to,$HTML,$subject,$from);
	exit();
} 

if($debug==1){echo "Setting the HTML body for data found /n";}

	       $HTML = "<head><style type='text/css'>
		   <!--
           .style6 {
	       font-family: Verdana, Arial, Helvetica, sans-serif;
	       font-weight: bold;
	       font-size: 12px;
	       color: #FFFFFF;
		   }
           -->
           </style>
    
           <style type='text/css'>
           #topbar{
	       position:absolute;
	       border: 1px solid black;
	       padding: 2px;
	       background-color: lightyellow;
	       width: 255px;
	       visibility: hidden;
	       z-index: 100;
	       left: 736px;
	       height: 28px;
           }
          .style8 {
	      font-family: Verdana, Arial, Helvetica, sans-serif;
	      font-weight: bold;
	      font-size: 9px;
          }
          .style16 {font-size: 9px; font-family: Verdana, Arial, Helvetica, sans-serif; }
          .style28 {color: #FFFFFF; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9px; }
          .style29 {
	      font-family: Verdana, Arial, Helvetica, sans-serif;
	      font-size: 10px;
	      font-weight: bold;
	      color: #FFFFFF;
          }
          .style30 {color: #000000}
           .style31 {font-family: Verdana, Arial, Helvetica, sans-serif; font-weight: bold; font-size: 9px; color: #000000; }
           </style></head>
          <table width='100%' align='center' cellspacing='0' cellpadding='0'>
  <tr>
    <td align='center'></td>
  </tr>
  <tr>
    <td valign='middle'></td>
  </tr>
  

  <tr>
    <td align='center' valign='top' bgcolor='#00188C' class='style6'>WIMAX TRIALS EXPIRY NOTIFICATION</td>
  </tr>
  
  
  <tr>
    <td align='left' valign='top' class='style8'><p><br>
    Dear NOC, the following Service trials expired yesterday. Please delete this trial from the AAA server.<br>
        <br>
        Thank you!</p>
      <table width='100%' border='0' align='center' cellpadding='1' cellspacing='1'>
                
        <tr>
          <td align='left' bgcolor='#000000' class='style29'>Lead Name</td>
          <td align='center' bgcolor='#000000' class='style29'>Test Username</td>
          <td align='center' bgcolor='#000000' class='style29'>Trial End Date</td>
          <td align='center' bgcolor='#000000' class='style29'>Trial Bandwidth</td>
		  <td align='center' bgcolor='#000000' class='style29'>Trial Status</td>
        </tr>
  ";
        
        do{
        
        $HTML .= "
        <tr>
          <td align='left' bgcolor='#FF0000' class='style28'>".$row_leadsstatus['first_name']." ".$row_leadsstatus['last_name']."</td>
          <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_leadsstatus['username_c']." </td>
          <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_leadsstatus['trial_end_c']." </td>
          <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_leadsstatus['testing_bandwidth']." </td>
		  <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_leadsstatus['status']." </td>
        </tr>
  ";
        }
        while ($row_leadsstatus = mysql_fetch_assoc($leadsstatus));
        
        $HTML .= "
      </table>      <p><br>
      </p>      </td>
    </tr>
</table>


";

		$to = "noc@waridtel.co.ug,CCFIELDENGINEERS@waridtel.co.ug,ccbusinessanalysis@waridtel.co.ug,ra@waridtel.co.ug";
		$to = "steven.ntambi@waridtel.co.ug";
        $subject = "BroadBand Service Trial Activities Status";
        sendHTMLemail($to,$HTML,$subject,'');
		function sendHTMLemail($to,$HTML,$subject,$from){
		// First we have to build our email headers
			if(!$from){
				$from = 'Data Reporting <ccnotify@waridtel.co.ug>';
			}
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: ".$from."\r\n";
			mail($to,$subject,$HTML,$headers);
		}
mysql_free_result($leadsstatus);
?>
