<?php
echo date('Y-m-d H:i:s')." Starting to run expired accounts ... \n";
set_time_limit(0);
error_reporting(E_ERROR);

$_POST[HOST] = 'wimaxcrm.waridtel.co.ug';

require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
LOAD_RESOURCE('SMS');

//error_reporting(1);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_ALL);

$hostname_expirynotice = "wimaxcrm.waridtel.co.ug";
//$hostname_expirynotice = "10.31.7.7";
$database_expirynotice = "wimax";
$username_expirynotice = "sugarcrm";
$password_expirynotice = "1sugarpass2";
$expirynotice = mysql_connect($hostname_expirynotice, $username_expirynotice, $password_expirynotice) or trigger_error(mysql_error(),E_USER_ERROR);

$sms_results = array('0'=>'QUEUE FAILURE','1'=>'QUEUE SUCCESS',''=>'NOT QUEUED');

//$use_date = date('Y-m-d',strtotime("-5 days"));
$use_date = date('Y-m-d');

mysql_select_db($database_expirynotice, $expirynotice);

$query_expires = "
SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= expiry_date)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= cn_contracts.expiry_date)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.bandwidth_package_count_c as quantity,
accounts_cstm.bandwidth_package_discount_c as discount,
cn_contracts.expiry_date as raw_expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts.`status`,
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts.status != 'Inactive' AND
cn_contracts.status != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= expiry_date)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= cn_contracts.expiry_date)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.maintenance_option_count_c as quantity,
'0' as discount,
cn_contracts.expiry_date as raw_expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts.`status`,
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.maintenance_option_c=ps_products.name)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts.status != 'Inactive' AND
cn_contracts.status != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= expiry_date)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= cn_contracts.expiry_date)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.bandwidth_count_1_c as quantity,
accounts_cstm.bandwidth_discount_c as discount,
cn_contracts.expiry_date as raw_expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts.expiry_date,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts.`status`,
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts.status != 'Inactive' AND
cn_contracts.status != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts.expiry_date,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= web_hosting_end_date_c)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= web_hosting_end_date_c)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.no_domains_web_hosting_c as quantity,
accounts_cstm.discount_web_hosting_c as discount,
cn_contracts_cstm.web_hosting_end_date_c as raw_expiry_date,
DATE_FORMAT(cn_contracts_cstm.web_hosting_end_date_c,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts_cstm.web_hosting_end_date_c,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts_cstm.web_hosting_status_c as status,
DATEDIFF(cn_contracts_cstm.web_hosting_end_date_c,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts_cstm.web_hosting_status_c != 'Inactive' AND
cn_contracts_cstm.web_hosting_status_c != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts_cstm.web_hosting_end_date_c,'".$use_date."') <= 7 AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= domain_reg_end_date_c)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= domain_reg_end_date_c)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.no_domains_registration_c as quantity,
accounts_cstm.discount_domain_registration_c as discount,
cn_contracts_cstm.domain_reg_end_date_c as raw_expiry_date,
DATE_FORMAT(cn_contracts_cstm.domain_reg_end_date_c,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts_cstm.domain_reg_end_date_c,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts_cstm.domain_reg_status_c as status,
DATEDIFF(cn_contracts_cstm.domain_reg_end_date_c,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.package_domain_registration_c=ps_products.name)
INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts_cstm.domain_reg_status_c != 'Inactive' AND
cn_contracts_cstm.domain_reg_status_c != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts_cstm.domain_reg_end_date_c,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= mail_hosting_end_date_c)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= mail_hosting_end_date_c)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.no_of_100mb_email_c as quantity,
accounts_cstm.discount_mail_hosting_c as discount,
cn_contracts_cstm.mail_hosting_end_date_c as raw_expiry_date,
DATE_FORMAT(cn_contracts_cstm.mail_hosting_end_date_c,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts_cstm.mail_hosting_end_date_c,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts_cstm.mail_hosting_status_c as status,
DATEDIFF(cn_contracts_cstm.mail_hosting_end_date_c,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts_cstm.mail_hosting_status_c != 'Inactive' AND
cn_contracts_cstm.mail_hosting_status_c != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts_cstm.mail_hosting_end_date_c,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union 

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= domain_hosting_end_date_c)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= domain_hosting_end_date_c)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.no_domains_web_hosting_c as quantity,
accounts_cstm.discount_domain_hosting_c as discount,
cn_contracts_cstm.domain_hosting_end_date_c as raw_expiry_date,
DATE_FORMAT(cn_contracts_cstm.domain_hosting_end_date_c,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts_cstm.domain_hosting_end_date_c,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts_cstm.domain_hosting_status_c as status,
DATEDIFF(cn_contracts_cstm.domain_hosting_end_date_c,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.package_type_domain_hosting_c=ps_products.name)
INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts_cstm.domain_hosting_status_c != 'Inactive' AND
cn_contracts_cstm.domain_hosting_status_c != 'Churned' AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts_cstm.domain_hosting_end_date_c,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

union 

SELECT
accounts.name,
accounts_cstm.preferred_username_c,
(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= hire_purchase_end_c)) as balance,
(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= hire_purchase_end_c)) as rate_date,
accounts_cstm.mem_id_c as parent_id,
accounts_cstm.crn_c,
accounts_cstm.contact_person_c,
accounts_cstm.contact_person_phone_c,
accounts_cstm.mobile_phone_c,
accounts_cstm.mem_id_c,
accounts_cstm.email_c,
accounts_cstm.hire_purchase_count_c as quantity,
accounts_cstm.hire_purchase_discount_c as discount,
cn_contracts_cstm.hire_purchase_end_c as raw_expiry_date,
DATE_FORMAT(cn_contracts_cstm.hire_purchase_end_c,'%W %D %M %Y') AS expiry_date,
DATE_FORMAT(cn_contracts_cstm.hire_purchase_end_c,'%a %D %b %Y') AS sms_expiry_date,
cn_contracts_cstm.hire_purchase_status_c as status,
DATEDIFF(cn_contracts_cstm.hire_purchase_end_c,'".$use_date."') AS expdiff,
ps_products.name as product_name,
round(ps_products.price*1.18,2) as product_price,
ps_products_cstm.billing_currency_c as billing_currency,
accounts_cstm.selected_billing_currency_c as selected_billing_currency
FROM
accounts
INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
INNER JOIN accounts_cn_contracts_c ON accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts.id 
INNER JOIN cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
INNER JOIN ps_products ON (accounts_cstm.hire_purchase_product_c=ps_products.name)
INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
WHERE
cn_contracts_cstm.hire_purchase_status_c NOT IN ('Inactive','Churned','') AND
cn_contracts.deleted = '0' AND
accounts.deleted = '0' AND
ps_products.deleted = '0' AND
accounts_cn_contracts_c.deleted = '0' AND
ps_products.type = 'Service' AND
DATEDIFF(cn_contracts_cstm.hire_purchase_end_c,'".$use_date."') <= '7' AND
accounts_cstm.service_type_internet_c = 'Prepaid'

