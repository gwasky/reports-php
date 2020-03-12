<?

function generate_adjustments_report($from, $to, $account_id, $adjustment_type,$proration){

	custom_query::select_db('wimax');

	$billing = new wimax_billing();
	$myquery = new custom_query();
	
	$conditions = array(array('entry_type','=','Adjustment'));

	if(($to) && ($from)){
		$from_range = array('entry_date','>=',$from);
		$to_range = array('entry_date','<=',$to);
		array_push($conditions,$from_range,$to_range);
	}else{
		$to_range = array('entry_date','=',date('Y-m-d'));
		array_push($conditions,$to_range);
	}

	if($account_id != ''){
		$account_range = array('parent_id','=',$account_id);
		array_push($conditions,$account_range);
	}
	
	if($adjustment_type){
		$type_range = array('entry','LIKE','%'.$account_id.'%');
		array_push($conditions,$type_range);
	}
	
	if($proration){
		$proration_range = array('entry','LIKE','%'.$proration.'%');
		array_push($conditions,$type_range);
	}
	
	$adjustments = $billing->GetList($conditions);
	
	foreach($adjustments as $adjustment){
		$entry = unserialize($adjustment->entry);
		$query = "
					SELECT 
					  accounts.name,
					  accounts_cstm.service_type_internet_c as service_type,
					  accounts_cstm.sales_rep_c as sales_rep
					FROM
					 accounts
					INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
					WHERE
					 accounts_cstm.crn_c = '$adjustment->account_id'
					";
		$parent_data = $myquery->single($query);
		if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}
		
		$adjustment->name = $parent_data[name];
		$adjustment->service_type = $parent_data[service_type];
		$adjustment->customer_type = $parent_data[customer_type];
		$adjustment->account_no = $adjustment->account_id;
		$adjustment->sales_rep = $parent_data[sales_rep];
		$adjustment->grouping = $entry[grouping];
		$adjustment->approved_by = $entry[approved_by];
		if($adjustment->approved_by == 'The Prorator function'){
			$adjustment->grouping = 'Prorate '.$adjustment->grouping;
		}
		$adjustment->details = $entry[details];
		$adjustment->entry = $entry[entry];
		$adjustment->parent_account_billing_currency = $entry[parent_account_billing_currency];
		
		$rate_date = get_rate_date($adjustment->entry_date,$adjustment->rate_date);
		$rate_row = get_rate($rate_date);
		$adjustment->rate = $rate_row[rate];
		//$adjustment->ugx_value = $adjustment->amount * $adjustment->rate;
		$adjustment->amount = convert_value($adjustment->amount, $adjustment->parent_account_billing_currency, $rate_date, 'USD');
		$adjustment->ugx_value = convert_value($adjustment->amount, $adjustment->parent_account_billing_currency, $rate_date, 'UGX');
		if($adjustment->currency == 'UGX'){
			$adjustment->currency_value = $adjustment->ugx_value;
		}else{
			$adjustment->currency_value = $adjustment->amount;
		}
		
		$report[data][$adjustment->id] = $adjustment;
		$report[totals][$adjustment->currency.' '.$adjustment->grouping.' (Entered by '.$adjustment->user.')'] += $adjustment->currency_value; 
		$report[totals][$adjustment->service_type.' adjustments in US$'] += $adjustment->amount;
		$report[totals][$adjustment->customer_type.' adjustments in US$'] += $adjustment->amount;
		$report[totals][$adjustment->customer_type.' adjustments in UGX'] += $adjustment->ugx_value;
		$report[totals][$adjustment->grouping.' in '.$adjustment->currency] += $adjustment->currency_value;
		$report[totals]["Value of All adjustments in US$"] += $adjustment->amount;
		$report[totals]["Value of All adjustments in UGX"] += $adjustment->ugx_value;
		$report[totals]['Total '.$adjustment->currency] += $adjustment->currency_value;
		$report[totals][$adjustment->sales_rep.' Sales (UGX)'] += $adjustment->ugx_value;
	}
	
	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 10){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;
	
	return display_adjustments_report($report);
}

function display_adjustments_report($report){
	
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
		  <th>Adjustment Type</th>
		  <th>Entry</th>
		  <th>Entry Details</th>
		  <th width="50">Matched Invoice</th>
		  <th>Amount</th>
		  <th>Entered by</th>
		  <th>Sales Rep</th>
		</tr>
	';
	foreach($report[data] as $row){
		//print_r($row); echo "<br>";
		$html .= '
		<tr>
			<td class="values">'.++$i.'</td>
			<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/cst_transaction.php?action=print&id='.$row->id.'&title=Adjustment" target="_blank">'.$row->id.'</a></td>
			<td class="values">'.$row->entry_date.'</td>
			<td class="values">'.$row->account_no.'</td>
			<td class="text_values">'.$row->name.'</td>
			<td class="text_values">'.$row->service_type.'</td>
			<td class="text_values">'.$row->grouping.'</td>
			<td class="text_values">'.$row->entry.'</td>
			<td class="wrap_text">'.$row->details.'</td>
			<td class="values">'.$row->matched_invoice.'</td>
			<td class="values">'.accounts_format($row->currency_value).'</td>
			<td class="text_values">'.$row->user.'</td>
			<td class="text_values">'.$row->sales_rep.'</td>
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