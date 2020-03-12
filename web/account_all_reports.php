<?php require_once('Connections/expirynotice.php');

$today = date("Y-m-d");

if(!isset($_POST['type'])|| $_POST['type'] == "")
	$type = 'All';
	else
	$type = $_POST['type'];
	
if($type == 'All'){
	mysql_select_db($database_expirynotice, $expirynotice);
	$query_acoount_start = "SELECT accounts.name,accounts.phone_alternate, accounts_cstm.email_c,  accounts_cstm.preferred_username_c,accounts_cstm.customer_type_c,  accounts_cstm.crn_c, accounts_cstm.platform_c,accounts_cstm.shared_packages_c, accounts_cstm.cpe_type_c,package_type_domain_hosting_c,package_domain_registration_c,package_mail_hosting_c,package_web_hosting_c, accounts_cstm.download_bandwidth_c FROM accounts INNER JOIN accounts_cstm ON ( accounts.id = accounts_cstm.id_c ) WHERE accounts.deleted = '0'";
	$acoount_start = mysql_query($query_acoount_start, $expirynotice) or die(mysql_error());
	$totalRows_acoount_start = mysql_num_rows($acoount_start);

//Who has a contract?
	$query_contract_accts = "SELECT accounts_cstm.crn_c FROM accounts INNER JOIN accounts_cstm ON ( accounts.id = accounts_cstm.id_c ) INNER JOIN cn_contracts ON ( accounts.id = cn_contracts.account) WHERE accounts.deleted = '0' AND cn_contracts.deleted = '0'";
	$contract_accts = mysql_query($query_contract_accts, $expirynotice) or die(mysql_error());
	$totalRows_contract_accts = mysql_num_rows($contract_accts);
	
}else{
	mysql_select_db($database_expirynotice, $expirynotice);
	$query_acoount_start = "SELECT accounts.name,accounts.phone_alternate, accounts_cstm.email_c,  accounts_cstm.preferred_username_c,accounts_cstm.customer_type_c,  accounts_cstm.crn_c, accounts_cstm.platform_c, accounts_cstm.shared_packages_c, accounts_cstm.cpe_type_c,package_type_domain_hosting_c,package_domain_registration_c,package_mail_hosting_c,package_web_hosting_c, accounts_cstm.download_bandwidth_c FROM accounts INNER JOIN accounts_cstm ON ( accounts.id = accounts_cstm.id_c ) WHERE accounts.deleted = '0' AND leads.deleted = '0' AND accounts_cstm.customer_type_c = '$type'";
	$acoount_start = mysql_query($query_acoount_start, $expirynotice) or die(mysql_error());
	$totalRows_acoount_start = mysql_num_rows($acoount_start);

//Who has a contract?
	$query_contract_accts = "SELECT accounts_cstm.crn_c FROM accounts INNER JOIN accounts_cstm ON ( accounts.id = accounts_cstm.id_c ) INNER JOIN cn_contracts ON (accounts.id = cn_contracts.account) WHERE accounts.deleted = '0' AND cn_contracts.deleted = '0' AND accounts_cstm.customer_type_c = '$type'";
	$contract_accts = mysql_query($query_contract_accts, $expirynotice) or die(mysql_error());
	$totalRows_contract_accts = mysql_num_rows($contract_accts);
}

while($row_acoount_start = mysql_fetch_assoc($acoount_start)){
	$list[$row_acoount_start['crn_c']]['name'] = $row_acoount_start['name'];
	$list[$row_acoount_start['crn_c']]['preferred_username_c'] = $row_acoount_start['preferred_username_c'];
	$list[$row_acoount_start['crn_c']]['customer_type_c'] = $row_acoount_start['customer_type_c'];
	$list[$row_acoount_start['crn_c']]['phone_alternate'] = $row_acoount_start['phone_alternate'];
	$list[$row_acoount_start['crn_c']]['email_c'] = $row_acoount_start['email_c'];
	$list[$row_acoount_start['crn_c']]['crn_c'] = $row_acoount_start['crn_c'];
	$list[$row_acoount_start['crn_c']]['platform_c'] = $row_acoount_start['platform_c'];
	$list[$row_acoount_start['crn_c']]['package_web_hosting_c'] = $row_acoount_start['package_web_hosting_c'];
	$list[$row_acoount_start['crn_c']]['package_mail_hosting_c'] = $row_acoount_start['package_mail_hosting_c'];
	$list[$row_acoount_start['crn_c']]['package_domain_registration_c'] = $row_acoount_start['package_domain_registration_c'];
	$list[$row_acoount_start['crn_c']]['package_type_domain_hosting_c'] = $row_acoount_start['package_type_domain_hosting_c'];
	$list[$row_acoount_start['crn_c']]['shared_packages_c'] = $row_acoount_start['shared_packages_c'];
	$list[$row_acoount_start['crn_c']]['cpe_type_c'] = $row_acoount_start['cpe_type_c'];
	$list[$row_acoount_start['crn_c']]['download_bandwidth_c'] = $row_acoount_start['download_bandwidth_c'];
	$list[$row_acoount_start['crn_c']][contract_status] = '<span style="color:#FF0000; font-weight:bold;">No contract</span>';
}