ORDER BY expdiff DESC
";

echo date('Y-m-d H:i:s')." Getting expired accounts for ".$use_date." ... \n";

//echo $query_expires."\n"; exit();

$expires = mysql_query($query_expires, $expirynotice) or die(mysql_error());
$totalRows_expires = mysql_num_rows($expires);

//Populating the table in HTML
while ($row_expires = mysql_fetch_assoc($expires)){
	
	//Incorperating the quantity and discounts
	$row_expires['product_price'] = $row_expires['product_price'] * $row_expires['quantity'] *((100 - $row_expires['discount'])/100);
	$row_expires['product_price'] = convert_value($row_expires['product_price'], $row_expires['billing_currency'], $row_expires['rate_date'], $row_expires['selected_billing_currency']);
	
	//For casaes where the billing date is in a different month from the expiry date we need to include the forth coming bill run
	if(strtotime($row_expires[raw_expiry_date]) > strtotime(LastDay($use_date,$expirynotice))){
		$row_expires['product_price'] *= 2;
	}
	
	if($list[$row_expires[crn_c]]){
		$list[$row_expires[crn_c]][monthly_charge] += $row_expires['product_price'];
	}else{
		$list[$row_expires[crn_c]][name] = $row_expires['name'];
		$list[$row_expires[crn_c]][email] = $row_expires['email_c'];
		$list[$row_expires[crn_c]][billing_currency] = $row_expires['billing_currency'];
		$list[$row_expires[crn_c]][selected_billing_currency] = $row_expires['selected_billing_currency'];
		$list[$row_expires[crn_c]][username] = $row_expires['preferred_username_c'];
		$list[$row_expires[crn_c]][contactperson] = $row_expires['contact_person_c'];
		$list[$row_expires[crn_c]][contactpersonphone] = $row_expires['contact_person_phone_c'];
		$list[$row_expires[crn_c]][sms_contacts] = obtain_contacts($row_expires['contact_person_phone_c']);
		$list[$row_expires[crn_c]][expiry_date] = $row_expires['expiry_date'];
		$list[$row_expires[crn_c]][sms_expiry_date] = $row_expires['sms_expiry_date'];
		$list[$row_expires[crn_c]][status] = $row_expires['status'];
		$list[$row_expires[crn_c]][expdiff] = $row_expires['expdiff'];
		$list[$row_expires[crn_c]][parent_id] = trim($row_expires['mem_id_c']);
		
		//converting the balance if billing and account currencies are different
		if($row_expires['billing_currency'] != $row_expires['selected_billing_currency']){
			/*
			$mysql_rate_resource = mysql_query("select rate from wimax_rates where rate_date = '".$row_expires['rate_date']."'", $expirynotice);
			$rate_result = mysql_fetch_assoc($mysql_rate_resource);
			$row_expires['balance'] *= $rate_result[rate];
			*/
		}
		
		$row_expires['balance'] = convert_value($row_expires['balance'], trim($row_expires['billing_currency']), $row_expires['rate_date'], trim($row_expires['selected_billing_currency']));
		
		//echo "Bal = ".$row_expires['balance']." Acnt [".$list[$row_expires[crn_c]][name]."] CUR ".trim($row_expires['selected_billing_currency'])." ";

		$list[$row_expires[crn_c]][monthly_charge] = $row_expires['product_price']-$row_expires['balance'];
		
		if($row_expires[balance] < 0){
			$list[$row_expires[crn_c]][balance] = number_format(-$row_expires[balance],2).' DR';
		}else{
			$list[$row_expires[crn_c]][balance] = number_format($row_expires[balance],2).' CR';
		}
		//echo "NewB = ".$list[$row_expires[crn_c]][balance]."\n";
	}
}

