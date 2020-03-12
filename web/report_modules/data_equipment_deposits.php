<?
function generate_equipment_deposits_report($from,$to,$account_ids,$report_type){
	
	function summarise($details){
		foreach($details as $row){
			$summary[$row[parent_id]][id] = $row[id];
			$summary[$row[parent_id]][parent_id] = $row[parent_id];
			$summary[$row[parent_id]][account_id] = $row[account_id];
			$summary[$row[parent_id]][account_name] = $row[account_name];
			$summary[$row[parent_id]][bandwidth_status] = $row[bandwidth_status];
			$summary[$row[parent_id]][amount] += $row[amount];
			$summary[$row[parent_id]][balance] = $row[balance];
		}
		
		return $summary;
	}
	
	custom_query::select_db('wimax');

	$myquery = new custom_query();

	if(trim($account_ids) != ''){
		$account_ids = explode($account_ids,',');
	}

	if(!$to){
		$to = date('Y-m-d');
		$_POST[to] = $to;
	}
	
	if(!$from){
		$from = substr($to,0,7)."-01";
		$_POST[from] = $from;
	}
	
	if((count($account_ids) > 0) and is_array($account_ids)){
		$account_id_query .= " AND main_billing.account_id in (";
		foreach($account_ids as $account_id){
			$account_id_query .= "'".$account_id."'";
			if(++$i < count($account_ids)){
				$account_id_query .= ",";
			}
		}
		$account_id_query .= ")";
	}
	
	$query = "
		SELECT
			wimax_billing.parent_id,
			wimax_billing.account_id,
			cn_contracts.status as bandwidth_status,
			accounts.name as account_name,
			accounts.id,
			wimax_billing.entry_date,
			wimax_billing.id as entry_id,
			wimax_billing.entry_type,
			wimax_billing.entry,
			-wimax_billing.amount as amount,
			-wimax_billing.balance as balance
		FROM
			wimax_billing
			INNER JOIN accounts_cstm ON wimax_billing.account_id = accounts_cstm.crn_c
			INNER JOIN accounts ON accounts.id = accounts_cstm.id_c
			INNER JOIN cn_contracts ON cn_contracts.account = accounts.id
		WHERE
			wimax_billing.entry like '%equipment deposit%' AND
			wimax_billing.entry_date between '".$from."' AND '".$to."'
			".$account_id_query."
	";
	
	//echo nl2br($query);
	
	$list = $myquery->multiple($query);

	switch($report_type){
		case 'detail':
			$report[detail] = $list;
			break;
		case 'both':
			$report[detail] = $list;
			$report[summary] = summarise($list);
			break;
		case 'summary':
		default:
			$report[summary] = summarise($list);
			break;
	}
	
	//print_r($list);
	
	return display_equipment_deposits_report($report);
}

function display_equipment_deposits_report($report){
	
	function explain_array($source,$excluded_keys,$element_delimiter = '<br>', $show_key = TRUE, $key_value_delimiter=' = '){
		if(!is_array($excluded_keys)) { $excluded_keys = array(); }
		if(is_array($source)){
			foreach($source as $key=>$value){
				if(!in_array($key,$excluded_keys) and trim($value) != ''){
					$explanation .= $key.$key_value_delimiter;
					if(is_array($value)){
						$explanation .= explain_array($value);
					}else{
						$explanation .= $value;
					}
					$explanation .= $element_delimiter;
				}
			}
		}
		
		return $explanation;
	}
	
	if($report[NO_DATA] == TRUE) { return "NO DATA" ;}
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
	';
	
	if($report[summary]){
		$html .= '
			<tr><th>SUMMARY</th></tr>
			<tr><td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
				<tr> 
				  <th></th>
				  <th>Parent Account</th>
				  <th>Account Number</th>
				  <th>Account Name</th>
				  <th>Account status - Bandwidth</th>
				  <th>Equipment Deposit</th>
				  <th>Closing balance</th>
				</tr>
		';
		foreach($report[summary] as $row){
			//print_r($row); echo "<br>";
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[parent_id].'" target="_blank">'.$row[parent_id].'</a></td>
					<td class="values">'.$row[account_id].'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.strtoupper($row[account_name]).'</a></td>
					<td class="text_values">'.$row[bandwidth_status].'</td>
					<td class="values">'.number_format($row[amount],2).'</td>
					<td class="'; if($row[balance] > 0){$html .= 'red_';} $html .= 'values">'.number_format($row[balance],2).'</td>
				</tr>
			';
		}
	
		$html .= '
			</table>
			</td></tr>
		';
	}
	
	if(($report[detail]) and ($report[summary])){
		$html .= '
			<tr><td style="height:15px;"></td></tr>
		';
	}
	
	if($report[detail]){
		$html .= '
			<tr><th>DETAIL</th></tr>
			<tr><td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
				<tr> 
				  <th></th>
				  <th>Date</th>
				  <th>Parent Account</th>
				  <th>Account Number</th>
				  <th>Account Name</th>
				  <th>Account status - Bandwidth</th>
				  <th>Entry Type</th>
				  <th>Entry Details</th>
				  <th>Amount</th>
				  <th>End balance</th>
				</tr>
		';
		
		foreach($report[detail] as $row){
			$row[entry] = unserialize($row[entry]);
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/cst_transaction.php?action=print&id='.$row[entry_id].'&title='.$row[entry_type].'" target="_blank">'.$row[entry_date].'</a></td>
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[parent_id].'" target="_blank">'.$row[parent_id].'</a></td>
					<td class="values">'.$row[account_id].'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.strtoupper($row[account_name]).'</a></td>
					<td class="text_values">'.$row[bandwidth_status].'</td>
					<td class="text_values">'.$row[entry_type].'</td>
					<td class="text_values">'.explain_array($row[entry],$excluded_keys = array('entry','approved_by','parent_account_billing_currency')).'</td>
					<td class="values">'.number_format($row[amount],2).'</td>
					<td class="'; if($row[balance] > 0){$html .= 'red_';} $html .= 'values">'.number_format($row[balance],2).'</td>
				</tr>
			';
		}
	
		$html .= '
			</table>
			</td></tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>