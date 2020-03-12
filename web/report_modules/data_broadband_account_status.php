<?php

function generate_broadband_account_status_report($account_id,$status){

	custom_query::select_db('wimax');

	$myquery = new custom_query();
	
	$query = "
		SELECT
			accounts_cstm.crn_c as account_no,
			accounts.name,
			concat(accounts.phone_alternate,'/',accounts_cstm.contact_person_phone_c) as contact_numbers, 
			accounts_cstm.email_c as email, 
			accounts_cstm.preferred_username_c as username,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.crn_c as account_id, 
			accounts_cstm.shared_packages_c as package, 
			accounts_cstm.cpe_type_c as cpe_type, 
			accounts_cstm.primary_base_c as primary_base,
			accounts_cstm.download_bandwidth_c as bandwidth, 
			if(cn_contracts.expiry_date IS NULL,'NO CONTRACT',cn_contracts.expiry_date) as expiry_date, 
			if(cn_contracts.start_date is NULL,'NO CONTRACT',cn_contracts.start_date) as start_date,
			if(cn_contracts.status is NULL,'NO CONTRACT', cn_contracts.status) as status
		FROM accounts 
			INNER JOIN accounts_cstm ON (accounts.id = accounts_cstm.id_c)
			LEFT OUTER JOIN cn_contracts ON (accounts.id = cn_contracts.account)
		WHERE 
			accounts.deleted = '0' AND 
	";
	
	if($account_id){
		$query .= " accounts_cstm.crn_c = '".$account_id."' and ";
	}
	if($status){
		$query .= " cn_contracts.status ";
		if($status != 'NO CONTRACT'){
			$query .= "= '".$status."'";
		}else{
			$query .= " IS NULL ";
		}
		$query .= " and ";
	}
	
	$query .= "
			(
				(cn_contracts.deleted = '0') OR (cn_contracts.deleted IS NULL)
			)
			order by
			accounts.name asc
	";
	
	//echo $query;
	
	return display_broadband_status_report($myquery->multiple($query));
}

function display_broadband_status_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th></th>
			  <th>Account Number</th>
			  <th>Account Name</th>
			  <th>Status</th>
			  <th>Username</th>
			  <th>Customer type</th>
			  <!--<th>CPE type</th>-->
			  <th>Bandwidth</th>
			  <!--<th>Package</th>-->
			  <th>Current period\'s Start date</th>
			  <th>Current period\'s Expiry date</th>
			  <th>Client Contacts</th>
			  <th>Primary Base</th>
		</tr>
	';
	
	foreach($report as $row){
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$row[account_no].'</td>
				<td class="text_values">'.$row[name].'</td>
				<td class="'; if($row[status] == 'NO CONTRACT'){ $html .= 'red_'; } $html .= 'text_values">'.$row[status].'</td>
				<td class="text_values">'.$row[username].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<!--<td class="text_values">'.$row[cpe_type].'</td>-->
				<td class="text_values">'.$row[bandwidth].'</td>
				<!--<td class="text_values">'.$row[package].'</td>-->
				<td class="text_values">'.$row[start_date].'</td>
				<td class="text_values">'.$row[expiry_date].'</td>
				<td class="text_values">'.$row[contact_numbers].'</td>
				<td class="text_values">'.strtoupper($row[primary_base]).'</td>
			</tr>
		';
	}
	
	$html . '
		</table>
	';
	return $html;
}

?>