$email_log = array();
$texts = array();

echo date('Y-m-d H:i:s')." Got [".count($list)."] expired accounts ... \n";
echo date('Y-m-d H:i:s')." Sending expiry MAIL and SMS ... \n";

foreach($list as $accnt_id=>$row){
		$email = check_email($row);
		$row[email] = $email[email];
		if($email[error]){
			$row[email] = $row[email].' ('.$email[error].')';
		}
	if($row['expdiff'] > 0){
		
		++$num_of_accounts_expiring_soon;
		
		if(	(!$email[error])&&
		   	($row[monthly_charge]>0)&&
			(($row[expdiff]==20)||($row[expdiff]==15)||($row[expdiff]==10)||($row[expdiff]==7)||($row[expdiff]==5)||($row[expdiff]==3)||($row[expdiff]==1))
			){
				$email[to] = $row[email];
				$email[bcc] = 'Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>,Yvonne Wekesa/Enterprise Business/Kampala <Yvonne.Wekesa@ug.airtel.com>,Gaudy Baine/Service Experience/Uganda <gaudy.baine@ug.airtel.com>, Catherine A. Alungur <Catherine.Alungur@ug.airtel.com>, Leonard Kibuuka/Enterprise/Uganda <Leonard.Kibuuka@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>';
				//$email[to] = 'Steven GMAIL <steven.ntambi@gmail.com>, Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>'; $email[bcc] = '';
				$email[from] = "Warid Customer Care <DATASUPPORT@waridtel.co.ug>";
				$email[body] = "
				<table style='font-family: Calibri, Verdana, Arial; font-size: 13px;'>
				<tr>
				<td>
				Dear Customer,<br>
				<br>
				You are listed as the contact person for ".strtoupper($row[name])."'s prepaid Broadband Account.<br>
				<br>Your current Balance is <span style='font-weight:bold;'>".$row[selected_billing_currency]." ".$row[balance].".</span> Please be advised that your Data Service(s) subscription against account number ".$accnt_id." will expire on <span style='font-weight:bold;'>".$row[expiry_date]."</span>. Please make a minimum payment of ".$row[selected_billing_currency]." ".number_format($row[monthly_charge],2)." to enjoy an un interrupted service.<br>
				<br>
				Should you have any enquiries, please feel free to contact customer care through<br>
				Email: DATASUPPORT@waridtel.co.ug or <br>
				Call: <strong>0700 777 776</strong> from all local networks<br>
				<br>
				Thank you for choosing Warid Broadband as you preferred data service provider. <br>
				<br>
				<br>
				____________________________________________________<br>
				With best wishes,<br>
				<span style='font-size:18px; font-weight:bold;'><span style='color:#000066;'>WARID CUST</span><span style='color:#FF0000;'>OMER CARE</span></span>
				</td>
				</tr>
				</table>
				";
				$email[subject] = "Your WARID Prepaid Broadband account expires in ".$row[expdiff]." days";
				$reply_to='DATASUPPORT@waridtel.co.ug';
				
				echo date('Y-m-d H:i:s')." Account [".$accnt_id."] : Sending email to [".$email[to]."] \n";
				my_mail($to=$email[to],$cc='',$bcc=$email[bcc],$message=$email[body],$subject=$email[subject],$from=$email[from],$fileparams=NULL,$reply_to);
				
				array_push($email_log,$email);
				$row[email] = $row[email].' (NOTIFICATION SENT)';
				
				//Generating the text messages
				if(($row[expdiff]==5)||($row[expdiff]==1)){
					$texts[account_id] = $row[parent_id];
					$texts[message_id] = $row[parent_id].'-'.str_replace("-","",$use_date);
					$texts[notification_type] = 'Expiry notification';
					//$texts[date_created] = "now() in GMT"
					$texts[msisdn] = '256'.$row[sms_contacts][numbers][0];
					$texts[message] = 'Warid Broadband acnt:'.$row[parent_id].'.
Balance:US$ '.$row[balance].'.
Expiry:'.$row[sms_expiry_date].'.
Amount to pay '.$row[selected_billing_currency].' '.number_format($row[monthly_charge],2).'.
For enquiries Call:0700777776';
					if(count($row[sms_contacts][numbers]) == 0){
						$texts[message_status] = 'MSISDN error';
					}else{
						$texts[message_status] = 'Ready';
					}
					
					$texts[notification_detail] = $row[sms_contacts][error];
							
					//NEW WAY BUT NEEDS TO SPLIT UP THE REQUIRED FILES ... MORE WORK TO BE DONE
					if($texts[message_status] == 'Ready'){
						echo date('Y-m-d H:i:s')." Account [".$accnt_id."] : Sending SMS to [".$texts[msisdn]."] : Result : ";
						//$send_result = log_sms_send_request($texts[message],$texts[msisdn],'DATA Expiry Notifications','0316','SMS','100');
						echo "[".$send_result[result]."] \n";
						
						$sms_log[$row[name].' ['.$texts[msisdn].']'] = $send_result[result];
						
						//echo print_r($send_result,true)."\n";
						sleep(1);
					}else{
						echo "Failed!!! =>> ".print_r($texts,true)."\n\n";
					}
				}
			}

		$Data_rows_expiring .= "
		<tr>
			<td bgcolor='#FF0000'><span class='style28'>".$row['name']."</span></td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['email']."</td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['username']."</span></td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['contactperson']."</span></td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['contactpersonphone']."</span></td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['expdiff']."</td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['expiry_date']."</td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['status']."</td>
			<td bgcolor='#CCCCCC' class='number16' align='right'>".$row['selected_billing_currency']." ".$row['balance']."</td>
			<td bgcolor='#CCCCCC' class='number16' align='right'>".$row['selected_billing_currency']." ".number_format($row[monthly_charge],2)."</td>
		</tr>";
	}else{
		$Data_rows_expired .= "
		<tr>
			<td bgcolor='#FF0000'><span class='style28'>".$row['name']."</span></td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['email']."</td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['username']."</span></td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['contactperson']."</span></td>
			<td bgcolor='#CCCCCC'><span class='style16'>".$row['contactpersonphone']."</span></td>
			<td bgcolor='#CCCCCC' class='style16'>".-$row['expdiff']."</td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['expiry_date']."</td>
			<td bgcolor='#CCCCCC' class='style16'>".$row['status']."</td>
			<td bgcolor='#CCCCCC' class='number16' align='right'>".$row['selected_billing_currency']." ".$row['balance']."</td>
			<td bgcolor='#CCCCCC' class='number16' align='right'>".$row['selected_billing_currency']." ".number_format($row[monthly_charge],2)."</td>
		</tr>
		";
	}
}

