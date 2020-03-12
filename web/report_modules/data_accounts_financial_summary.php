<?
function generate_accounts_financial_summary($upto, $account_id, $cn_status){
	$report[start] = date('Y-m-d H:i:s');
	custom_query::select_db('wimax');
	
	$billing = new wimax_billing();
	$myquery = new custom_query();
	
	$conditions = array();
	
	$upto = ($upto == '')?date('Y-m-d'):$upto;
	array_push($conditions,array('entry_date','<=',$upto));
	$_POST[to] = $upto;
	
	if($account_id != ''){
		$account_range = array('parent_id','=',$account_id);
		array_push($conditions,$account_range);
	}
	
	$alltx = $billing->GetList($conditions);
	
	foreach($alltx as $tx){
		//START CLEAN IDS
		$tx->parent_id = trim($tx->parent_id);
		$tx->account_id = trim($tx->account_id);
		//END CLEAN IDS
		
		$entry = unserialize($tx->entry);
		$entry[grouping] = ($entry[grouping])?$entry[grouping]:'UN SPECIFIED';
		if(count($report[data][$tx->parent_id]) == 0){
			$accnt_data_query = "
				SELECT 
					accounts_cstm.service_type_internet_c as service_type,
					TRIM(accounts_cstm.crn_c) as account_no,
					TRIM(accounts_cstm.mem_id_c) as parent_no,
					accounts_cstm.download_bandwidth_c as bandwidth,
					accounts.name,
					accounts.id,
					cn_contracts.`status`
				FROM
					accounts
					INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
					INNER JOIN cn_contracts ON (cn_contracts.account=accounts_cstm.id_c)
				WHERE
					TRIM(accounts_cstm.crn_c) = '".$tx->parent_id."' and
					cn_contracts.deleted = 0
			";
			$parent_data = $myquery->single($accnt_data_query);
			if($parent_data[account_no] != $tx->parent_id){
				echo "Query : [".$accnt_data_query."] HAS NO DATA.<BR>".PrintR($parent_data);
			}
			if($parent_data[service_type] == ''){
				$parent_data[service_type] = 'No Contract';
				$parent_data[name] = $tx->username;
				$parent_data[bandwidth] = 'No Contract Data';
				$parent_data[account_no] = $tx->account_id;
				$parent_data[status] = 'No Contract';
				//echo $accnt_data_query."<br>";
			}
			$report[data][$tx->parent_id][name] = $parent_data[name];
			$report[data][$tx->parent_id][bandwidth] = $parent_data[bandwidth];
			$report[data][$tx->parent_id][service_type] = $parent_data[service_type];
			$report[data][$tx->parent_id][account_no] = $parent_data[account_no];
			$report[data][$tx->parent_id][crm_id] = $parent_data[id];
			$report[data][$tx->parent_id][status] = $parent_data[status];
			
		}
		
		$tx->grouping = $entry[grouping];
		$tx->entry = $entry[entry];
		
		$rate_row = get_rate(get_rate_date($tx->entry_date,$tx->rate_date));
		$tx->rate = $rate_row[rate];
		$tx->ugx_value = ($tx->amount * $tx->rate);
		
		$report[data][$tx->parent_id][balance] = $tx->balance;
		$report[data][$tx->parent_id][$tx->entry_type] += $tx->amount;
		$report[data][$tx->parent_id][$tx->entry_type.' in UGX'] += $tx->ugx_value;
		$report[totals]['NET Balance in US$'] += $tx->amount;
		$report[totals]['NET Balance in UGX'] += $tx->ugx_value;
		$report[totals][$tx->entry_type.' in US$'] += $tx->amount;
		$report[totals][$tx->entry_type.' in UGX'] += $tx->ugx_value;
		
		if($tx->entry_type == 'Adjustment'){
			$report[data][$tx->parent_id][$tx->grouping] += $tx->amount;
			$report[totals]['Adjustment ['.$tx->grouping.'] in US$'] += $tx->amount;
			$report[totals]['Adjustment ['.$tx->grouping.'] in UGX'] += $tx->ugx_value;
		}
		
		$report[data][$tx->parent_id]['Total Charges'] = $report[data][$tx->parent_id][Charges] + $report[data][$tx->parent_id][Services];
		$report[data][$tx->parent_id][Debits] = $report[data][$tx->parent_id]['Total Charges'] + $report[data][$tx->parent_id][Adjustment];
		
		$report[data][$tx->parent_id]['Debits in UGX'] = $report[data][$tx->parent_id][Charges.' in UGX'] + $report[data][$tx->parent_id][Services.' in UGX'] + $report[data][$tx->parent_id][Adjustment.' in UGX'];
		
		if($report[data][$tx->parent_id][Payment] > abs($report[data][$tx->parent_id][Debits])){
			$report[data][$tx->parent_id]['Realised Rev'] = abs($report[data][$tx->parent_id][Debits]);
			$report[data][$tx->parent_id]['Realised Rev (UGX)'] = abs($report[data][$tx->parent_id]['Debits in UGX']);
			
			$report[data][$tx->parent_id]['Unrealised Rev'] = $report[data][$tx->parent_id][Payment] - abs($report[data][$tx->parent_id][Debits]);
			$report[data][$tx->parent_id]['Unrealised Rev (UGX)'] = $report[data][$tx->parent_id][Payment.' in UGX'] - abs($report[data][$tx->parent_id]['Debits in UGX']);
		}else{
			$report[data][$tx->parent_id]['Realised Rev'] = $report[data][$tx->parent_id][Payment];
			$report[data][$tx->parent_id]['Realised Rev (UGX)'] = $report[data][$tx->parent_id][Payment.' in UGX'];
			
			$report[data][$tx->parent_id]['Unrealised Rev'] = 0;
			$report[data][$tx->parent_id]['Unrealised Rev (UGX)'] = 0;
		}
		
		if($report[data][$tx->parent_id][b_count] != TRUE){
			$report[totals]['Count of '.$parent_data[bandwidth].' clients'] += 1;
		}
		$report[data][$tx->parent_id][b_count] = TRUE;
	}
	
	if($cn_status){
		foreach($report[data] as $key=>$row){
			if($row[status]!=$cn_status){
				unset($report[data][$key]);
			}
		}
	}
	
	foreach($report[data] as $row){
		$report[totals]['Total Realised Revenue US$'] += $row['Realised Rev'];
		$report[totals]['Total Realised Revenue UGX'] += $row['Realised Rev (UGX)'];

		$report[totals]['Total Unrealised Revenue US$'] += $row['Unrealised Rev'];
		$report[totals]['Total Unrealised Revenue UGX'] += $row['Unrealised Rev (UGX)'];
	}
	
	//SORT TOTALS BY KEY ASC
	ksort($report[totals]);
	
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - strtotime($report[start]);
	return display_accounts_financial_summary($report);
}

