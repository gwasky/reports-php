<?
function generate_rr_revenue_report($date,$account_id,$customer_types,$service_type){
	
	$myquery = new custom_query();
	$billing = new wimax_billing();
	
	$conditions = array();
	$period_condition = '';
	
	if($date){
		$from = date_reformat($date,'%Y-%m').'-01';
		array_push($conditions,array('entry_date','>=',$from));
		$period_condition .= " AND entry_date >= '".$from."' ";
		
		$to = last_day($date);
		array_push($conditions,array('entry_date','<=',$to));
		$period_condition .= " AND entry_date <= '".$to."' ";
	}else{
		$from = date('Y-m-')."01";
		array_push($conditions,array('entry_date','>=',$from));
		$period_condition .= " AND entry_date >= '".$from."' ";	

		//$to = last_day(date('Y-m-d'));
		$to = date('Y-m-d'); $_POST[month] = $to;
		array_push($conditions,array('entry_date','<=',$to));
		$period_condition .= " AND entry_date <= '".$to."' ";
	}
	
	if($account_id != ''){
		$account_range = array('account_id','=',$account_id);
		$account_condition = " AND TRIM(accounts_cstm.crn_c) = '".$account_id."' ";
		$billing_account_condition = " AND TRIM(account_id) = '".$account_id."' ";
		array_push($conditions,$account_range);
	}
	
	if($product != ''){
		$product = '%'.$product.'%';
		$product_range = array('entry','LIKE',$product);
		array_push($conditions,$product_range);
	}
	
	if(($customer_types) && (!in_array('%%',$customer_types))){
		$customer_type_condition .= "AND (";
		foreach($customer_types as $count=>$customer_type){
			$customer_type_condition .= " accounts_cstm.customer_type_c = '".$customer_type."'";
			if(count($customer_types) > $count+1){
				$customer_type_condition .= " OR ";
			}
		}
		$customer_type_condition .= ")";
	}
	
	if($service_type){
		$service_type_condition = " AND accounts_cstm.service_type_internet_c = '".$service_type."' ";
	}


	$accnt_data_query = "
		SELECT
			accounts.id,
			accounts.name,
			accounts_cstm.selected_billing_currency_c as billing_currency,
			accounts_cstm.crn_c AS account_no,
			accounts_cstm.mem_id_c AS parent_no,
			accounts_cstm.platform_c AS platform,
			accounts_cstm.customer_type_c AS customer_type,
			accounts_cstm.service_type_internet_c AS service_type,
			(select wimax_invoicing.invoice_number from wimax_invoicing where parent_id = accounts_cstm.mem_id_c AND wimax_invoicing.billing_date <= '".$to."' AND wimax_invoicing.deleted = 0 LIMIT 1) as inv_num,
			(select wimax_invoicing.id from wimax_invoicing where parent_id = accounts_cstm.mem_id_c AND wimax_invoicing.billing_date <= '".$to."' AND wimax_invoicing.deleted = 0 limit 1) as inv_id,
			accounts_cstm.shared_packages_c AS package,
			(select price from ps_products where accounts_cstm.shared_packages_c = ps_products.name limit 1) as p_price,
			accounts_cstm.download_bandwidth_c AS bandwidth,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts.status) as status_p,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts.start_date) as start_p,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts.expiry_date) as end_p,
			accounts_cstm.bandwidth_package_count_c as num_p,
			accounts_cstm.bandwidth_package_discount_c as disc_p,
			(select price from ps_products where accounts_cstm.download_bandwidth_c = ps_products.name limit 1) as b_price,
			accounts_cstm.bandwidth_count_1_c as num_b,
			accounts_cstm.bandwidth_discount_c as disc_b,
			accounts_cstm.package_type_domain_hosting_c AS domain_hosting,
			(select price from ps_products where accounts_cstm.package_type_domain_hosting_c = ps_products.name limit 1) as dh_price,
			accounts_cstm.discount_domain_hosting_c as disc_dh,
			accounts_cstm.no_domains_d_hosting_c as num_dh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_hosting_start_date_c) as start_dh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_hosting_end_date_c) as end_dh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_hosting_status_c) as status_dh,
			accounts_cstm.package_domain_registration_c AS domain_registration,
			(select price from ps_products where accounts_cstm.package_domain_registration_c = ps_products.name limit 1) as dr_price,
			accounts_cstm.no_domains_registration_c as num_dr,
			accounts_cstm.discount_domain_registration_c as disc_dr,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_reg_start_date_c) as start_dr,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_reg_end_date_c) as end_dr,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.domain_reg_status_c) as status_dr,
			accounts_cstm.package_web_hosting_c AS web_hosting,
			(select price from ps_products where accounts_cstm.package_web_hosting_c = ps_products.name limit 1) as wh_price,
			accounts_cstm.discount_web_hosting_c as disc_wh,
			accounts_cstm.no_domains_web_hosting_c as num_wh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.web_hosting_start_c) as start_wh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.web_hosting_end_date_c) as end_wh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.web_hosting_status_c) as status_wh,
			accounts_cstm.package_mail_hosting_c AS mail_hosting,
			(select price from ps_products where accounts_cstm.package_mail_hosting_c = ps_products.name limit 1) as mh_price,
			accounts_cstm.discount_mail_hosting_c as disc_mh,
			accounts_cstm.no_of_100mb_email_c as num_mh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.mail_hosting_start_date_c) as start_mh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.mail_hosting_end_date_c) as end_mh,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.mail_hosting_status_c) as status_mh,
			accounts_cstm.hire_purchase_product_c AS hire_purchase,
			(select price from ps_products where accounts_cstm.hire_purchase_product_c = ps_products.name limit 1) as hp_price,
			accounts_cstm.hire_purchase_discount_c as disc_hp,
			accounts_cstm.hire_purchase_count_c as num_hp,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.hire_purchase_start_c) as start_hp,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.hire_purchase_end_c) as end_hp,
			if(cn_contracts.deleted is NULL,'NO CONTRACT', cn_contracts_cstm.hire_purchase_status_c) as status_hp
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			LEFT OUTER JOIN cn_contracts ON (cn_contracts.account=accounts_cstm.id_c)
			LEFT OUTER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
		WHERE
			accounts.deleted = 0 ".
			$service_type_condition.
			$customer_type_condition.
			$account_condition.
			"
			AND 
			(
			 cn_contracts.deleted = 0 OR cn_contracts.deleted is null
			)
		ORDER BY 
			accounts_cstm.crn_c ASC
			";
	
			/*AND accounts_cstm.crn_c in (
			select 
				distinct account_id 
			from 
				wimax_billing 
				inner join accounts_cstm on accounts_cstm.crn_c=wimax_billing.account_id 
			where 
				date_format(wimax_billing.entry_date,'%Y-%m') = date_format('".$to."','%Y-%m')
			)*/
		
	//ORDER BY accounts_cstm.service_type_internet_c ASC, accounts_cstm.crn_c ASC

	//echo "Q => ".nl2br($accnt_data_query)."<br>";

	custom_query::select_db('wimax');
	$parent_data = $myquery->multiple($accnt_data_query);
	
	//echo my_print_r($parent_data)."<hr>";
	
	if(count($parent_data) == 0) { echo "Your condition does not retreive any data <br>"; exit(); }
	//echo "count is ".count($parent_data)."<br>";
	
	foreach($parent_data as $key=>$row){
		if($parent_data[$key][service_type] == ''){$parent_data[$key][service_type] = 'un_defined';}
		$parent_data[$key][account_no] = trim($parent_data[$key][account_no]);
		$row[parent_no] = trim($row[parent_no]);
		$parent_data[$parent_data[$key][account_no]] = $row;
		/*++$gff;
		if($gff <= 3){
			echo "Key [".$parent_data[$key][account_no]."] ==>> "; print_r($row); echo "<br>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
		}*/
		unset($parent_data[$key]);
	}
	
	//echo "count is ".count($parent_data)."<br>";
	
	//OLD QUERY
	/*$billing_query = "
		select 
			* 
		from 
			wimax_billing 
			inner join accounts_cstm on accounts_cstm.crn_c=wimax_billing.account_id
			inner join accounts on accounts.id = accounts_cstm.id_c
		where
			accounts.deleted = 0 AND
			wimax_billing.entry_date between '".$from."' and '".$to."'".
			$service_type_condition.
			$customer_type_condition.
			$billing_account_condition;*/
			
	$billing_query = "
		select 
			wimax_billing.id,
			wimax_billing.parent_id,
			wimax_billing.entry_id,
			wimax_billing.entry_date,
			wimax_billing.rate_date,
			wimax_billing.account_id,
			wimax_billing.bill_start,
			wimax_billing.billing_date,
			wimax_billing.bill_end,
			wimax_billing.entry_type,
			wimax_billing.entry,
			wimax_billing.matched_invoice,
			wimax_billing.currency,
			wimax_billing.amount,
			wimax_billing.balance,
			wimax_billing.`user`,
			wimax_billing.status
		from 
			wimax_billing 
			inner join accounts_cstm on accounts_cstm.crn_c=wimax_billing.account_id
			inner join accounts on accounts.id = accounts_cstm.id_c
		where
			accounts.deleted = 0 AND
			wimax_billing.entry_date between '".$from."' and '".$to."'".
			$service_type_condition.
			$customer_type_condition.
			$billing_account_condition;
	
	//echo "Q => ".nl2br($billing_query)."<br>";
	//exit();

	$alltx = $myquery->multiple($billing_query);
	
	foreach($alltx as $tx){
		$tx[parent_id] = trim($tx[parent_id]);
		$tx[account_id] = trim($tx[account_id]);
		$entry = unserialize(str_replace(array(':\"','\";'),array(':"','";'),$tx[entry]));
	
		if(trim($report[data][$tx[parent_id]][$tx[account_id]][name]) != ''){
			//general account info
			$report[data][$tx[parent_id]][$tx[account_id]][name] = $parent_data[$tx[account_id]][name];
			$report[data][$tx[parent_id]][$tx[account_id]][customer_type] = $parent_data[$tx[account_id]][customer_type];
			$report[data][$tx[parent_id]][$tx[account_id]][platform] = $parent_data[$tx[account_id]][platform];
			$report[data][$tx[parent_id]][$tx[account_id]][service_type] = $parent_data[$tx[account_id]][service_type];
			$report[data][$tx[parent_id]][$tx[account_id]][inv_num] = $parent_data[$tx[account_id]][inv_num];
			$report[data][$tx[parent_id]][$tx[account_id]][inv_id] = $parent_data[$tx[account_id]][inv_id];
			$report[data][$tx[parent_id]][$tx[account_id]][billing_currency] = $parent_data[$tx[account_id]][billing_currency];
			//$report[data][$tx[parent_id]][$tx[account_id]][account_no] = $parent_data[$tx[account_id]][account_no];
			$report[data][$tx[parent_id]][$tx[account_id]][account_no] = $tx[account_id];
			$report[data][$tx[parent_id]][$tx[account_id]][parent_no] = $tx[parent_id];
			//Package stuff
			$report[data][$tx[parent_id]][$tx[account_id]][package] = $parent_data[$tx[account_id]][package];
			$report[data][$tx[parent_id]][$tx[account_id]][num_p] = $parent_data[$tx[account_id]][num_p];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_p] = $parent_data[$tx[account_id]][disc_p];
			$report[data][$tx[parent_id]][$tx[account_id]][p_price] = $parent_data[$tx[account_id]][p_price];
			$report[data][$tx[parent_id]][$tx[account_id]][tp_price] = $parent_data[$tx[account_id]][p_price] * $parent_data[$tx[account_id]][num_p] * (1 - ($parent_data[$tx[account_id]][disc_p]/100));
			//Bandwidth stuff
			$report[data][$tx[parent_id]][$tx[account_id]][bandwidth] = $parent_data[$tx[account_id]][bandwidth];
			$report[data][$tx[parent_id]][$tx[account_id]][num_b] = $parent_data[$tx[account_id]][num_b];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_b] = $parent_data[$tx[account_id]][disc_b];
			$report[data][$tx[parent_id]][$tx[account_id]][b_price] = $parent_data[$tx[account_id]][b_price];
			$report[data][$tx[parent_id]][$tx[account_id]][tb_price] = $parent_data[$tx[account_id]][b_price] * $parent_data[$tx[account_id]][num_b] * (1 - ($parent_data[$tx[account_id]][disc_b]/100));
			//Contract stuff for both bandwidth and package stuff
			$report[data][$tx[parent_id]][$tx[account_id]][status] = $parent_data[$tx[account_id]][status_p];
			$report[data][$tx[parent_id]][$tx[account_id]][start_date] = $parent_data[$tx[account_id]][start_p];
			$report[data][$tx[parent_id]][$tx[account_id]][expiry_date] = $parent_data[$tx[account_id]][end_p];
			//Domain hosting detail
			$report[data][$tx[parent_id]][$tx[account_id]][DH] = $parent_data[$tx[account_id]][domain_hosting];
			$report[data][$tx[parent_id]][$tx[account_id]][status_dh] = $parent_data[$tx[account_id]][status_dh];
			$report[data][$tx[parent_id]][$tx[account_id]][start_dh] = $parent_data[$tx[account_id]][start_dh];
			$report[data][$tx[parent_id]][$tx[account_id]][end_dh] = $parent_data[$tx[account_id]][end_dh];
			$report[data][$tx[parent_id]][$tx[account_id]][num_dh] = $parent_data[$tx[account_id]][num_dh];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_dh] = $parent_data[$tx[account_id]][disc_dh];
			$report[data][$tx[parent_id]][$tx[account_id]][dh_price] = $parent_data[$tx[account_id]][dh_price];
			$report[data][$tx[parent_id]][$tx[account_id]][tdh_price] = $parent_data[$tx[account_id]][dh_price] * $parent_data[$tx[account_id]][num_dh] * (1 - ($parent_data[$tx[account_id]][disc_dh]/100));
			//Domain registration detail
			$report[data][$tx[parent_id]][$tx[account_id]][DR] = $parent_data[$tx[account_id]][domain_registration];
			$report[data][$tx[parent_id]][$tx[account_id]][status_dr] = $parent_data[$tx[account_id]][status_dr];
			$report[data][$tx[parent_id]][$tx[account_id]][start_dr] = $parent_data[$tx[account_id]][start_dr];
			$report[data][$tx[parent_id]][$tx[account_id]][end_dr] = $parent_data[$tx[account_id]][end_dr];
			$report[data][$tx[parent_id]][$tx[account_id]][num_dr] = $parent_data[$tx[account_id]][num_dr];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_dr] = $parent_data[$tx[account_id]][disc_dr];
			$report[data][$tx[parent_id]][$tx[account_id]][dr_price] = $parent_data[$tx[account_id]][dr_price];
			$report[data][$tx[parent_id]][$tx[account_id]][tdr_price] = $parent_data[$tx[account_id]][dr_price] * $parent_data[$tx[account_id]][num_dr] * (1 - ($parent_data[$tx[account_id]][disc_dr]/100));
			//Web hosting detail
			$report[data][$tx[parent_id]][$tx[account_id]][WH] = $parent_data[$tx[account_id]][web_hosting];
			$report[data][$tx[parent_id]][$tx[account_id]][status_wh] = $parent_data[$tx[account_id]][status_wh];
			$report[data][$tx[parent_id]][$tx[account_id]][start_wh] = $parent_data[$tx[account_id]][start_wh];
			$report[data][$tx[parent_id]][$tx[account_id]][end_wh] = $parent_data[$tx[account_id]][end_wh];
			$report[data][$tx[parent_id]][$tx[account_id]][num_wh] = $parent_data[$tx[account_id]][num_wh];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_wh] = $parent_data[$tx[account_id]][disc_wh];
			$report[data][$tx[parent_id]][$tx[account_id]][wh_price] = $parent_data[$tx[account_id]][wh_price];
			$report[data][$tx[parent_id]][$tx[account_id]][twh_price] = $parent_data[$tx[account_id]][wh_price] * $parent_data[$tx[account_id]][num_wh] * (1 - ($parent_data[$tx[account_id]][disc_wh]/100));
			//Mail hosting detail
			$report[data][$tx[parent_id]][$tx[account_id]][MH] = $parent_data[$tx[account_id]][mail_hosting];
			$report[data][$tx[parent_id]][$tx[account_id]][status_mh] = $parent_data[$tx[account_id]][status_mh];
			$report[data][$tx[parent_id]][$tx[account_id]][start_mh] = $parent_data[$tx[account_id]][start_mh];
			$report[data][$tx[parent_id]][$tx[account_id]][end_mh] = $parent_data[$tx[account_id]][end_mh];
			$report[data][$tx[parent_id]][$tx[account_id]][num_mh] = $parent_data[$tx[account_id]][num_mh];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_mh] = $parent_data[$tx[account_id]][disc_mh];
			$report[data][$tx[parent_id]][$tx[account_id]][mh_price] = $parent_data[$tx[account_id]][mh_price];
			$report[data][$tx[parent_id]][$tx[account_id]][tmh_price] = $parent_data[$tx[account_id]][mh_price] * $parent_data[$tx[account_id]][num_mh] * (1 - ($parent_data[$tx[account_id]][disc_mh]/100));
			//Hire Purchase detail
			$report[data][$tx[parent_id]][$tx[account_id]][HP] = $parent_data[$tx[account_id]][hire_purchase];
			$report[data][$tx[parent_id]][$tx[account_id]][status_hp] = $parent_data[$tx[account_id]][status_hp];
			$report[data][$tx[parent_id]][$tx[account_id]][start_hp] = $parent_data[$tx[account_id]][start_hp];
			$report[data][$tx[parent_id]][$tx[account_id]][end_hp] = $parent_data[$tx[account_id]][end_hp];
			$report[data][$tx[parent_id]][$tx[account_id]][num_hp] = $parent_data[$tx[account_id]][num_hp];
			$report[data][$tx[parent_id]][$tx[account_id]][disc_hp] = $parent_data[$tx[account_id]][disc_hp];
			$report[data][$tx[parent_id]][$tx[account_id]][hp_price] = $parent_data[$tx[account_id]][hp_price];
			$report[data][$tx[parent_id]][$tx[account_id]][thp_price] = $parent_data[$tx[account_id]][hp_price] * $parent_data[$tx[account_id]][num_hp] * (1 - ($parent_data[$tx[account_id]][disc_hp]/100));
			
			/*if(
			   ($report[data][$tx[parent_id]][$tx[account_id]][status_mh] != 'churned')
			){
			}*/
		}
		
		if(!$report[data][$tx[parent_id]][$tx[parent_id]][opening_balance]){
			$bal_array = $myquery->single("select balance from wimax_billing where id = (select max(id) from wimax_billing where parent_id = '".$tx[parent_id]."' and entry_date < '".$tx[entry_date]."')");
			$report[data][$tx[parent_id]][$tx[parent_id]][opening_balance] = $bal_array[balance];
			$report[data][$tx[parent_id]][$tx[parent_id]][balance] = $report[data][$tx[parent_id]][$tx[parent_id]][opening_balance];
		}

		$tx[grouping] = $entry[grouping];
		$tx[entry] = $entry[entry];
		$rate_row = get_rate(get_rate_date($tx[entry_date],$tx[rate_date]));
		$tx[rate] = $rate_row[rate];
		$tx[ugx_value] = ($tx[amount] * $tx[rate]);
		
		$report[data][$tx[parent_id]][$tx[parent_id]][balance] += $tx[amount];

		if($tx[entry_type] == 'Payment'){
			$report[data][$tx[parent_id]][$tx[parent_id]][$tx[entry_type]] += $tx[amount];
			$report[data][$tx[parent_id]][$tx[parent_id]][$tx[entry_type].' (UGX)'] += $tx[ugx_value];
			
			$report[totals][$tx[parent_id]][$tx[parent_id]][$tx[entry_type]] += $tx[amount];
			$report[totals][$tx[parent_id]][$tx[parent_id]][$tx[entry_type].' (UGX)'] += $tx[ugx_value];
		}

		if($tx[entry_type] == 'Charges'){
			if($tx[entry] != 'Equipment Deposit'){
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[grouping]] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[entry_type]] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[entry_type].' (UGX)'] += $tx[ugx_value]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue'] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
				
				$report[totals][$tx[entry_type]] += $tx[amount]/1.18;
				$report[totals][$tx[entry_type].' (UGX)'] += $tx[ugx_value]/1.18;
				$report[totals]['Tax exclusive Revenue'] += $tx[amount]/1.18;
				$report[totals]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
				$report[totals]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
				$report[totals]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
			}else{
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[grouping]] += $tx[amount];
				$report[data][$tx[parent_id]][$tx[account_id]][Untaxed] += $tx[amount];
				$report[data][$tx[parent_id]][$tx[account_id]]['Untaxed (UGX)'] += $tx[ugx_value];
				
				$report[totals][$tx[grouping]] += $tx[amount];
				$report[totals][$tx[grouping].' (UGX)'] += $tx[ugx_value];
			}
		}
	
		if($tx[entry_type] == 'Services'){
			$report[data][$tx[parent_id]][$tx[account_id]][$tx[grouping]] += $tx[amount]/1.18;
			$report[data][$tx[parent_id]][$tx[account_id]]['Total '.$tx[entry_type]] += $tx[amount]/1.18;
			$report[data][$tx[parent_id]][$tx[account_id]]['Total '.$tx[entry_type].' (UGX)'] += $tx[ugx_value]/1.18;
			$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue'] += $tx[amount]/1.18;
			$report[data][$tx[parent_id]][$tx[account_id]]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
			$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
			$report[data][$tx[parent_id]][$tx[account_id]]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
			
			$report[totals][$tx[grouping]] += $tx[amount]/1.18;
			$report[totals]['Monthly Charges'] += $tx[amount]/1.18;
			$report[totals]['Monthly Charges (UGX)'] += $tx[ugx_value]/1.18;
			$report[totals]['Tax exclusive Revenue'] += $tx[amount]/1.18;
			$report[totals]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
			$report[totals]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
			$report[totals]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
		}
		
		if($tx[entry_type] == 'Adjustment'){
			$query = "
				SELECT
					ps_products_cstm.product_grouping_c as grouping
				FROM
					ps_products_cstm
					INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
				WHERE 
					ps_products.name='".$tx[entry]."' AND
					ps_products.deleted != 1
				";
			$result = $myquery->single($query);
			
			if($entry[approved_by] == "The Prorator function"){
				$prefix = "PRFX ";
				//print_r($entry); echo "<br><br>";
			}else{
				$prefix = '';
			}
			
			if(!(($tx[grouping] == 'Cash Discount') || ($tx[grouping] == 'Waiver on Equipment'))){
				$report[data][$tx[parent_id]][$tx[account_id]][$prefix.$tx[grouping].' - '.$result[grouping]] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]][$prefix.$tx[grouping].' - '.$result[grouping].' (UGX)'] += $tx[ugx_value]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue'] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
				
				$report[totals][$prefix.$tx[grouping].' - '.$result[grouping]] += $tx[amount]/1.18;
				$report[totals][$prefix.$tx[grouping].' - '.$result[grouping].' (UGX)'] += $tx[ugx_value]/1.18;
				$report[totals]['Tax exclusive Revenue'] += $tx[amount]/1.18;
				$report[totals]['Tax'] += ($tx[amount] - $tx[amount]/1.18);
				$report[totals]['Tax exclusive Revenue (UGX)'] += $tx[ugx_value]/1.18;
				$report[totals]['Tax (UGX)'] += ($tx[ugx_value] - $tx[ugx_value]/1.18);
			}else{
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[grouping].' - '.$result[grouping]] += $tx[amount];
				$report[data][$tx[parent_id]][$tx[account_id]][$tx[grouping].' - '.$result[grouping].' (UGX)'] += $tx[ugx_value];
				$report[data][$tx[parent_id]][$tx[account_id]][Untaxed] += $tx[amount];
				$report[data][$tx[parent_id]][$tx[account_id]]['Untaxed (UGX)'] += $tx[ugx_value];
				
				$report[totals][$tx[grouping].' - '.$result[grouping]] += $tx[amount];
				$report[totals][$tx[grouping].' - '.$result[grouping].' (UGX)'] += $tx[ugx_value];
				$report[totals][Untaxed] += $tx[amount];
				$report[totals]['Untaxed (UGX)'] += $tx[ugx_value];
			}
		}	
	}
	
	//echo "4 ===> "; print_r($report[data]['201003-651']['201003-651']); echo "<br><br>";
	
	foreach($report[data] as &$parent_row){
		foreach($parent_row as &$row){
			$row['Total Account Charges'] = ($row['Tax exclusive Revenue'] + $row[Tax] + $row[Untaxed] + $row["Cash Discount -"]);
		
			//incase credit note entries exceed the charge entries we need to add the positive charge to the payments ie deposits 
			if($row['Total Account Charges'] > 0){
				$row['Actual Account Charges'] = 0;
				$row[deposits] = $row[Payment] + $row['Total Account Charges'];
			}else{
				$row['Actual Account Charges'] = $row['Total Account Charges'];
				$row[deposits] = $row[Payment];
			}
			$row['Tax inclusive Revenue'] = ($row['Tax exclusive Revenue'] + $row[Tax]);
			$report[totals]['Total Charged Revenue'] += ($row['Total Account Charges']);
			/*if($row[opening_balance] > 0){
				$row[credits] = $row[opening_balance] + $row[deposits];
				
				if($row[credits] > abs($row['Actual Account Charges'])){
					$row['Realised Revenue'] = abs($row['Actual Account Charges']);
					$row['Unrealised Revenue'] = ($row[credits] + $row['Actual Account Charges']);
					$report[totals]['Realised Revenue'] += $row['Realised Revenue'];
					$report[totals]['Unrealised Revenue'] += $row['Unrealised Revenue'];
				}else{
					$report[totals]['Unpaid Revenue'] += abs($row[balance]);
					$row['Realised Revenue'] = $row[credits];
					$report[totals]['Realised Revenue'] += $row['Realised Revenue'];
				}
			}else{
				$row[debits] = $row[opening_balance] + $row['Actual Account Charges'];
				
				if($row[deposits] > abs($row[debits])){
					$row['Realised Revenue'] = abs($row[debits]);
					$row['Unrealised Revenue'] = ($row[deposits] + $row[debits]);
					$report[totals]['Realised Revenue'] += $row['Realised Revenue'];
					$report[totals]['Unrealised Revenue'] += $row['Unrealised Revenue'];
				}else{
					$row['Realised Revenue'] = $row[deposits];
					$report[totals]['Unpaid Revenue'] += abs($row[balance]);
					$report[totals]['Realised Revenue'] += $row['Realised Revenue'];
				}
			}*/
		}
	}
	//Just to be sure Important
	unset($row,$parent_row);
	
	//Accounts that do not have billing txs this month in question
	foreach($parent_data as $account_no=>$row){
		if(!$report[data][$row[parent_no]][$account_no][name]){
			//general account info
			$report[data][$row[parent_no]][$account_no][name] = $row[name];
			$report[data][$row[parent_no]][$account_no][platform] = $row[platform];
			$report[data][$row[parent_no]][$account_no][customer_type] = $row[customer_type];
			$report[data][$row[parent_no]][$account_no][service_type] = $row[service_type];
			$report[data][$row[parent_no]][$account_no][inv_num] = $row[inv_num];
			$report[data][$row[parent_no]][$account_no][inv_id] = $row[inv_id];
			$report[data][$row[parent_no]][$account_no][account_no] = $row[account_no];
			$report[data][$row[parent_no]][$account_no][billing_currency] = $row[billing_currency];
			//Package stuff
			$report[data][$row[parent_no]][$account_no][package] = $row[package];
			$report[data][$row[parent_no]][$account_no][num_p] = $row[num_p];
			$report[data][$row[parent_no]][$account_no][disc_p] = $row[disc_p];
			$report[data][$row[parent_no]][$account_no][p_price] = $row[p_price];
			$report[data][$row[parent_no]][$account_no][tp_price] = $row[p_price] * $row[num_p] * (1 - ($row[disc_p]/100));
			//Bandwidth stuff
			$report[data][$row[parent_no]][$account_no][bandwidth] = $row[bandwidth];
			$report[data][$row[parent_no]][$account_no][num_b] = $row[num_b];
			$report[data][$row[parent_no]][$account_no][disc_b] = $row[disc_b];
			$report[data][$row[parent_no]][$account_no][b_price] = $row[b_price];
			$report[data][$row[parent_no]][$account_no][tb_price] = $row[b_price] * $row[num_b] * (1 - ($row[disc_b]/100));
			//Contract stuff for both bandwidth and package stuff
			$report[data][$row[parent_no]][$account_no][status] = $row[status_p];
			$report[data][$row[parent_no]][$account_no][start_date] = $row[start_p];
			$report[data][$row[parent_no]][$account_no][expiry_date] = $row[end_p];
			//Domain hosting detail
			$report[data][$row[parent_no]][$account_no][DH] = $row[domain_hosting];
			$report[data][$row[parent_no]][$account_no][status_dh] = $row[status_dh];
			$report[data][$row[parent_no]][$account_no][start_dh] = $row[start_dh];
			$report[data][$row[parent_no]][$account_no][end_dh] = $row[end_dh];
			$report[data][$row[parent_no]][$account_no][num_dh] = $row[num_dh];
			$report[data][$row[parent_no]][$account_no][disc_dh] = $row[disc_dh];
			$report[data][$row[parent_no]][$account_no][dh_price] = $row[dh_price];
			$report[data][$row[parent_no]][$account_no][tdh_price] = $row[dh_price] * $row[num_dh] * (1 - ($row[disc_dh]/100));
			//Domain registration detail
			$report[data][$row[parent_no]][$account_no][DR] = $row[domain_registration];
			$report[data][$row[parent_no]][$account_no][status_dr] = $row[status_dr];
			$report[data][$row[parent_no]][$account_no][start_dr] = $row[start_dr];
			$report[data][$row[parent_no]][$account_no][end_dr] = $row[end_dr];
			$report[data][$row[parent_no]][$account_no][num_dr] = $row[num_dr];
			$report[data][$row[parent_no]][$account_no][disc_dr] = $row[disc_dr];
			$report[data][$row[parent_no]][$account_no][dr_price] = $row[dr_price];
			$report[data][$row[parent_no]][$account_no][tdr_price] = $row[dr_price] * $row[num_dr] * (1 - ($row[disc_dr]/100));
			//Web hosting detail
			$report[data][$row[parent_no]][$account_no][WH] = $row[web_hosting];
			$report[data][$row[parent_no]][$account_no][status_wh] = $row[status_wh];
			$report[data][$row[parent_no]][$account_no][start_wh] = $row[start_wh];
			$report[data][$row[parent_no]][$account_no][end_wh] = $row[end_wh];
			$report[data][$row[parent_no]][$account_no][num_wh] = $row[num_wh];
			$report[data][$row[parent_no]][$account_no][disc_wh] = $row[disc_wh];
			$report[data][$row[parent_no]][$account_no][wh_price] = $row[wh_price];
			$report[data][$row[parent_no]][$account_no][twh_price] = $row[wh_price] * $row[num_wh] * (1 - ($row[disc_wh]/100));
			//Mail hosting detail
			$report[data][$row[parent_no]][$account_no][MH] = $row[mail_hosting];
			$report[data][$row[parent_no]][$account_no][status_mh] = $row[status_mh];
			$report[data][$row[parent_no]][$account_no][start_mh] = $row[start_mh];
			$report[data][$row[parent_no]][$account_no][end_mh] = $row[end_mh];
			$report[data][$row[parent_no]][$account_no][num_mh] = $row[num_mh];
			$report[data][$row[parent_no]][$account_no][disc_mh] = $row[disc_mh];
			$report[data][$row[parent_no]][$account_no][mh_price] = $row[mh_price];
			$report[data][$row[parent_no]][$account_no][tmh_price] = $row[mh_price] * $row[num_wh] * (1 - ($row[disc_mh]/100));
			//Hire purchase detail
			$report[data][$row[parent_no]][$account_no][HP] = $row[hire_purchase];
			$report[data][$row[parent_no]][$account_no][status_hp] = $row[status_hp];
			$report[data][$row[parent_no]][$account_no][start_hp] = $row[start_hp];
			$report[data][$row[parent_no]][$account_no][end_hp] = $row[end_hp];
			$report[data][$row[parent_no]][$account_no][num_hp] = $row[num_hp];
			$report[data][$row[parent_no]][$account_no][disc_hp] = $row[disc_hp];
			$report[data][$row[parent_no]][$account_no][hp_price] = $row[hp_price];
			$report[data][$row[parent_no]][$account_no][thp_price] = $row[hp_price] * $row[num_hp] * (1 - ($row[disc_hp]/100));
		}
	}

	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;

	return display_rr_revenue_report($report);
}

