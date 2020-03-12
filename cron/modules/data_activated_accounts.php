<?php
function generate_activated_accounts($from,$to){
	
	custom_query::select_db('wimax');
	$myquerys = new custom_query();

	if(trim($from) == ''){ $from = date('Y-m-d')." 00:00:00"; }else{ $from .= " 00:00:00"; }
	if(trim($to) == ''){ $to = date('Y-m-d')." 23:59:59"; }else{ $to .= " 23:59:59"; }
	
	$report[time_run] = date('H:i:s');
	
	$report[period] = "From ".$from." to ".$to;
	
	$query = "
		SELECT
			accounts.id as account_id,
			accounts_cstm.crn_c,
			accounts.name,
			accounts_cstm.contact_person_c,
			accounts_cstm.contact_person_phone_c,
			date_add(cn_contracts_audit.date_created,interval 3 hour) AS date_activated
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join cn_contracts ON accounts.id = cn_contracts.account
			Inner Join cn_contracts_audit ON cn_contracts.id = cn_contracts_audit.parent_id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c
		WHERE
			accounts.deleted = 0 AND
			cn_contracts.deleted = 0 AND
			cn_contracts_audit.after_value_string = 'Active' and cn_contracts_audit.before_value_string NOT IN ('Inactive','Churned') AND
			cn_contracts_audit.date_created BETWEEN date_add('".$from."',interval -3 hour) AND date_add('".$to."',interval -3 hour)
		GROUP BY
			accounts_cstm.crn_c
	";
	
	//echo "$query"; exit();
	
	$report[data] = $myquerys->multiple($query);
	
	if(count($report[data]) == 0) { $report[NO_DATA] = TRUE; }
	
	//echo $query."\n\n\n".print_r($report[data]);
	
	//exit("exiting .... ");
	return display_activated_accounts($report);
}

function display_activated_accounts($report){
	
	if($report[NO_DATA]){
		return "There are no Accounts activated in this period ".$report[period]." .... ";
	}else{
		$html = '
			<table border="0" cellpadding="2" cellspacing="0">
				<tr>
				<td>
				<table border="0" cellpadding="2" cellspacing="0" width="600px">
					<tr>
						<th>#</th>
						<th>ACCOUNT NUMBER</th>
						<th>ACCOUNT NAME</th>
						<th>ACCOUNT CONTACT PERSON</th>
						<th>ACCOUNT CONTACT NUMBER(S)</th>
						<th>DATE ACTIVATED</th>
					</tr>
		';

		foreach($report[data] as $row){
			++$i;
			if($i%2 == 0) {$row_class = 'even'; }else{ $row_class = 'odd'; }
			$html .= '
				<tr class="'.$row_class.'">
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[account_id].'" target="_blank">'.$i.'</a></td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[account_id].'" target="_blank">'.$row[crn_c].'</a></td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[contact_person_c].'</td>
					<td class="text_values">'.$row[contact_person_phone_c].'</td>
					<td class="values">'.$row[date_activated].'</td>
				</tr>
			';
		}
		
		$html .= '
				</table>
			</td>
			</tr>
			<tr>
				<td height="20px"></td>
			</tr>
			<tr>
				<td class="text_values">This report was run at '.$report[time_run].'</td>
			</tr>
			</table>
		';
	}

	return $html;
}
?>