function display_accounts_financial_summary($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr><td> Report took '.sec_to_time($report[duration]).' to run</td></tr>
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr> 
			  <th></th>
			  <th width="55">Account Number</th>
			  <th>Account Name</th>
			  <th>Bandwidth</th>
			  <th width="45">Service Type</th>
			  <th width="40">Status</th>
			  <th width="50">Payments</th>
			  <th width="50">One time Charges</th>
			  <th width="55">Service Charges</th>
			  <th width="55">Total Charges</th>
			  <th width="50">Credit Notes</th>
			  <th width="50">Debit Notes</th>
  			  <th width="60">Adjustments</th>
			  <th width="60">Net Charges</th>
			  <th width="50">Realised Revenue</th>
			  <th width="50">Unrealised Revenue</th>
			  <th width="60">Acct Balance</th>
			</tr>
	';
	foreach($report[data] as $row){
		$html .= '
		<tr>
			<td class="values">'.++$i.'</td>
			<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[account_no].'" target="_blank">'.$row[account_no].'</a></td>
			<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[crm_id].'" target="_blank">'.$row[name].'</a></td>
			<td class="text_values">'.$row[bandwidth].'</td>
			<td class="text_values">'.$row[service_type].'</td>
			<td class="text_values">'.$row[status].'</td>
			<td class="values">'.number_format($row[Payment],2).'</td>
			<td class="values">'.number_format($row[Charges],2).'</td>
			<td class="values">'.number_format($row[Services],2).'</td>
			<td class="values">'.number_format($row['Total Charges'],2).'</td>
			<td class="values">'.number_format($row['Credit Note'],2).'</td>
			<td class="values">'.number_format($row['Debit Note'],2).'</td>
			<td class="values">'.number_format($row[Adjustment],2).'</td>
			<td class="values">'.number_format($row[Debits],2).'</td>
			<td class="values">'.number_format($row['Realised Rev'],2).'</td>
			<td class="values">'.number_format($row['Unrealised Rev'],2).'</td>
			<td class="values" '; if($row[balance] < 0){ $html .= 'style="background-color:#FF0000; color: #FFFFFF; font-weight: bold;"';} $html .= '>'.number_format($row[balance],2).'</td>
		</tr>
		';
	}
	
	$html .= '
		</table>
		</td>
		</tr>
		<tr><td height="15">&nbsp;</td></tr>
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="2" cellspacing="0" class="sortable"> 
					<tr>
						<th>Parameter</th>
						<th>Value</th>
					</tr>
				';
				
				foreach($report[totals] as $title=>$value){
					$html .= '
					<tr>
						<td class="text_values">'.$title.'</td>
						<td class="values">'.number_format($value,2).'</td>
					</tr>
					';
				}
				
				$html .= '
				</table>
			</td>
		</tr>
		';
	
	$html .= '
	</table>
	';
	
	return $html;
}
?>