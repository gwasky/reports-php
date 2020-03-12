<?
function generate_all_revenue_report($from,$to,$account_id){
	
	custom_query::select_db('wimax');
	
	$billing = new wimax_billing();
	$myquery = new custom_query();
	$conditions = array();
	
	if($from){
		array_push($conditions,array('entry_date','>=',$from));
	}else{
		array_push($conditions,array('entry_date','>=',date('Y-m')."-01"));
	}
	
	if($to){
		array_push($conditions,array('entry_date','<=',$to));
	}else{
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

	$alltx = $billing->GetList($conditions);
	
	foreach($alltx as $tx){
		$entry = unserialize($tx->entry);
		if(count($report[data][$tx->parent_id]) == 0){
			$parent_data = $myquery->single("
				SELECT
				  accounts_cstm.crn_c,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.shared_packages_c as package,
				  accounts_cstm.crn_c as account_no,
				  accounts_cstm.mem_id_c as parent_no,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.download_bandwidth_c as bandwidth,
				  accounts_cstm.package_type_domain_hosting_c as domain_hosting,
				  accounts_cstm.package_domain_registration_c as domain_registration,
				  accounts_cstm.package_web_hosting_c as web_hosting,
				  accounts_cstm.package_mail_hosting_c as mail_hosting,
				  accounts_cstm.platform_c as platform,
				  accounts.name,
				  cn_contracts.`status`,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (cn_contracts.account=accounts_cstm.id_c)
				WHERE
				  accounts_cstm.crn_c = '$tx->parent_id'
			");
			if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}

			$report[data][$tx->parent_id][first_name] = $parent_data[first_name];
			$report[data][$tx->parent_id][last_name] = $parent_data[last_name];
			$report[data][$tx->parent_id][name] = $parent_data[name];
			$report[data][$tx->parent_id][account_number] = $parent_data[crn_c];
			$report[data][$tx->parent_id][platform] = $parent_data[platform];
			$report[data][$tx->parent_id][customer_type] = $parent_data[customer_type];
			$report[data][$tx->parent_id][package] = $parent_data[package];
			$report[data][$tx->parent_id][bandwidth] = $parent_data[bandwidth];
			$report[data][$tx->parent_id][DH] = $parent_data[domain_hosting];
			$report[data][$tx->parent_id][DR] = $parent_data[domain_registration];
			$report[data][$tx->parent_id][WH] = $parent_data[web_hosting];
			$report[data][$tx->parent_id][MH] = $parent_data[mail_hosting];
			$report[data][$tx->parent_id][last_name] = $parent_data[last_name];
			$report[data][$tx->parent_id][service_type] = $parent_data[service_type];
			$report[data][$tx->parent_id][account_no] = $parent_data[account_no];
			$report[data][$tx->parent_id][status] = $parent_data[status];
			$report[data][$tx->parent_id][start_date] = $parent_data[start_date];
			$report[data][$tx->parent_id][expiry_date] = $parent_data[expiry_date];
			
			$bal_array = $myquery->single("select balance from wimax_billing where id = (select max(id) from wimax_billing where parent_id = '$tx->parent_id' and entry_date < '$tx->entry_date')");
			$report[data][$tx->parent_id][opening_balance] = $bal_array[balance];
			$report[data][$tx->parent_id][balance] = $report[data][$tx->parent_id][opening_balance];
		}

		$tx->grouping = $entry[grouping];
		$tx->entry = $entry[entry];
		$rate_row = get_rate(get_rate_date($tx->entry_date,$tx->rate_date));
		$tx->rate = $rate_row[rate];
		$tx->ugx_value = ($tx->amount * $tx->rate);
		
		$report[data][$tx->parent_id][balance] += $tx->amount;

		if($tx->entry_type == 'Payment'){
			$report[data][$tx->parent_id][$tx->entry_type] += $tx->amount;
			$report[data][$tx->parent_id][$tx->entry_type.' (UGX)'] += $tx->ugx_value;
			
			$report[totals][$tx->entry_type] += $tx->amount;
			$report[totals][$tx->entry_type.' (UGX)'] += $tx->ugx_value;
		}

		if($tx->entry_type == 'Charges'){
			if($tx->entry != 'Equipment Deposit'){
				$report[data][$tx->parent_id][$tx->grouping] += $tx->amount/1.18;
				$report[data][$tx->parent_id][$tx->entry_type] += $tx->amount/1.18;
				$report[data][$tx->parent_id][$tx->entry_type.' (UGX)'] += $tx->ugx_value/1.18;
				$report[data][$tx->parent_id]['Tax exclusive Revenue'] += $tx->amount/1.18;
				$report[data][$tx->parent_id]['Tax'] += ($tx->amount - $tx->amount/1.18);
				$report[data][$tx->parent_id]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
				$report[data][$tx->parent_id]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				
				$report[totals][$tx->entry_type] += $tx->amount/1.18;
				$report[totals][$tx->entry_type.' (UGX)'] += $tx->ugx_value/1.18;
				$report[totals]['Tax exclusive Revenue'] += $tx->amount/1.18;
				$report[totals]['Tax'] += ($tx->amount - $tx->amount/1.18);
				$report[totals]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
				$report[totals]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			}else{
				$report[data][$tx->parent_id][$tx->grouping] += $tx->amount;
				$report[data][$tx->parent_id][Untaxed] += $tx->amount;
				$report[data][$tx->parent_id]['Untaxed (UGX)'] += $tx->ugx_value;
				
				$report[totals][$tx->grouping] += $tx->amount;
				$report[totals][$tx->grouping.' (UGX)'] += $tx->ugx_value;
			}
		}
	
		if($tx->entry_type == 'Services'){
			$report[data][$tx->parent_id][$tx->grouping] += $tx->amount/1.18;
			$report[data][$tx->parent_id]['Total '.$tx->entry_type] += $tx->amount/1.18;
			$report[data][$tx->parent_id]['Total '.$tx->entry_type.' (UGX)'] += $tx->ugx_value/1.18;
			$report[data][$tx->parent_id]['Tax exclusive Revenue'] += $tx->amount/1.18;
			$report[data][$tx->parent_id]['Tax'] += ($tx->amount - $tx->amount/1.18);
			$report[data][$tx->parent_id]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
			$report[data][$tx->parent_id]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			
			$report[totals][$tx->grouping] += $tx->amount/1.18;
			$report[totals]['Monthly Charges'] += $tx->amount/1.18;
			$report[totals]['Monthly Charges (UGX)'] += $tx->ugx_value/1.18;
			$report[totals]['Tax exclusive Revenue'] += $tx->amount/1.18;
			$report[totals]['Tax'] += ($tx->amount - $tx->amount/1.18);
			$report[totals]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
			$report[totals]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
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
			
			if($entry[approved_by] == "The Prorator function"){
				$prefix = "PRFX ";
				//print_r($entry); echo "<br><br>";
			}else{
				$prefix = '';
			}
			
			if(!(($tx->grouping == 'Cash Discount') || ($tx->grouping == 'Waiver on Equipment'))){
				$report[data][$tx->parent_id][$prefix.$tx->grouping.' - '.$result[grouping]] += $tx->amount/1.18;
				$report[data][$tx->parent_id][$prefix.$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value/1.18;
				$report[data][$tx->parent_id]['Tax exclusive Revenue'] += $tx->amount/1.18;
				$report[data][$tx->parent_id]['Tax'] += ($tx->amount - $tx->amount/1.18);
				$report[data][$tx->parent_id]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
				$report[data][$tx->parent_id]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
				
				$report[totals][$prefix.$tx->grouping.' - '.$result[grouping]] += $tx->amount/1.18;
				$report[totals][$prefix.$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value/1.18;
				$report[totals]['Tax exclusive Revenue'] += $tx->amount/1.18;
				$report[totals]['Tax'] += ($tx->amount - $tx->amount/1.18);
				$report[totals]['Tax exclusive Revenue (UGX)'] += $tx->ugx_value/1.18;
				$report[totals]['Tax (UGX)'] += ($tx->ugx_value - $tx->ugx_value/1.18);
			}else{
				$report[data][$tx->parent_id][$tx->grouping.' - '.$result[grouping]] += $tx->amount;
				$report[data][$tx->parent_id][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value;
				$report[data][$tx->parent_id][Untaxed] += $tx->amount;
				$report[data][$tx->parent_id]['Untaxed (UGX)'] += $tx->ugx_value;
				
				$report[totals][$tx->grouping.' - '.$result[grouping]] += $tx->amount;
				$report[totals][$tx->grouping.' - '.$result[grouping].' (UGX)'] += $tx->ugx_value;
				$report[totals][Untaxed] += $tx->amount;
				$report[totals]['Untaxed (UGX)'] += $tx->ugx_value;
			}
		}	
	}
	
	foreach($report[data] as &$row){
		
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
		if($row[opening_balance] > 0){
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
		}
	}

	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;

	return display_all_revenue_report($report);
}

function display_all_revenue_report($report){
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th></th>
			<th>Account Number</th>
			<th>Account Name</th>
			<th>Platform</th>
			<th>Customer Type</th>
			<th>Service Type</th>
			<th>Package</th>
			<th>Bandwidth</th>
			<th>Start Date</th>
			<th>Expiry Date</th>
			<th>Status</th>
			<th>Bandwidth Charges</th>
			<th>Prorated Bandwidth CN</th>
			<th>Prorated Bandwidth DN</th>
			<th>Net Bandwidth Charges</th>
			<th>Bandwidth CN</th>
			<th>Bandwidth DN</th>
			
			<th>Equipment Rental</th>
			<th>Prorated Rental CN</th>
			<th>Prorated Rental DN</th>
			<th>Net Equipment Rental</th>
			<th>Equipment Rental CN</th>
			<th>Equipment Rental DN</th>
			
			<th>Equipment Sale</th>
			<th>Connection Fees</th>
			<th>Access Point Fees</th>
			
			<th>WH pack</th>
			<th>Web H</th>
			<th>Prorated WH CN</th>
			<th>Prorated WH DN</th>
			<th>Net WH</th>
			<th>WH CN</th>
			<th>WH DN</th>
			
			<th>MH pack</th>
			<th>Mail H</th>
			<th>Prorated MH CN</th>
			<th>Prorated MH DN</th>
			<th>Net MH</th>
			<th>MH CN</th>
			<th>MH DN</th>
			
			<th>DR pack</th>
			<th>Domain R</th>
			<th>Prorated DR CN</th>
			<th>Prorated DR DN</th>
			<th>Net DR</th>
			<th>DR CN</th>
			<th>DR DN</th>
			
			<th>DH pack</th>
			<th>Domain H</th>
			<th>Prorated DH CN</th>
			<th>Prorated DH DN</th>
			<th>Net DH</th>
			<th>DH CN</th>
			<th>DH DN</th>
			
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
			<th>Realised Revenue</th>
			<th>Unealised Revenue</th>
			<th>Balance</th>			
		</tr>
	';
	foreach($report[data] as $row){
		//print_r($row); echo '<br><br>';
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$row[account_number].'</td>
				<td class="text_values">'.$row[name].'</td>
				<td class="text_values">'.$row[platform].'</td>
				<td class="text_values">'.$row[customer_type].'</td>
				<td class="text_values">'.$row[service_type].'</td>
				<td class="text_values">'.$row[package].'</td>
				<td class="text_values">'.$row[bandwidth].'</td>
				<td class="values">'.$row[start_date].'</td>
				<td class="values">'.$row[expiry_date].'</td>
				<td class="values">'.$row[status].'</td>
				
				<td class="values">'.accounts_format(-$row[Service]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-($row[Service] +
													   $row["PRFX Debit Note - Service"] +
													   $row["PRFX Credit Note - Service"]
													   )
													 ).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Service"]).'</td>
				
				<td class="values">'.accounts_format(-$row["Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Rental Fees"]).'</td>
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
				<td class="values">'.accounts_format(-$row["Web Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Web Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Web Hosting"]).'</td>
				<td class="values">'.accounts_format(-($row["Web Hosting"] +
													   $row["PRFX Debit Note - Web Hosting"] +
													   $row["PRFX Credit Note - Web Hosting"]
													   )
													).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Web Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Web Hosting"]).'</td>
				
				<td class="text_values">'.$row[MH].'</td>
				<td class="values">'.accounts_format(-$row["Mail Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Mail Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Mail Hosting"]).'</td>
				<td class="values">'.accounts_format(-($row["Mail Hosting"] +
													   $row["PRFX Debit Note - Mail Hosting"] +
													   $row["PRFX Credit Note - Mail Hosting"]
													   )
													).'</td>

				<td class="values">'.accounts_format(-$row["Credit Note - Mail Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Mail Hosting"]).'</td>
				
				<td class="text_values">'.$row[DR].'</td>
				<td class="values">'.accounts_format(-$row["Domain Registration"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Domain Registration"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Domain Registration"]).'</td>
				<td class="values">'.accounts_format(-($row["Domain Registration"] +
													   $row["PRFX Debit Note - Domain Registration"] +
													   $row["PRFX Credit Note - Domain Registration"]
													   )
													).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Domain Registration"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Domain Registration"]).'</td>
				
				<td class="text_values">'.$row[DH].'</td>
				<td class="values">'.accounts_format(-$row["Domain Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Credit Note - Domain Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["PRFX Debit Note - Domain Hosting"]).'</td>
				<td class="values">'.accounts_format(-($row["Domain Hosting"] +
													   $row["PRFX Debit Note - Domain Hosting"] +
													   $row["PRFX Credit Note - Domain Hosting"]
													   )
													).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Domain Hosting"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Domain Hosting"]).'</td>
				
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
				<td class="values">'.accounts_format($row['Realised Revenue']).'</td>
				<td class="values">'.accounts_format($row['Unrealised Revenue']).'</td>
				<td class="values" '; if($row[balance] < 0){ $html .= 'style="background-color:#FF0000; color: #FFFFFF; font-weight: bold;"';} $html .= '>'.accounts_format($row[balance]).'</td>
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