<?php

function generate_churn_date_report($from, $to, $account_id){

	custom_query::select_db('wimax');

	$myquery = new custom_query();
	
	$query = "
		select
			accounts_cstm.crn_c as account_no,
			accounts.name,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.platform_c as platform,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.download_bandwidth_c as bandwidth,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.cpe_type_c as cpe_type,
			date(date_add(cn_contracts_audit.date_created, interval 3 hour)) as deactivation_date,
			concat(users.first_name,concat(' ',users.last_name)) as deactivated_by
		from cn_contracts_audit
			INNER JOIN cn_contracts ON (cn_contracts.id=cn_contracts_audit.parent_id)
			INNER JOIN accounts on (accounts.id=cn_contracts.account)
			INNER JOIN accounts_cstm on (accounts.id=accounts_cstm.id_c)
			INNER JOIN users on (users.id=cn_contracts_audit.created_by)
		where 
			accounts.deleted = 0 and
			cn_contracts.deleted = 0 and
			cn_contracts_audit.field_name = 'status' and
	";
	
	if($from){
		$query .= " cn_contracts_audit.date_created >= '$from' and";
	}else{
		$Y = date('Y'); $m = date('m') - 1;
		$query .= " cn_contracts_audit.date_created >= '".$Y."-".$m."-01' and";
	}
	
	if($to){
		$query .= " cn_contracts_audit.date_created <= '".$to."' and";
	}else{
		$query .= " cn_contracts_audit.date_created <= '".date('Y-m-d')."' and";
	}
	
	if($account_id){
		$query .= " accounts_cstm.crn_c = '".$account_id."' and";
	}
	
	$query .= "
			cn_contracts_audit.after_value_string = 'Churned'
			group by
			accounts_cstm.crn_c
			order by
			cn_contracts_audit.date_created asc
	";
	
	//echo $query;
	
	return display_churn_date_report($myquery->multiple($query));
}

function display_churn_date_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th></th>
			  <th>Account Number</th>
			  <th>Account Name</th>
			  <th>Platform</th>
			  <th>Package</th>
			  <th>Bandwidth</th>
			  <th>CPE Type</th>
			  <th>Sales Rep</th>
			  <th>Service type</th>
			  <th>Customer type</th>
			  <th>Deactivation Date</th>
			  <th>Deactivated By</th>
		</tr>
	';
	
	foreach($report as $row){
		$html .= '
			<tr>
				<td class="text_values">'.++$i.'</td>
				<td class="text_values">'.$row[account_no].'</td>
				<td class="text_values">'.$row[name].'</td>
				<td class="text_values">'.$row[platform].'</td>
				<td class="text_values">'.$row[package].'</td>
				<td class="text_values">'.$row[bandwidth].'</td>
				<td class="text_values">'.$row[cpe_type].'</td>
				<td class="text_values">'.$row[sales_rep].'</td>				
				<td class="text_values">'.$row[service_type].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<td class="text_values">'.$row[deactivation_date].'</td>
				<td class="text_values">'.$row[deactivated_by].'</td>
			</tr>
		';
	}
	
	$html . '
		</table>
	';
	return $html;
}

?>