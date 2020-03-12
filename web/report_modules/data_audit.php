<?
function generate_accounts_audit_report($from, $to, $account_id, $changed_by, $parent_id, $field_name){
	
	custom_query::select_db('wimax');

	$myquery = new custom_query();

	$acc_query = "
		SELECT
		  accounts.name,
		  accounts_cstm.crn_c as account_id,
		  accounts_cstm.mem_id_c as parent_id,
		  date_add(accounts_audit.date_created, interval 3 hour) as change_date,
		  accounts_audit.created_by as user_id,
		  accounts_audit.field_name as field,
		  accounts_audit.before_value_string as luli,
		  accounts_audit.after_value_string as kati
		FROM
		 accounts
		 INNER JOIN accounts_audit ON (accounts.id=accounts_audit.parent_id)
		 INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
		WHERE
		 accounts.deleted = '0' and
		 accounts_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	$contct_query = "
		SELECT
		  accounts.name,
		  accounts_cstm.crn_c as account_id,
		  accounts_cstm.mem_id_c as parent_id,
		  cn_contracts_audit.after_value_string as kati,
		  cn_contracts_audit.before_value_string as luli,
		  cn_contracts_audit.field_name as field,
		  cn_contracts_audit.created_by as user_id,
		  date_add(cn_contracts_audit.date_created, interval 3 hour) as change_date
		FROM
		 accounts
		 INNER JOIN cn_contracts ON (cn_contracts.account=accounts.id)
		 INNER JOIN cn_contracts_audit ON (cn_contracts_audit.parent_id=cn_contracts.id)
		 INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
		WHERE
		  accounts.deleted ='0' and
		  cn_contracts_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	$s_trial_query = "
			SELECT 
			  leads.last_name as name,
			  tr_trials.tr_trials_number as account_id,
			  tr_trials.name as parent_id,
			  tr_trials_audit.date_created as change_date,
			  tr_trials_audit.created_by as user_id,
			  tr_trials_audit.field_name as field,
			  tr_trials_audit.before_value_string as luli,
			  tr_trials_audit.after_value_string as kati
			FROM
			 tr_trials
			 INNER JOIN tr_trials_audit ON (tr_trials.id=tr_trials_audit.parent_id)
			 INNER JOIN leads_tr_trials_c ON (leads_tr_trials_c.leads_tr_trstr_trials_idb=tr_trials.id)
			 INNER JOIN leads ON (leads.id=leads_tr_trials_c.leads_tr_trialsleads_ida)
			WHERE
			 leads.last_name != ''
	";
	$cases_query = "
			SELECT 
			  accounts.name,
			  accounts_cstm.crn_c as account_id,
			  accounts_cstm.mem_id_c as parent_id,
			  cases_audit.date_created as change_date,
			  cases_audit.created_by as user_id,
			  cases_audit.field_name as field,
			  cases_audit.before_value_string as luli,
			  cases_audit.after_value_string as kati
			FROM
			 cases_audit
			 INNER JOIN cases ON (cases_audit.parent_id=cases.id)
			 INNER JOIN accounts ON (accounts.id=cases.account_id)
			 INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
			 INNER JOIN cases_cstm ON (cases_cstm.id_c=cases.id)
			WHERE
			 cases_cstm.subject_setting_c != '' AND
			 cases_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	
	if($from){
		$acc_query .= "and date(accounts_audit.date_created) >= '$from'";
		$contct_query .= "and date(cn_contracts_audit.date_created) >= '$from'";
		$s_trial_query .= "and date(tr_trials_audit.date_created) >= '$from'";
		$cases_query .= "and date(cases_audit.date_created) >= '$from'";
	}else{
		$acc_query .= "and date(accounts_audit.date_created) >= date(now())";
		$contct_query .= "and date(cn_contracts_audit.date_created) >= date(now())";
		$s_trial_query .= "and date(tr_trials_audit.date_created) >= date(now())";
		$cases_query .= "and date(cases_audit.date_created) >= date(now())";
	}
	if($to){
		$acc_query .= "and date(accounts_audit.date_created) <= '$to'";
		$contct_query .= "and date(cn_contracts_audit.date_created) <= '$to'";
		$s_trial_query .= "and date(tr_trials_audit.date_created) <= '$to'";
		$cases_query .= "and date(cases_audit.date_created) <= '$to'";
	}else{
		$acc_query .= "and date(accounts_audit.date_created) <= date(now())";
		$contct_query .= "and date(cn_contracts_audit.date_created) <= date(now())";
		$s_trial_query .= "and date(tr_trials_audit.date_created) <= date(now())";
		$cases_query .= "and date(cases_audit.date_created) <= date(now())";
	}
	if($account_id){
		$acc_query .= "and accounts_cstm.crn_c = '$account_id'";
		$contct_query .= "and accounts_cstm.crn_c = '$account_id'";
		$cases_query .= "and accounts_cstm.crn_c = '$account_id'";
	}
	if($parent_id){
		$acc_query .= "and accounts_cstm.mem_id_c = '$parent_id'";
		$contct_query .= "and accounts_cstm.mem_id_c = '$parent_id'";
		$cases_query .= "and accounts_cstm.mem_id_c = '$parent_id'";

	}
	if($changed_by){
		$acc_query .= "and accounts_audit.created_by = '$changed_by'";
		$contct_query .= "and cn_contracts_audit.created_by = '$changed_by'";
		$s_trial_query .= "and tr_trials_audit.created_by = '$changed_by'";
		$cases_query .= "and cases_audit.created_by = '$changed_by'";
	}
	if($field_name){
		$acc_query .= "and accounts_audit.field_name = '$field_name'";
		$contct_query .= "and cn_contracts_audit.field_name = '$field_name'";
		$s_trial_query .= "and tr_trials_audit.field_name = '$field_name'";
		$cases_query .= "and cases_audit.field_name = '$field_name'";
	}
	
	$accounts = $myquery->multiple($acc_query);	
	$contracts = $myquery->multiple($contct_query);
	/*$service_trials = $myquery->multiple($s_trial_query);*/
	/*$cases = $myquery->multiple($cases_query);*/
	
	//Ordering
	$order = array();
	$unorderedlist = array();
	foreach($accounts as $accnt){
		$accnt['KEY'] = strtotime($accnt[change_date]);
		while(count($unorderedlist[$accnt['KEY']]) != 0){
			$accnt['KEY'] += 1;
		}
		$accnt[module] = 'Accounts';
		$accnt[module_name] = $accnt[module];
		array_push($order,$accnt['KEY']);
		$unorderedlist[$accnt['KEY']] = $accnt;
	}
	foreach($contracts as $accnt){
		$accnt['KEY'] = strtotime($accnt[change_date]);
		while(count($unorderedlist[$accnt['KEY']]) != 0){
			$accnt['KEY'] += 1;
		}
		$accnt[module] = 'cn_Contracts';
		$accnt[module_name] = 'Contracts';
		array_push($order,$accnt['KEY']);
		$unorderedlist[$accnt['KEY']] = $accnt;
	}
	/*foreach($service_trials as $accnt){
		$accnt['KEY'] = strtotime($accnt[change_date]);
		while(count($unorderedlist[$accnt['KEY']]) != 0){
			$accnt['KEY'] += 1;
		}
		$accnt[module] = 'tr trails';
		$accnt[module_name] = 'Service Trials';
		array_push($order,$accnt['KEY']);
		$unorderedlist[$accnt['KEY']] = $accnt;
	}
	foreach($cases as $accnt){
		$accnt['KEY'] = strtotime($accnt[change_date]);
		while(count($unorderedlist[$accnt['KEY']]) != 0){
			$accnt['KEY'] += 1;
		}
		$accnt[module] = 'Cases';
		$accnt[module_name] = 'Cases';
		array_push($order,$accnt['KEY']);
		$unorderedlist[$accnt['KEY']] = $accnt;
	}*/
	
	sort($order);
	
	//combining
	$ordered_list = array();
	foreach($order as $date){
		array_push($ordered_list,$unorderedlist[$date]);
	}
	
	//Preping tabular data
	foreach($ordered_list as &$row){
		$row[user] = display_user($row[user_id]);
		$row[field] = display_label($row[field]);
	}
		
	return display_accounts_audit_report($ordered_list);
}

function display_accounts_audit_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="99%" class="sortable">
		<tr> 
		  <th>Account/Id No</th>
		  <th>Name</th>
		  <!--<th>Parent Account</th>-->
		  <th>Module</th>
		  <th>Feild</th>
		  <th>Old Value</th>
		  <th>New Value</th>
		  <th>Changed by</th>
		  <th>Change Time</th>
		</tr>'
	;
	foreach($report as $row){
		//print_r($row); echo "<br>";
		$html .= '
		<tr>
			<td class="values">'.$row[account_id].'</td>
			<td class="text_values">'.$row[name].'</td>
			<!--<td class="text_values">'.$row[parent_id].'</td>-->
			<td class="text_values">'.$row[module_name].'</td>
			<td class="text_values">'.$row[field].'</td>
			<td class="text_values">'.$row[luli].'</td>
			<td class="text_values">'.$row[kati].'</td>
			<td class="text_values">'.$row[user].'</td>
			<td class="text_values">'.$row[change_date].'</td>
		</tr>'
		;
	}
	

	$html .= '</table>';
	
	return $html;
}
?>