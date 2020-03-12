<?php
function generate_telesales_commission_report($from,$to,$report_type,$user,$msisdn,$item_sold){
	custom_query::select_db('telesales');
	$myquery = new custom_query();

	function commission($product, $count, $unit_charge, $sales_value, $date=FALSE){
		$ratings['Modem'][com_value] = 8500;
		$ratings['Airtime'][com_value] = 0.06;
		$ratings['Phone [Chali]'][com_value] = 4000;
		$ratings['Phone [DaboLine]'][com_value] = 4000;
		$ratings['Phone [DaboLine Slim]'][com_value] = 4000;
		$ratings['Phone [DaboLine Smart]'][com_value] = 4000;
		$ratings['PCO Phone'][com_value] = 0.1;
		$ratings['Special Number'][com_value] = 0.12;
		
		//$ratings['GPRS'][com_value] = 0.05; //$commission[]
		if($product == 'GPRS'){
			if(intval(strtotime($date)) >= strtotime('2013-06-15 00:00:00')){
				if($sales_value == '15000'){
					//NEW AFTER JUN 15TH 2013 VALUES FOR 250MB 1 MONTH CHANGED TO 400MB ` MONTH AT SAME PRICE
					$ratings['GPRS'][com_value] = 0.05;
				}elseif($sales_value == '25000' || $sales_value == '45000' || $sales_value == '100000' || $sales_value == '290000' || $sales_value == '1500000'){
					//NEW AFTER JUN 15TH 2013 VALUES 1 GB REDUCED TO 25K AND 500MB WAS REMOVED
					$ratings['GPRS'][com_value] = 0.1; }
				else{
					$ratings['GPRS'][com_value] = 0;
				}
			}else{
				//OLD BEFORE JUN 15TH 2013 VALUES FOR 250MB 1 MONTH AND 500MB 1 MONTH
				if($sales_value == '15000' || $sales_value == '25000' || $sales_value == '20000'){
					$ratings['GPRS'][com_value] = 0.05;
				}elseif($sales_value == '35000' || $sales_value == '60000' || $sales_value == '100000' || $sales_value == '140000' || $sales_value == '700000' || $sales_value == '300000'){ $ratings['GPRS'][com_value] = 0.1; }
				else{
					$ratings['GPRS'][com_value] = 0;
				}
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
		
		$ratings['CMB Bundle 1(Kapo 3 Months) 50K'][com_value] = 0.04;
		$ratings['CMB Bundle 2(Kapo 6 Months) 100K'][com_value] = 0.04;
		$ratings['CMB Bundle 3(Kapo 12 Months) 200K'][com_value] = 0.04;
		//Off the airtime ie 390 - 225K
		//$ratings['3GWiFi Router'][com_value] = 0.08;
		$ratings['Airtime Chap Chap'][com_value] = 0.06;
		$ratings['Kawa'][com_value] = 0.01;
		$ratings['Pakalast'][com_value] = 0.01;
		$ratings['Fixed Internet Leads'][com_value] = 0.05;
		$ratings['Postpaid'][com_value] = 0.08;
		$ratings['Fixed Lines'][com_value] = 0.15;
		$ratings['Corporate CRBT'][com_value] = 0.08;
		
		$ratings['Modem'][com_type] = 'flat';
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
		
		$ratings['CMB Bundle 1(Kapo 3 Months) 50K'][com_type] = 'percent';
		$ratings['CMB Bundle 2(Kapo 6 Months) 100K'][com_type] = 'percent';
		$ratings['CMB Bundle 3(Kapo 12 Months) 200K'][com_type] = 'percent';
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
		}elseif($ratings[$product][com_type] == 'flat'){
			$commission = $count * $ratings[$product][com_value];
			//print $count.' '.$product.': '.$ratings[$product][com_value].' COM:'.$commission."<br>";
		}else{
			$commission = 0;
		}
		//print $product.'->CNT: '.$count.'->UC: '.$unit_charge.'->CM: '.$commission;
		$product = ''; $count = 0; $unit_charge = 0; $sales_value = 0;
		return $commission;
	}
	
	
	function trend($data){
		
		foreach($data as $row){
			$trenddata[months][date('M-Y' ,strtotime($row[date_entered]))] += $row[sales_value];
			$trenddata[types][$row[sales_type]][date('M-Y' ,strtotime($row[date_entered]))] += $row[sales_value];
			$trenddata[types][All][date('M-Y' ,strtotime($row[date_entered]))] += $row[sales_value];
		}
		
		return $trenddata;
	}
	

	function summarise($data){
		
		$flat_rate_products = array('Phone [Chali]', 'Phone [DaboLine]', 'Phone [DaboLine Slim]', 'Phone [DaboLine Smart]', 'Modem');
		
		foreach($data as $row){
			$summary[products][$row[Product_sold]] = $row[Product_sold];
			
			$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num] += $row[sales_num];
			$summary[grand_totals][products][$row[Product_sold]][total_items] += $row[sales_num];
			//++$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num];
			$summary[agents][$row[Agent]][products][$row[Product_sold]][sales] += $row[sales_value];
			
			//Check for flat rate products and treat the comission differently(not cumulative)
			if(in_array((string) $row[Product_sold],$flat_rate_products,true)){
				$summary[agents][$row[Agent]][products][$row[Product_sold]][salescommission] = commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value], $row[date_entered]);
				
				//$summary[agents][$row[Agent]][totals][salescommission] = commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
				
				//$summary[Grandtotals][products][$row[Product_sold]][totalcommission][$row[Agent]] = commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
			}else{
				$summary[agents][$row[Agent]][products][$row[Product_sold]][salescommission] += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value], $row[date_entered]);
				
				//$summary[agents][$row[Agent]][totals][salescommission] += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
				
				//$summary[Grandtotals][products][$row[Product_sold]][totalcommission][$row[Agent]] += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);
			}
			
			//$total_sales[$row[Agent]] += $row[sales_value];
			
			$summary[agents][$row[Agent]][totals][sales] += $row[sales_value];	
					
