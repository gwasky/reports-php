<?php
function generate_first_time_activation_report($from, $to, $account_id){

	custom_query::select_db('wimax');

	$myquery = new custom_query();
	
	$query = "
		SELECT
			accounts.id as account_id,
			cn_contracts.id as contract_id,
			accounts_cstm.crn_c as account_no,
			accounts.name,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.platform_c as platform,
			accounts_cstm.sales_rep_c as sales_rep,
			accounts_cstm.cpe_type_c as cpe_type,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.download_bandwidth_c as bandwidth,
			min(date(date_add(contracts_audit_a.date_created, interval 3 hour))) as activation_date,
			concat(users.first_name,concat(' ',users.last_name)) as activated_by,
			(select contracts_audit_b.after_value_string from cn_contracts_audit contracts_audit_b where contracts_audit_b.parent_id = contracts_audit_a.parent_id AND contracts_audit_b.field_name = 'INITIALDATE_Active') as first_active_date
		FROM
			cn_contracts_audit contracts_audit_a
			INNER JOIN cn_contracts ON (cn_contracts.id=contracts_audit_a.parent_id)
			INNER JOIN accounts ON (accounts.id=cn_contracts.account)
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			INNER JOIN users ON (users.id=contracts_audit_a.created_by)
		WHERE 
			accounts.deleted = 0 AND
			cn_contracts.deleted = 0 AND
			contracts_audit_a.field_name = 'status' AND
			contracts_audit_a.after_value_string = 'Active' AND
	";
	
	if($from == ''){
		$from = date('Y-m-')."01";
		$_POST[activate_from] = $from;
		$from .= " 00:00:00";
	}
	$query .= " contracts_audit_a.date_created >= DATE_ADD('".$from."', INTERVAL -3 HOUR) AND";
	
	if($to == ''){
		$to = date('Y-m-d');
		$_POST[activate_to] = $to;
		$to .= " 23:59:59";
	}
	$query .= " contracts_audit_a.date_created <= DATE_ADD('".$to."', INTERVAL -3 HOUR) AND";
	
	if($account_id){
		$query .= " accounts_cstm.crn_c = '".$account_id."' AND";
	}
	
	$query .= "
			contracts_audit_a.before_value_string != 'Inactive'
		GROUP BY
			accounts_cstm.crn_c
		ORDER BY
			contracts_audit_a.date_created ASC
	";
	
	//echo nl2br($query);
	$report[execution_time] = strtotime(date('Y-m-d H:i:s'));
	$report[data] = $myquery->multiple($query);
	$report[execution_time] -= strtotime(date('Y-m-d H:i:s'));
	
	return display_first_time_activation_report($report);
}

function display_first_time_activation_report($report){
	
	if(count($report[data]) == 0) { return "NO DATA";}
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr><td>
			<table border="0" cellpadding="2" cellspacing="0" width="100%">
				<tr> 
					<td class="text_values">Execution time : '.number_format(-$report[execution_time],0).' seconds</td>
				</tr>
				<tr> 
					<td style="height:10px;"></td>
				</tr>
			</table>
		</tr></td>
		<tr><td>
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
				<th>Activation Date</th>
				<th>First Start Date</th>
				<th>Activated By</th>
			</tr>
	';
	
	foreach($report[data] as $row){
		if($row[first_active_date] == '') $row[first_active_date] = '____________';
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$row[account_no].'</td>
				<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[contract_id].'" target="_blank">'.$row[name].'</a></td>
				<td class="text_values">'.$row[bandwidth].'</td>
				<!--
				<td class="text_values">'.$row[package].'</td>
				<td class="text_values">'.$row[cpe_type].'</td>
				-->
				<td class="text_values">'.$row[platform].'</td>
				<td class="text_values">'.$row[sales_rep].'</td>
				<td class="text_values">'.$row[service_type].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=cn_Contracts&action=DetailView&record='.$row[contract_id].'" target="_blank">'.$row[activation_date].'</a></td>
				<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Audit&action=Popup&query=true&record='.$row[contract_id].'&module_name=cn_Contracts&mode=undefined&create=undefined&metadata=undefined" target="_blank">'.$row[first_active_date].'</a></td>
				<td class="text_values">'.$row[activated_by].'</td>
			</tr>
		';
	}
	
	$html . '
		</table>
		</tr></td>
		</table>
	';
	return $html;
}

?>