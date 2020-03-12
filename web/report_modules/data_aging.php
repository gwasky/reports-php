<?
function generate_new_aging_report($from, $to, $account_id, $status, $customer_types){
	
	$billing = new wimax_billing();
	$myquery = new custom_query();
	
	if(!$to){
		$to_date_query = "SELECT last_day(date_format(concat(PERIOD_ADD(".date('Ym').",-1),'01'),'%Y-%m-%d')) as to_date";
		custom_query::select_db('wimax');
		$result = $myquery->single($to_date_query);
		$to = last_day($result[to_date]);
	}
	
	$_POST[to] = $to;
	
	if(!$from){
		$from = 1;
	}
	
	$period_bands = array(
				'1'=>array('period'=>1,'name'=>'1 month'),
				'2'=>array('period'=>1,'name'=>'2 months'),
				'3'=>array('period'=>1,'name'=>'3 months'),
				'4'=>array('period'=>1,'name'=>'4 months'),
				'5'=>array('period'=>1,'name'=>'5 months'),
				'6'=>array('period'=>1,'name'=>'6 months'),
				'7'=>array('period'=>6,'name'=>'7 - 12 months'),
				'13'=>array('period'=>NULL,'name'=>'beyond 12 months'),
	);

	//start from the first last 30 days
	$i = 1;
	$to_f = date_reformat($to,'%Y%m');
	$first_from = date_reformat($to,'%Y-%m-01');
	$first_to = last_day($first_to);
	$report[periods] = array();
	/*while($i<=$from){
		custom_query::select_db('wimax');
		$period_q = "SELECT concat(PERIOD_ADD(".$to_f.",-".$i."), '01') as date1;";
		$result = $myquery->single($period_q);
		echo $i." - ".$period_q." <> ".print_r($result,true)."<hr>";
		$date = last_day($result[date1]);
		array_push($report[periods],$date);
		$i++;

	}
	$i = 0;*/
	
	$report[periods]['Input month'][from] = $first_from;
	$report[periods]['Input month'][to] = $to;
	foreach($period_bands as $period_band=>$period_band_row){
		if($period_band <= $from){
			
			custom_query::select_db('wimax');
			$period_q = "SELECT concat(PERIOD_ADD(".$to_f.",-".$period_band."), '01') as the_date;";
			$result = $myquery->single($period_q);
			
			if($period_band_row[period] == 1){
				$report[periods][$period_band_row[name]][from] = date_reformat($result[the_date],'%Y-%m-%d');
			}elseif($period_band_row[period] != NULL){
				$inner_period_q = "SELECT concat(PERIOD_ADD(".$to_f.",-".($period_band + $period_band_row[period] - 1)."), '01') as the_date;";
				$inner_result = $myquery->single($inner_period_q);
				
				$report[periods][$period_band_row[name]][from] = date_reformat($inner_result[the_date],'%Y-%m-%d');
			}
			$report[periods][$period_band_row[name]][to] = last_day($result[the_date]);
		}else{
			break;
		}
	}
	
	//echo "Initial period is ".nl2br(print_r($report[periods],true))."<br>"; exit();
	
	$query = "
		SELECT
		  accounts_cstm.crn_c,
		  accounts_cstm.selected_billing_currency_c as billing_currency,
		  accounts.name,
		  accounts_cstm.service_type_internet_c as service_type,
		  cn_contracts.`status`,
		  accounts_cstm.customer_type_c as customer_type,
		 (select balance from wimax_billing where id = (select max(id) from wimax_billing where parent_id=accounts_cstm.crn_c and entry_date <= '".$to."')) as balance,
		 (select entry_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id=accounts_cstm.crn_c and wimax_billing.entry_type = 'Payment' and entry_date > '".$to."')) as last_payment_date,
		 (select sum(amount) from wimax_billing where wimax_billing.entry_type = 'Payment' and wimax_billing.entry_date > '".$to."' and parent_id = accounts_cstm.crn_c group by wimax_billing.parent_id) as latest_payment,
		 (select sum(amount) from wimax_billing where wimax_billing.entry_date BETWEEN '".$first_from."' AND '".$to."' and parent_id = accounts_cstm.crn_c and amount < 0 group by parent_id) as charges,
		 (select sum(amount) from wimax_billing where wimax_billing.entry_date BETWEEN '".$first_from."' AND '".$to."' and parent_id = accounts_cstm.crn_c and (amount > 0) group by parent_id) as payments
		FROM
		  accounts
		  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
		  INNER JOIN cn_contracts ON (cn_contracts.account=accounts.id)
		WHERE
		  accounts_cstm.crn_c = accounts_cstm.mem_id_c AND
		  accounts.deleted = 0 AND
		  cn_contracts.deleted = 0
	";
	
	if($account_id != ''){
		$query .= " AND accounts_cstm.crn_c = '".$account_id."'";
	}
	
	if($status != ''){
		$query .= " AND cn_contracts.`status` = '".$status."'";
	}
	
	if(($customer_types) && (!in_array('%%',$customer_types))){
		$query .= "AND (";
		foreach($customer_types as $count=>$customer_type){
			$query .= "accounts_cstm.customer_type_c = '".$customer_type."'";
			if(count($customer_types) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ")";
	}
	
	$query .= "
		order by balance asc 
	";
	
	//echo $query.'<br><br>'; //exit();
	
	custom_query::select_db('wimax');
	$account_list = $myquery->multiple($query);
	
	if(count($account_list) == 0){ exit("Your conditions return no data ...");}
	
	foreach($account_list as $accnt){
		if($accnt[balance] != ''){
			$report[data][$accnt[crn_c]][no] = $accnt[crn_c];
			$report[data][$accnt[crn_c]][name] = $accnt[name];
			$report[data][$accnt[crn_c]][customer_type] = $accnt[customer_type];
			$report[data][$accnt[crn_c]][service_type] = $accnt[service_type];
			$report[data][$accnt[crn_c]][status] = $accnt[status];
			$report[data][$accnt[crn_c]][billing_currency] = $accnt[billing_currency];
			$report[data][$accnt[crn_c]][periods]['Input month'] = array('balance'=>$accnt[balance],'payments'=>$accnt[payments],'charges'=>$accnt[charges]);
			$report[data][$accnt[crn_c]][cum_payments] = $report[data][$accnt[crn_c]][periods]['Input month'][payments];
			$report[data][$accnt[crn_c]][last_payment_date] = $accnt[last_payment_date];
			$report[data][$accnt[crn_c]][latest_payment] = $accnt[latest_payment];
			if($accnt[balance] < 0){
				$report[data][$accnt[crn_c]][total_owed] = abs($accnt[balance]);
			}
			/*if($accnt[crn_c] == '200905-408'){
				echo $to." ->"; print_r($report[data][$accnt[crn_c]][periods][date_reformat($to,'%b %Y')]); 
				echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
			}*/
			$i = 0;
			custom_query::select_db('wimax');
			foreach($report[periods] as $period=>$period_data){
				++$i;
				//skip the first one because it has been catered for in the query above
				if($i == 1) { continue; }
				
				if($i < count($report[periods])){
					$period_q = "
select
	(select wimax_billing.balance from wimax_billing where id = (select max(wimax_billing.id) from wimax_billing where wimax_billing.parent_id='".$report[data][$accnt[crn_c]][no]."' and wimax_billing.entry_date <= '".$period_data[to]."')) as balance,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date BETWEEN '".$period_data[from]."' AND '".$period_data[to]."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount < 0) group by wimax_billing.parent_id) as charges,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date BETWEEN '".$period_data[from]."' AND '".$period_data[to]."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount > 0) group by wimax_billing.parent_id) as payments
					";
					//echo "1 -> ".$period_q."<br>";
					$result = $myquery->single($period_q);
					//$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][charges] = $result[charges];
				}else{
					$period_q = "
select
	(select wimax_billing.balance from wimax_billing where id = (select max(wimax_billing.id) from wimax_billing where wimax_billing.parent_id='".$report[data][$accnt[crn_c]][no]."' and wimax_billing.entry_date <= '".$period_data[to]."')) as balance,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date <= '".$period_data[to]."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount < 0) group by wimax_billing.parent_id) as charges,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date <= '".$period_data[to]."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount > 0) group by wimax_billing.parent_id) as payments
					";
					//echo "2 -> ".$period_q."<br>";
					$result = $myquery->single($period_q);
					//$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][charges] = $result[balance];
				}
				$report[data][$accnt[crn_c]][periods][$period][charges] = $result[charges];
				$report[data][$accnt[crn_c]][periods][$period][balance] = $result[balance];
				$report[data][$accnt[crn_c]][periods][$period][payments] = $result[payments];
				$report[data][$accnt[crn_c]][cum_payments] += $report[data][$accnt[crn_c]][periods][$period][payments];
				/*if($accnt[crn_c] == '200905-408'){
					echo $period." ->";print_r($report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')]);
					echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
				}*/
			}
			$i = 0;
			
			/*if($accnt[crn_c] == '200905-408'){
				print_r($report[data][$accnt[crn_c]]);
				echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
			}*/
		}
	}
	
	//print_r($report[data]); echo "<br>";
	
	//Adding the First period to the beginning of the array for Future use
	//Desc order
	//$report[periods];
	
	//print_r($report[periods]); echo "<hr>";
	
	//Getting Asc order
	$report[periods_asc] = $report[periods];
	$report[periods_asc] = array_reverse_associative($report[periods_asc]);
	
	//print_r($report[periods_asc]); exit();
	
	foreach($report[data] as &$row){
		foreach($report[periods] as $period_name=>$period_data){
			foreach($report[periods] as $period_1_name=>$period_1_data){
				if(strtotime($period_1_data[to]) <= strtotime($period_data[to])){
					/*if($row[no] == '200905-408'){
						echo "Asc Adding $period_1 charges [".$row[periods][date_reformat($period_1,'%b %Y')][charges]."] to $period cum charges [".$row[periods][date_reformat($period,'%b %Y')][cum_charges_asc]."]";
					}*/
					$row[periods][$period_name][cum_charges_asc] += $row[periods][$period_1_name][charges];
					/*if($row[no] == '200905-408'){
						echo " = [".$row[periods][date_reformat($period,'%b %Y')][cum_charges_asc]."] <br>";
					}*/
				}
			}
		}
		//if($row[no] == '200905-408'){ echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>"; }
		unset($period_name,$period_data,$period_1_name,$period_1_data);
		foreach($report[periods_asc] as $period_name=>$period_data){
			foreach($report[periods] as $period_1_name=>$period_1_data){
				if(strtotime($period_1_data[to]) >= strtotime($period_data[to])){
					/*if($row[no] == '200905-408'){
						echo "Desc Adding $period_1 charges [".$row[periods][date_reformat($period_1,'%b %Y')][charges]."] to $period cum charges [".$row[periods][date_reformat($period,'%b %Y')][cum_charges_desc]."]";
					}*/
					$row[periods][$period_name][cum_charges_desc] += $row[periods][$period_1_name][charges];
					/*if($row[no] == '200905-408'){
						echo " = [".$row[periods][date_reformat($period,'%b %Y')][cum_charges_desc]."] <br>";
					}*/
				}
			}
		}
				
		foreach($report[periods] as $period=>$period_data){
			/*if($row[no] == '200905-408'){
				echo $period."==>> <br>";
			}*/
			if((($row[cum_payments] + $row[periods][$period][cum_charges_asc]) < 0) and ($row[periods][$period][charges] < 0)){
				if(($row[cum_payments] + $row[periods][$period][cum_charges_asc]) < $row[periods][$period][charges]){
					$row[periods][$period][due] = $row[periods][$period][charges];
				}else{
					/*if($row[no] == '200905-408'){
						$hh = $row[cum_payments] + $row[periods][date_reformat($period,'%b %Y')][cum_charges_asc];
						echo " Accum payment [".$row[cum_payments]."] + Acc charges [".$row[periods][date_reformat($period,'%b %Y')][cum_charges_asc]."] = [".$hh."] less than ".$row[periods][date_reformat($period,'%b %Y')][charges]."<br>";
					}*/
					$row[periods][$period][due] = $row[cum_payments] + $row[periods][$period][cum_charges_asc];
				}
			}else{
				/*if($row[no] == '200905-408'){
					echo "[".($row[cum_payments] + $row[periods][date_reformat($period,'%b %Y')][cum_charges_asc])."] may be > 0 or [".$row[periods][date_reformat($period,'%b %Y')][charges]."] is > 0 <br>";
				}*/
			}
			
			//GET ALL ARREAS FROM THE OTHER MONTH BACKWORDS
			if($period != 'Input month'){
				$row[arreas] += $row[periods][$period][due];
			}
		}
		
	}
	
	return display_new_aging_report($report);
}