/*			$summary[Totals][counts][$row[Product_sold]] += $row[sales_num];
			$summary[Totals][products][$row[Product_sold]][sales] += $row[sales_value];
			
			$summary[Grandtotals][products][$row[Product_sold]][sales] += $row[sales_value];
			
			$summary[Grandtotals][totals][sales] += $row[sales_value];
			$summary[Grandtotals][totals][totalcommission] += commission($row[Product_sold],$summary[agents][$row[Agent]][products][$row[Product_sold]][sales_num],$row[unit_price],$row[sales_value]);*/
		}
/*		foreach($total_sales as $agent=>$total){
			if($total>=2000000 && $total<3000000){ $summary[agents][$agent][Retainer] = 50000; }
			if($total>=3000000 && $total<4000000){ $summary[agents][$agent][Retainer] = 140000; }
			if($total>=4000000 && $total<7000000){ $summary[agents][$agent][Retainer] = 240000; }
			if($total>=7000000){ $summary[agents][$agent][Retainer] = 490000; }
			$summary[agents][$agent][totals][commission] = $summary[agents][$agent][totals][salescommission];
			$summary[agents][$agent][totals][commission] += $summary[agents][$agent][Retainer];

			$summary[Grandtotals][totals][totalcommission][$agent] += $summary[agents][$agent][totals][commission];
			$summary[Grandtotals][totals][Retainer] += $summary[agents][$agent][Retainer];
		}*/
		
		foreach($summary[agents] as $agent=>$agent_row){
			foreach($summary[products] as $product){
				$summary[agents][$agent][totals][total_sales_commission] += $agent_row[products][$product][salescommission];
				$summary[agents][$agent][totals][total_product_sales] += $agent_row[products][$product][sales];
				
				$summary[grand_totals][products][$product][total_sales_commission] += $agent_row[products][$product][salescommission];
				$summary[grand_totals][products][$product][total_sales] += $agent_row[products][$product][sales];
				
			}
			
			$summary[grand_totals][total_sales_commission] += $summary[agents][$agent][totals][total_sales_commission];
			$summary[grand_totals][total_sales] += $summary[agents][$agent][totals][total_product_sales];
				
			$total = $summary[agents][$agent][totals][total_product_sales];
			if($total>=2000000 && $total<3000000){ $summary[agents][$agent][totals][Retainer] = 50000; }
			if($total>=3000000 && $total<4000000){ $summary[agents][$agent][totals][Retainer] = 140000; }
			if($total>=4000000 && $total<7000000){ $summary[agents][$agent][totals][Retainer] = 240000; }
			if($total>=7000000){ $summary[agents][$agent][totals][Retainer] = 490000; }
			$summary[agents][$agent][totals][total_commission] = $summary[agents][$agent][totals][total_sales_commission] +  $summary[agents][$agent][totals][Retainer];
			
			$summary[grand_totals][Retainer] += $summary[agents][$agent][totals][Retainer];
			$summary[grand_totals][total_commission] += $summary[agents][$agent][totals][total_commission];
		}
		
		return $summary;	
	}
	
	//print_r($summary['agents']['Rona Nankya']['products']['Modem']['salescommission']);

	if(!$to){
		$to = date('Y-m-d');
	}
	
	$to_time = '23:59:59';
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	$from_time = '00:00:00';
	
	if($msisdn != ""){
		$msisdn_condition = " AND up_upsell.`name` = '".$msisdn."'";
	}
	
	if($user != ""){
		$username_condition = " AND users.user_name = '".$user."'";
		$username_conditionon = " AND product_user.user_name = '".$user."'";
	}
	
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
			up_product_interest.deleted AS imei,
			up_product_interest.id
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
			AND up_product_interest.activation_date between '".$from."' AND '".$to."'".$msisdn_condition.$username_conditionon;
	
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
			AND tel_items_sold.date_entered BETWEEN date_sub('".$from." ".$from_time."', interval 3 hour) AND date_sub('".$to." ".$to_time."', interval 3 hour)".$msisdn_condition.$username_condition;
	
	$sales[offnets] = $myquery->multiple($query);

	//print nl2br($query).'<hr>';
	//COMBINE SALES
	foreach($sales as $sales_type){
		foreach($sales_type as $row){
			$row[commission] = commission($row[Product_sold],$row[sales_num],$row[unit_price],$row[sales_value], $row[date_entered]);
			$realised_sales[] = $row;
			unset($row);
		}
	}

	
	if(count($realised_sales) == 0){
		return display_telesales_commission_report("NO DATA");
	}
	
	$report[dates][start_date] = date('Y-m-d', strtotime($from));
	$report[dates][end_date] = date('Y-m-d', strtotime($to));
	$report[dates][month] = date('m-Y', strtotime($to));
	//print_r($report[dates]);
	
	switch($report_type){
		case 'summary':
			$report[summary] = summarise($realised_sales);
			break;
		case 'trend':
			$report[trends] = trend($realised_sales);
			break;
		case 'both':
			$report[rows] = $realised_sales;
			$report[summary] = summarise($realised_sales);
			break;
		case 'detail':
		default:
			$_POST[report_type] = 'detail';
			$report[rows] = $realised_sales;
	}

	return display_telesales_commission_report($report);
}

