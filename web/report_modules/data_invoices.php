<?
function generate_invoice_report($from, $to){
	
	custom_query::select_db('wimax');
	
	$invoicing = new wimax_invoicing();
	$myquery = new custom_query();

	$conditions = array();
	
	array_push($conditions,array('deleted','=','0'));
	
	if($from == ''){
		$from = substr(date('Y-m-d', strtotime("-1 month")),0,8)."01";
	}
	$_POST[from] = $from;
	array_push($conditions,array('billing_date','>=',$from));
	
	if($to == ''){
		$to = date('Y-m-d');
	}
	$_POST[to] = $to;
	array_push($conditions,array('billing_date','<=',$to));
	
	$invoices = $invoicing->GetList($conditions);
	
	if($invoices){foreach($invoices as $invoice){
		$invoice->details = unserialize($invoice->details);
		$report[data][$invoice->id][account_number] = $invoice->details[Other_details][account_number];
		$report[data][$invoice->id][account_name] = $invoice->details[Other_details][account_name];
		$report[data][$invoice->id][invoice_no] = $invoice->invoice_number;
		$report[data][$invoice->id][service_type] = $invoice->details[Other_details][service_type];
		$report[data][$invoice->id][billing_date] = $invoice->billing_date;
		$report[data][$invoice->id][period] = month_reformat($invoice->billing_date);
		$report[data][$invoice->id][currency] = 'USD';
			
		//print_r($invoice->xtra); echo "<br><br>";
		foreach($invoice->details['Break Down'][items] as $item){
			//For old invoices
			if(($item[grouping] == 'Credit Note')||($item[grouping] == 'Debit Note')){
				$query = "SELECT
							 ps_products_cstm.product_grouping_c as grouping
							FROM
							 ps_products_cstm
							 INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
							WHERE ps_products.name='$item[item]' and ps_products.deleted != 1
							";
				$result = $myquery->single($query);
				//if($result[grouping] == ''){ echo $invoice->id." -> ".$query."<br>";}
				$item[grouping] .= " - ".$result[grouping];
			}elseif($item[grouping] == 'Service'){
				$report[data][$invoice->id][bandwidth_package] = $item[item];
			}
			$report[data][$invoice->id][$item[grouping]] += $item[value];
			$report[data][$invoice->id][Tax] += ($item[value] * 0.18);
			$report[data][$invoice->id]["Revenue (Tax exclusive)"] += $item[value];
			$report[data][$invoice->id]["Revenue (Tax Inclusive)"] += ($item[value] + ($item[value] * 0.18));
			
			$report[totals][$item[grouping]] += $item[value];
			$report[totals][Tax] += ($item[value] * 0.18);
			$report[totals]["Revenue (Tax exclusive)"] += $item[value];
			$report[totals]["Revenue (Tax Inclusive)"] += ($item[value] + ($item[value] * 0.18));
			//print_r($item); echo "<br>";
		}

		foreach($invoice->details['Break Down'][adjustments] as $item){
			if(($item[grouping] == 'Credit Note')||($item[grouping] == 'Debit Note')){
				$query = "SELECT
							 ps_products_cstm.product_grouping_c as grouping
							FROM
							 ps_products_cstm
							 INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
							WHERE ps_products.name='$item[item]' and ps_products.deleted != 1
							";
				$result = $myquery->single($query);
				//if($result[grouping] == ''){ echo $invoice->id." -> ".$query."<br>";}
				$item[grouping] .= " - ".$result[grouping];
			}
			$report[data][$invoice->id][$item[grouping]] += $item[value];
			$report[data][$invoice->id][Tax] += ($item[value] * 0.18);
			$report[data][$invoice->id]["Revenue (Tax exclusive)"] += $item[value];
			$report[data][$invoice->id]["Revenue (Tax Inclusive)"] += ($item[value] + ($item[value] * 0.18));
			
			$report[totals][$item[grouping]] += $item[value];
			$report[totals][Tax] += ($item[value] * 0.18);
			$report[totals]["Revenue (Tax exclusive)"] += $item[value];
			$report[totals]["Revenue (Tax Inclusive)"] += ($item[value] + ($item[value] * 0.18));
			//print_r($item); echo "<br>";
		}

		foreach($invoice->details['Break Down'][untaxed][items] as $item){
			$report[data][$invoice->id][$item[grouping]] += $item[value];
			$report[totals][$item[grouping]] += $item[value];
			$report[data][$invoice->id][tot_untxd] += $item[value];
			//print_r($item); echo "<br><br>";
		}
		
		$report[data][$invoice->id][tot_inv_val] = $report[data][$invoice->id]["Revenue (Tax Inclusive)"] + $report[data][$invoice->id][tot_untxd];
		if(($report[data][$invoice->id]["Revenue (Tax Inclusive)"] + $report[data][$invoice->id][tot_untxd]) < 0){
			$report[totals]["Sum of all Invoice Values"] += ($report[data][$invoice->id]["Revenue (Tax Inclusive)"] + $report[data][$invoice->id][tot_untxd]);
		}
	}}else{
		echo "No data queried. Count is ".count($invoices)."<br>";
	}
	
	foreach($report[data] as &$data_row){
		$net_charge = abs($data_row[Service] + $data_row["Debit Note - Service"] + $row["Credit Note - Service"]);
		if(($data_row[Service] != 0) || ($data_row[Service] != '')){
			$daily_cost = abs($data_row[Service]/month_days($data_row[billing_date]));
			$data_row[active_days] = accounts_format($net_charge/$daily_cost);
		}else{
			$data_row[active_days] = 0;
		}
		if($data_row[active_days] > month_days($data_row[billing_date])){
			$over_style = 'style="background-color:#FF0000; color: #FFFFFF; font-weight: bold; display:block; width: 100%;"';
			$under_style = 'style="background-color:#00C; color: #FFFFFF; font-weight: bold; display:block; width: 100%;"';
			$data_row[active_days] = '<span '.$over_style.'>'.$data_row[active_days].'</span>';
		}elseif($data_row[active_days] < month_days($data_row[billing_date])){
			$data_row[active_days] = '<span '.$under_style.'>'.$data_row[active_days].'</span>';
		}else{
			//Do nothing
		}
	}
	
	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;
	
	return display_invoice_report($report);
}

