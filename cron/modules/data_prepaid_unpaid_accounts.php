<?php

function generate_prepaid_unpaid_accounts($upto){
		//custom_query::select_db('wimax');
		$myquery = new custom_query();
		custom_query::select_db('wimax');
		if(!$upto){
			$upto = date('Y-m-d',strtotime("-5 days"));
			//$upto = date('Y-m-d');
		}
		
		$query = "
			SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.bandwidth_package_count_c as quantity,
				accounts_cstm.bandwidth_package_discount_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts.expiry_date as raw_expiry_date,
				cn_contracts.expiry_date as the_expiry_date,
				cn_contracts.`status`,
				DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				accounts_cstm.customer_type_c as customer_type,
				accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts.status = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' AND
				accounts_cstm.service_type_internet_c = 'Prepaid' AND
				cn_contracts.expiry_date <= '$upto'						
				union
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.maintenance_option_count_c as quantity,
				'0' as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts.expiry_date as raw_expiry_date,
				cn_contracts.expiry_date as the_expiry_date,
				cn_contracts.`status`,
				DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.maintenance_option_c=ps_products.name)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts.status = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' AND
				accounts_cstm.service_type_internet_c = 'Prepaid' AND
				cn_contracts.expiry_date <= '$upto'
				
				union
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.bandwidth_count_1_c as quantity,
				accounts_cstm.bandwidth_discount_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts.expiry_date as raw_expiry_date,
				cn_contracts.expiry_date as the_expiry_date,
				cn_contracts.`status`,
				DATEDIFF(cn_contracts.expiry_date,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts.status = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' AND
				accounts_cstm.service_type_internet_c = 'Prepaid' AND
				cn_contracts.expiry_date <= '$upto'						
				
				union
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.no_domains_web_hosting_c as quantity,
				accounts_cstm.discount_web_hosting_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts_cstm.web_hosting_end_date_c as raw_expiry_date,
				cn_contracts_cstm.web_hosting_end_date_c as the_expiry_date,
				cn_contracts_cstm.web_hosting_status_c as status,
				DATEDIFF(cn_contracts_cstm.web_hosting_end_date_c,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
				INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts_cstm.web_hosting_status_c = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' AND 
				accounts_cstm.service_type_internet_c = 'Prepaid' and cn_contracts_cstm.web_hosting_end_date_c <= '$upto'						
				
				union
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.no_domains_registration_c as quantity,
				accounts_cstm.discount_domain_registration_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts_cstm.domain_reg_end_date_c as raw_expiry_date,
				cn_contracts_cstm.domain_reg_end_date_c as the_expiry_date,
				cn_contracts_cstm.domain_reg_status_c as status,
				DATEDIFF(cn_contracts_cstm.domain_reg_end_date_c,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.package_domain_registration_c=ps_products.name)
				INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts_cstm.domain_reg_status_c = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' 
				AND
				accounts_cstm.service_type_internet_c = 'Prepaid' and cn_contracts_cstm.domain_reg_end_date_c <= '$upto'						
				
				union
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.no_of_100mb_email_c as quantity,
				accounts_cstm.discount_mail_hosting_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts_cstm.mail_hosting_end_date_c as raw_expiry_date,
				cn_contracts_cstm.mail_hosting_end_date_c as the_expiry_date,
				cn_contracts_cstm.mail_hosting_status_c as status,
				DATEDIFF(cn_contracts_cstm.mail_hosting_end_date_c,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
				INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts_cstm.mail_hosting_status_c = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service'  
				AND
				accounts_cstm.service_type_internet_c = 'Prepaid' and cn_contracts_cstm.mail_hosting_end_date_c <= '$upto'						
				
				union 
				
				SELECT
				accounts.name,
				accounts.id,
				accounts_cstm.preferred_username_c,
				(select round(balance,2) from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as balance,
				(select rate_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id = mem_id_c and entry_date <= '$upto')) as rate_date,
				accounts_cstm.mem_id_c as parent_id,
				accounts_cstm.crn_c,
				accounts_cstm.contact_person_c,
				accounts_cstm.contact_person_phone_c,
				accounts_cstm.mobile_phone_c,
				accounts_cstm.mem_id_c,
				accounts_cstm.email_c,
				accounts_cstm.no_domains_web_hosting_c as quantity,
				accounts_cstm.discount_domain_hosting_c as discount,
				accounts_cstm.sales_rep_c as sales_rep,
				cn_contracts_cstm.domain_hosting_end_date_c as raw_expiry_date,
				cn_contracts_cstm.domain_hosting_end_date_c as the_expiry_date,
				cn_contracts_cstm.domain_hosting_status_c as status,
				DATEDIFF(cn_contracts_cstm.domain_hosting_end_date_c,CURDATE()) AS expdiff,
				ps_products.name as product_name,
				round(ps_products.price*1.18,2) as product_price,
				ps_products_cstm.billing_currency_c as billing_currency,
				accounts_cstm.selected_billing_currency_c as selected_billing_currency
				,accounts_cstm.customer_type_c as customer_type
				,accounts_cstm.service_type_internet_c,
				accounts_cstm.service_type_voice_c
				FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				INNER JOIN ps_products ON (accounts_cstm.package_type_domain_hosting_c=ps_products.name)
				INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				cn_contracts_cstm.domain_hosting_status_c = 'Inactive' AND
				cn_contracts.deleted = '0' AND
				accounts.deleted = '0' AND
				ps_products.deleted = '0' AND
				ps_products.type = 'Service' AND
				accounts_cstm.service_type_internet_c = 'Prepaid' AND
				cn_contracts_cstm.domain_hosting_end_date_c <= '$upto'						
				
			ORDER BY
			the_expiry_date DESC
		";
						
		//echo "Q=>>> \n ".$query."\n\n";
			
		$data = $myquery->multiple($query);
				
		//exit("leaving ");
				
		foreach($data as $row_expires){
			$row_id = $row_expires[crn_c];

			if($account_list[$row_id][name] == ''){
				$account_list[$row_id][id] = $row_expires['id'];
				$account_list[$row_id][trim($row_expires['product_name'])] = "COVERED";
				$account_list[$row_id][name] = $row_expires['name'];
				$account_list[$row_id][email] = $row_expires['email_c'];
				$account_list[$row_id][billing_currency] = $row_expires['billing_currency'];
				$account_list[$row_id][selected_billing_currency] = $row_expires['selected_billing_currency'];
				$account_list[$row_id][contactperson] = $row_expires['contact_person_c'];
				$account_list[$row_id][contactpersonphone] = $row_expires['contact_person_phone_c'];
				$account_list[$row_id][sms_contacts] = $row_expires['contact_person_phone_c'];
				$account_list[$row_id][sales_rep] = $row_expires['sales_rep'];
				$account_list[$row_id][parent_id] = trim($row_expires['mem_id_c']);
				$account_list[$row_id][customer_type] = trim($row_expires['customer_type']);
				$account_list[$row_id][status] = $row_expires['status'];
				//$account_list[$row_id][expiry_date] = $row_expires['the_expiry_date'];
				//$account_list[$row_id][service_type_internet] = trim($row_expires['service_type_internet_c']);
				//$account_list[$row_id][service_type_voice] = trim($row_expires['service_type_voice_c']);
			}
			
			$account_list[$row_id][product_name] .= trim($row_expires['product_name'])." Expired ".$row_expires['the_expiry_date']." Status ".$row_expires['status'].", ";
			$row_expires[balance] = convert_value($row_expires[balance], trim($row_expires[billing_currency]), $row_expires[rate_date], trim($row_expires[selected_billing_currency]));
			$account_list[$row_id][balance] = round($row_expires[balance],2);

			if($row_expires[balance] < 0){
				$account_list[$row_id][formated_balance] = number_format(-$row_expires[balance],2).' DR';
				$balance[unpaid][$row_id] = $account_list[$row_id];
			}else{
				$account_list[$row_id][formated_balance] = number_format($row_expires[balance],2).' CR';
				$balance[paidup][$row_id] = $account_list[$row_id];
			}
		}
		
		//print_r($balance[unpaid]);
					
	return display_prepaid_unpaid_accounts($balance[unpaid]);
}

function display_prepaid_unpaid_accounts($report){
	if(count($report)>0){
		$html ='
			<table border="0" cellpadding="2" cellspacing="0" width="100%" style="min-width:1000px;">
			<tr>
				<th>No</th>
				<th>Account Name</th>
				<th>Account Balance</th>
				<th>CRN</th>
				<th>Customer Type</th>
				<th>Account Status</th>
				<th width="40%" style="min-width:300px;">Products</th>
				<th>Contact Person</td>
				<th>Contact Person Phone</th>
			</tr>
		';
				
		foreach($report as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&offset=18&stamp=1313152069098708800&return_module=Accounts&action=DetailView&record='.$row[id].'">'.$row[name].'</a></td>
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[parent_id].'" target="_blank">'.$row[billing_currency].' '.$row[formated_balance].'</a></td>	
					<td class="values">'.$row[parent_id].'</td>
					<td class="text_values">'.$row[customer_type].'</td>
					<td class="text_values">'.$row[status].'</td>
					<td class="wrap_text" width="40%" style="min-width:300px;">'.$row[product_name].'</td>
					<td class="text_values">'.$row[contactperson].'</td>
					<td class="text_values">'.$row[contactpersonphone].'</td>
				</tr>
			';
		}
		$html .= '</table>';
	}else{
		$html = "NO DATA";
	}
		
	//exit('Exiting !!!');
	return $html;
}

?>