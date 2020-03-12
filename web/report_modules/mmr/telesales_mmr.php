<?php
	function generate_telesales_mmr($period){
		
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		
		$to = last_day($period);
		$from = substr($period,0,8)."01";
		
		
	function commission($product, $count, $unit_charge, $sales_value){
		$ratings['Modem'][com_value] = 0.1;
		$ratings['Airtime'][com_value] = 0.06;
		$ratings['Phone [Chali]'][com_value] = 4000;
		$ratings['Phone [DaboLine]'][com_value] = 4000;
		$ratings['Phone [DaboLine Slim]'][com_value] = 4000;
		$ratings['Phone [DaboLine Smart]'][com_value] = 4000;
		$ratings['PCO Phone'][com_value] = 0.1;
		$ratings['Special Number'][com_value] = 0.12;
		
		//$ratings['GPRS'][com_value] = 0.05; //$commission[]
		if($product == 'GPRS'){
			if($sales_value == '15000' || $sales_value == '20000' || $sales_value == '25000' || $sales_value == '600000'){
				$ratings['GPRS'][com_value] = 0.05;
			}elseif($sales_value == '35000' || $sales_value == '60000' || $sales_value == '100000' || $sales_value == '140000' || $sales_value == '700000' || $sales_value == '300000'){ $ratings['GPRS'][com_value] = 0.1; }
			else{
				$ratings['GPRS'][com_value] = 0;
			}
		}
		
		$ratings['CMB Old SIM'][com_value] = 0.01;
		$ratings['CMB New SIM'][com_value] = 0.03;
		$ratings['CMB Bundle 1(Kapo 3 Months)'][com_value] = 0.04;
		$ratings['CMB Bundle 2(Kapo 6 Months)'][com_value] = 0.04;
		$ratings['CMB Bundle 3(Kapo 12 Months)'][com_value] = 0.04;
		$ratings['CRBT Activation'][com_value] = 0.02;
		$ratings['CRBT Activation and Download'][com_value] = 0.02;
		$ratings['CRBT Download'][com_value] = 0.02;
		//Off the airtime ie 390 - 225K
		//$ratings['3GWiFi Router'][com_value] = 0.08;
		$ratings['Airtime Chap Chap'][com_value] = 0.06;
		$ratings['Kawa'][com_value] = 0.01;
		$ratings['Pakalast'][com_value] = 0.01;
		$ratings['Fixed Internet Leads'][com_value] = 0.05;
		$ratings['Postpaid'][com_value] = 0.08;
		$ratings['Fixed Lines'][com_value] = 0.15;
		$ratings['Corporate CRBT'][com_value] = 0.08;
		
		$ratings['Modem'][com_type] = 'percent';
		$ratings['Airtime'][com_type] = 'percent';
		$ratings['Phone [Chali]'][com_type] = 'flat';
		$ratings['Phone [DaboLine]'][com_type] = 'flat';
		$ratings['Phone [DaboLine Slim]'][com_type] = 'flat';
		$ratings['Phone [DaboLine Smart]'][com_type] = 'flat';
		$ratings['PCO Phone'][com_type] = 'percent';
		$ratings['Special Number'][com_type] = 'percent';
		
		$ratings['GPRS'][com_type] = 'percent';
		
		$ratings['CMB Old SIM'][com_type] = 'percent';
		$ratings['CMB New SIM'][com_type] = 'percent';
		$ratings['CMB Bundle 1(Kapo 3 Months)'][com_type] = 'percent';
		$ratings['CMB Bundle 2(Kapo 6 Months)'][com_type] = 'percent';
		$ratings['CMB Bundle 3(Kapo 12 Months)'][com_type] = 'percent';
		$ratings['CRBT Activation'][com_type] = 'percent';
		$ratings['CRBT Activation and Download'] [com_type]= 'percent';
		$ratings['CRBT Download'][com_type] = 'percent';
		//Off the airtime ie 390 - 225K
		//$ratings['3GWiFi Router'][com_type] = 'percent';
		$ratings['Airtime Chap Chap'][com_type] = 'percent';
		$ratings['Kawa'][com_type] = 'percent';
		$ratings['Pakalast'][com_type] = 'percent';
		$ratings['Fixed Internet Leads'][com_type] = 'percent';
		$ratings['Postpaid'][com_type] = 'percent';
		$ratings['Fixed Lines'][com_type] = 'percent';
		$ratings['Corporate CRBT'][com_type] = 'percent';
		//print_r($ratings).'<hr>';
		if($ratings[$product][com_type] == 'percent'){
		
			/*if($product=='GPRS'){
			$commission = $unit_charge * $ratings[$product][com_value];
			}else{
			$commission = ($count * $unit_charge) * $ratings[$product][com_value];
			}*/
			//$commission = $unit_charge * $ratings[$product][com_value];
			//if($product=='3GWiFi Router'){ $sales_value = 280000; }
			$commission = $sales_value * $ratings[$product][com_value];
		}else{
			$commission = $count * $ratings[$product][com_value];
			//print $count.' '.$product.': '.$ratings[$product][com_value].' COM:'.$commission."<br>";
		}
		//print $product.'->CNT: '.$count.'->UC: '.$unit_charge.'->CM: '.$commission;
		return $commission;
	}
		
	$to_time = '23:59:59';
	$from_time = '00:00:00';
		
		/*$query = 'SELECT
				tel_items_sold.name as product,
				tel_items_sold.total_price as amount,
				tel_items_sold.date_entered as sale_date,
				tel_items_sold.date_entered as creation_data
				FROM
				tel_customer_details
				Inner Join tel_customel_items_sold_c ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
				Inner Join tel_items_sold ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
';

		$query .= " AND tel_items_sold.date_entered BETWEEN date_sub('".$from." ".$from_time."', interval 3 hour) AND date_sub('".$to." ".$to_time."', interval 3 hour)";

		
		$items = $myquery->multiple($query);
		foreach($items as $row){
			$data[sales_totals_by_date][$row[sale_date]][$row[product]] += $row[amount];
			$data[sales_totals_by_month][substr($row[sale_date],0,7)][$row[product]][amounts] += $row[amount];
			$data[sales_totals_by_month][substr($row[sale_date],0,7)][$row[product]][commission] += commission($row[product], $row[amount]);
			++$data[sales_counts_by_date][$row[sale_date]][$row[product]];
		}*/
		
		
		
			//Queries
	$query = "
		SELECT
			'ONNET SALES' AS sales_type,
			up_product_interest.activation_date AS date_entered,
			if(
			   concat(trim(product_user.first_name),' ',trim(product_user.last_name)) IS NOT NULL,
			   concat(trim(product_user.first_name),' ',trim(product_user.last_name)),
			   concat(trim(upsell_assigned_user.first_name),' ',trim(upsell_assigned_user.last_name))
			)AS Agent,
			up_upsell.`name` AS msisdn,
			up_product_interest.name AS Product_sold,
			up_product_interest.quantity AS sales_num,
			up_product_interest.service_charge as unit_price,
			up_product_interest.service_charge * up_product_interest.quantity AS sales_value,
			up_product_interest.deleted AS imei
		FROM
			up_product_interest
			LEFT OUTER JOIN users product_user ON (up_product_interest.created_by = product_user.id)
			LEFT OUTER JOIN up_upsell_uuct_interest_c ON up_upsell_uuct_interest_c.up_upsell_87d5nterest_idb = up_product_interest.id
			LEFT OUTER JOIN up_upsell ON up_upsell.id = up_upsell_uuct_interest_c.up_upsell_47e3_upsell_ida
			LEFT OUTER JOIN users upsell_assigned_user ON (up_upsell.assigned_user_id = upsell_assigned_user.id)
		WHERE
			up_product_interest.deleted = 0
			AND up_upsell.deleted=0 
			AND up_upsell_uuct_interest_c.deleted = 0
			AND up_product_interest.service_charge IS NOT NULL
			AND up_product_interest.service_charge > 0
			AND up_product_interest.activation_date between '".$from."' AND '".$to."'
	";
	
	$sales[onnets] = $myquery->multiple($query);
	
	//print nl2br($query).'<hr>';
	
	//exit("<br>Leaving ...");
	
	$query = "
		SELECT
			'OFFNET SALES' AS sales_type,
			date(date_add(tel_items_sold.date_entered, interval 3 hour)) AS date_entered,
			concat(users.first_name,' ',users.last_name) AS Agent,
			tel_customer_details.name AS msisdn,
			tel_items_sold.name AS Product_sold,
			(tel_items_sold.total_price/tel_items_sold.unit_price) AS sales_num,
			tel_items_sold.unit_price,
			tel_items_sold.total_price AS sales_value,
			tel_items_sold.imei AS imei
		FROM
			tel_items_sold
			Inner Join tel_customel_items_sold_c ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
			Inner Join tel_customer_details ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
			Inner Join users ON tel_customer_details.assigned_user_id = users.id
		WHERE
			tel_customel_items_sold_c.deleted = 0 and tel_customer_details.deleted = 0 and tel_customel_items_sold_c.deleted = 0 
			AND tel_items_sold.date_entered BETWEEN date_sub('".$from." ".$from_time."', interval 3 hour) AND date_sub('".$to." ".$to_time."', interval 3 hour)
	";
	
	$sales[offnets] = $myquery->multiple($query);
	//print nl2br($query).'<hr>';
	//COMBINE SALES
	foreach($sales as $sales_type){
		foreach($sales_type as $row){
			$row[commission] = commission($row[Product_sold],$row[sales_num],$row[unit_price],$row[sales_value]);
			$realised_sales[] = $row;
			unset($row);
		}
	}
	
	if(count($realised_sales) == 0){
		return display_telesales_commission_report("NO DATA");
	}
		
	
	foreach($realised_sales as $row){
			$data[sales_type][$row[sales_type]][sales_totals_by_date][$row[date_entered]][$row[Product_sold]] += $row[sales_value];
			$data[sales_type][$row[sales_type]][sales_totals_by_month][substr($row[date_entered],0,7)][$row[Product_sold]][amounts] += $row[sales_value];

			$data[sales_type][$row[sales_type]][sales_totals_by_month][substr($row[date_entered],0,7)][$row[Product_sold]][commission] += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
			++$data[sales_type][$row[sales_type]][sales_counts_by_date][$row[date_entered]][$row[Product_sold]];
			
			$total_sales_value += $row[sales_value];
			$total_commission += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
			
			unset($row);
		}
		
		unset($realised_sales);
		
		//echo '<textarea rows=50 cols=50>'.print_r($sales[onnets],true).'</textarea>';
		//unset($on_net_calls);
		
		return display_telesales_mmr($data, $total_sales_value, $total_commission);
		
	}
	
	function display_telesales_mmr($report, $total_sales_value, $total_commission){
				if(count($report)>0){		
				
					$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
						<tr>
							<th>Total Sales Value</th>
							<th>Total Commission</th>
						</tr>
						<td class="text_values">'.number_format($total_sales_value).'</td>
						<td class="text_values">'.number_format($total_commission).'</td>
						</table><br>';
				
						
					foreach($report[sales_type] as $sales_type => $rows){
						$html .= '<table border="0" cellpadding="2" cellspacing="0" width="400px">
						<tr>
							<th colspan="2"> '.$sales_type.' BY MONTH(Gross sales by item)</th>
						</tr>
						';
	
								foreach($report[sales_type][$sales_type][sales_totals_by_month] as $month=>$product_array){
								$html .= '<tr>
											<th colspan="2">'.$month.'</th>
										  </tr>
										  <tr>
										  	<!--<th>Sale Type</th>-->
											<th>Item</th>
											<th colspan=2>'.date("M", mktime(0, 0, 0, substr($month,5,2), 10)).' - '.substr($month,2,2).'</th>
										</tr>';
										  $total=0;
										  
										  foreach($product_array as $product=>$value){
	
												$html .= '<tr>
													<!--<td class="text_values">'.$sales_type.'</td>-->
													<td class="text_values">'.$product.'</td>
													<td class="text_values">'.number_format($value[amounts]).'</td>
													<td class="text_values">'.number_format($value[commission]).'</td>';
													$total += $value[amounts];
													$value[amounts]=0;
													$totalcommission += $value[commission];
													$value[commission]=0;
													$html .= '</tr>';
										  }
										  $html .= '</tr>
													<tr>
													<td class="text_values">Total</td>
													<td class="text_values">'.number_format($total).'</td>
													<td class="text_values">'.number_format($totalcommission).'</td>
													</tr>';
								}
						$html .= '</table>';
						
					}
				}
				
	return $html;			
}


?>