function display_new_aging_report($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th></th>
			  <th>Account Number</th>
			  <th>Account Name</th>
			  <th>Service type</th>
			  <th>Customer type</th>
			  <th>Status</th>
			  <th>Currency</th>
	';
	
	$period_names = array_keys($report[periods]);
	
	foreach($period_names as $period){
		$html .='
			  <th>'.$period.'</th>
		';
	}
	
	$html .='
			  <th>Total Owed</th>
			  <th>New Payments</th>
			  <th>Date Received</th>
			  <th>Arrears</th>
		</tr>
	';

	foreach($report[data] as $row){
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values" width="85"><a href="http://wimaxcrm.waridtel.co.ug/billing/payments.php?parent_id='.$row[no].'" target="_blank">'.$row[no].'</a></td>
				<td class="text_values">'.strtoupper($row[name]).'</td>
				<td class="text_values" width="70">'.$row[service_type].'</td>
				<td class="text_values" width="70">'.$row[customer_type].'</td>
				<td class="text_values" width="50">'.$row[status].'</td>
				<td class="text_values" width="50">'.$row[billing_currency].'</td>
		';
		
		//echo "Account =>>[".$row[no]."] "; print_r($row[periods]); echo "<br>";

		foreach($period_names as $period){
			$html .='
				<td class="'; if($row[periods][$period][due] < 0){$html .='red_'; } $html .='values">'.number_format(abs($row[periods][$period][due]),2,'.',',').'</td>
			';
		}
		
		$html .='
				<td class="'; if($row[total_owed] > 0){$html .='red_'; } $html .='values">'.number_format($row[total_owed],2,".",",").'</td>
				<td class="values">'.number_format($row[latest_payment],2,".",",").'</td>
				<td class="text_values">'.$row[last_payment_date].'</td>
				<td class="'; if($row[arreas] < 0){$html .='red_'; } $html .='values">'.number_format(-$row[arreas],2,".",",").'</td>
			</tr>
		';
	}
	
	$html .= '</table>';
	
	return $html;
}

