<?
function generate_revenue_by_period_report($from, $to, $customer_types,$account_nums, $datagroups=''){
	
	//custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	$billing = new wimax_billing();
	
	$conditions = array();
	$report[months] = array();
	$period_condition = '';
	
	if($from){
		$from = date_reformat($from,'%Y-%m').'-01';
		array_push($conditions,array('entry_date','>=',$from));
		$period_condition .= " AND entry_date >= '".$from."' ";
	}else{
		$from = date('Y-m').'-01';
		array_push($conditions,array('entry_date','>=',$from));
		$period_condition .= " AND entry_date >= '".$from."' ";
	}
	
	if($to){
		$to = last_day($to);
		array_push($conditions,array('entry_date','<=',$to));
		$period_condition .= " AND entry_date <= '".$to."' ";
	}else{
		$to = last_day(date('Y-m-d'));
		array_push($conditions,array('entry_date','<=',$to));
		$period_condition .= " AND entry_date <= '".$to."' ";
	}
	
	$_POST[to] = $to; $_POST[from] = $from;
	
	if(($customer_types) && (!in_array('%%',$customer_types))){
		$customer_type_condition .= "AND (";
		foreach($customer_types as $count=>$customer_type){
			$customer_type_condition .= " accounts_cstm.customer_type_c = '".$customer_type."'";
			if(count($customer_types) > $count+1){
				$customer_type_condition .= " OR ";
			}
		}
		$customer_type_condition .= ")";
	}
	
	if(is_array($datagroups) and !in_array('',$datagroups)){
		$product_group_condition = " AND (";
		
		foreach($datagroups as $group_key=>$datagroup){
			$query = "
				SELECT
					ps_products.`name`
				FROM
					ps_products
					INNER JOIN ps_products_cstm ON ps_products.id = ps_products_cstm.id_c
				WHERE
					ps_products.deleted = 0 AND
					ps_products_cstm.product_grouping_c = '".$datagroup."'
			";
			custom_query::select_db('wimax');
			$products = $myquery->multiple($query);

			foreach($products as $product_key=>$product_row){
				$product = $product_row[name];
				$product_group_condition .= "wimax_billing.entry LIKE '%".$product."%'";
				if($product_key+1 < count($products)) $product_group_condition .= " OR ";
			}
			if($group_key+1 < count($product_groups)) $product_group_condition .= " OR ";
		}
		
		$product_group_condition .= ") ";
	}
	
	if($account_nums){
		$account_nums = "('".str_replace(array(',',';',':','.'),"','",trim($account_nums))."')";
		$accounts_list_accounts = " accounts_cstm.crn_c in ".$account_nums." AND ";
		$accounts_list_billing = " account_id in ".$account_nums." AND ";
		//echo $account_nums."<br>";
	}

	$accnt_data_query = "
		SELECT
			accounts.name,
			customer_type_c AS customer_type,
			accounts_cstm.crn_c AS account_no,
			accounts_cstm.mem_id_c AS parent_no,
			accounts_cstm.platform_c AS platform,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts.status) AS bw_status,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts_cstm.domain_hosting_status_c) AS DH_STATUS,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts_cstm.domain_reg_status_c) AS DR_STATUS,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts_cstm.hire_purchase_status_c) AS HP_STATUS,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts_cstm.mail_hosting_status_c) AS MH_STATUS,
			if(cn_contracts.deleted IS NULL,'NO CONTRACT',cn_contracts_cstm.web_hosting_status_c) AS WH_STATUS,
			accounts.id
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			LEFT OUTER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			LEFT OUTER JOIN cn_contracts_cstm ON (cn_contracts_cstm.id_c = cn_contracts.id)
		WHERE
			".$accounts_list_accounts."
			accounts.deleted = '0' AND
			(cn_contracts.deleted = '0' OR cn_contracts.deleted IS NULL) ".
			$customer_type_condition."
		ORDER BY
			accounts_cstm.crn_c ASC
	";

	/*AND accounts_cstm.crn_c in (
		select 
			distinct account_id 
		from 
			wimax_billing 
			inner join accounts_cstm on accounts_cstm.crn_c=wimax_billing.account_id 
		where 
			wimax_billing.entry_date >= '".$from."' AND
			wimax_billing.entry_date <= '".$to."'
		)*/

	//echo "Q => ".$accnt_data_query."<br>";
	
	custom_query::select_db('wimax');
	$parent_data = $myquery->multiple($accnt_data_query);
	
	//echo "count is ".count($parent_data)."<br>";
	
	foreach($parent_data as $key=>$row){
		if($parent_data[$key][service_type] == ''){$parent_data[$key][service_type] = 'un_defined';}
		$parent_data[$key][account_no] = trim($parent_data[$key][account_no]);
		$row[parent_no] = trim($row[parent_no]);
		$parent_data[$parent_data[$key][account_no]] = $row;
		unset($parent_data[$key]);
	}
	
	//echo "count is ".count($parent_data)."<br>";
	
	$billing_query = "
		select 
			wimax_billing.parent_id,
			wimax_billing.entry_date,
			wimax_billing.rate_date,
			wimax_billing.account_id,
			wimax_billing.entry_type,
			wimax_billing.entry,
			wimax_billing.currency,
			wimax_billing.amount
		from 
			wimax_billing 
			inner join accounts_cstm on accounts_cstm.crn_c=wimax_billing.account_id
			inner join accounts on accounts.id = accounts_cstm.id_c
		where
			".$accounts_list_billing."
			accounts.deleted = 0 AND
			wimax_billing.entry_type != 'Payment' AND
			wimax_billing.entry_date >= '".$from."' AND
			wimax_billing.entry_date <= '".$to."'".
			$customer_type_condition.
			$product_group_condition;

	//echo "Qeury => ".$billing_query."<br>"; exit("Exiting ... ");
	$alltx = $myquery->multiple($billing_query);
	
	//echo "Count ".count($alltx)." => R => ".$billing_query."<br>";
	
	foreach($alltx as $tx){
		if(!in_array(date_reformat($tx[entry_date],'%Y-%b'),$report[months])){
			array_push($report[months],date_reformat($tx[entry_date],'%Y-%b'));
		}
		$tx[parent_id] = trim($tx[parent_id]);
		$tx[account_id] = trim($tx[account_id]);
		$entry = unserialize($tx[entry]);
		
		//general account info
		if(!$report[data][$tx[parent_id]][$tx[account_id]][name]){
			$report[data][$tx[parent_id]][$tx[account_id]][name] = $parent_data[$tx[account_id]][name];
			$report[data][$tx[parent_id]][$tx[account_id]][id] = $parent_data[$tx[account_id]][id];
			$report[data][$tx[parent_id]][$tx[account_id]][platform] = $parent_data[$tx[account_id]][platform];
			$report[data][$tx[parent_id]][$tx[account_id]][bandwidth_status] = $parent_data[$tx[account_id]][bw_status];
			$report[data][$tx[parent_id]][$tx[account_id]][domain_hosting_status] = $parent_data[$tx[account_id]][DH_STATUS];
			$report[data][$tx[parent_id]][$tx[account_id]][domain_registration_status] = $parent_data[$tx[account_id]][DR_STATUS];
			$report[data][$tx[parent_id]][$tx[account_id]][hire_purchase_status] = $parent_data[$tx[account_id]][HP_STATUS];
			$report[data][$tx[parent_id]][$tx[account_id]][mail_hosting_status] = $parent_data[$tx[account_id]][MH_STATUS];
			$report[data][$tx[parent_id]][$tx[account_id]][web_hosting_status] = $parent_data[$tx[account_id]][WH_STATUS];
			$report[data][$tx[parent_id]][$tx[account_id]][customer_type] = $parent_data[$tx[account_id]][customer_type];
			$report[data][$tx[parent_id]][$tx[account_id]][account_no] = $parent_data[$tx[account_id]][account_no];
		}
		
		$tx[grouping] = $entry[grouping];
		$tx[entry] = $entry[entry];
		//for old USD entries
		if($entry[parent_account_billing_currency] == ''){ $entry[parent_account_billing_currency] = 'USD';}
		$tx[parent_account_billing_currency] = $entry[parent_account_billing_currency];
		//Converting all tx to USD
		$tx[amount] = convert_value($tx[amount], $tx[parent_account_billing_currency], $tx[rate_date],'USD');
		
		$tx[month] = date_reformat($tx[entry_date],'%Y-%b');
		
		if($tx[entry_type] == 'Payment'){
			$report[data][$tx[parent_id]][$tx[parent_id]][months][payments][$tx[month]][USD] += $tx[amount];
			
			$report[data][$tx[parent_id]][$tx[parent_id]][totals][payments][USD] += $tx[amount];
			
			$report[totals][$tx[month]." USD Payments"] += $tx[amount];
			
			$report[totals]['Total payments USD'] += $tx[amount];
		}

		if($tx[entry_type] == 'Charges'){
			if($tx[entry] != 'Equipment Deposit'){
				$report[data][$tx[parent_id]][$tx[account_id]][months][charges][$tx[month]][USD] += $tx[amount]/1.18;
				
				$report[data][$tx[parent_id]][$tx[account_id]][totals][charges][USD] += $tx[amount]/1.18;

				$report[totals][$tx[month]." USD Charges"] += $tx[amount]/1.18;
			
				$report[totals]['Total Charges USD'] += $tx[amount]/1.18;
			}else{
				//Deposits are not revenue
				$report[data][$tx[parent_id]][$tx[account_id]][months][deposits][$tx[month]][USD] += $tx[amount];
				
				$report[data][$tx[parent_id]][$tx[account_id]][totals][deposits][USD] += $tx[amount];

				$report[totals][$tx[month]." USD Deposits"] += $tx[amount];
			
				$report[totals]['Total Deposits USD'] += $tx[amount];
			}
		}
	
		if($tx[entry_type] == 'Services'){
			$report[data][$tx[parent_id]][$tx[account_id]][months][charges][$tx[month]][USD] += $tx[amount]/1.18;
			
			$report[data][$tx[parent_id]][$tx[account_id]][totals][charges][USD] += $tx[amount]/1.18;

			$report[totals][$tx[month]." USD Charges"] += $tx[amount]/1.18;
		
			$report[totals]['Total Charges USD'] += $tx[amount]/1.18;
		}
		
		if($tx[entry_type] == 'Adjustment'){
			$query = "
				SELECT
					ps_products_cstm.product_grouping_c as grouping
				FROM
					ps_products_cstm
					INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
					ps_products.name='".$tx[entry]."' AND
					ps_products.deleted != 1
			";
			
			$result = $myquery->single($query);
			
			if($entry[approved_by] == "The Prorator function"){
				$prefix = "PRFX ";
				//print_r($entry); echo "<br><br>";
			}else{
				$prefix = '';
			}
			
			if($tx[grouping] != 'Waiver on Equipment'){
				if($tx[grouping] != 'Cash Discount'){
					$report[data][$tx[parent_id]][$tx[account_id]][months][charges][$tx[month]][USD] += $tx[amount]/1.18;
					
					$report[data][$tx[parent_id]][$tx[account_id]][totals][charges][USD] += $tx[amount]/1.18;
	
					$report[totals][$tx[month]." USD Charges"] += $tx[amount]/1.18;
				
					$report[totals]['Total Charges USD'] += $tx[amount]/1.18;
				}else{
					$report[data][$tx[parent_id]][$tx[account_id]][months][charges][$tx[month]][USD] += $tx[amount];
					
					$report[data][$tx[parent_id]][$tx[account_id]][totals][charges][USD] += $tx[amount];
	
					$report[totals][$tx[month]." USD Charges"] += $tx[amount];
				
					$report[totals]['Total Charges USD'] += $tx[amount];
				}
				
				//Strict accounting definition, it alsi takes the parent if of if($entry[approved_by] == "The Prorator function")			
				/*$report[data][$tx[parent_id]][$tx[account_id]][months][charges][$tx[month]][USD] += $tx[amount]/1.18;
				$report[data][$tx[parent_id]][$tx[account_id]][totals][charges][USD] += $tx[amount]/1.18;
				$report[totals][$tx[month]." USD Charges"] += $tx[amount]/1.18;
				$report[totals]['Total Charges USD'] += $tx[amount]/1.18;*/
			}
		}	
	}
	
	//For accounts with no transactions in the period being run
	foreach($parent_data as $account_no=>$row){
		//general account info
		if(!$report[data][$row[parent_no]][$account_no][name]){
			$report[data][$row[parent_no]][$account_no][name] = $row[name];
			$report[data][$row[parent_no]][$account_no][id] = $row[id];
			$report[data][$row[parent_no]][$account_no][platform] = $row[platform];
			$report[data][$row[parent_no]][$account_no][bandwidth_status] = $row[bw_status];
			$report[data][$row[parent_no]][$account_no][domain_hosting_status] = $row[DH_STATUS];
			$report[data][$row[parent_no]][$account_no][domain_registration_status] = $row[DR_STATUS];
			$report[data][$row[parent_no]][$account_no][hire_purchase_status] = $row[HP_STATUS];
			$report[data][$row[parent_no]][$account_no][mail_hosting_status] = $row[MH_STATUS];
			$report[data][$row[parent_no]][$account_no][web_hosting_status] = $row[WH_STATUS];
			$report[data][$row[parent_no]][$account_no][customer_type] = $row[customer_type];
			$report[data][$row[parent_no]][$account_no][account_no] = $row[account_no];
		}
	}

	$i = 0;
	foreach($report[totals] as $key=>$total){
		$totals[$i][$key] = $total; ++$j;
		if($j == 6){ ++$i; $j = 0; }
	}
	$report[totals] = $totals;

	return display_revenue_by_period_report($report);
}