function display_invoice_report($report){
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th></th>
			<th>Invoice Number</th>
			<th>Account Number</th>
			<th>Account</th>
			<th>Period</th>
			<th>Pre/Post paid</th>
			<th>Bandwidth</th>
			<th>Bandwidth Charges</th>
			<th>Active Days</th>
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
			<th>Total Invoice Value</th>
		</tr>
	';
	foreach($report[data] as $id=>$row){
		//print_r($row); echo '<br><br>';
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/print_invoice.php?id='.$id.'" target="_blank" >'.$row[invoice_no].'</a></td>
				<td class="text_values">'.$row[account_number].'</td>				
				<td class="text_values">'.$row[account_name].'</td>
				<td class="values">'.$row[period].'</td>
				<td class="text_values">'.$row[service_type].'</td>
				<td class="text_values">'.$row[bandwidth_package].'</td>
				<td class="values">'.accounts_format(-$row[Service]).'</td>
				<td class="values">'.$row[active_days].'</td>
				<td class="values">'.accounts_format(-$row["Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Connection Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Access Point Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Rental Fees"]).'</td>
				<td class="values">'.accounts_format(-$row["Credit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["Debit Note - Service"]).'</td>
				<td class="values">'.accounts_format(-$row["Equipment Sale"]).'</td>
				<td class="values">'.accounts_format(-$row["Revenue (Tax exclusive)"]).'</td>
				<td class="values">'.accounts_format(-$row[Tax]).'</td>
				<td class="values">'.accounts_format(-$row["Revenue (Tax Inclusive)"]).'</td>
				<td class="values">'.accounts_format(-$row["Equipment Deposits"]).'</td>
				<td class="values">'.accounts_format(-$row["Waiver on Equipment"]).'</td>
				<td class="values">'.accounts_format(-$row[tot_untxd]).'</td>
				<td class="values">'.accounts_format(-$row[tot_inv_val]).'</td>
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