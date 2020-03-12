<?
function generate_sales_account_report($status,$queues,$platform,$wimax_site=NULL){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			accounts.id,
			accounts_cstm.mem_id_c as parent_id,
			accounts_cstm.crn_c as acc_no,
			accounts.name as acc_name,
			qs_queues.name as queue,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.customer_type_c as customertype,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.cpe_type_c as equipment,
			accounts_cstm.platform_c as platform,
			accounts_cstm.contact_person_c as contact_person,
			accounts_cstm.contact_person_phone_c as contact_phone,
			accounts_cstm.email_c AS contact_email,
			accounts_cstm.primary_base_c as old_site,
			accounts_cstm.site_c as new_site,
			accounts_cstm.technical_contact_person_c as tech_person,
			accounts_cstm.technical_contact_phone_c as tech_phone,
			accounts_cstm.billing_contact_person_c as billing_person,
			accounts_cstm.billing_contact_phone_c as billing_phone,
			accounts_cstm.invoice_by_email_c AS invoice_by_email,
			accounts_cstm.billing_add_strt_c as billing_street,
			accounts_cstm.billing_add_area_c as billing_area,
			accounts_cstm.billing_add_town_c as billing_town,
			accounts_cstm.billing_add_plot_c as billing_plot,
			accounts_cstm.billing_add_district_c as billing_district,
		
			if(bandwidth_contract.deleted is null,'NO CONTRACT',bandwidth_contract.start_date) as bandwidth_start,
			if(bandwidth_contract.deleted is null,'NO CONTRACT',bandwidth_contract.expiry_date) as bandwidth_end,
			if(bandwidth_contract.deleted is null,'NO CONTRACT',bandwidth_contract.status) as bandwidth_status,
			accounts_cstm.download_bandwidth_c as bandwidth_name,
			bandwidth_product.price as bandwidth_price,
			accounts_cstm.bandwidth_count_1_c as bandwidth_quantity,
			accounts_cstm.bandwidth_discount_c as bandwidth_discount,
			bandwidth_product_cstm.product_grouping_c as bandwidth_grouping,
			bandwidth_product_cstm.billing_currency_c as bandwidth_billing_currency,
		
			accounts_cstm.shared_packages_c as package_name,
			package_product.price as package_price,
			accounts_cstm.bandwidth_package_count_c as package_quantity,
			accounts_cstm.bandwidth_package_discount_c as package_discount,
			package_product_cstm.product_grouping_c as package_grouping,
			package_product_cstm.billing_currency_c as package_billing_currency,
		
			accounts_cstm.maintenance_option_c as maintenance_name,
			maintenance_product.price as maintenance_price,
			accounts_cstm.maintenance_option_count_c as maintenance_quantity,
			maintenance_product_cstm.product_grouping_c as maintenance_grouping,
			maintenance_product_cstm.billing_currency_c as maintenance_billing_currency,
		
			if( dh_contract.deleted is null,'NO CONTRACT',dh_contract_cstm.domain_hosting_start_date_c) AS dh_start_date,
			if( dh_contract.deleted is null,'NO CONTRACT',dh_contract_cstm.domain_hosting_end_date_c) as dh_expiry_date,
			if( dh_contract.deleted is null,'NO CONTRACT',dh_contract_cstm.domain_hosting_status_c) as dh_status,
			accounts_cstm.package_type_domain_hosting_c as dh_name,
			dh_product.price as dh_price,
			accounts_cstm.no_domains_d_hosting_c as dh_quantity,
			accounts_cstm.discount_domain_hosting_c as dh_discount,
			dh_product_cstm.product_grouping_c as dh_grouping,
			dh_product_cstm.billing_currency_c as dh_billing_currency,
		
			if( dr_contract.deleted is null,'NO CONTRACT',dr_contract_cstm.domain_reg_start_date_c ) AS dr_start_date,
			if( dr_contract.deleted is null,'NO CONTRACT',dr_contract_cstm.domain_reg_end_date_c ) as dr_expiry_date,
			if( dr_contract.deleted is null,'NO CONTRACT',dr_contract_cstm.domain_reg_status_c ) as dr_status,
			accounts_cstm.package_domain_registration_c as dr_name,
			dr_product.price as dr_price,
			accounts_cstm.no_domains_registration_c as dr_quantity,
			accounts_cstm.discount_domain_registration_c as dr_discount,
			dr_product_cstm.product_grouping_c as dr_grouping,
			dr_product_cstm.billing_currency_c as dr_billing_currency,
		
			if( mh_contract.deleted is null,'NO CONTRACT',mh_contract_cstm.domain_reg_start_date_c ) AS mh_start_date,
			if( mh_contract.deleted is null,'NO CONTRACT',mh_contract_cstm.domain_reg_end_date_c ) as mh_expiry_date,
			if( mh_contract.deleted is null,'NO CONTRACT',mh_contract_cstm.domain_reg_status_c ) as mh_status,
			accounts_cstm.package_mail_hosting_c as mh_name,
			mh_product.price as mh_price,
			accounts_cstm.no_of_100mb_email_c as mh_quantity,
			accounts_cstm.discount_mail_hosting_c as mh_discount,
			mh_product_cstm.product_grouping_c as mh_grouping,
			mh_product_cstm.billing_currency_c as mh_billing_currency,
		
			if( wh_contract.deleted is null,'NO CONTRACT',wh_contract_cstm.domain_reg_start_date_c ) AS wh_start_date,
			if( wh_contract.deleted is null,'NO CONTRACT',wh_contract_cstm.domain_reg_end_date_c ) as wh_expiry_date,
			if( wh_contract.deleted is null,'NO CONTRACT',wh_contract_cstm.domain_reg_status_c ) as wh_status,
			accounts_cstm.package_web_hosting_c as wh_name,
			wh_product.price as wh_price,
			accounts_cstm.no_of_100mb_email_c as wh_quantity,
			accounts_cstm.discount_mail_hosting_c as wh_discount,
			wh_product_cstm.product_grouping_c as wh_grouping,
			wh_product_cstm.billing_currency_c as wh_billing_currency,
		
			if( hp_contract.deleted is null,'NO CONTRACT',hp_contract_cstm.hire_purchase_start_c ) AS hp_start_date,
			if( hp_contract.deleted is null,'NO CONTRACT',hp_contract_cstm.hire_purchase_end_c ) as hp_expiry_date,
			if( hp_contract.deleted is null,'NO CONTRACT',hp_contract_cstm.hire_purchase_status_c ) as hp_status,
			accounts_cstm.hire_purchase_product_c as hp_name,
			hp_product.price as hp_price,
			accounts_cstm.hire_purchase_count_c as hp_quantity,
			accounts_cstm.hire_purchase_discount_c as hp_discount,
			hp_product_cstm.product_grouping_c as hp_grouping,
			hp_product_cstm.billing_currency_c as hp_billing_currency
		FROM
			accounts
			INNER JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
			LEFT OUTER JOIN qs_queues_accounts_c ON (qs_queues_accounts_c.qs_queues_atsaccounts_idb=accounts.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues.id=qs_queues_accounts_c.qs_queues_asqs_queues_ida)
		
			LEFT OUTER JOIN cn_contracts bandwidth_contract ON (accounts.id=bandwidth_contract.account)
			LEFT OUTER JOIN ps_products bandwidth_product ON (accounts_cstm.download_bandwidth_c=bandwidth_product.name)
			LEFT OUTER JOIN ps_products_cstm bandwidth_product_cstm ON (bandwidth_product.id=bandwidth_product_cstm.id_c)
		
			LEFT OUTER JOIN ps_products package_product ON (accounts_cstm.shared_packages_c=package_product.name)
			LEFT OUTER JOIN ps_products_cstm package_product_cstm ON (package_product.id=package_product_cstm.id_c)
		
			LEFT OUTER JOIN ps_products maintenance_product ON (accounts_cstm.maintenance_option_c = maintenance_product.name)
			LEFT OUTER JOIN ps_products_cstm maintenance_product_cstm ON ( maintenance_product.id=maintenance_product_cstm.id_c)
		
			LEFT OUTER JOIN cn_contracts dh_contract ON ( accounts.id = dh_contract.account)
			LEFT OUTER JOIN cn_contracts_cstm dh_contract_cstm ON ( dh_contract.id = dh_contract_cstm.id_c)
			LEFT OUTER JOIN ps_products dh_product ON ( accounts_cstm.package_type_domain_hosting_c = dh_product.name)
			LEFT OUTER JOIN ps_products_cstm dh_product_cstm ON ( dh_product.id = dh_product_cstm.id_c)
		
			LEFT OUTER JOIN cn_contracts dr_contract ON ( accounts.id = dr_contract.account)
			LEFT OUTER JOIN cn_contracts_cstm dr_contract_cstm ON ( dr_contract.id = dr_contract_cstm.id_c)
			LEFT OUTER JOIN ps_products dr_product ON ( accounts_cstm.package_domain_registration_c = dr_product.name)
			LEFT OUTER JOIN ps_products_cstm dr_product_cstm ON ( dr_product.id = dr_product_cstm.id_c)
		
			LEFT OUTER JOIN cn_contracts mh_contract ON ( accounts.id = mh_contract.account)
			LEFT OUTER JOIN cn_contracts_cstm mh_contract_cstm ON ( mh_contract.id = mh_contract_cstm.id_c)
			LEFT OUTER JOIN ps_products mh_product ON ( accounts_cstm.package_mail_hosting_c = mh_product.name)
			LEFT OUTER JOIN ps_products_cstm mh_product_cstm ON ( mh_product.id = mh_product_cstm.id_c)
		
			LEFT OUTER JOIN cn_contracts wh_contract ON ( accounts.id = wh_contract.account)
			LEFT OUTER JOIN cn_contracts_cstm wh_contract_cstm ON ( wh_contract.id = wh_contract_cstm.id_c)
			LEFT OUTER JOIN ps_products wh_product ON ( accounts_cstm.package_web_hosting_c = wh_product.name)
			LEFT OUTER JOIN ps_products_cstm wh_product_cstm ON ( wh_product.id = wh_product_cstm.id_c)
		
			LEFT OUTER JOIN cn_contracts hp_contract ON ( accounts.id = hp_contract.account)
			LEFT OUTER JOIN cn_contracts_cstm hp_contract_cstm ON ( hp_contract.id = hp_contract_cstm.id_c)
			LEFT OUTER JOIN ps_products hp_product ON ( accounts_cstm.package_web_hosting_c = hp_product.name)
			LEFT OUTER JOIN ps_products_cstm hp_product_cstm ON ( hp_product.id = hp_product_cstm.id_c)
		WHERE
			accounts.deleted = 0 AND
			( qs_queues_accounts_c.deleted = 0 OR qs_queues_accounts_c.deleted IS NULL) AND
			( qs_queues.deleted = 0 OR qs_queues.deleted IS NULL ) AND
			
			(
			( bandwidth_contract.deleted = 0 OR bandwidth_contract.deleted IS NULL) OR
			( dh_contract.deleted = 0 OR dh_contract.deleted IS NULL) OR
			( dr_contract.deleted = 0 OR dr_contract.deleted IS NULL) OR
			( mh_contract.deleted = 0 OR mh_contract.deleted IS NULL) OR
			( wh_contract.deleted = 0 OR wh_contract.deleted IS NULL) OR
			( hp_contract.deleted = 0 OR hp_contract.deleted IS NULL)
			) AND
			
			(
			( bandwidth_product.deleted = 0 OR bandwidth_product.deleted IS NULL) OR
			( package_product.deleted = 0 OR package_product.deleted IS NULL) OR
			( maintenance_product.deleted = 0 OR maintenance_product.deleted IS NULL) OR
			( dh_product.deleted = 0 OR dh_product.deleted IS NULL) OR
			( dr_product.deleted = 0 OR dr_product.deleted IS NULL) OR
			( mh_product.deleted = 0 OR mh_product.deleted IS NULL) OR
			( wh_product.deleted = 0 OR wh_product.deleted IS NULL) OR
			( hp_product.deleted = 0 OR hp_product.deleted IS NULL)
			)
	";
	
	if($status){
		$query .= "
			AND
			(
			bandwidth_contract.status = '".$status."' OR
			dh_contract_cstm.domain_hosting_status_c = '".$status."' OR
			dr_contract_cstm.domain_reg_status_c = '".$status."' OR
			mh_contract_cstm.domain_reg_status_c = '".$status."' OR
			wh_contract_cstm.domain_reg_status_c = '".$status."' OR
			hp_contract_cstm.hire_purchase_status_c = '".$status."'
			)
		";
	}
	
	if($platform){
		$query .= "
			AND
			accounts_cstm.platform_c = '".$platform."'
		";
	}
	
	if($wimax_site != NULL){
		$query .= "
			AND
			(accounts_cstm.site_c LIKE '%".$wimax_site."%' OR accounts_cstm.primary_base_c LIKE '%".$wimax_site."%')
		";
	}
	
	if(count($queues) > 0){
		$query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$query .= "qs_queues.id = '".$queue."'";
			}else{
				$query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$query.= " OR ";
			}
		}
		$query .= ")";
	}
	
	$query .= "
		GROUP BY
			accounts_cstm.crn_c
	";
	
	$report[data] = $myquery->multiple($query);

	//print_r($accnt_services);
	return  display_sales_account_report($report);
}
	