function display_rr_revenue_report($report){
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th></th>
			<th>Parent Number</th>
			<th>Account Number</th>
			<th>Invoice Number</th>
			<th>Account Name</th>
			<th>Platform</th>
			<th>Customer Type</th>
			<th>Service Type</th>
			<th>Billing Currency</th>

			<th>Bandwidth</th>
			<th>Data price</th>
			<th>Data Num</th>
			<th>Data Disc</th>
			<th>Start Date</th>
			<th>Expiry Date</th>
			<th>Status</th>
			<th>Net Bandwidth Charges</th>
			<th>Bandwidth CN</th>
			<th>Bandwidth DN</th>
			
			<th>Package</th>
			<th>Pack price</th>
			<th>Pack Num</th>
			<th>Pack Disc</th>
			<th>Net Equipment Rental</th>
			<th>Equipment Rental CN</th>
			<th>Equipment Rental DN</th>
			
			<th>Equipment Sale</th>
			<th>Connection Fees</th>
			<th>Access Point Fees</th>
			
			<th>WH pack</th>
			<th>WH price</th>
			<th>WH Num</th>
			<th>WH Disc</th>
			<th>WH Start Date</th>
			<th>WH Expiry Date</th>
			<th>WH Status</th>
			<th>Net WH</th>
			<th>WH CN</th>
			<th>WH DN</th>
			
			<th>MH pack</th>
			<th>MH price</th>
			<th>MH Num</th>
			<th>MH Disc</th>
			<th>MH Start Date</th>
			<th>MH Expiry Date</th>
			<th>MH Status</th>
			<th>Net MH</th>
			<th>MH CN</th>
			<th>MH DN</th>
			
			<th>DR pack</th>
			<th>DR price</th>
			<th>DR Num</th>
			<th>DR Disc</th>
			<th>DR Start Date</th>
			<th>DR Expiry Date</th>
			<th>DR Status</th>
			<th>Net DR</th>
			<th>DR CN</th>
			<th>DR DN</th>
			
			<th>DH pack</th>
			<th>DH price</th>
			<th>DH Num</th>
			<th>DH Disc</th>
			<th>DH Start Date</th>
			<th>DH Expiry Date</th>
			<th>DH Status</th>
			<th>Net DH</th>
			<th>DH CN</th>
			<th>DH DN</th>
			
			<th>HP pack</th>
			<th>HP price</th>
			<th>HP Num</th>
			<th>HP Disc</th>
			<th>HP Start Date</th>
			<th>HP Expiry Date</th>
			<th>HP Status</th>
			<th>Net HP</th>
			<th>HP CN</th>
			<th>HP DN</th>
			
			<th>Total Revenue Excluding Tax</th>
			<th>VAT 18%</th>
			<th>Total Revenue Including Tax</th>
			<th>Equipment Deposits</th>
			<th>Discounts on Equipment</th>
			<th>Total Untaxed</th>
			<th>Charged Revenue</th>
			<th>Cash Discounts</th> 
			<th>Opening Balance</th>
			<th>Payments</th>
			<th>Balance</th>			
		</tr>
	';
	foreach($report[data] as $parent_no=>$parent_row){
		//print_r($row); echo '<br><br>';
		foreach($parent_row as $account_no=>$row){
			//print_r($row); exit();
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$parent_no.'</td>
					<td class="text_values">'.$account_no.'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/billing/print_invoice.php?id='.$row[inv_id].'" target="_blank">'.$row[inv_num].'</a></td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[platform].'</td>
					<td class="text_values">'.$row[customer_type].'</td>
					<td class="text_values">'.$row[service_type].'</td>
					<td class="text_values">'.$row[billing_currency].'</td>
					
					<td class="values">'.$row[bandwidth].'</td>
					<td class="values">'.accounts_format($row[b_price]).'</td>
					<td class="values">'.accounts_format($row[num_b]).'</td>
					<td class="values">'.accounts_format($row[disc_b]).'</td>
					<td class="values">'.$row[start_date].'</td>
					<td class="values">'.$row[expiry_date].'</td>
					<td class="values">'.$row[status].'</td>
					<td class="values">'.accounts_format(-($row[Service] +
														   $row["PRFX Debit Note - Service"] +
														   $row["PRFX Credit Note - Service"]
														   )
														 ).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Service"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Service"]).'</td>
					
					<td class="text_values">'.$row[package].'</td>
					<td class="values">'.accounts_format($row[p_price]).'</td>
					<td class="values">'.accounts_format($row[num_p]).'</td>
					<td class="values">'.accounts_format($row[disc_p]).'</td>
					<td class="values">'.accounts_format(-($row["Rental Fees"] +
														   $row["PRFX Debit Note - Rental Fees"] +
														   $row["PRFX Credit Note - Rental Fees"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Rental Fees"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Rental Fees"]).'</td>
					
					<td class="values">'.accounts_format(-$row["Equipment Sale"]).'</td>
					<td class="values">'.accounts_format(-$row["Connection Fees"]).'</td>
					<td class="values">'.accounts_format(-$row["Access Point Fees"]).'</td>
					
					<td class="text_values">'.$row[WH].'</td>
					<td class="values">'.accounts_format($row[wh_price]).'</td>
					<td class="values">'.accounts_format($row[num_wh]).'</td>
					<td class="values">'.accounts_format($row[disc_wh]).'</td>
					<td class="values">'.$row[start_wh].'</td>
					<td class="values">'.$row[end_wh].'</td>
					<td class="values">'.$row[status_wh].'</td>
					<td class="values">'.accounts_format(-($row["Web Hosting"] +
														   $row["PRFX Debit Note - Web Hosting"] +
														   $row["PRFX Credit Note - Web Hosting"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Web Hosting"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Web Hosting"]).'</td>
					
					<td class="text_values">'.$row[MH].'</td>
					<td class="values">'.accounts_format($row[mh_price]).'</td>
					<td class="values">'.accounts_format($row[num_mh]).'</td>
					<td class="values">'.accounts_format($row[disc_mh]).'</td>
					<td class="values">'.$row[start_mh].'</td>
					<td class="values">'.$row[end_mh].'</td>
					<td class="values">'.$row[status_mh].'</td>
					<td class="values">'.accounts_format(-($row["Mail Hosting"] +
														   $row["PRFX Debit Note - Mail Hosting"] +
														   $row["PRFX Credit Note - Mail Hosting"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Mail Hosting"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Mail Hosting"]).'</td>
					
					<td class="text_values">'.$row[DR].'</td>
					<td class="values">'.accounts_format($row[dr_price]).'</td>
					<td class="values">'.accounts_format($row[num_dr]).'</td>
					<td class="values">'.accounts_format($row[disc_dr]).'</td>
					<td class="values">'.$row[start_dr].'</td>
					<td class="values">'.$row[end_dr].'</td>
					<td class="values">'.$row[status_dr].'</td>
					<td class="values">'.accounts_format(-($row["Domain Registration"] +
														   $row["PRFX Debit Note - Domain Registration"] +
														   $row["PRFX Credit Note - Domain Registration"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Domain Registration"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Domain Registration"]).'</td>
					
					<td class="text_values">'.$row[DH].'</td>
					<td class="values">'.accounts_format($row[dh_price]).'</td>
					<td class="values">'.accounts_format($row[num_dh]).'</td>
					<td class="values">'.accounts_format($row[disc_dh]).'</td>
					<td class="values">'.$row[start_dh].'</td>
					<td class="values">'.$row[end_dh].'</td>
					<td class="values">'.$row[status_dh].'</td>
					<td class="values">'.accounts_format(-($row["Domain Hosting"] +
														   $row["PRFX Debit Note - Domain Hosting"] +
														   $row["PRFX Credit Note - Domain Hosting"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Domain Hosting"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Domain Hosting"]).'</td>
					
					<td class="text_values">'.$row[HP].'</td>
					<td class="values">'.accounts_format($row[hp_price]).'</td>
					<td class="values">'.accounts_format($row[num_hp]).'</td>
					<td class="values">'.accounts_format($row[disc_hp]).'</td>
					<td class="values">'.$row[start_hp].'</td>
					<td class="values">'.$row[end_hp].'</td>
					<td class="values">'.$row[status_hp].'</td>
					<td class="values">'.accounts_format(-($row["Hire Purchase"] +
														   $row["PRFX Debit Note - Hire Purchase"] +
														   $row["PRFX Credit Note - Hire Purchase"]
														   )
														).'</td>
					<td class="values">'.accounts_format(-$row["Credit Note - Hire Purchase"]).'</td>
					<td class="values">'.accounts_format(-$row["Debit Note - Hire Purchase"]).'</td>
					
					<td class="values">'.accounts_format(-$row["Tax exclusive Revenue"]).'</td>
					<td class="values">'.accounts_format(-$row[Tax]).'</td>
					<td class="values">'.accounts_format(-$row["Tax inclusive Revenue"]).'</td>
					<td class="values">'.accounts_format(-$row["Equipment Deposits"]).'</td>
					<td class="values">'.accounts_format(-$row["Waiver on Equipment - Equipment Deposits"]).'</td>
					<td class="values">'.accounts_format(-$row[Untaxed]).'</td>
					<td class="values">'.accounts_format($row["Total Account Charges"]).'</td>
					<td class="values">'.accounts_format(-$row["Cash Discount -"]).'</td>
					<td class="values">'.accounts_format($row[opening_balance]).'</td>
					<td class="values">'.accounts_format($row[Payment]).'</td>
					<td class="values" '; if($row[balance] < 0){ $html .= 'style="background-color:#FF0000; color: #FFFFFF; font-weight: bold;"';} $html .= '>'.accounts_format($row[balance]).'</td>
				</tr>
			';
		}
	}
	
	$html .= '
		</table>
		</td>
		</tr>
		';
	
	/*$html .= '
		<tr><td colspan="76">&nbsp;</td></tr>
		<tr><td colspan="76">
			<table width="100%">
				<tr>
		';

		foreach($report[totals] as $total){
			$html .= '
				<tr><td colspan="76" height="10">&nbsp;</td></tr>
				<tr><td colspan="76">
				<table width="100%" border="0" cellpadding="2" cellspacing="0"> 
					<tr> 
			';
				foreach($total as $title=>$value){
					$html .= '<th>'.$title.'</th>';
				}
				
				$html .= '
						</tr>
						<tr>
						';
				
				foreach($total as $values){
					$html .= '<td class="values">'.accounts_format($values).'</td>';
				}
			
			$html .= '
					</tr>
				</table>
				</td></tr>
			';
		}
	
	$html .= '
				</tr>
			</table>
			</td>
		</tr>
		';*/
	
	$html .= '
		</table>
	';
	
	return $html;
}?>