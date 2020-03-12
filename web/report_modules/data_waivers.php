<?
function generate_waiver_report($from, $to, $account_id){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			wimax_billing.id,
			accounts.name,
			wimax_billing.entry_id,
			wimax_billing.account_id,
			wimax_billing.parent_id,
			wimax_billing.`user`,
			wimax_billing.amount,
			wimax_billing.currency,
			wimax_billing.entry,
			wimax_billing.entry_type,
			wimax_billing.rate_date,
			wimax_billing.entry_date,
			wimax_billing.matched_invoice
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			INNER JOIN wimax_billing ON (accounts_cstm.crn_c=wimax_billing.account_id)
		where
			wimax_billing.entry like '%Waiver%'
	";
	
	if($account_id){
		$query .= " and wimax_billing.account_id = '$account_id'";
	}
	
	if($from){
		$query .= " AND wimax_billing.entry_date >= '".$from."' ";
	}else{
		$from = date('Y-m').'-01';
		$query .= " AND wimax_billing.entry_date >= '".$from."' ";
	}
	
	if($to){
		$query .= " AND wimax_billing.entry_date <= '".$to."' ";
	}else{
		$to = date('Y-m-d');
		$query .= " AND entry_date <= '".$to."' ";
	}
	
	$waivers = $myquery->multiple($query);
	
	//echo $query." ==>> ".count($waivers)."<br>";
	
	foreach($waivers as $waiver){
		$entry = unserialize($waiver[entry]);
		$query = "
			SELECT 
				accounts.name,
				accounts_cstm.service_type_internet_c as service_type,
				accounts_cstm.sales_rep_c as sales_rep
			FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
			WHERE
				accounts_cstm.crn_c = '$waiver[account_id]'
		";
		$parent_data = $myquery->single($query);
		if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}
		
		$waiver[name] = $parent_data[name];
		$waiver[service_type] = $parent_data[service_type];
		$waiver[customer_type] = $parent_data[customer_type];
		$waiver[account_no] = $waiver[account_id];
		$waiver[sales_rep] = $parent_data[sales_rep];
		$waiver[grouping] = $entry[grouping];
		$waiver[approved_by] = $entry[approved_by];
		
		$waiver[details] = $entry[details];
		$waiver[grouping] = $entry[grouping];
		$waiver[entry] = $entry[entry];
		
		$charge_query = "
			SELECT
				sum(amount) as charge
			FROM
				wimax_billing
			where
				wimax_billing.account_id = '".$waiver[account_no]."' and
				wimax_billing.entry_type in ('Services','Charges') and
				wimax_billing.entry like '%".$waiver[entry]."%' and
				date_format(wimax_billing.entry_date, '%Y%m') =  date_format('".$waiver[entry_date]."', '%Y%m')
			group by
				wimax_billing.account_id
		";
		$charge_data = $myquery->single($charge_query);
		$waiver[charge] = $charge_data[charge];
		
		if($waiver[grouping] == 'Waiver'){
			//Remove VAT
			$waiver[amount]/=1.18;
			$waiver[charge]/=1.18;
		}else{
			//No need to Remove VAT
		}
		
		$waiver[amount_payable] = -($waiver[charge] + $waiver[amount]);
		
		//echo $charge_query." => count is ".count($charge_data)."<br>";
		
		$rate_row = get_rate(get_rate_date($waiver[entry_date],$waiver[rate_date]));
		$waiver[rate] = $rate_row[rate];
		$waiver[ugx_value] = $waiver[amount] * $waiver[rate];
		if($waiver[currency] == 'UGX'){
			$waiver[currency_value] = $waiver[ugx_value];
		}else{
			$waiver[currency_value] = $waiver[amount];
		}
		
		$report[data][$waiver[id]] = $waiver;
		$report[totals][$waiver[currency].' '.$waiver->grouping.' (Entered by '.$waiver[user].')'] += $waiver[currency_value]; 
		$report[totals][$waiver[service_type].' waivers in US$'] += $waiver[amount];
		$report[totals][$waiver[customer_type].' waivers in US$'] += $waiver[amount];
		$report[totals][$waiver[customer_type].' waivers in UGX'] += $waiver[ugx_value];
		$report[totals][$waiver[grouping].' in '.$waiver[currency]] += $waiver[currency_value];
		$report[totals]["Value of All waivers in US$"] += $waiver[amount];
		$report[totals]["Value of All waivers in UGX"] += $waiver[ugx_value];
		$report[totals]['Total '.$waiver->currency] += $waiver[currency_value];
		$report[totals][$waiver[sales_rep].' Sales (UGX)'] += $waiver[ugx_value];
	}
	
	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 10){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;
	
	return display_waivers_report($report);
}

function display_waivers_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0"> 
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
		  <th></th>
		  <th width="50">Receipt Number</th>
		  <th>Entry Date</th>
		  <th>Account Number</th>
		  <th>Account Name</th>
		  <th>Service Type</th>
		  <th>Waiver Category</th>
		  <th>Product Waived</th>
		  <th>Entry Details</th>
		  <th>Month\'s Charge</th>
		  <th>Amount Waived</th>
		  <th>Amount Payable</th>
		  <th>Entered by</th>
		  <th>Approved by</th>
		  <th>Sales Rep</th>
		</tr>
	';
	foreach($report[data] as $row){
		//print_r($row); echo "<br>";
		$html .= '
		<tr>
			<td class="values">'.++$i.'</td>
			<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/cst_transaction.php?action=print&id='.$row[id].'&title=Adjustment" target="_blank">'.$row[id].'</a></td>
			<td class="values">'.$row[entry_date].'</td>
			<td class="values">'.$row[account_no].'</td>
			<td class="text_values">'.$row[name].'</td>
			<td class="text_values">'.$row[service_type].'</td>
			<td class="text_values">'.$row[grouping].'</td>
			<td class="text_values">'.$row[entry].'</td>
			<td class="wrap_text">'.$row[details].'</td>
			<td class="values">'.round(-$row[charge],2).'</td>
			<td class="values">'.round($row[currency_value],2).'</td>
			<td class="values">'.round($row[amount_payable],2).'</td>
			<td class="text_values">'.$row[user].'</td>
			<td class="text_values">'.$row[approved_by].'</td>
			<td class="text_values">'.$row[sales_rep].'</td>
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
			<tr><td colspan="12" height="10">&nbsp;</td></tr>
			<tr><td colspan="12">
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