//echo "SMS LOG \n\n";
//print_r($sms_log);

echo date('Y-m-d H:i:s')." Generating summary of [".count($email_log)."] mails sent out to customers for Enterprise Sales records \n";

$log[body] = '
		<table style="font-family: Calibri, Verdana, Arial; font-size:13px;">
		<tr>
		<td style="font-weight:bold;">The following mails were sent out by the Data Expiry Notification Mailer:</td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		</tr>';

foreach($email_log as $email){
	$log[body] .=' 
		<tr>
		<td>TO : '.$email[to].';</td>
		</tr>
		<tr>
		<td>FROM : '.$email[from].';</td>	
		</tr>
		<tr>
		<td>SUBJECT : '.$email[subject].';</td>	
		</tr>
		<tr>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td>'.$email[body].'</td>
		</tr>
		<tr>
		<td><hr /></td>
		</tr>
	';
}

$log[body] .= '</table>';

$log[to] = 'Yvonne Wekesa/Enterprise Business/Kampala <Yvonne.Wekesa@ug.airtel.com>, Kenneth Muzahura/Customer Service/Uganda <Kenneth.Muzahura@ug.airtel.com>,Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>, Gaudy Baine/Service Experience/Uganda <gaudy.baine@ug.airtel.com>, Catherine A. Alungur <Catherine.Alungur@ug.airtel.com>, Leonard Kibuuka/Enterprise/Uganda <Leonard.Kibuuka@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>';
//$log[to] = 'Steven GMAIL <steven.ntambi@gmail.com>, Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>';
$log[cc] = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Geoffrey Kasibante/Customer Care/Airtel Ug <Geoffrey.Kasibante@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>';
$log[from] = 'Enterprise DATA CRM <ccnofity@waridtel.co.ug>';
$log[subject] = 'Enterprise DATA Prepaid Account expiry notification mails sent';
$reply_to = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Geoffrey Kasibante/Customer Care/Airtel Ug <Geoffrey.Kasibante@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>';
echo date('Y-m-d H:i:s')." Sending Mail [".$log[subject]."] : To ".$log[to]."\n\n"; sleep(2);
my_mail($to=$log[to],$cc=$log[cc],$bcc='',$message=$log[body],$subject=$log[subject],$from=$log[from],$fileparams=NULL,$reply_to);