function display_revenue_by_period_report($report){
	
	$html = '
	<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
		<tr>
			<th></th>
			<th>Parent</th>
			<th>Account Number</th>
			<th>Account Name</th>
			<th>Platform</th>
			<th>Customer Type</th>
			<th>Bandwidth Status</th>
			<th>D Hosting Status</th>
			<th>D Reg Status</th>
			<th>Hire Purchase Status</th>
			<th>M Hosting Status</th>
			<th>W Hosting Status</th>
	';
	foreach($report[months] as $month){
		$html .= '
			<th>'.$month.'</th>	
		';
	}

	$html .= '
			<th>Total USD</th>
		</tr>
	';
	foreach($report[data] as $parent_no=>$parent_row){
		foreach($parent_row as $account_no=>$row){
			$html .= '
				<tr>
					<td class="text_values">'.++$i.'</td>
					<td class="text_values">'.$parent_no.'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.$row[account_no].'</a></td>
					<td class="text_values">'.$row[name].'</td>
					<td class="text_values">'.$row[platform].'</td>
					<td class="text_values">'.$row[customer_type].'</td>
					<td class="text_values">'.$row[bandwidth_status].'</td>
					<td class="text_values">'.$row[domain_hosting_status].'</td>
					<td class="text_values">'.$row[domain_registration_status].'</td>
					<td class="text_values">'.$row[hire_purchase_status].'</td>
					<td class="text_values">'.$row[mail_hosting_status].'</td>
					<td class="text_values">'.$row[web_hosting_status].'</td>
			';
			
			foreach($report[months] as $month){
				$html .= '
					<td class="values">'.accounts_format(-$row[months][charges][$month][USD]).'</td>
				';
			}
					
			$html .= '
					<td class="values">'.accounts_format(-$row[totals][charges][USD]).'</td>
				</tr>
			';
		}
	}
	
	$columns = (count($report[months])+4);
	
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
	
	$html .= '
				</tr>
			</table>
			</td>
		</tr>
		';
	
	$html .= '
		</table>
	';
	
	return $html;
}?>