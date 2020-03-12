<?
function generate_projected_revenue_report($from,$to,$account_id, $cn_status){
	
	$billing = new wimax_billing();
	$myquery = new custom_query();

	$conditions = array(
					array('entry_type','!=','Payment'),
					array('entry_type','!=','Services')
						);
	
	if($from){
		$from = date_reformat($from,'%Y-%m-').'01';
	}else{
		$from = date('Y-m-').'01';
	}
	array_push($conditions,array('entry_date','>=',$from));
	
	if($to){
		$to = date_reformat(last_day($to),'%Y-%m-%d');
	}else{
		$to = date_reformat(last_day(date('Y-m-d')),'%Y-%m-%d');
	}
	array_push($conditions,array('entry_date','<=',$to));
	
	//Getting the service multiplier
	$month_diff_q = "select PERIOD_DIFF(date_format('$to','%Y%m'),date_format('$from','%Y%m')) + 1 as value";
	$month_diff = $myquery->single($month_diff_q);

	/*ps_products.`type` as prod_type,*/
	$acct_query = "
		SELECT
			ps_products.name as prod_name,
			ps_products.price * -$month_diff[value] as amount,
			ps_products_cstm.product_grouping_c as grouping,
			accounts_cstm.preferred_username_c,
			accounts_cstm.download_bandwidth_c as bandwidth,
			accounts_cstm.shared_packages_c as package,
			accounts_cstm.preferred_username_c,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.crn_c as account_no,
			accounts_cstm.mem_id_c as parent_no,
			accounts_cstm.customer_type_c as customer_type,
			accounts.name,
			accounts.id
		FROM
			ps_products
			INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
			INNER JOIN accounts_cstm ON (accounts_cstm.shared_packages_c=ps_products.name)
			OR (accounts_cstm.download_bandwidth_c=ps_products.name)
			INNER JOIN accounts ON (accounts.id=accounts_cstm.id_c)
		WHERE
			accounts.deleted = '0' AND
			ps_products_cstm.product_grouping_c != '' AND
			ps_products.deleted != '1'
	";

	if($account_id != ''){
		$account_range = array('parent_id','=',$account_id);
		array_push($conditions,$account_range);
		$acct_query .= "AND 
			((accounts_cstm.mem_id_c = '$account_id') OR 
			 ((accounts_cstm.mem_id_c = '') AND (accounts_cstm.crn_c = '$account_id'))
			)
		";
	}

	custom_query::select_db('wimax');
	$accts_data = $myquery->multiple($acct_query);
	if(count($accts_data) == 0) { echo "Your condition does not retreive any data <br>"; exit(); }
	
	//correcting accounts without parent ids and populating the report data phase 1 
	foreach($accts_data as $acct){
		if($acct[parent_no] == ''){
			$acct[parent_no] = $acct[account_no];
		}
		
		//Populating the report with accounts data
		if(!$phase1[data][$acct[parent_no]][account_number]){
			$phase1[data][$acct[parent_no]][name] = $acct[name];
			$phase1[data][$acct[parent_no]][account_number] = $acct[account_no];
			$phase1[data][$acct[parent_no]][customer_type] = $acct[customer_type];
			$phase1[data][$acct[parent_no]][service_type] = $acct[service_type];
			$phase1[data][$acct[parent_no]][parent_no] = $acct[parent_no];
			$phase1[data][$acct[parent_no]][id] = $acct[id];
			$phase1[data][$acct[parent_no]][bandwidth] = $acct[bandwidth];
			$phase1[data][$acct[parent_no]][package] = $acct[package];
		}
		
		$phase1[data][$acct[parent_no]][$acct[grouping]] += $acct[amount];
		$phase1[data][$acct[parent_no]]['Tax exclusive Revenue'] += $acct[amount];
		$phase1[data][$acct[parent_no]]['Tax'] += ($acct[amount] * 0.18);
		
		$phase1[totals][$acct[grouping]] += $acct[amount];
		$phase1[totals]['Tax exclusive Revenue'] += $acct[amount];
		$phase1[totals]['Tax'] += ($acct[amount] * 0.18);
	}
	$phase2[totals] = $phase1[totals];

	//Adding start and end dates and status to report data phase 2
	foreach($phase1[data] as $row){
		$contract_query = "
			SELECT 
				start_date, 
				expiry_date, 
				status 
			FROM cn_contracts 
			WHERE 
				account = '$row[id]' AND
				cn_contracts.deleted = '0'
		";
		if(($cn_status != '') && ($cn_status != 'blank')){
			$contract_query .= "
				AND status = '$cn_status'
			";
			
			$contract_data = $myquery->single($contract_query);
	
			if($contract_data[status] == $cn_status){
				foreach($contract_data as $colname=>$colvalue){
					$row[$colname] = $colvalue;
				}
				$phase2[data][$row[parent_no]] = $row;
			}
		}else{
			$contract_data = $myquery->single($contract_query);
			if($contract_data == NULL){
				$row[start_date] = 'No contract';
				$row[expiry_date] = 'No contract';
				$row[status] = 'No contract';

				$phase2[data][$row[parent_no]] = $row;
			}else{
				foreach($contract_data as $colname=>$colvalue){
					$row[$colname] = $colvalue;
				}
				if($cn_status != 'blank'){
					$phase2[data][$row[parent_no]] = $row;
				}
			}
		}
	}
	
	$report = $phase2;
	/*foreach($phase2[data] as $row){
		print_r($row); echo "<br>+++++++++++++++++++<br>";
	}*/
	
	//Adding ontime charges and Adjustments to report data phase 3
	foreach($report[data] as &$row){
		$this_conditions = $conditions;
		array_push($this_conditions,array('parent_id','=',$row[parent_no]));
		
		$txs = $billing->GetList($this_conditions);
		
		foreach($txs as $tx){
			//print_r($tx); echo "<br><br>";
			$entry = unserialize($tx->entry);
			$tx->grouping = $entry[grouping];
			$tx->entry = $entry[entry];
			$rate_row = get_rate(get_rate_date($tx->entry_date,$tx->rate_date));
			$tx->rate = $rate_row[rate];
			$tx->ugx_value = ($tx->amount * $tx->rate);
			
			$bal_array = $myquery->single("select balance from wimax_billing where id = (select max(id) from wimax_billing where parent_id = '$tx->parent_id' and entry_date < '$tx->entry_date')");
			$report[data][$row[parent_no]][opening_balance] = $bal_array[balance];
			
			//$tx->parent_id = $row[parent_no]
			
			if($tx->entry_type == 'Charges'){
				if($tx->entry != 'Equipment Deposit'){
					$report[data][$row[parent_no]][$tx->grouping] += $tx->amount/1.18;
					$report[data][$row[parent_no]][$tx->entry_type] += $tx->amount/1.18;
					$report[data][$row[parent_no]]['Tax exclusive Revenue'] += $tx->amount/1.18;
					$report[data][$row[parent_no]]['Tax'] += ($tx->amount - $tx->amount/1.18);
					/*$report[data][$row[parent_no]]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
					$report[data][$row[parent_no]]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);*/
					
					$report[totals][$tx->entry_type] += $tx->amount/1.18;
					$report[totals]['Tax exclusive Revenue'] += $tx->amount/1.18;
					$report[totals]['Tax'] += ($tx->amount - $tx->amount/1.18);
					/*$report[totals]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
					$report[totals]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);*/
				}else{
					$report[data][$row[parent_no]][$tx->grouping] += $tx->amount;
					$report[data][$row[parent_no]][Untaxed] += $tx->amount;
					/*$report[data][$row[parent_no]]['Untaxed (UGX)'] += $tx->ugx_value;*/
					
					$report[totals][$tx->grouping] += $tx->amount;
					/*$report[totals][$tx->grouping.' (UGX)'] += $tx->ugx_value;*/
				}
			}
			
			if($tx->entry_type == 'Adjustment'){
				$query = "SELECT
							 ps_products_cstm.product_grouping_c as grouping
							FROM
							 ps_products_cstm
							 INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
							WHERE ps_products.name='$tx->entry' and ps_products.deleted != 1
							";
				$result = $myquery->single($query);
				if(!(($tx->grouping == 'Cash Discount') || ($tx->grouping == 'Waiver on Equipment'))){
					$report[data][$row[parent_no]][$tx->grouping.' - '.$result[grouping]] += $tx->amount/1.18;
					$report[data][$row[parent_no]]['Tax exclusive Revenue'] += $tx->amount/1.18;
					$report[data][$row[parent_no]]['Tax'] += ($tx->amount - $tx->amount/1.18);
					/*$report[data][$row[parent_no]][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value/1.18;
					$report[data][$row[parent_no]]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
					$report[data][$row[parent_no]]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);*/
					
					$report[totals][$tx->grouping.' - '.$result[grouping]] += $tx->amount/1.18;
					$report[totals]['Tax exclusive Revenue'] += $tx->amount/1.18;
					$report[totals]['Tax'] += ($tx->amount - $tx->amount/1.18);
					/*$report[totals][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value/1.18;
					$report[totals]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
					$report[totals]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);*/
				}else{
					$report[data][$row[parent_no]][$tx->grouping.' - '.$result[grouping]] += $tx->amount;
					$report[data][$row[parent_no]][Untaxed] += $tx->amount;
					/*$report[data][$row[parent_no]][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value;
					$report[data][$row[parent_no]]['Untaxed (UGX)'] += $tx->ugx_value;*/
					
					$report[totals][$tx->grouping.' - '.$result[grouping]] += $tx->amount;
					$report[totals][Untaxed] += $tx->amount;
					/*$report[totals][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value;
					$report[totals]['Untaxed (UGX)'] += $tx->ugx_value;*/
				}
			}	
		}
	}
	
	//Getting totals
	foreach($report[data] as &$row){
		$row['Tax inclusive Revenue'] = ($row['Tax exclusive Revenue'] + $row[Tax]);
		$row['Total Account Charges'] = ($row['Tax inclusive Revenue'] + $row[Untaxed]);
		//$row['Net Account Charges'] = ($row[opening_balance] + $row['Total Account Charges'] + $row["Cash Discount -"]);
		$row['Net Account Charges'] = ($row['Total Account Charges'] + $row["Cash Discount -"]);
		
		$report[totals]['Total Projected Revenue'] += ($row['Total Account Charges']);
	}

	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;

	return display_projected_revenue_report($report);
}

