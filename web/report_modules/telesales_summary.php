<?php
	function generate_telesales_summary($from,$to){
	
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		$query = 'SELECT
				tel_items_sold.name as product,
				tel_items_sold.total_price as amount,
				tel_items_sold.actual_pick_date as sale_date,
				tel_items_sold.date_entered as creation_data
				FROM
				tel_customer_details
				Inner Join tel_customel_items_sold_c ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
				Inner Join tel_items_sold ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
';
		if($from){
		$query .= " AND tel_items_sold.actual_pick_date >= '".$from."' "; }
		else { 
		$query .= " AND tel_items_sold.actual_pick_date >= '".date('Y-m-d')."'"; }
	
		if($to){
		$query .= " AND tel_items_sold.actual_pick_date <= '".$to."'";
		}else { 
		$query .= " AND tel_items_sold.actual_pick_date <= '".date('Y-m-d')."'"; }
		
		$items = $myquery->multiple($query);
		foreach($items as $row){
			$data[sales_totals_by_date][$row[sale_date]][$row[product]] += $row[amount];
			$data[sales_totals_by_month][substr($row[sale_date],0,7)][$row[product]] += $row[amount];
			++$data[sales_counts_by_date][$row[sale_date]][$row[product]];
		}
		unset($items);
		$sweet_query = "
				SELECT
					tel_warid_number.name as number,
					left(tel_warid_number.date_entered,10) as sale_date,
					tel_warid_number.type_of_no as type_of_no
					FROM
					tel_customer_details
					Inner Join tel_customewarid_number_c ON tel_customer_details.id = tel_customewarid_number_c.tel_customeba1details_ida
					Inner Join tel_warid_number ON tel_customewarid_number_c.tel_custom6347_number_idb = tel_warid_number.id 
					 and tel_warid_number.deleted = 0 and tel_warid_number.deleted = 0
		";
		if($from){
		$sweet_query .= " AND date(date_add(tel_warid_number.date_entered, interval 3 hour)) >= '".$from."' "; }
		else { 
		$sweet_query .= " AND date(date_add(tel_warid_number.date_entered, interval 3 hour)) >= '".date('Y-m-d')."'"; }
	
		if($to){
		$sweet_query .= " AND date(date_add(tel_warid_number.date_entered, interval 3 hour)) <= '".$to."'";
		}else { 
		$sweet_query .= " AND date(date_add(tel_warid_number.date_entered, interval 3 hour)) <= '".date('Y-m-d')."'"; }
		
		$numbers = $myquery->multiple($sweet_query);
		foreach($numbers as $row){
			++$data[warid_nos][$row[sale_date]][$row[type_of_no]];
		}
		
		unset($numbers);
		$off_net_calls_query = 'SELECT
						concat(users.first_name," ",users.last_name) AS Agent,
						tel_customer_details.name AS msisdn,
						tel_customer_details.customer_name,
						tel_calls.reason_for_rejection,
						tel_calls.call_success,
						tel_calls.id as id,
						left(date(date_add(tel_calls.date_entered, interval 3 hour)),10) as date_entered
						FROM
						tel_customer_details
						Inner Join users ON tel_customer_details.assigned_user_id = users.id
						Inner Join tel_customels_tel_calls_c ON tel_customer_details.id = tel_customels_tel_calls_c.tel_custom49aadetails_ida
						Inner Join tel_calls ON tel_customels_tel_calls_c.tel_customce50l_calls_idb = tel_calls.id
						WHERE
						tel_customer_details.deleted =  0 
						AND tel_customels_tel_calls_c.deleted =  0 
						AND tel_calls.deleted =  0
						';
		  	if($from){
			$off_net_calls_query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) >= '".$from."'";
				}else { $query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) >= '".date('Y-m-d')."'"; }
				
				if($to){
					$off_net_calls_query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) <= '".$to."'";
				}else { $off_net_calls_query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) <= '".date('Y-m-d')."'"; }
				
				$off_net_calls = $myquery->multiple($off_net_calls_query);
				foreach($off_net_calls as $row){
					++$data[callsbyDate][off_net_calls_by_date][$row[date_entered]];
					++$data[callsbyAgent][off_net_calls_by_caller][$row[Agent]];
					++$data[callsbySucess][off_net_calls_by_status][$row[call_success]];
				}
			unset($off_net_calls);
		$onnet_sales_query = '
			SELECT
				concat(users.first_name," ",users.last_name) AS Agent,
				up_call_up.name,
				date(date_add(up_call_up.date_entered, interval 3 hour)) as date_entered,
				up_upsell.name,
				up_call_up.call_success
			FROM
				up_upsell
				Inner Join up_upsell_up_call_up_c ON up_upsell.id = up_upsell_up_call_up_c.up_upsell_e0eb_upsell_ida
				Inner Join up_call_up ON up_upsell_up_call_up_c.up_upsell_09c6call_up_idb = up_call_up.id
				Inner Join users ON up_upsell.assigned_user_id = users.id
		';
		
		if($from){
			$onnet_sales_query .= " AND date(date_add(up_call_up.date_entered, interval 3 hour)) >= '".$from."'";
		}else{
			$onnet_sales_query .= " AND date(date_add(up_call_up.date_entered, interval 3 hour)) >= '".date('Y-m-d')."'";
		}
				
		if($to){
			$onnet_sales_query .= " AND date(date_add(up_call_up.date_entered, interval 3 hour)) <= '".$to."'";
		}else{
			$onnet_sales_query .= " AND date(date_add(up_call_up.date_entered, interval 3 hour)) <= '".date('Y-m-d')."'";
		}
	
		$on_net_calls = $myquery->multiple($onnet_sales_query);
		
		foreach($on_net_calls as $row){
				++$data[callsbyDate][on_net_calls_by_date][$row[date_entered]];
				++$data[callsbyAgent][on_net_calls_by_caller][$row[Agent]];
				++$data[callsbySucess][on_net_calls_by_status][$row[call_success]];
		}
		
		$data[callsbyDate] = call_summation($data[callsbyDate]);
		$data[callsbyAgent] = call_summation($data[callsbyAgent]);
		$data[callsbySucess] = call_summation($data[callsbySucess]);
		//print_r($data[callsbyAgent]);
		//unset($on_net_calls);
		
		return display_telesales_summary($data);
		
	}
	
	function display_telesales_summary($report){
		
				if(count($report[callsbyDate])>0){
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> CALLS BY DATE </th>
					</tr>
					<tr>
						<th> State</th>
						<th> Count</th>
						</tr>';
							foreach($report[callsbyDate] as $date=>$value){
							$html .= '<tr>
											<td class="text_values">'.$date.'</td>
											<td class="text_values">'.number_format($value).'</td>
									</tr>';
							}
					$html .= '</table>';
				}
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				if(count($report[callsbySucess])>0){
				$value = 0;
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> CALL SUCCESS</th>
					</tr>
					<tr>
						<th>State</th>
						<th>Count</th>
					</tr>';
							foreach($report[callsbySucess] as $status=>$value){
							$html .= '<tr>
											<td class="text_values">'.$status.'</td>
											<td class="text_values">'.number_format($value).'</td>
									</tr>';
							}
					$html .= '</table>';
				}
				if(count($report[callsbyAgent])>0){
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="3"> CALLS BY AGENT</th>
					</tr>
					<tr>
						<th></th>
						<th> Agent Name</th>
						<th> Count</th>
						</tr>';
							foreach($report[callsbyAgent] as $agent=>$value){
							++$i;
							$html .= '<tr>
											<td class="text_values">'.$i.'</td>
											<td class="text_values">'.$agent.'</td>
											<td class="text_values">'.number_format($value).'</td>
									</tr>';
							}
							unset($i);
					$html .= '</table>';
				}
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				if(count($report[callsbySucess])>0){
				$value = 0;
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> CALL SUCCESS</th>
					</tr>
					<tr>
						<th>State</th>
						<th>Count</th>
					</tr>';
							foreach($report[callsbySucess] as $status=>$value){
							$html .= '<tr>
											<td class="text_values">'.$status.'</td>
											<td class="text_values">'.number_format($value).'</td>
									</tr>';
							}
					$html .= '</table>';
				}
				
				if(count($report[sales_totals_by_date])>0){
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				$value = 0;
				
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> OFF NET SALES BY MONTH</th>
					</tr>
					<tr>
						<th>PRODUCT</th>
						<th>TOTAL AMOUNT</th>
					</tr>';

							foreach($report[sales_totals_by_month] as $month=>$product_array){
							
							$html .= '<tr>
										<th colspan="2">'.$month.'</th>
									  </tr>';
									  $total=0;
									  foreach($product_array as $product=>$value){
											
											$html .= '<tr>
												<td class="text_values">'.$product.'</td>
												<td class="text_values">'.number_format($value).'</td>';
												$total += $value;
									  			$value=0;
												$html .= '</tr>';
									  }
									  $html .= '</tr>
									 		 	<tr>
									  			<td class="text_values">Total</td>
												<td class="text_values">'.number_format($total).'</td>
									  			</tr>';
							}
					$html .= '</table>';
				
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> OFF NET SALES BY DATE</th>
					</tr>
					<tr>
						<th>PRODUCT</th>
						<th>TOTAL AMOUNT</th>
					</tr>';

							foreach($report[sales_totals_by_date] as $date=>$product_array){
							
							$html .= '<tr>
										<th colspan="2">'.$date.'</th>
									  </tr>';
									  $total=0;
									  foreach($product_array as $product=>$value){
											
											$html .= '<tr>
												<td class="text_values">'.$product.'</td>
												<td class="text_values">'.number_format($value).'</td>';
												$total += $value;
									  			$value=0;
												$html .= '</tr>';
									  }
									  $html .= '</tr>
									 		 	<tr>
									  			<td class="text_values">Total</td>
												<td class="text_values">'.number_format($total).'</td>
									  			</tr>';
							}
					$html .= '</table>';
					
				}
				
				if(count($report[sales_counts_by_date])>0){
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				$value = 0;
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> OFF NET SALES COUNTS</th>
					</tr>
					<tr>
						<th>PRODUCT</th>
						<th>TOTAL AMOUNT</th>
					</tr>';

							foreach($report[sales_counts_by_date] as $date=>$product_array){
							
							$html .= '<tr>
										<th colspan="2">'.$date.'</th>
									  </tr>';
									  $total=0;
									  foreach($product_array as $product=>$value){
											
											$html .= '<tr>
												<td class="text_values">'.$product.'</td>
												<td class="text_values">'.number_format($value).'</td>';
												$total += $value;
									  			$value=0;
									  }
							}
					$html .= '</table>';
				}
				
				
				if(count($report[warid_nos])>0){
				$html .= ' <tr>
						<th colspan="2">&nbsp;</th>
					</tr>';
				$value = 0;
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
					<tr>
						<th colspan="2"> WARID NUMBER SALES</th>
					</tr>
					<tr>
						<th>PRODUCT</th>
						<th>TOTAL AMOUNT</th>
					</tr>';

							foreach($report[warid_nos] as $date=>$number_type_array){
							
							$html .= '<tr>
										<th colspan="2">'.$date.'</th>
									  </tr>';
									  $total=0;
									  foreach($number_type_array as $number_type=>$value){
											
											$html .= '<tr>
												<td class="text_values">'.$number_type.'</td>
												<td class="text_values">'.number_format($value).'</td>';
												$total += $value;
									  			$value=0;
									  }
							}
					$html .= '</table>';
				}
				
				
				
				return $html;
	}

function call_summation($calls_array){
	$sumArray = array();
			foreach($calls_array as $k=> $subArray) {
			  foreach ($subArray as $id=>$value) {
				$sumArray[$id] += $value;
			  }
			}
	return $sumArray;
}


?>