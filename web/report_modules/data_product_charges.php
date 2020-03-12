<?
function generate_prod_charges_report($from, $to, $products){
	
	custom_query::select_db('wimax');

	$billing = new wimax_billing();
	$myquery = new custom_query();

	$conditions = array(array('entry_type','!=','Payment'));
	
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
	
	if(is_array($products) and !in_array('',$products)){
		array_push($conditions,array('AND'));
		array_push($conditions,array('('));
		foreach($products as $key=>$product){
			$product_range = array('entry','LIKE','%'.$product.'%');
			array_push($conditions,$product_range);
			
			if($key+1 < count($products)) array_push($conditions,array('OR'));
		}
		array_push($conditions,array(')'));
	}
	
	$charges = $billing->GetList($conditions,'entry_date',true,'');
	
	//echo $billing->pog_query."<hr>"; exit();
	
	foreach($charges as $charge){
		$entry = unserialize($charge->entry);
		$parent_data = $myquery->single("
					SELECT 
					  accounts.name,
					  accounts_cstm.service_type_internet_c as service_type,
					  accounts_cstm.crn_c as account_no
					FROM
					 accounts
					 INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
					WHERE
					  accounts_cstm.preferred_username_c = '".$charge->username."'
					");
		if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}
		
		$charge->first_name = $parent_data[first_name];
		$charge->last_name = $parent_data[last_name];
		$charge->name = $parent_data[name];
		$charge->last_name = $parent_data[last_name];
		$charge->service_type = $parent_data[service_type];
		$charge->account_no = $parent_data[account_no];
		$charge->grouping = $entry[grouping];
		$charge->entry = $entry[entry];
		$rate_row = get_rate(get_rate_date($charge->entry_date,$charge->rate_date));
		$charge->rate = $rate_row[rate];
		$charge->ugx_value = ($charge->amount * $charge->rate);
		
		if(count($report[data][$charge->entry]) == 0){
			$report[data][$charge->entry][name] = $charge->entry;
		}
		$report[data][$charge->entry]['Number of transactions'] += 1;
		if(($charge->entry_type == 'Charges')||($charge->entry_type == 'Services')){
			$report[data][$charge->entry][Charges] += $charge->amount;
			$report[totals]['Total Charges in USD'] += $charge->amount;
			$report[totals]['Total Charges in UGX'] += $charge->ugx_value;
		}else{
			$report[data][$charge->entry][$charge->grouping] += $charge->amount;
			$report[totals]['Total '.$charge->grouping.' in USD'] += $charge->amount;
			$report[totals]['Total '.$charge->grouping.' in UGX'] += $charge->ugx_value;
		}
		$report[data][$charge->entry]['Total USD'] += $charge->amount;
		$report[data][$charge->entry]['Total UGX'] += $charge->ugx_value;
		$report[data][$charge->entry][avg_charges_USD] = $report[data][$charge->entry]['Total USD']/$report[data][$charge->entry]['Number of transactions'];
		$report[data][$charge->entry][avg_charges_UGX] = $report[data][$charge->entry]['Total UGX']/$report[data][$charge->entry]['Number of transactions'];
	}
	
	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;
	
	/*foreach($report[data] as $row){
		print_r($row); echo "<br><br>";
	}*/

	return display_prod_charges_report($report);
}

function display_prod_charges_report($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
			<td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr> 
			  <th></th>
			  <th>Product Name</th>
			  <th>Number of Transactions</th>
			  <th>Gross Charges</th>
			  <th>Total Debit Notes</th>
			  <th>Total Credit Notes</th>
			  <th>Total Waivers</th>
			  <th>Total Cash Discounts</th>
			  <th>Total Waivers on Equipment</th>
			  <th>Total Other Adjustments</th>
			  <th>Average Charge(USD)</th>
			  <th>Net Charges in USD</th>
			  <th>Average Charge(UGX)</th>
			  <th>Net Charges in UGX</th>
			</tr>
	';
	foreach($report[data] as $row){
		$html .= '
		<tr>
			<td class="text_values">'.++$i.'</td>
			<td class="text_values">'.$row[name].'</td>
			<td class="values">'.$row["Number of transactions"].'</td>
			<td class="values">'.accounts_format(-$row[Charges]).'</td>
			<td class="values">'.accounts_format(-$row["Debit Note"]).'</td>
			<td class="values">'.accounts_format(-$row["Credit Note"]).'</td>
			<td class="values">'.accounts_format(-$row["Waiver"]).'</td>
			<td class="values">'.accounts_format(-$row["Cash Discount"]).'</td>
			<td class="values">'.accounts_format(-$row["Waiver on Equipment"]).'</td>
			<td class="values">'.accounts_format(-$row[Other]).'</td>
			<td class="values">'.accounts_format(-$row[avg_charges_USD]).'</td>
			<td class="values">'.accounts_format(-$row['Total USD']).'</td>
			<td class="values">'.accounts_format(-$row[avg_charges_UGX]).'</td>
			<td class="values">'.accounts_format(-$row['Total UGX']).'</td>
		</tr>
		';
	}
	
	$html .= '
		</table>
		</td>
		</tr>
		';
	
	foreach($report[totals] as $total){
		$html .= '
			<tr><td colspan="13" height="15">&nbsp;</td></tr>
			<tr><td colspan="13">
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
			$html .= '<td class="values">'.accounts_format(-$values).'</td>';
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