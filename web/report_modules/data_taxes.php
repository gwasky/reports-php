<?
function generate_data_tax_report($from,$to,$account_id){
	
	custom_query::select_db('wimax');
	
	$billing = new wimax_billing();
	$myquery = new custom_query();
	$conditions = array();
	
	if($from){
		array_push($conditions,array('entry_date','>=',$from));
	}else{
		$_POST[from] = date('Y-m')."-01";
		$from = $_POST[from];
		array_push($conditions,array('entry_date','>=',$from));
	}
	
	if($to){
		array_push($conditions,array('entry_date','<=',$to));
	}else{
		$_POST[to] = date('Y-m-d');
		$to = $_POST[to];
		array_push($conditions,array('entry_date','<=',date('Y-m-d')));
	}
	
	if($account_id != ''){
		$account_range = array('parent_id','=',$account_id);
		array_push($conditions,$account_range);
	}
	
	if($product != ''){
		$product = '%'.$product.'%';
		$product_range = array('entry','LIKE',$product);
		array_push($conditions,$product_range);
	}

	$entry_range = array('entry_type','!=','Payment');
	array_push($conditions,$entry_range);

	$alltx = $billing->GetList($conditions);
	
	foreach($alltx as $tx){
		$entry = unserialize($tx->entry);
		if(count($report[data][$tx->account_id]) == 0){
			$parent_data = $myquery->single("
				SELECT
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts.name
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				WHERE
				  accounts_cstm.crn_c = '$tx->account_id'
			");
			if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}

			$report[data][$tx->account_id][name] = $parent_data[name];
			$report[data][$tx->account_id][customer_type] = $parent_data[customer_type];
			$report[data][$tx->account_id][service_type] = $parent_data[service_type];
			$report[data][$tx->account_id][account_no] = $tx->account_id;
		}

		$tx->grouping = $entry[grouping];
		$tx->entry = $entry[entry];
		$tx->parent_account_billing_currency = $entry[parent_account_billing_currency];
		
		$rate_date = get_rate_date($tx->entry_date,$tx->rate_date);
		$rate_row = get_rate($rate_date);
		$tx->rate = $rate_row[rate];
		//$tx->ugx_value = ($tx->amount * $tx->rate);
		$temp_amount = $tx->amount;
		$tx->amount = convert_value($temp_amount, $tx->parent_account_billing_currency, $rate_date, 'USD');
		$tx->ugx_value = convert_value($temp_amount, $tx->parent_account_billing_currency, $rate_date, 'UGX');

		if($tx->entry_type == 'Charges'){
			if($tx->entry != 'Equipment Deposit'){
				$report[data][$tx->account_id]['taxable charges'] += $tx->amount/1.18;
				$report[data][$tx->account_id]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
				$report[data][$tx->account_id]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
				$report[data][$tx->account_id]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				
				$report[data][$tx->account_id]['account tax'] += ($tx->amount - $tx->amount/1.18);
				$report[data][$tx->account_id]['account tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				$report[data][$tx->account_id]['account charges'] += $tx->amount;
				$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
				
				$report[totals]['taxable charges'] += $tx->amount/1.18;
				$report[totals]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
				$report[totals]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
				$report[totals]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			}else{
				$report[data][$tx->account_id]['nontaxable charges'] += $tx->amount;
				$report[data][$tx->account_id]['nontaxable charges (UGX)'] += $tx->ugx_value;
				
				//$report[data][$tx->account_id]['account charges'] += $tx->amount;
				//$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
				
				$report[totals]['nontaxable charges'] += $tx->amount;
				$report[totals]['nontaxable charges (UGX)'] += $tx->ugx_value;
			}
		}
	
		if($tx->entry_type == 'Services'){
			$report[data][$tx->account_id]['taxable charges'] += $tx->amount/1.18;
			$report[data][$tx->account_id]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
			$report[data][$tx->account_id]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
			$report[data][$tx->account_id]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			
			$report[data][$tx->account_id]['account tax'] += ($tx->amount - $tx->amount/1.18);
			$report[data][$tx->account_id]['account tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			$report[data][$tx->account_id]['account charges'] += $tx->amount;
			$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
			
			$report[totals]['taxable charges'] += $tx->amount/1.18;
			$report[totals]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
			$report[totals]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
			$report[totals]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
		}
		
		if($tx->entry_type == 'Adjustment'){
			if($entry[approved_by] == "The Prorator function"){
				$prefix = "PRFX ";
			}else{
				$prefix = '';
			}
			
			if(!(($tx->grouping == 'Cash Discount') || ($tx->grouping == 'Waiver on Equipment'))){
				if($prefix == "PRFX "){
					$report[data][$tx->account_id]['taxable charges'] += $tx->amount/1.18;
					$report[data][$tx->account_id]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
					$report[data][$tx->account_id]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
					$report[data][$tx->account_id]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
					
					$report[data][$tx->account_id]['account tax'] += ($tx->amount - $tx->amount/1.18);
					$report[data][$tx->account_id]['account tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
					$report[data][$tx->account_id]['account charges'] += $tx->amount;
					$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
					
					$report[totals]['taxable charges'] += $tx->amount/1.18;
					$report[totals]['taxable charges tax'] += ($tx->amount - $tx->amount/1.18);
					$report[totals]['taxable charges (UGX)'] += $tx->ugx_value/1.18;
					$report[totals]['taxable charges tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				}else{
					$report[data][$tx->account_id][$tx->grouping] += $tx->amount/1.18;
					$report[data][$tx->account_id][$tx->grouping.' tax'] += ($tx->amount - $tx->amount/1.18);
					$report[data][$tx->account_id][$tx->grouping.' (UGX)'] += $tx->ugx_value/1.18;
					$report[data][$tx->account_id][$tx->grouping.' tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
					
					$report[data][$tx->account_id]['account tax'] += ($tx->amount - $tx->amount/1.18);
					$report[data][$tx->account_id]['account tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
					$report[data][$tx->account_id]['account charges'] += $tx->amount;
					$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
					
					$report[totals][$tx->grouping] += $tx->amount/1.18;
					$report[totals][$tx->grouping.' tax'] += ($tx->amount - $tx->amount/1.18);
					$report[totals][$tx->grouping.' (UGX)'] += $tx->ugx_value/1.18;
					$report[totals][$tx->grouping.' tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				}
			}else{
				if($prefix == "PRFX "){
					$report[data][$tx->account_id]['nontaxable charges'] += $tx->amount;
					$report[data][$tx->account_id]['nontaxable charges (UGX)'] += $tx->amount;
					
					//$report[data][$tx->account_id]['account charges'] += $tx->amount;
					//$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
					
					$report[totals]['nontaxable charges'] += $tx->amount;
					$report[totals]['nontaxable charges (UGX)'] += $tx->ugx_value;
				}else{
					$report[data][$tx->account_id]['nontaxable adjustments'] += $tx->amount;
					$report[data][$tx->account_id]['nontaxable adjustments (UGX)'] += $tx->ugx_value;
					
					//$report[data][$tx->account_id]['account charges'] += $tx->amount;
					//$report[data][$tx->account_id]['account charges (UGX)'] += $tx->ugx_value;
					
					$report[totals]['nontaxable adjustments'] += $tx->amount;
					$report[totals]['nontaxable adjustments (UGX)'] += $tx->ugx_value;
				}
			}
		}	
	}
	
	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;

	return display_data_tax_report($report);
}

function display_data_tax_report($report){
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th rowspan="2" scope="col"></th>
				<th rowspan="2" scope="col">Account Number</th>
				<th rowspan="2" scope="col">Account Name</th>
				<th rowspan="2" scope="col">Customer Type</th>
				<th rowspan="2" scope="col">Service Type</th>
				<th colspan="2" scope="col">Deposits</th>
				<th colspan="2" scope="col">Taxable Charges</th>
				<th colspan="2" scope="col">VAT - Taxable Charges</th>
				<th colspan="2" scope="col">Deposit Adjustments</th>
				<th colspan="2" scope="col">Credit Notes</th>
				<th colspan="2" scope="col">VAT - Credit Notes</th>
				<th colspan="2" scope="col">Debit Notes</th>
				<th colspan="2" scope="col">VAT - Debit Notes</th>
				<th colspan="2" scope="col">Total Deposits</th>
				<th colspan="2" scope="col">Total VAT</th>
				<th colspan="2" scope="col">Total - Charges + VAT</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
				<th>USD</th>
				<th>UGX</th>
			</tr>
	';
	foreach($report[data] as $row){
		//print_r($row); echo '<br><br>';
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$row[account_no].'</td>
				<td class="text_values">'.$row[name].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<td class="text_values">'.$row[service_type].'</td>
				<td class="values">'.accounts_format(-$row['nontaxable charges']).'</td>
				<td class="values">'.accounts_format(-$row['nontaxable charges (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['taxable charges']).'</td>
				<td class="values">'.accounts_format(-$row['taxable charges (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['taxable charges tax']).'</td>
				<td class="values">'.accounts_format(-$row['taxable charges tax (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['nontaxable adjustments']).'</td>
				<td class="values">'.accounts_format(-$row['nontaxable adjustments (UGX)']).'</td>
				<td class="values">'.accounts_format($row['Credit Note']).'</td>
				<td class="values">'.accounts_format($row['Credit Note (UGX)']).'</td>
				<td class="values">'.accounts_format($row['Credit Note tax']).'</td>
				<td class="values">'.accounts_format($row['Credit Note tax (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['Debit Note']).'</td>
				<td class="values">'.accounts_format(-$row['Debit Note (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['Debit Note tax']).'</td>
				<td class="values">'.accounts_format(-$row['Debit Note tax (UGX)']).'</td>
				<td class="values">'.accounts_format(-($row['nontaxable charges'] + $row['nontaxable adjustments'])).'</td>
				<td class="values">'.accounts_format(-($row['nontaxable charges (UGX)'] + $row['nontaxable adjustments (UGX)'])).'</td>
				<td class="values">'.accounts_format(-$row['account tax']).'</td>
				<td class="values">'.accounts_format(-$row['account tax (UGX)']).'</td>
				<td class="values">'.accounts_format(-$row['account charges']).'</td>
				<td class="values">'.accounts_format(-$row['account charges (UGX)']).'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
		</td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		<tr><td>
			<table width="100%">
		';

		foreach($report[totals] as $total){
			$html .= '
				<tr><td height="10">&nbsp;</td></tr>
				<tr>
					<td>
					<table width="100%" border="0" cellpadding="2" cellspacing="0"> 
					<tr> 
			';
				foreach($total as $title=>$value){
					$html .= '<th>'.ucwords($title).'</th>';
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
					</td>
				</tr>
			';
		}
		
	$html .= '
			</table>
		</td></tr>
	</table>
	';
	
	return $html;
}
?>