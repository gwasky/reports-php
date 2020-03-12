<?

function generate_standard_package_billing($measure_options,$billed_amount=25,$billed_times=12,$billed_total){
	
	if(floatval($billed_total) > 0){
		$target_value = $billed_total;
	}else{
		$target_value = $billed_amount * $billed_times;
	}
	
	$_POST[measure_options] = $measure_options; 
	$_POST[billed_amount] = floatval($billed_amount); 
	$_POST[billed_times] = floatval($billed_times); 
	$_POST[billed_total] = floatval($billed_total);
	
	require_once('config.wimax.php');
	//echo $target_value;
	$myquery = new custom_query();
	$query = "
		SELECT
			accounts.name as account_name,
			abs(sum(wimax_billing.amount)/1.18) as amount
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join wimax_billing ON accounts_cstm.crn_c = wimax_billing.account_id
		WHERE
			accounts.deleted = 0 and
			wimax_billing.entry_type != 'Payment' and
			wimax_billing.entry like '%Monthly Equipment Rental [New]%' and
			accounts_cstm.shared_packages_c = 'Monthly Equipment Rental [New]'
		GROUP BY 
			accounts.name
		having amount ".$measure_options." '".$target_value."'
	";
	//echo $query;
	$total_billed = $myquery->multiple($query);
	
	return display_standard_package_billing_report($total_billed);
}

function dislay_blanks_standard_package(){
	$html .= '<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr><td>&nbsp;</td></tr>
	<tr><td align = "center">No data falls within the Selected Paremeters</td></tr>
	</table>
	';
	return $html;
}

function display_standard_package_billing_report($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%"> 
		<tr> 
			  <th>Account Name</th>
			  <th>Total Billed Amount</th>
		</tr>
	';
	if(isset($report)){
	foreach($report as $row)
	{
		$html .= '
			<tr>
				<td class="text_values">'.$row[account_name].' '.$row[lname].'</td>
				<td class="values">'.number_format($row[amount],2).'</td>
			</tr>';
	
	}} else { 
		$html .= '
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td align = "center">No data falls within the Selected Paremeters</td></tr>
	';
		
		}
	return $html;
}

?>