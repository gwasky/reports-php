<?php


function generate_expired_accounts($from,$to,$expiryDays)
{
		//custom_query::select_db('wimax');
		$myquery = new custom_query();
		$myquery->select_db('wimax');
		if($from){
		 $from = $from;
		}else{
			$from = date('Y-m-d').'" 00:00:00"';
		}
		if($to){
		 $to = $to;
		}else{
			$to = date('Y-m-d').'" 23:59:59"';
		}
		$query = 
		"
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
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts.status != 'Inactive' AND
						cn_contracts.status != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) <= '$expiryDays' 
						AND
						accounts_cstm.service_type_internet_c = 'Prepaid' and cn_contracts.expiry_date >= '$from' and cn_contracts.expiry_date <= '$to'						
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
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.maintenance_option_c=ps_products.name)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts.status != 'Inactive' AND
						cn_contracts.status != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) <= '$expiryDays' AND
						accounts_cstm.service_type_internet_c = 'Prepaid' 
						AND cn_contracts.expiry_date >= '$from' and cn_contracts.expiry_date <= '$to'
						
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
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts.status != 'Inactive' AND
						cn_contracts.status != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts.expiry_date,CURDATE()) <= '$expiryDays' 
						AND
						accounts_cstm.service_type_internet_c = 'Prepaid' and cn_contracts.expiry_date >= '$from' 
						and cn_contracts.expiry_date <= '$to'
						
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
						DATEDIFF(cn_contracts_cstm.web_hosting_end_date_c,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
						INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts_cstm.web_hosting_status_c != 'Inactive' AND
						cn_contracts_cstm.web_hosting_status_c != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts_cstm.web_hosting_end_date_c,CURDATE()) <= '$expiryDays' AND
						accounts_cstm.service_type_internet_c = 'Prepaid' AND 
						cn_contracts.expiry_date >= '$from' 
						and cn_contracts.expiry_date <= '$to'
						
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
						DATEDIFF(cn_contracts_cstm.domain_reg_end_date_c,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.package_domain_registration_c=ps_products.name)
						INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts_cstm.domain_reg_status_c != 'Inactive' AND
						cn_contracts_cstm.domain_reg_status_c != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts_cstm.domain_reg_end_date_c,CURDATE()) <= '$expiryDays' 
						AND
						accounts_cstm.service_type_internet_c = 'Prepaid' and 
						cn_contracts.expiry_date >= '$from' and cn_contracts.expiry_date <= '$to'
						
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
						DATEDIFF(cn_contracts_cstm.mail_hosting_end_date_c,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
						INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts_cstm.mail_hosting_status_c != 'Inactive' AND
						cn_contracts_cstm.mail_hosting_status_c != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts_cstm.mail_hosting_end_date_c,CURDATE()) <= '$expiryDays' 
						AND
						accounts_cstm.service_type_internet_c = 'Prepaid' 
						and cn_contracts.expiry_date >= '$from' and cn_contracts.expiry_date <= '$to'
						
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
						DATEDIFF(cn_contracts_cstm.domain_hosting_end_date_c,CURDATE()) AS expdiff,
						ps_products.name as product_name,
						round(ps_products.price*1.18,2) as product_price,
						ps_products_cstm.billing_currency_c as billing_currency,
						accounts_cstm.selected_billing_currency_c as selected_billing_currency
						FROM
						accounts
						INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						INNER JOIN ps_products ON (accounts_cstm.package_type_domain_hosting_c=ps_products.name)
						INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
						INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						cn_contracts_cstm.domain_hosting_status_c != 'Inactive' AND
						cn_contracts_cstm.domain_hosting_status_c != 'Churned' AND
						cn_contracts.deleted = '0' AND
						accounts.deleted = '0' AND
						ps_products.deleted = '0' AND
						ps_products.type = 'Service' AND
						DATEDIFF(cn_contracts_cstm.domain_hosting_end_date_c,CURDATE()) <= '$expiryDays' 
						AND
						accounts_cstm.service_type_internet_c = 'Prepaid' 
						and cn_contracts.expiry_date >= '$from' 
						and cn_contracts.expiry_date <= '$to'
						
						ORDER BY expdiff desc ";
				
					$data = $myquery->multiple($query);
					foreach($data as $row_expires){
							$row_expires['product_price'] = $row_expires['product_price'] * $row_expires['quantity'] *((100 - $row_expires['discount'])/100);
							$row_expires['product_price'] = convert_value($row_expires['product_price'], $row_expires['billing_currency'], $row_expires['rate_date'], $row_expires['selected_billing_currency']);
								if(strtotime($row_expires[raw_expiry_date]) > strtotime(last_day(date('Y-m-d'),$expirynotice))){
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
									$list[$row_expires[crn_c]][sms_contacts] = $row_expires['contact_person_phone_c'];
									$list[$row_expires[crn_c]][expiry_date] = $row_expires['expiry_date'];
									$list[$row_expires[crn_c]][sms_expiry_date] = $row_expires['sms_expiry_date'];
									$list[$row_expires[crn_c]][status] = $row_expires['status'];
									$list[$row_expires[crn_c]][expdiff] = $row_expires['expdiff'];
									$list[$row_expires[crn_c]][parent_id] = trim($row_expires['mem_id_c']);
		
									//converting the balance if billing and account currencies are different
									if($row_expires['billing_currency'] != $row_expires['selected_billing_currency']){
			
									}
		
							$row_expires['balance'] = convert_value($row_expires['balance'], trim($row_expires['billing_currency']), $row_expires['rate_date'], trim($row_expires['selected_billing_currency']));
							
							//echo "Bal = ".$row_expires['balance']." Acnt [".$list[$row_expires[crn_c]][name]."] CUR ".trim($row_expires['selected_billing_currency'])." ";
					
							$list[$row_expires[crn_c]][monthly_charge] = $row_expires['product_price']-$row_expires['balance'];
		
									if($row_expires[balance] < 0){
										$list[$row_expires[crn_c]][balance] = number_format(-$row_expires[balance],2).' DR';
									}else{
										$list[$row_expires[crn_c]][balance] = number_format($row_expires[balance],2).' CR';
									}
								}
					}
					//print_r($list);
					
		return display_expired_accounts($list);
}

