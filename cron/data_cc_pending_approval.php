<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);	
error_reporting(E_ERROR);
echo date('Y-m-d H:i:s')." - Started Leads created \n";

$hostname = "wimaxcrm.waridtel.co.ug";
$database = "wimax";
$username = "sugarcrm";
$password = "1sugarpass2";

$data_support_list = 'Catherine Alungur/CC/Kampala <catherine.alungur@waridtel.co.ug>';

$conn = mysql_pconnect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR);
set_time_limit(0);
mysql_select_db($database, $conn);

	$query = "SELECT 
  		qs_queues.name,
  		leads.first_name as fname,
  		leads.last_name as lname,
  		leads.modified_user_id as user_id,
  		leads.title as title,
  		users.user_name as username,
  		leads.date_entered as date_entered,
		DATE_ADD(leads.date_modified, INTERVAL +3 HOUR) as date_modified,
  		users.first_name as user_fname,
		leads.converted as converted,
  		users.last_name as user_lname,
  		leads.account_name as account_name,
  		leads_cstm.sales_rep_c as sales_rep,
		leads.phone_mobile as mobile_number,
		leads.primary_address_street as installation_address,
		leads_cstm.installation_address_1_c as installation_address1,
		leads_cstm.near_by_land_mark_c as near_by,
		TIMEDIFF(NOW(),DATE_ADD(leads.date_modified, INTERVAL +3 HOUR)) as age_of_lead
		FROM
  		qs_queues
  		INNER JOIN qs_queues_leads_c ON (qs_queues.id = qs_queues_leads_c.qs_queues_lsqs_queues_ida)
  		INNER JOIN leads ON (qs_queues_leads_c.qs_queues_leadsleads_idb = leads.id)
  		INNER JOIN users ON (leads.modified_user_id = users.id)
  		INNER JOIN leads_cstm ON (leads.id = leads_cstm.id_c)
		WHERE
  		leads.deleted = 0 AND 
  		qs_queues.deleted = 0 AND 
  		qs_queues_leads_c.deleted = 0 AND 
  		qs_queues.name = 'C & C Pending Approval'";
			
	$lead_site_surveys = mysql_query($query, $conn) or die(mysql_error());
	$row_lead_site_surveys = mysql_fetch_assoc($lead_site_surveys);
	$totalRows_lead_site_surveys = mysql_num_rows($lead_site_surveys);
	
	if($totalRows_lead_site_surveys==0)
	{
	$to = 'ccbusinessanalysis@waridtel.co.ug';
	$HTML = "NO Leads Pending Site Surveys";
	$subject = $HTML;
	sendHTMLemail($to,$HTML,$subject,$from);
	exit();
	} 
	$HTML .= "<head><style type='text/css'>
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
    <td align='center' valign='top' bgcolor='#00188C' class='style6'>LEAD AGE IN THE C & C PENDING APPROVAL QUEUE</td>
  </tr>
  
  
  <tr>
    <td align='left' valign='top' class='style8' style='color:#0000FF'><p><br>
    Dear Credit and Collection Team, the following Leads are in the C & C Pending Approval queue,Please see that they are acted upon within KPI of 24 Hours.
.<br>
        <br>
        Thank you for your expeditious attention.
</p>
      <table width='100%' border='0' align='center' cellpadding='2' cellspacing='2' bgcolor='#FFFFFF'>
                
        <tr>
          <td align='left' bgcolor='#000000' class='style29'>Lead Name</td>
		   <td align='center' bgcolor='#000000' class='style29'>Mobile Number</td>
		   <td align='center' bgcolor='#000000' class='style29'>Installation Address</td>
		    <td align='center' bgcolor='#000000' class='style29'>Near By Land Mark</td>
		   <td align='center' bgcolor='#000000' class='style29'>Converted</td>
          <td align='center' bgcolor='#000000' class='style29'>SS Request Date</td>
		  <td align='center' bgcolor='#000000' class='style29'>Age in Queue</td>
		   <td align='center' bgcolor='#000000' class='style29'>Sales Rep</td>
        </tr>
  ";
        do{
        
        $HTML .= "
        <tr>
          <td align='left' bgcolor='#FF0000' class='style28'>".$row_lead_site_surveys['lname']."</td>
          <td align='left' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['mobile_number']." </td>";
		  if($row_lead_site_surveys['installation_address'] != ''){ 
		  $HTML .= "<td align='left' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['installation_address']."</td>";
		  }else{
		  	$HTML .= "<td align='left' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['installation_address1']."</td>";
			}
		$HTML .="	
		<td align='left' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['near_by']." </td>";
		  if($row_lead_site_surveys['converted'] == 0){	
		  $print = "Not Converted";
			$HTML .= "<td align='center' bgcolor='#CCCCCC' class='style16'>".$print."</td>";	
		  	} else { 
		  		 $print = "Converted"; 
				 $HTML .= "<td align='center' bgcolor='#CCCCCC' class='style16'>".$print."</td>";
			}
          $HTML .=" <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['date_modified']." </td>
          <td align='center' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['age_of_lead']." </td>
		  <td align='left' bgcolor='#CCCCCC' class='style16'>".$row_lead_site_surveys['sales_rep']." </td>
        </tr>
  ";
        }
        while ($row_lead_site_surveys = mysql_fetch_assoc($lead_site_surveys));
        
        $HTML .= "
		<tr></tr>
		<tr></tr>
	<tr>
	<td colspan ='6' align='left' class='style8' style='color:#0000FF'>NB: This is a system generated email notification do not reply to it. For any complaints please email: complaintsupport@waridtel.co.ug or call contact centre: 0700777000</td>
	</tr>
      </table><p><br>
      </p></td>
    </tr>
</table>";


        $to ="CREDIT&COLLECTION@waridtel.co.ug,ccbusinessanalysis@waridtel.co.ug,moses.wamono@waridtel.co.ug,robert.walakira@waridtel.co.ug,corporatesales@waridtel.co.ug,david.daka@waridtel.co.ug,Isaac Kiyingi/CC/Kampala <Isaac.Kiyingi@waridtel.co.ug>,".$data_support_list;
        $subject = "LEAD AGE IN THE C & C PENDING APPROVAL QUEUE";
      	
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
		mysql_free_result($lead_site_surveys);
		
echo date('Y-m-d H:i:s')." - Stopped Leads created execution \n";

?>