while($row_contract_accts = mysql_fetch_assoc($contract_accts)){
	$list[$row_contract_accts['crn_c']][contract_status] = '<span style="color:#090; font-weight:bold;">Has contract</span>';
}

mysql_select_db($database_expirynotice, $expirynotice);
$query_types = "SELECT accounts_cstm.customer_type_c FROM accounts_cstm GROUP BY accounts_cstm.customer_type_c";
$types = mysql_query($query_types, $expirynotice) or die(mysql_error());
$row_types = mysql_fetch_assoc($types);
$totalRows_types = mysql_num_rows($types);

/*mysql_select_db($database_expirynotice, $expirynotice);
$query_expires = "SELECT 
  cn_contracts.billing_date,
  cn_contracts.expiration_notice,
  cn_contracts.expiry_date,
  cn_contracts.start_date,
  cn_contracts.`status`,
  cn_contracts.name,
  accounts.name,
  accounts_cstm.preferred_username_c
FROM
 accounts
 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c) WHERE cn_contracts.expiry_date = '$today' AND cn_contracts.deleted = '0'";*/

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>


<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><title>Dashboard</title>

<link href="images/provisioning.css" rel="stylesheet" type="text/css"><style type="text/css">
<!--
.style3 {font-size: 10px}
.style11 {color: #1C2BCB; font-weight: bold; font-size: 10px; }
.style22 {
	font-weight: bold;
	color: #FF0000;
}
-->
</style></head><body>
<table align="center" border="0" cellpadding="0" cellspacing="2" width="100%">
  <tbody><tr>
    <td>


<table border="0" cellpadding="0" cellspacing="0" width="100%">
 <tbody><tr>
    <td>&nbsp;</td>
  </tr><tr>
     <td align="center" bgcolor="#D9DBEC" height="20"><a href="account_all_reports.php">Browse All Accounts</a> <font color="red" size="2"><b>|</b> </font><a href="account_reports.php">Accounts In Billing System</a> <font color="red" size="2"><b>|</b></font>&nbsp;&nbsp;<a href="account_active.php"> Status Report</a>&nbsp;&nbsp;<font color="red" size="2"> <b>|</b></font>&nbsp;&nbsp;<a href="account_activation.php">Bill Activation Report</a>&nbsp;&nbsp;<font color="red" size="2"> <b>|</b></font>&nbsp;&nbsp;<a href="account_expired.php">Expired Accounts Report </a>&nbsp;&nbsp;<font color="red" size="2"> <b>|</b></font>&nbsp;&nbsp;<a href="site_survey_cods.php">Site Survey Coordinates </a>&nbsp;&nbsp;<font color="red" size="2"> <b>|</b></font>&nbsp;&nbsp;<a href="accounts_views.php">Other Finance Reports</a>&nbsp;&nbsp;<font color="red" size="2"> <b>|</b></font></td>
  </tr>
 <tr>
   <td align="center">&nbsp;</td>
</tr>
   <tr>
    <td><table border="0" cellpadding="2" cellspacing="0" width="100%">
        <tbody><tr>
          <td align="left" width="54%">Welcome <b><i>Infinity Wimax Reporting Platform</i></b></td>
          <td class="style3" align="right" width="33%">&nbsp;</td>
          <td class="style3" width="13%">&nbsp;</td>
        </tr>
                <tr>
          <td colspan="3" align="left">&nbsp;</td>
          </tr>
    </tbody></table>
</td>
  </tr>

</tbody></table>
</td>
  </tr>
  <tr>
    <td><table class="formtable" border="0" cellpadding="2" cellspacing="2" width="100%">
  <tbody><tr>
<td colspan="2" align="center" background="images/titlebak.jpg" height="25"><span class="style22">THIS PAGE SHOWS ALL THE ACCOUNTS CREATED IN THE CRM, WITH OR WITHOUT CONTRACTS</span></td>    
  </tr>
  
    <tr>
      <td colspan="2"><form id="form1" name="form1" method="post" action="account_all_reports.php">
Filter by:
<label>
<select name="type" id="type">
  <option></option>
  <option value="All">All</option>
  <?php
do {  
?>
  <option value="<?php echo $row_types['customer_type_c']?>"><?php echo $row_types['customer_type_c']?></option>
  <?php
} while ($row_types = mysql_fetch_assoc($types));
  $rows = mysql_num_rows($types);
  if($rows > 0) {
      mysql_data_seek($types, 0);
	  $row_types = mysql_fetch_assoc($types);
  }
?>
</select> 
<input type="submit" name="button2" id="button2" value="Submit" />
</label>      
              </form>      </td>
    </tr>
    <tr>
    <td colspan="2"><label>
      <input type="button" name="button" id="button" value="Generate Excel" onclick="javascript:window.open('account_all_reports_excel.php?type=<?php echo $type; ?>')" />
    </label>
      <br />
      <table width="100%" border="0">
        <tr>
          <td bgcolor="#D9DBEC" class="style11">Has Contract or not</td>
          <td bgcolor="#D9DBEC" class="style11">Account Number</td>
          <td bgcolor="#D9DBEC" class="style11">Account Name</td>
          <td bgcolor="#D9DBEC" class="style11">Username</td>
          <td bgcolor="#D9DBEC" class="style11">Customer Type</td>
          <td bgcolor="#D9DBEC" class="style11">Platform</td>
          <td bgcolor="#D9DBEC" class="style11">Contact No.</td>
          <td bgcolor="#D9DBEC" class="style11">Email</td>
          <td bgcolor="#D9DBEC" class="style11">Package</td>
          <!--<td bgcolor="#D9DBEC" class="style11">CPE Type</td>-->
          <td bgcolor="#D9DBEC" class="style11">Bandwidth</td>
          <td bgcolor="#D9DBEC" class="style11">Web Hosting</td>
          <td bgcolor="#D9DBEC" class="style11">Mail Hosting</td>
          <td bgcolor="#D9DBEC" class="style11">Domain Reg</td>
          <td bgcolor="#D9DBEC" class="style11">Domain Hosting</td>
          </tr>
        <?php foreach($list as $row){ ?>
          <tr>
          	<td class="style3"><?php echo $row['contract_status']; ?></td>
          	<td class="style3"><?php echo $row['crn_c']; ?></td>
            <td class="style3"><?php echo $row['name']; ?></td>
            <td class="style3"><?php echo $row['preferred_username_c']; ?></td>
            <td class="style3"><span class="smalltext"><?php echo $row['customer_type_c']; ?></span></td>
            <td class="style3"><?php echo $row['platform_c']; ?></td>
            <td class="style3"><?php echo $row['phone_alternate']; ?></td>
            <td class="style3"><?php echo $row['email_c']; ?></td>
            <td class="style3"><?php echo $row['shared_packages_c']; ?></td>
                       <!--<td class="style3"><?php echo $row['cpe_type_c']; ?></td>-->
            <td class="style3"><?php echo $row['download_bandwidth_c']; ?></td>
             <td class="style3"><?php if(!$row['package_web_hosting_c']){echo "-";}else{ echo "<color=#ff0000><b>".$row['package_web_hosting_c']."</b></font>"; } ?></td>
            <td class="style3"><?php if(!$row['package_mail_hosting_c']){echo "-";}else{echo "<color=#ff0000><b>".$row['package_mail_hosting_c']."</b></font>";} ?></td>
            <td class="style3"><?php if(!$row['package_domain_registration_c']){echo "-";} else{ echo "<color=#ff0000><b>".$row['package_domain_registration_c']."</b></font>";} ?></td>
            <td class="style3"><?php if(!$row['package_type_domain_hosting_c']){echo "-";} else{echo "<color=#ff0000><b>".$row['package_type_domain_hosting_c']."</b></font>";} ?></td>
            </tr>
          <?php } ?>
      </table></td>
    </tr>
   <tr>
    <td colspan="2" align="left" valign="top">&nbsp;</td>
    </tr>
        <tr>
    <td width="51%" align="left" valign="top">&nbsp;</td>
    <td width="49%" valign="top">&nbsp;</td>
        </tr> 
</tbody></table>
    </td>
  </tr>
  <tr>
    <td><table valign="top" class="text2" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" height="50" width="100%"> 
 
<tbody>
	<tr>
    	<td height="15" align="center" bgcolor="#ffffff" class="text2">
    	   Copyright © 2008, Warid Telecom (U) Limited All rights reserved.
    	</td>
	</tr>
  	<tr>
    	<td class="textfooter" align="center" bgcolor="#034DA2" height="40">
   <img src="images/plot.gif">  Warid Telecom Building, Plot 16A, Clement Hill Road <br> 
   <img src="images/tel.gif" alt="Telephone" height="14" width="20"><strong> Tel :</strong>&nbsp;100 Toll Free for WARID Customers      or 0700100100 for non WARID Customers<br>
    <img src="images/address.gif" alt="Address" height="15" width="20">  P.O Box 70665, Kampala   <img src="images/email.gif"> <b>Email : </b>customercare@waridtel.co.ug<br>
    <br>
 		</td>
        </tr>
</tbody>
</table>
</td>
  </tr>
</tbody></table>
</body></html>
<?php
mysql_free_result($acoount_start);

mysql_free_result($acoount_start);

mysql_free_result($types);
?>