function display_telesales_commission_report($report){

	if($report == "NO DATA"){
		return "There is no data to match the selected filters .... ";
	}
	
	
	$commission_items['Offnet Sales'][0] = 'Airtime';
	$commission_items['Offnet Sales'][1] = 'Airtime Chap Chap';
	$commission_items['Offnet Sales'][2] = 'Special Number';
	$commission_items['Offnet Sales'][3] = 'Phone [DaboLine Smart]';
	$commission_items['Offnet Sales'][4] = 'Phone [DaboLine Slim]';
	$commission_items['Offnet Sales'][5] = 'Phone [DaboLine]';
	$commission_items['Offnet Sales'][6] = 'Phone [Chali]';
	$commission_items['Offnet Sales'][7] = 'PCO Phone';
	$commission_items['Offnet Sales'][8] = 'Modem';
	$commission_items['Offnet Sales'][9] = 'Postpaid';
	$commission_items['Offnet Sales'][10] = 'Fixed Lines';
	$commission_items['Offnet Sales'][11] = 'Fixed Internet Leads';
	
			
	$commission_items['On-net Sales'][0] = 'GPRS';		
	$commission_items['On-net Sales'][1] = 'CMB Old SIM';
	$commission_items['On-net Sales'][2] = 'CMB New SIM';
	$commission_items['On-net Sales'][3] = 'CMB Bundle 1(Kapo 3 Months)';
	$commission_items['On-net Sales'][4] = 'CMB Bundle 2(Kapo 6 Months)';
	$commission_items['On-net Sales'][5] = 'CMB Bundle 3(Kapo 12 Months)';
	$commission_items['On-net Sales'][6] = 'CMB Bundle 1(Kapo 3 Months) 50K';
	$commission_items['On-net Sales'][7] = 'CMB Bundle 2(Kapo 6 Months) 100K';
	$commission_items['On-net Sales'][8] = 'CMB Bundle 3(Kapo 12 Months) 200K';
	$commission_items['On-net Sales'][9] = 'CRBT Activation';
	$commission_items['On-net Sales'][10] = 'CRBT Activation and Download';
	$commission_items['On-net Sales'][11] = 'Pakalast';
	$commission_items['On-net Sales'][12] = 'Kawa';
	$commission_items['On-net Sales'][13] = 'CRBT Download';
	$commission_items['On-net Sales'][14] = 'Corporate CRBT';

	if(count($report[summary]) > 0){

		$html = '
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable"> 
			<tr> 
				<th>AGENT</th>';
		foreach($report[summary][products] as $product){
			$html .= '
				<th>'.$product.' Counts</th>
				<th>'.$product.' Sales</th>
				<th>'.$product.' Commission</th>
			';
		}
		$html .= '
				<th>Total Sales</th>
				<th>Total Sales Commission</th>
				<th>Retainer</th>
				<th>Total Commission</th>
			</tr>
		';
			foreach($report[summary][agents] as $agent=>$agent_row){
				$html .= '
					<tr>
						<th>'.$agent.'</th>';
						foreach($report[summary][products] as $product){
							$html .= '
								<td class="values">'.number_format($agent_row[products][$product][sales_num],0).'</td>
								<td class="values">'.number_format($agent_row[products][$product][sales],0).'</td>
								<td class="values">'.number_format($agent_row[products][$product][salescommission],0).'</td>
							'; 
						}
/*				$html .= '
						<td class="values">'.number_format($agent_row[totals][sales],0).'</td>
						<td class="values">'.number_format($agent_commssion[$agent],0).'</td>
						<td class="values">'.number_format($agent_row[Retainer],0).'</td>
						<td class="values">'.number_format($agent_commssion[$agent] + $agent_row[Retainer],0).'</td>
					</tr>
				';*/
				$html .= '
						<td class="values">'.number_format($agent_row[totals][total_product_sales],0).'</td>
						<td class="values">'.number_format($agent_row[totals][total_sales_commission],0).'</td>
						<td class="values">'.number_format($agent_row[totals][Retainer],0).'</td>
						<td class="values">'.number_format($agent_row[totals][total_commission],0).'</td>
					</tr>
				';
				//$agent_commssion = 0;
			}
			
			$html .='
				</table>
			';
			
			$html .= '<p>Sales/Commision Totals</p>

				<table border="0" cellpadding="1" cellspacing="0" width="100%">
					<tr>
					<th>Products</th>
					<th>Sales total</th>
					<th>Commission</th>
					</tr>';
					
			foreach($report[summary][grand_totals][products] as $product=>$product_totals){
				$html .= '
					<tr>
					<td class="text_values">'.$product.'</th>
					<td class="text_values">'.number_format($product_totals[total_sales],0).'</td>
					<td class="text_values">'.number_format($product_totals[total_sales_commission],0).'</td>
					</tr>
				';
			}
				
			$html .= '
				<tr>
				<th>Totals</th><td class="values">'.number_format($report[summary][grand_totals][total_sales],0).'</td>
				<td class="values">'.number_format($report[summary][grand_totals][total_sales_commission],0).'</td>
				</tr>
				<tr>
				<th>Retainer</th>
				<td class="values">-</td>
				<td class="values">'.number_format($report[summary][grand_totals][Retainer],0).'</td>
				</tr>
				<tr>
				<th>Total Commission</th>
				<td class="values">-</td>
				<td class="values">'.number_format($report[summary][grand_totals][total_commission],0).'</td>
				</tr>
				</table>';
			
			foreach($commission_items as $type => $type_array){
				$html .= '<p>'.$type.'</p>';
				$html .= '<table border="0" cellpadding="1" cellspacing="0" width="100%">
							<tr>
								<th>Item</th>
								<th>Qty</th>
								<th>'.$report[dates][month].'</th>
							</tr>';
				$type_total = 0;
				$type_commission = 0;
				foreach($type_array as $com_item){
					$type_total += $report[summary][grand_totals][products][$com_item][total_sales];
					$type_commission += $report[summary][grand_totals][products][$com_item][total_sales_commission];
					if($report[summary][grand_totals][products][$com_item][total_sales]>0){
						$html .= '
							<tr>
								<td class="text_values">'.$com_item.'</td>
								<td class="values">'.number_format($report[summary][grand_totals][products][$com_item][total_items],0).'</td>
								<td class="values">'.number_format($report[summary][grand_totals][products][$com_item][total_sales],0).'</td>
								</tr>';	
					}
				}
				$html .= '<tr><td class="text_values"><strong>Total</strong></td><td colspan="2" class="values">'.number_format($type_total,0).'</td>';
				$html .= '<tr><td class="text_values">Commission owed</td><td colspan="2" class="values">'.number_format($type_commission,0).'</td>';
				$html .= '</table>';
			}
			
			include("includes/FusionCharts.php");
		
			$strXML  = "";
			$strXML .= "<chart caption='Telesales Revenue By Agent.' xAxisName='Agent' yAxisName='Revenue' numberPrefix='Sh' showValues='0' formatNumberScale='0' showBorder='0'>";
			
			$strXML .= "<categories>";
			foreach($report[summary][agents] as $agent=>$agent_row){
				$strXML .= "<category label='".$agent."' />";				
			}
			$strXML .= "</categories>";
			
			$strXML .= "<dataset seriesName='Sales Totals'>";
			foreach($report[summary][agents] as $agent=>$agent_row){
				$strXML .= "<set label='".$agent."' value='".$agent_row[totals][total_product_sales]."' />";
			}
			$strXML .= "</dataset>";
			
			$strXML .= "<dataset seriesName='Sales Commission' ParentYAxis='s' showValues='0'>";
			foreach($report[summary][agents] as $agent=>$agent_row){
				$strXML .= "<set label='".$agent."' value='".$agent_row[totals][total_commission]."' />";
			}
			$strXML .= "</dataset>";
			$strXML .= "</chart>";
			
			$html .= renderChartHTML("includes/FusionCharts/MSColumn3DLineDY.swf", "", $strXML, "myNext", 1090, 500, false, true);
	}
	
	if(count($report[summary]) > 0 and count($report[rows]) > 0){
		$html .= '<div style="height:20px;"></div>';
	}
	
	if(count($report[rows])> 0){

		$html .= '
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
				<tr>
					<th>#</th>
					<th>SALES ID</th>
					<th>SALES TYPE</th>
					<th>ACTUAL SALE DATE</th>
					<th>AGENT</th>
					<th>MSISDN</th>
					<th>PRODUCT</th>
					<th>IMEI</th>
					<th>QTY</th>
					<th>UNIT PRICE</th>
					<th>SALES VALUE</th>
					<th>COMMISSION</th>
				</tr>
		';
		
		foreach($report[rows] as $row){
			if($row[imei]=='' || $row[imei]==0){ $row[imei]= '&nbsp;'; }
			$html .= '
				<tr>
					<td class="values">'.++$ii.'</td>
					<td class="text_values">'.$row[id].'</td>
					<td class="text_values">'.$row[sales_type].'</td>
					<td class="values">'.$row[date_entered].'</td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="values">'.$row[msisdn].'</td>
					<td class="text_values">'.$row[Product_sold].'</td>
					<td class="text_values">'.$row[imei].'</td>
					<td class="values">'.number_format($row[sales_num],0).'</td>
					<td class="values">'.number_format($row[unit_price],0).'</td>
					<td class="values">'.number_format($row[sales_value],0).'</td>
					<td class="values">'.number_format($row[commission],0).'</td>
				</tr>
			';
		}
		
		$html .= '
			</table>
		';
		
	}
	
	if(count($report[trends])> 0){

		$html .= '<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">';
		
		$html .= '<tr>';
		$html .= '<th>Month</th>';
		$html .= '<th>Sales Value</th>';
		$html .= '</tr>';
		
		foreach($report[trends][months] as $month => $salesvalue){
			$html .= '<tr>';
				$html .= '<td class="text_values">'.$month.'</td>';
				$html .= '<td class="values">'.number_format($salesvalue,0).'</td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		
		include("includes/FusionCharts.php");
		
	$strXML  = "";
	$strXML .= "<chart caption='Telesales By Month Revenue Trend Report.' xAxisName='Month' yAxisName='Revenue' numberPrefix='UGX ' showValues='1'    formatNumberScale='1' showBorder='0'>";
	
	foreach($report[trends][months] as $month => $salesvalue){
		$strXML .= "<categories>
		  				<category label='".$month."' />
					</categories>";
	}
	
	foreach($report[trends][types] as $type => $data){
	
		$strXML .= "<dataset seriesName='".$type."'>";
			foreach($data as $month => $salesvalue){
				$strXML .= "<set label='".$month."' value='".$salesvalue."' />";
			}
		$strXML .= "</dataset>";
	}
	
	$strXML .= "<trendLines>
					<line startValue='60000000' color='009933' displayvalue='Target' /> 
				</trendLines>";

	
	$strXML .= "</chart>";
	
	print $strXML;
	
	$html .= renderChartHTML("includes/FusionCharts/MSLine.swf", "", $strXML, "myNext", 800, 500, false, true);
	return $html;
		
		
	}
	
	return $html;
}


?>