function display_expired_accounts($report){
	if(count($report)>0){
			$html ='
					<table border="0" cellpadding="2" cellspacing="0" width="50%">
					<tr>
						<th>No</th>
						<th>Account Name</th>
						<th>CRN</th>
						<th>User Name</th>
						<th>Contact Person</td>
						<th>Contact Person Phone</th>
						<th>Days Remaining</th>
						<th>Expiry Date</th>
						<th>CRM Status</th>
						<th>Account Balance</th>
						<th>Amount to pay</th>
					</tr>';
					$accountData = array();
					foreach($report as $crn=>$accountDataRow){							
						array_push($accountData,$accountDataRow);
					}
						foreach($accountData as $row)
						{
							$html .= '<tr>
										<td class="values">'.++$i.'</td>
										<td class="text_values">'.$row[name].'</td>
										<td class="values">'.$row[parent_id].'</td>
										<td class="text_values">'.$row[username].'</td>
										<td class="text_values">'.$row[contactperson].'</td>
										<td class="text_values">'.$row[contactpersonphone].'</td>
										<td class="values">'.$row[expdiff].'</td>
										<td class="text_values">'.$row[expiry_date].'</td>
										<td class="text_values">'.$row[status].'</td>
										<td class="values">'.number_format($row[balance],2).' '.$row[billing_currency].'</td>
										<td class="values">'.number_format($row[monthly_charge],2).' '.$row[billing_currency].'</td>
								</tr>';
						}
					//}
					$html .= '</table>';
		}
		return $html;
}

?>