function display_aging_report($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th>Account Number</th>
			  <th>Account Name</th>
			  <th>Service type</th>
			  <th>Customer type</th>
			  <th>Status</th>
	';

	$_row = array_slice($report[data],0,1);
	$accnt_array = array_keys($_row);
	//print_r($_row[$accnt_array[0]][periods]);
	$periods = array_keys($_row[$accnt_array[0]][periods]);
	
	foreach($periods as $period){
		$html .='
			  <th>'.$period.'</th>
		';
	}
	
	$html .='
		</tr>
	';

	foreach($report[data] as $row){
		$html .= '
		<tr>
			<td class="text_values" width="85">'.$row[no].'</td>
			<td class="text_values">'.$row[name].'</td>
			<td class="text_values" width="70">'.$row[service_type].'</td>
			<td class="text_values" width="70">'.$row[customer_type].'</td>
			<td class="text_values" width="50">'.$row[status].'</td>
		';

		foreach($row[periods] as $period){		
			$html .='
				<td class="'; if($period[bal] < 0){$html .='red_'; } $html .='values">'.accounts_format($period[bal]).'</td>
			';
		}
		
		$html .='
			</tr>
		';
	}
	
	$html .= '</table>';
	
	return $html;
}

function array_reverse_associative(&$array){
	$keys = array_keys($array);
	krsort($keys);
	$values = $array;
	unset($array);
	foreach($keys as $key){
		$array[$key] = $values[$key];
	}
	return $array;
}