echo date('Y-m-d H:i:s')." Generating A list for [".count($list)."] accounts which expire in the next 7 days and those which are expired and are still active \n";

 $HTML .= "<head><style type='text/css'>
		   <!--
           .style1 {
	       color: #FFFFFF;
	       font-weight: bold;
           }
           .style5 {font-family: Verdana, Arial, Helvetica, sans-serif; font-weight: bold; font-size: 14px; color: #FFFFFF; }
           .style6 {
	       font-family: Verdana, Arial, Helvetica, sans-serif;
	       font-weight: bold;
	       font-size: 12px;
	       color: #FFFFFF;
		   }
           .style7 {
	       font-family: Verdana, Arial, Helvetica, sans-serif;
	       font-size: 12px;
	       font-weight: bold;
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
          .style9 {color: #001B8F}
          .style10 {color: #FF0000}
          .style11 {color: #FFFFFF}
          .style12 {font-size: 10px}
          .style16 {font-size: 9px; font-family: Verdana, Arial, Helvetica, sans-serif; }
		  .number16 {font-size: 9px; font-family: Verdana, Arial, Helvetica, sans-serif; text-align:right;}
          .style22 {font-family: Verdana, Arial, Helvetica, sans-serif}
          .style28 {color: #FFFFFF; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9px; }
          .style29 {
	      font-family: Verdana, Arial, Helvetica, sans-serif;
	      font-size: 10px;
	      font-weight: bold;
	      color: #FFFFFF;
          }
          .style30 {color: #000000}
          .style32 {
	      font-size: 8pt;
	      font-weight: bold;
          }
          .style33 {
	      color: #00188C;
	      font-size: 11px;
          }
          </style></head>	
      <table border='0' align='center' cellpadding='1' cellspacing='1'>
	    <tr>
   			<td align='center' valign='top' bgcolor='#00188C' class='style6' colspan='10'>ACCOUNTS EXPIRING WITHIN 7 DAYS OR LESS</td>
  		</tr>
        <tr>
          <td bgcolor='#000000' class='style29'>Account Name</td>
		  <td bgcolor='#000000' class='style29'>Email Address</td>
          <td bgcolor='#000000' class='style29'>User Name</td>
		  <td bgcolor='#000000' class='style29'>Contact Person</td>
		  <td bgcolor='#000000' class='style29'>Contact Person Phone</td>
		  <td bgcolor='#000000' class='style29'>Days Remaining</td>
		  <td bgcolor='#000000' class='style29'>Expiry Date</td>
		  <td bgcolor='#000000' class='style29'>CRM Status</td>
		  <td bgcolor='#000000' class='style29'>Account Balance</td>
		  <td bgcolor='#000000' class='style29'>Amount to pay</td>
        </tr>
 		".$Data_rows_expiring."
      </table>
	  <table border='0' align='center' cellpadding='1' cellspacing='1'>
	  <tr>
		<td align='left' valign='top' class='style8'>&nbsp;</td>
	  </tr>
	  </table>
	  <tr>
	  <table border='0' align='center' cellpadding='1' cellspacing='1'>
	  <tr>
	  <td  align='center' valign='top' bgcolor='#00188C' class='style6' colspan='10'>
	  ACCOUNTS THAT HAVE PASSED THEIR EXPIRY DATE <span style='color:#FF0000;'>BUT ARE STILL ACTIVE
	  </td>
	  </tr>
	   <tr>
		  <td bgcolor='#000000' class='style29'>Account Name</td>
		  <td bgcolor='#000000' class='style29'>Email Address</td>
		  <td bgcolor='#000000' class='style29'>User Name</td>
		  <td bgcolor='#000000' class='style29'>Contact Person</td>
		  <td bgcolor='#000000' class='style29'>Contact Person Phone</td>
		  <td bgcolor='#000000' class='style29'>EXTRA Days</td>
		  <td bgcolor='#000000' class='style29'>Expiry Date</td>
		  <td bgcolor='#000000' class='style29'>CRM Status</td>
		  <td bgcolor='#000000' class='style29'>Account Balance</td>
		  <td bgcolor='#000000' class='style29'>Amount to pay</td>
		</tr>
		".$Data_rows_expired."
	  </table>
	  <table border='0' align='center' cellpadding='1' cellspacing='1' width='100%'>
	  <tr>
		<td align='left' valign='top' class='style8'>&nbsp;</td>
	  </tr>
	  </table>
	  <table border='0' align='center' cellpadding='1' cellspacing='1' width='100%'>
	  	<tr>
	 		<td  align='center' valign='top' bgcolor='#00188C' class='style6' colspan='2'>
				SMS REMINDER SUCCESS REPORT
			</td>
		</tr>
	  	<tr>
		  <td bgcolor='#000000' class='style29'>Account - Contact</td>
		  <td bgcolor='#000000' class='style29'>SMS SUCCESS</td>
		</tr>
";
	foreach($sms_log as $company_contact=>$sms_result){
		$HTML.= "
		<tr>
		  <td bgcolor='#FF0000'><span class='style28'>".$company_contact."</span></td>
		  <td bgcolor='#CCCCCC' class='style16'>".$sms_result."</td>
		</tr>
		";
	}
$HTML.= "
	  </table>
";

$to = "Yvonne Wekesa/Enterprise Business/Kampala <Yvonne.Wekesa@ug.airtel.com>, Kenneth Muzahura/Customer Service/Uganda <Kenneth.Muzahura@ug.airtel.com>, Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>, Catherine A. Alungur <Catherine.Alungur@ug.airtel.com>, Phiona N. Ireemera <Phiona.Ireemera@ug.airtel.com>, Gaudy Baine/Service Experience/Uganda <gaudy.baine@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>,Leonard Kibuuka/Enterprise/Uganda <Leonard.Kibuuka@ug.airtel.com>";
//$to ="Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Steven GMAIL <steven.ntambi@gmail.com>";
//, Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>
$cc = "Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Geoffrey Kasibante/Customer Care/Airtel Ug <Geoffrey.Kasibante@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>";
$subject = "Enterprise DATA Expiring Accounts Report for ".date('D jS M Y',strtotime($use_date));
$log[from] = "Enterprise DATA CRM <ccnotify@waridtel.co.ug>";
$from = $log[from];
$reply_to = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Geoffrey Kasibante/Customer Care/Airtel Ug <Geoffrey.Kasibante@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>';

echo date('Y-m-d H:i:s')." Sending Mail [".$subject."] : To ".$to."\n\n"; sleep(2);
my_mail($to,$cc,$bcc='',$message=$HTML,$subject,$from,$fileparams=NULL,$reply_to);
		
function check_email($row){
	if($row[email]){
		$result[email] = rtrim(rtrim($row[email]),'.+-');
		$email_array = explode('@',$result[email]);
		if(count($email_array) == 2){
			if(strlen($email_array[0]) != 0){
				$domain_array = explode('.',$email_array[1]);
				if(count($domain_array)>1){
					foreach($domain_array as $sub){
						if(strlen($sub) == 0){
							$suberror = ' Wrong domain format';
						}
					}
					if($suberror){
						$result[error] = $suberror;
					}
				}else{
					$result[error] = 'Wrong email format';
				}
			}else{
				$result[error] = 'Wrong email format';
			}
		}else{
			$result[error] = 'Wrong email format';
		}
	}else{
		$result[error] = 'No emaill address';
	}
	
	return $result;
}

function obtain_contacts($entry){
	
	$contact[enumbers] = array();
	$contact[numbers] = array();
	
	if(strlen($entry) > 0){
		$list1 = explode('/',$entry);
		if(count($list1) > 0){
			foreach($list1 as $temp1){
				//If number is too long 
				if(strlen($temp1) > 12){
					//they must be more numbers split by ,
					$list2 = explode(',',$temp1);
					if(count($list2) > 1){
						foreach($list2 as $temp2){
							//inserting the sub unmbers from the long one
							$temp_list[$temp2] = $temp2;
							//echo "Adding $temp2 ;";
						}
					}
				}else{
					//the number is not too long
					$temp_list[$temp1] = $temp1;
					//echo "Adding $temp1 ;";
				}
			}
		}
		
		foreach($temp_list as $temp){
			//$number = preg_replace("#[A-Z]#i", "",str_replace(array('-','+',' '),'',trim($temp)));
			$number = preg_replace('/[^0-9]*/', "",trim($temp));
			
			if(substr($number,0,3) == '256'){
				$number = substr_replace($number,'',0,3);
			}
			if(substr($number,0,1) == '0'){
				$number = substr_replace($number,'',0,1);
			}
			if((substr($number,0,2) == '41')||(substr($number,0,2) == '20')){
				$contact[error] .= '; 256-'.$number.' is a Landline';
				array_push($contact[enumbers],$number);
			}else{
				if(strlen($number) != 9){
					$contact[error] .= '; 256-'.$number.' has incorrect number ['.strlen($number).'] of digits';
					array_push($contact[enumbers],$number);
				}else{
					array_push($contact[numbers],$number);
				}
			}
		}
	}else{
		$contact[error] .= 'Client has no contact MISISDN';
		array_push($contact[enumbers],'000000000');
	}
	return $contact;
}

function Query($query,$connection){
	
	$result = array();
	
	$identifier = mysql_query($query, $connection);
	while($row = mysql_fetch_assoc($identifier)){
		array_push($result,$row);
	}
	
	return $result;
}

function convert_value($value, $from, $date, $to){
	
	//echo "Converting [".$value."] From [".$from."] to [".$to."] with date `".$date."` \n";
	
	$myquery = new custom_query();
	
	if(($from =='USD')&&($to =='')){
		$to = 'UGX';
	}elseif(($from == 'UGX')&&($to =='')){
		$to = 'USD';
	}
	
	$query = "SELECT rate FROM wimax_rates WHERE rate_date <= '".$date."' ORDER BY rate_date DESC LIMIT 1";
	$result = $myquery->single($query,'wimax');
	
	$rate = $result[rate];
	//echo $query." : ".print_r($result,true)."\n\n";
	
	if($from != $to){
		if($rate != ''){
			if(($from == 'UGX')&&($to == 'USD')){
				$new_value = $value / $rate;
			}elseif(($from == 'USD')&&($to == 'UGX')){
				$new_value = $value * $rate;
			}else{
				$new_value = $value;
			}
		}else{
			echo "No rate set for ".$date."<br>\n";
			//exit("exiting ... <br>\n");
		}
	}else{
		$new_value = $value;
	}

	return $new_value;
}

mysql_free_result($expires);

echo date('Y-m-d H:i:s')." Ending Run : Expired accounts ... \n";
?>