function display_projected_revenue_report($report){

	//echo count($report[data]);
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th>Account Number</th>
			<th>Account Name</th>
			<th>Customer Type</th>
			<th>Service Type</th>
			<th>Package</th>
			<th>Bandwidth</th>
			<th>current Status</th>
			<th>Start Date</th>
			<th>Expiry Date</th>
			<th>Bandwidth Charges</th>
			<th>Equipment Rental</th>
			<th>Connection Fees</th>
			<th>Access Point Fees</th>
			<th>Equipment Rental CN</th>
			<th>Equipment Rental DN</th>
			<th>Bandwidth CN</th>
			<th>Bandwidth DN</th>
			<th>Equipment Sale</th>
			<th>Total Revenue Excluding Tax</th>
			<th>VAT 18%</th>
			<th>Total Revenue Including Tax</th>
			<th>Equipment Deposits</th>
			<th>Discounts on Equipment</th>
			<th>Total Untaxed</th>
			<th>Projected Revenue</th>
			<th>Cash Discounts</th>
			<!--<th>Previous Balance</th>-->
			<th>Net Projected Revenue</th>		
		</tr>
	';
	foreach($report[data] as $row){
		//print_r($row); echo '<br><br>';
		$html .= '
			<tr>
				<td class="text_values">'.$row[account_number].'</td>
				<td class="text_values">'.$row[name].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<td class="text_values">'.$row[service_type].'</td>
				<td class="text_values">'.$row[package].'</td>
				<td class="text_values">'.$row[bandwidth].'</td>
				<td class="text_values">'.$row[status].'</td>
				<td class="values">'.$row[start_date].'</td>
				<td class="values">'.$row[expiry_date].'</td>
				<td class="values">'.accounts_format(-$row[Service]).'</td>
				<td class="values">'.accounts_format(-$row["Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Connection Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Access Point Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["Equipment Sale"]).'</td>
				<td class="values">'.accounts_format(-$row["Tax exclusive Revenue"]).'</td>
				<td class="values">'.accounts_format(-$row[Tax]).'</td>
				<td class="values">'.accounts_format(-$row["Tax inclusive Revenue"]).'</td>
				<td class="values">'.accounts_format(-$row["Equipment Deposits"]).'</td>
				<td class="values">'.accounts_format(-$row["Waiver on Equipment - Equipment Deposits"]).'</td>
				<td class="values">'.accounts_format(-$row[Untaxed]).'</td>
				<td class="values">'.accounts_format(-$row["Total Account Charges"]).'</td>
				<td class="values">'.accounts_format(-$row["Cash Discount -"]).'</td>
				<!--<td class="values">'.accounts_format($row[opening_balance]).'</td>-->
				<td class="values">'.accounts_format(-$row["Net Account Charges"]).'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
		</td>
		</tr>
	
		<tr><td >&nbsp;</td></tr>
		<tr><td >
			<table width="100%">
				<tr>
		';

		foreach($report[totals] as $total){
		$html .= '
			<tr><td height="10">&nbsp;</td></tr>
			<tr><td >
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
	$html .= '</table>';
	
	return $html;
}
?>