/*
	$billing = new wimax_billing();
	$myquery = new custom_query();
	
	if(!$to){
		$to_date_query = "SELECT last_day(date_format(concat(PERIOD_ADD(".date('Ym').",-1),'01'),'%Y-%m-%d')) as to_date";
		custom_query::select_db('wimax');
		$result = $myquery->single($to_date_query);
		$to = last_day($result[to_date]);
	}
	
	$_POST[to] = $to;
	
	if(!$from){
		$from = 1;
	}

	//start from the first last 30 days
	$i = 1;
	$to_f = date_reformat($to,'%Y%m');
	$report[periods] = array();
	while($i<=$from){
		custom_query::select_db('wimax');
		$result = $myquery->single("SELECT concat(PERIOD_ADD(".$to_f.",-".$i."), '01') as date1;");
		$date = last_day($result[date1]);
		array_push($report[periods],$date);
		$i++;
	}
	$i = 0;
	
	//echo "Initial period is "; print_r($report[periods]); echo "<br>";
	
	$query = "
		SELECT
		  accounts_cstm.crn_c,
		  accounts_cstm.selected_billing_currency_c as billing_currency,
		  accounts.name,
		  accounts_cstm.service_type_internet_c as service_type,
		  cn_contracts.`status`,
		  accounts_cstm.customer_type_c as customer_type,
		 (select balance from wimax_billing where id = (select max(id) from wimax_billing where parent_id=accounts_cstm.crn_c and entry_date <= '$to')) as balance,
		 (select entry_date from wimax_billing where id = (select max(id) from wimax_billing where parent_id=accounts_cstm.crn_c and wimax_billing.entry_type = 'Payment' and entry_date >'$to')) as last_payment_date,
		 (select sum(amount) from wimax_billing where wimax_billing.entry_type = 'Payment' and wimax_billing.entry_date > '$to' and parent_id = accounts_cstm.crn_c group by wimax_billing.parent_id) as latest_payment,
		 (select sum(amount) from wimax_billing where date_format(entry_date,'%Y%m') = date_format('$to','%Y%m') and parent_id = accounts_cstm.crn_c and amount < 0 group by parent_id) as charges,
		 (select sum(amount) from wimax_billing where date_format(entry_date,'%Y%m') = date_format('$to','%Y%m') and parent_id = accounts_cstm.crn_c and (amount > 0) group by parent_id) as payments
		FROM
		  accounts
		  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
		  INNER JOIN cn_contracts ON (cn_contracts.account=accounts.id)
		WHERE
		  accounts_cstm.crn_c = accounts_cstm.mem_id_c AND
		  accounts.deleted = 0 AND
		  cn_contracts.deleted = 0
	";
	
	if($account_id != ''){
		$query .= " AND accounts_cstm.crn_c = '".$account_id."'";
	}
	
	if($status != ''){
		$query .= " AND cn_contracts.`status` = '".$status."'";
	}
	
	if(($customer_types) && (!in_array('%%',$customer_types))){
		$query .= "AND (";
		foreach($customer_types as $count=>$customer_type){
			$query .= "accounts_cstm.customer_type_c = '".$customer_type."'";
			if(count($customer_types) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ")";
	}
	
	$query .= "
		order by balance asc 
	";
	
	//echo $query.'<br><br>'; exit();
	
	custom_query::select_db('wimax');
	$account_list = $myquery->multiple($query);
	
	if(count($account_list) == 0){ exit("Your conditions return no data ...");}
	
	foreach($account_list as $accnt){
		if($accnt[balance] != ''){
			$report[data][$accnt[crn_c]][no] = $accnt[crn_c];
			$report[data][$accnt[crn_c]][name] = $accnt[name];
			$report[data][$accnt[crn_c]][customer_type] = $accnt[customer_type];
			$report[data][$accnt[crn_c]][service_type] = $accnt[service_type];
			$report[data][$accnt[crn_c]][status] = $accnt[status];
			$report[data][$accnt[crn_c]][billing_currency] = $accnt[billing_currency];
			$report[data][$accnt[crn_c]][periods][date_reformat($to,'%b %Y')] = array('balance'=>$accnt[balance],'payments'=>$accnt[payments],'charges'=>$accnt[charges]);
			$report[data][$accnt[crn_c]][cum_payments] = $report[data][$accnt[crn_c]][periods][date_reformat($to,'%b %Y')][payments];
			$report[data][$accnt[crn_c]][last_payment_date] = $accnt[last_payment_date];
			$report[data][$accnt[crn_c]][latest_payment] = $accnt[latest_payment];
			if($accnt[balance] < 0){
				$report[data][$accnt[crn_c]][total_owed] = abs($accnt[balance]);
			}
			$i = 0;
			custom_query::select_db('wimax');
			foreach($report[periods] as $period){
				if(++$i < count($report[periods])){
					$period_q = "
select
	(select wimax_billing.balance from wimax_billing where id = (select max(wimax_billing.id) from wimax_billing where wimax_billing.parent_id='".$report[data][$accnt[crn_c]][no]."' and wimax_billing.entry_date <= '".$period."')) as balance,
	(select sum(wimax_billing.amount) from wimax_billing where date_format(wimax_billing.entry_date,'%Y%m') = date_format('".$period."','%Y%m') and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount < 0) group by wimax_billing.parent_id) as charges,
	(select sum(wimax_billing.amount) from wimax_billing where date_format(wimax_billing.entry_date,'%Y%m') = date_format('".$period."','%Y%m') and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount > 0) group by wimax_billing.parent_id) as payments
					";
					//echo "1 -> ".$period_q."<br>";
					$result = $myquery->single($period_q);
					//$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][charges] = $result[charges];
				}else{
					$period_q = "
select
	(select wimax_billing.balance from wimax_billing where id = (select max(wimax_billing.id) from wimax_billing where wimax_billing.parent_id='".$report[data][$accnt[crn_c]][no]."' and wimax_billing.entry_date <= '".$period."')) as balance,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date <= '".$period."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount < 0) group by wimax_billing.parent_id) as charges,
	(select sum(wimax_billing.amount) from wimax_billing where wimax_billing.entry_date <= '".$period."' and wimax_billing.parent_id = '".$report[data][$accnt[crn_c]][no]."' and (wimax_billing.amount > 0) group by wimax_billing.parent_id) as payments
					";
					//echo "2 -> ".$period_q."<br>";
					$result = $myquery->single($period_q);
					//$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][charges] = $result[balance];
				}
				$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][charges] = $result[charges];
				$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][balance] = $result[balance];
				$report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][payments] = $result[payments];
				$report[data][$accnt[crn_c]][cum_payments] += $report[data][$accnt[crn_c]][periods][date_reformat($period,'%b %Y')][payments];
			}
			$i = 0;
			
		}
	}
	
	//print_r($report[data]); echo "<br>";
	
	//Adding the First period to the beginning of the array for Future use
	//Desc order
	array_unshift($report[periods],$to);
	
	//Getting Asc order
	$report[periods_asc] = $report[periods];
	asort($report[periods_asc]);
	
	foreach($report[data] as &$row){
		foreach($report[periods] as $period){
			foreach($report[periods] as $period_1){
				if(strtotime($period_1) <= strtotime($period)){
					$row[periods][date_reformat($period,'%b %Y')][cum_charges_asc] += $row[periods][date_reformat($period_1,'%b %Y')][charges];
				}
			}
		}
		//if($row[no] == '200905-408'){ echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>"; }
		foreach($report[periods_asc] as $period){
			foreach($report[periods] as $period_1){
				if(strtotime($period_1) >= strtotime($period)){
					$row[periods][date_reformat($period,'%b %Y')][cum_charges_desc] += $row[periods][date_reformat($period_1,'%b %Y')][charges];
				}
			}
		}
				
		foreach($report[periods] as $period){

			if((($row[cum_payments] + $row[periods][date_reformat($period,'%b %Y')][cum_charges_asc]) < 0) and ($row[periods][date_reformat($period,'%b %Y')][charges] < 0)){
				if(($row[cum_payments] + $row[periods][date_reformat($period,'%b %Y')][cum_charges_asc]) < $row[periods][date_reformat($period,'%b %Y')][charges]){
					$row[periods][date_reformat($period,'%b %Y')][due] = $row[periods][date_reformat($period,'%b %Y')][charges];
				}else{
					$row[periods][date_reformat($period,'%b %Y')][due] = $row[cum_payments] + $row[periods][date_reformat($period,'%b %Y')][cum_charges_asc];
				}
			}else{
			}
			
			if($report[periods][0] != $period){
				$row[arreas] += $row[periods][date_reformat($period,'%b %Y')][due];
			}
		}
		
	}
	
	return display_new_aging_report($report);
*/
?>