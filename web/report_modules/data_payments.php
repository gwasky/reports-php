<?
function generate_payments_report($from, $to, $account_id, $reporttype='detail'){

	custom_query::select_db('wimax');
	
	if(trim($reporttype) == ''){ $reporttype = 'detail'; $_POST[reporttype] = $reporttype; }
	
	//echo "<pre>".print_r($_POST,true)."<hr>";

	$billing = new wimax_billing();
	$myquery = new custom_query();
	
	$conditions = array(array('entry_type','=','Payment'));

	if($account_id != ''){
		$account_range = array('parent_id','=',$account_id);
		array_push($conditions,$account_range);
	}
	if(($to) && ($from)){
		$from_range = array('entry_date','>=',$from);
		$to_range = array('entry_date','<=',$to);
		array_push($conditions,$from_range,$to_range);
	}else{
		$to_range = array('entry_date','=',date('Y-m-d'));
		array_push($conditions,$to_range);
		$_POST[from] = date('Y-m-d');
		$_POST[to] = date('Y-m-d');
	}
	
	$payments = $billing->GetList($conditions);
	
	foreach($payments as $payment){
		$entry = unserialize($payment->entry);
		$query = "
			SELECT 
				accounts.name,
				accounts_cstm.customer_type_c as customer_type,
				accounts_cstm.service_type_internet_c as service_type,
				accounts_cstm.sales_rep_c as sales_rep
			FROM
				accounts
				INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
			WHERE
				accounts_cstm.crn_c = '$payment->account_id'
			";
		$parent_data = $myquery->single($query);
		if($parent_data[service_type] == ''){$parent_data[service_type] = 'un_defined';}
		
		$payment->name = $parent_data[name];
		$payment->service_type = $parent_data[service_type];
		$payment->customer_type = $parent_data[customer_type];
		$payment->account_no = $payment->account_id;
		$payment->sales_rep = $parent_data[sales_rep];
		$payment->grouping = $entry[grouping];
		$payment->entry = $entry[entry];
		$payment->parent_account_billing_currency = $entry[parent_account_billing_currency];
		
		$rate_date = get_rate_date($payment->entry_date,$payment->rate_date);
		$rate_row = get_rate($rate_date);
		$payment->rate = $rate_row[rate];
		//$payment->ugx_value = $payment->amount * $payment->rate;
		$payment->usd_amount = convert_value($payment->amount, $payment->parent_account_billing_currency, $rate_date,'USD');
		$payment->ugx_value = convert_value($payment->amount, $payment->parent_account_billing_currency, $rate_date,'UGX');
		if($payment->currency == 'UGX'){
			$payment->currency_value = $payment->ugx_value;
		}else{
			$payment->currency_value = $payment->usd_amount;
		}
		
		//print_r($payment);
		if(in_array($reporttype,array('detail','both'))){
			$report[data][$payment->id] = $payment;
		}
		
		if(in_array($reporttype,array('summary','both'))){
			$report[summary]["Sum of All payments (both US$ & UGX) by Currency"]['USD'] += $payment->usd_amount;
			$report[summary]["Sum of All payments (both US$ & UGX) by Currency"]['UGX'] += $payment->ugx_value;
			$report[summary]['Payments by Currency'][$payment->currency] += $payment->currency_value;
			$report[summary]['Payments by Payment type by Currency'][$payment->grouping.' >> '.$payment->currency] += $payment->currency_value;
			$report[summary]['Total UGX equivalent Payments by Account Sales Representatives'][$payment->sales_rep] += $payment->ugx_value;
			
			$report[summary]['Payments by Date by Payment type by Cashier by Currency'][$payment->entry_date.' >> '.$payment->grouping.' >> '.$payment->user.' >> '.$payment->currency] += $payment->currency_value;
			$report[summary]['All Payments in US$ by Month by Payment type'][substr($payment->entry_date,0,7).' >> '.$payment->grouping] += $payment->usd_amount;
		
			/*
			$report[summary][$payment->service_type.' payments in US$'] += $payment->usd_amount;
			$report[summary][$payment->customer_type.' payments in US$'] += $payment->usd_amount;
			$report[summary][$payment->customer_type.' payments in UGX'] += $payment->ugx_value;
			*/
		}
	}

	
	return display_payments_report($report);
}

function display_payments_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
	';
	
	if(count($report[data])> 0){
		$html .= '
				<tr>
				<td>
				<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
					<tr> 
					  <th></th>
					  <th width="50">Receipt Number</th>
					  <th>Entry Date</th>
					  <th>Account Number</th>
					  <th>Account Name</th>
					  <th>Customer Type</th>
					  <th>Service Type</th>
					  <th>Payment Type</th>
					  <th>Entry</th>
					  <th width="50">Matched Invoice</th>
					  <th>Currency</th>
					  <th>Amount</th>
					  <th>Rate</th>
					  <th>Cashier</th>
					  <th>Sales Rep</th>
			</tr>
		';
		foreach($report[data] as $row){
			//print_r($row); echo "<br>";
			$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/billing/cst_transaction.php?action=print&id='.$row->id.'&title=Payment" target="_blank">'.$row->id.'</a></td>
				<td class="values">'.$row->entry_date.'</td>
				<td class="text_values">'.$row->account_no.'</td>
				<td class="text_values">'.$row->name.'</td>
				<td class="text_values">'.$row->customer_type.'</td>
				<td class="text_values">'.$row->service_type.'</td>
				<td class="text_values">'.$row->grouping.'</td>
				<td class="text_values">'.$row->entry.'</td>
				<td class="values">'.$row->matched_invoice.'</td>
				<td class="values">'.$row->currency.'</td>
				<td class="values">'.accounts_format($row->currency_value).'</td>
				<td class="values">'.$row->rate.'</td>
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
	}
	
	if(count($report[summary])> 0){
		$html .= '
			<tr><td height="20">&nbsp;</td></tr>
			<tr><th style="font-size:16px;">SUMMARIES</th></tr>
		';

		foreach($report[summary] as $title=>$title_data){
			$headings = explode(' by ',$title);
			//Remove the first element which is 'Numbers'. It actually goes to the end of the row
			unset($headings[0]);
			$html .= '
			<tr><th>'.$title.'</th></tr>
			<tr><td>
				<table border="0" cellpadding="1" cellspacing="0" width="100%"  class="sortable">
					<tr>
			';
				foreach($headings as $heading){
					$html .= '
						<th>'.strtoupper($heading).'</th>
					';
				}
			$html .= '
						<th width="20%">Value</th>
					</tr>
			';
			
			unset($number_sum);
			
			foreach($title_data as $parameter_string=>$number){
				$parameter_columns = explode(' >> ',$parameter_string);
				$html .= '
					<tr>
				';
				
				foreach($parameter_columns as $column){
					$html .= '
						<td class="text_values">'.$column.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($number,0).'</td>
					</tr>
				';
				
				//$number_sum += $number;
			}
			
			$html .= '
					<!--
					NOT NEEDED COZ WE ARE MIXING UP CURRENCIES
					<tr id="totals">
						<td class="text_values" colspan="'.count($parameter_columns).'">TOTAL</td>
						<td class="values">'.number_format($number_sum,0).'</td>
					</tr>
					-->
				</table>
			</td></tr>
			<tr><td height="8">&nbsp;</td></tr>
			';
		}

	}
	
	$html .= '</table>';
	
	return $html;
}
?>