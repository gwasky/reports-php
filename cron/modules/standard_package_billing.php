<?php
function generate_standard_package_billing($measure_options='>=',$target_value='354',$package='Monthly Equipment Rental [New]'){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			wimax_billing.account_id as account_number,
			accounts.name as account_name,
			abs(sum(wimax_billing.amount)) as amount
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join wimax_billing ON accounts_cstm.crn_c = wimax_billing.account_id
		WHERE
			accounts.deleted = 0 and
			wimax_billing.entry_type != 'Payment' and
			wimax_billing.entry like '%".$package."%' and
			accounts_cstm.shared_packages_c = '".$package."'
		GROUP BY 
			accounts.name
		having amount ".$measure_options." '".$target_value."'
	";
	//echo "\n".$query."\n";
	$report[rows] = $myquery->multiple($query);
	$report[product] = $package;
	$report[target_value] = $target_value;
	$report[target_currency] = 'US$';
	
	return display_standard_package_billing_report($report);
}

function display_standard_package_billing_report($report){
	if(count($report[rows]) > 0){	
		$html = '
			<table border="0" cellpadding="1" cellspacing="0" width="100%"> 
			<tr> 
				  <th colspan="4">Accounts that have been charged atleast '.$report[target_currency].' '.number_format($report[target_value],2).' against '.$report[product].'</th>
			</tr>
			<tr> 
				  <th></th>
				  <th>Account Number</th>
				  <th>Account Name</th>
				  <th>Total Billed Amount '.$report[target_currency].'</th>
			</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$ww.'</td>
					<td class="values">'.$row[account_number].'</td>
					<td class="text_values">'.$row[account_name].'</td>
					<td class="values">'.number_format($row[amount],2).'</td>
				</tr>
			';
		}	
		$html .= '
			</table>
		';
	}else{
		exit("No Accounts need their packages changed .... ");
	}
	
	return $html;
}

?>