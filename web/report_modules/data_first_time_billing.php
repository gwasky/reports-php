<?php
function generate_first_billing_date_report($bill_from, $bill_to, $activate_from, $activate_to, $account_id){

	custom_query::select_db('wimax');

	$myquery = new custom_query();
	
	if($bill_from == ''){
		$bill_from = date('Y-m-')."01";
		$_POST[bill_from] = $bill_from;
	}
	
	if($bill_to == ''){
		$bill_to = date('Y-m-d');
		$_POST[bill_to] = $bill_to;
	}
	
	$query = "
		select
			accounts.id,
			accounts_cstm.crn_c as account_no,
			accounts_cstm.mem_id_c as parent_id,
			accounts.name,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.platform_c as platform,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.cpe_type_c as cpe_type,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.download_bandwidth_c as bandwidth,
			min(wimax_billing.entry_date) as first_billing_date
		FROM
			accounts
			INNER JOIN accounts_cstm on (accounts.id=accounts_cstm.id_c)
			LEFT OUTER JOIN wimax_billing on (wimax_billing.account_id=TRIM(accounts_cstm.crn_c))
		where
			(wimax_billing.entry_type = 'Services' OR (wimax_billing.amount < 0 AND wimax_billing.entry_type = 'Adjustment')) AND
	";
	
	if($account_id){
		$query .= " accounts_cstm.crn_c = '".$account_id."' AND ";
	}
	
	$query .= "
			accounts.deleted = 0
		GROUP BY
			accounts_cstm.crn_c
		HAVING
			first_billing_date BETWEEN '".$bill_from."' AND '".$bill_to."'
		ORDER BY
			min(wimax_billing.entry_date) ASC
	";
	
	//echo nl2br($query);
	
	$report = $myquery->multiple($query);
	
	if(count($report) == 0) $report = FALSE;
	
	return display_first_billing_date_report($report);
}


function display_first_billing_date_report($report){
	
	if($report == FALSE) return "!!NO DATA!!";
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th></th>
			  <th>Account Number</th>
			  <th>Account Name</th>
			  <th>Bandwidth</th>
			  <!--
			  <th>Package</th>
			  <th>CPE Type</th>
			  -->
			  <th>Platform</th>
			  <th>Sales Rep</th>
			  <th>Service type</th>
			  <th>Customer type</th>
			  <th>First billing Date</th>
		</tr>
	';
	
	foreach($report as $row){
		if($row[first_billing_date] == ''){ $tr_class = 'flagged'; }else{unset($tr_class);}
		$html .= '
		<tr class="'.$tr_class.'">
			<td class="values">'.++$i.'</td>
			<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[parent_id].'" target="_blank">'.$row[account_no].'</a></td>
			<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.$row[name].'</a></td>
			<td class="text_values">'.$row[bandwidth].'</td>
			<!--
			<td class="text_values">'.$row[package].'</td>
			<td class="text_values">'.$row[cpe_type].'</td>
			-->
			<td class="text_values">'.$row[platform].'</td>
			<td class="text_values">'.$row[sales_rep].'</td>
			<td class="text_values">'.$row[service_type].'</td>
			<td class="text_values">'.$row[customer_type].'</td>
			<td class="text_values">'.$row[first_billing_date].'</td>
		</tr>
		';
	}
	
	$html . '
		</table>
	';
	
	return $html;
}

/*
		select
			accounts_cstm.crn_c as account_no,
			accounts_cstm.mem_id_c as parent_id,
			accounts.name,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.platform_c as platform,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.cpe_type_c as cpe_type,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.download_bandwidth_c as bandwidth,
			cn_contracts.status,
			min(date(date_add(cn_contracts_audit.date_created, interval 3 hour))) as activation_date,
			min(wimax_billing.entry_date) as first_billing_date,
			concat(users.first_name,concat(' ',users.last_name)) as activated_by
		from accounts
			INNER JOIN accounts_cstm on (accounts.id=accounts_cstm.id_c)
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			INNER JOIN cn_contracts_audit ON (cn_contracts.id=cn_contracts_audit.parent_id)
			INNER JOIN users on (users.id=cn_contracts_audit.created_by)
			LEFT OUTER JOIN wimax_billing on (wimax_billing.account_id=TRIM(accounts_cstm.crn_c))
		where
			accounts.deleted = 0 AND
			cn_contracts.deleted = 0 AND
			cn_contracts_audit.field_name = 'status' AND
			cn_contracts_audit.after_value_string = 'Active' AND
			cn_contracts_audit.before_value_string != 'Inactive' AND
			(
				(wimax_billing.entry_type = 'Services' OR (wimax_billing.amount < 0 AND wimax_billing.entry_type = 'Adjustment')) OR
				wimax_billing.account_id IS NULL
			)
		GROUP BY
			accounts_cstm.crn_c
		ORDER BY
			min(wimax_billing.entry_date) ASC

	$query = "
		select
			accounts.id,
			accounts_cstm.crn_c as account_no,
			accounts_cstm.mem_id_c as parent_id,
			accounts.name,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.platform_c as platform,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.cpe_type_c as cpe_type,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.download_bandwidth_c as bandwidth,
			min(wimax_billing.entry_date) as first_billing_date
		from accounts
			INNER JOIN accounts_cstm on (accounts.id=accounts_cstm.id_c)
			LEFT OUTER JOIN wimax_billing on (wimax_billing.account_id=TRIM(accounts_cstm.crn_c))
		where
			(
				(wimax_billing.entry_type = 'Services' OR (wimax_billing.amount < 0 AND wimax_billing.entry_type = 'Adjustment')) OR
				wimax_billing.account_id IS NULL
			) AND
	";
	
	if($bill_from == ''){
		$bill_from = date('Y-m-')."01";
		$_POST[bill_from] = $bill_from;
	}
	$query .= " (wimax_billing.entry_date >= '".$bill_from."' OR wimax_billing.entry_date IS NULL) AND ";
	
	if($bill_to == ''){
		$bill_to = date('Y-m-d');
		$_POST[bill_to] = $bill_to;
	}
	$query .= " (wimax_billing.entry_date <= '".$bill_to."' OR wimax_billing.entry_date IS NULL) AND ";
	
	if($account_id){
		$query .= " accounts_cstm.crn_c = '".$account_id."' AND ";
	}
	
	$query .= "
			accounts.deleted = 0
		GROUP BY
			accounts_cstm.crn_c
		ORDER BY
			min(wimax_billing.entry_date) DESC
	";
*/
?>