function display_sales_account_report($report){
	
	function formulate_address(&$row){
		if($row[billing_plot] != ''){
			$row[aggregated_billing_address] .= 'Plot : '.$row[billing_plot]."; ";
		}
		if($row[billing_street] != ''){
			$row[aggregated_billing_address] .= 'Street : '.$row[billing_street]."; ";
		}
		/*if($row[billing_area] != ''){
			$row[aggregated_billing_address] .= 'Area : '.$row[billing_area]." - ";
		}
		if($row[billing_town] != ''){
			$row[aggregated_billing_address] .= $row[billing_town].";";
		}*/
		if($row[billing_district] != ''){
			$row[aggregated_billing_address] .= 'District : '.$row[billing_district]."; ";
		}
	}
	
	if(count($report[data]) == 0 ){ return "NO DATA";}

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th></th>
				<th>Parent</th>
				<th>Account Number</th>
				<th>Account Name</th>
				<th>Contact Person (Admin)</th>
				<th>Contact Phone (Admin)</th>
				<th>Contact Email (Admin)</th>
				<th>Invoice by Email</th>
				<th>Current Queue</th>
				<th>Customer Type</th>
				<th>Bandwidth Allocated</th>
				<th>Platform</th>
				<th>New site</th>
				<th>Old Site</th>
				<th>Bandwidth Price</th>
				<th>Discounts (%)</th>
				<th>Equipment Type</th>
				<th>Equipment Terms</th>
				<th>Hire Purchase Product</th>
				<th>Hire Purchase Status</th>
				<th>Hire Purchase Contract period</th>
				<th>Status</th>
				<th>Start date(Bandwidth)</th>
				<th>End date(Bandwidth)</th>
				<th>Account Manager</th>
				<th>Mail hosting</th>
				<th>Domain Hosting</th>
				<th>Web hosting</th>
				<th>Domain Registration</th>
				<th>Tech Contact</th>
				<th>Tech Phone</th>
				<th>Billing Contact</th>
				<th>Billing Phone</th>
				<th>Billing Address</th>
			</tr>
	';
	
	$row_style[1] = 'blue';
	foreach($report[data] as $row){
		formulate_address($row);
		++$i;
		
		$html .= '
			<tr class="'.$row_style[($i%2)].'">
				<td class="values">'.$i.'</td>
				<td class="text_values">'.$row[parent_id].'</td>
				<td class="text_values">'.$row[acc_no].'</td>
				<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.$row[acc_name].'</a></td>
				<td class="text_values">'.$row[contact_person].'</td>
				<td class="text_values">'.$row[contact_phone].'</td>
				<td class="text_values">'.$row[contact_email].'</td>
				<td class="text_values">'.$row[invoice_by_email].'</td>
				<td class="text_values">'.$row[queue].'</td>
				<td class="text_values">'.$row[customertype].'</td>
				<td class="text_values">'.$row[bandwidth_name].'</td>
				<td class="text_values">'.$row[platform].'</td>
				<td class="text_values">'.$row[new_site].'</td>
				<td class="text_values">'.translate_wimax_site($row[old_site]).'</td>
				<td class="values">'.number_format($row[bandwidth_price],2).'</td>
				<td class="values">'.number_format($row[bandwidth_discount],2).'</td>
				<td class="text_values">'.$row[equipment].'</td>
				<td class="text_values">'.$row[package_name].'</td>
				<td class="text_values">'.$row[hp_name].'</td>
				<td class="text_values">'.$row[hp_status].'</td>
				<td class="text_values">';
					if(trim($row[hp_start_date]) != '' and trim($row[hp_expiry_date]) != ''){ 
						$html .= '['.$row[hp_start_date].'] to ['.$row[hp_expiry_date].']'; 
					} $html .= '
				</td>
				<td class="text_values">'.$row[bandwidth_status].'</td>
				<td class="values">'.$row[bandwidth_start].'</td>
				<td class="values">'.$row[bandwidth_end].'</td>
				<td class="text_values">'.$row[sales_rep].'</td>
				<td class="text_values">'.$row[mh_name].'</td>
				<td class="text_values">'.$row[dh_name].'</td>
				<td class="text_values">'.$row[wh_name].'</td>
				<td class="text_values">'.$row[dr_name].'</td>
				<td class="text_values">'.$row[tech_person].'</td>
				<td class="text_values">'.$row[tech_phone].'</td>
				<td class="text_values">'.$row[billing_person].'</td>
				<td class="text_values">'.$row[billing_phone].'</td>
				<td class="text_values">'.$row[aggregated_billing_address].'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>