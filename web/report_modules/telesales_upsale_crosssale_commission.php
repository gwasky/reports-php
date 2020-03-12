<?
error_reporting(E_ALL);

function generate_upsale_crossale_commission_report($from,$report_type,$commission,$agents,$flag){
	$myquery = new custom_query();
	if($from){
		$from = substr($from,0,8)."01";
	}else{
		$from = date("Y-m-")."01";
	}
	//$from = '2011-01-10';
	//$to = '2011-10-17';
	$to = last_day($from);
	
	$_POST[from] = $from;
	
	/*
	$ratings[GPRS]['4000']['10'] = 35*22; //$commission[]
	$ratings[GPRS]['15000']['10'] = 2*22;
	$ratings[GPRS]['25000']['10'] = 2*22;
	$ratings[GPRS]['20000']['10'] = 5*22;
	$ratings[GPRS]['60000']['10'] = 1*22;
	$ratings[GPRS]['300000']['10'] = 999999; //Sales are at 10%
	$ratings[GPRS]['600000']['10'] = 999999; //Sales are at 10%
	
	$ratings['CRBT Download']['500']['10'] = 10*22;
	$ratings['CRBT Activation']['500']['10'] = 10*22;
	*/
	
	$query = "
		SELECT
			if(
			   concat(trim(product_user.first_name),' ',trim(product_user.last_name)) IS NOT NULL,
			   concat(trim(product_user.first_name),' ',trim(product_user.last_name)),
			   concat(trim(upsell_assigned_user.first_name),' ',trim(upsell_assigned_user.last_name))
			) AS agent,
			up_product_interest.service_charge,
			up_product_interest.name AS Product_sold,
			up_product_interest.activation_date,
			(count(up_product_interest.service_charge) * up_product_interest.quantity) AS sales_num,
			((count(up_product_interest.service_charge) * up_product_interest.quantity) * up_product_interest.service_charge) AS sales_value
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
			AND up_product_interest.activation_date between '".$from."' and '".$to."'
	";
	if(trim($agents) != ''){
		$agents = trim($agents);
		$query .= "
			AND (trim(users.first_name) LIKE '%".$agents."%' OR trim(users.last_name) LIKE '%".$agents."%')
		";
	}
	$query .= "
		group by
			up_upsell.name,
			up_product_interest.activation_date,
			agent,
			Product_sold,
			up_product_interest.service_charge
	";
	
	//echo nl2br($query);
	custom_query::select_db('telesales');
	$counts = $myquery->multiple($query);
	
	//echo nl2br($query)."<br>".count($counts); exit("hjkh kjhkjh k hkjh<br>");
	
	if(count($counts) == 0){ return display_upsale_crossale_commission_report($report[NO_DATA] = TRUE);}
	
	function details($rows){
		foreach($rows as &$row){
			if($row[agent] != ''){
				//The if is to skip the entries with no productinterest.created_by or upsell.assigned_user_id
				if($row[sales_num] > 1){
					//THE COUNT PER DAY PER MSISDN SHOULD NEVER EXCEED 1.
					$row[sales_value] = $row[service_charge]; $row[sales_num] = 1;
				}
				
				$detail[rows][] = $row;
				$detail[agents][ucwords(strtolower($row[agent]))][$row[Product_sold]][$row[service_charge]] += $row[sales_num];
				
				unset($row);
			}
		}
		return $detail;
	}
	
	function calc_commission($product, $count, $unit_charge){
		$ratings['Modem'][com_value] = 8500;
		$ratings['Airtime'][com_value] = 0.05;
		$ratings['Phone [Chali]'][com_value] = 0;
		$ratings['Phone [DaboLine]'][com_value] = 0.01;
		$ratings['PCO Phone'][com_value] = 0.1;
		$ratings['Special Number'][com_value] = 0.1;
		
		$ratings['GPRS'][com_value] = 0.05; //$commission[]
		$ratings['CMB Old SIM'][com_value] = 0.01;
		$ratings['CMB New SIM'][com_value] = 0.03;
		$ratings['CMB Bundle 1(Kapo 3 Months)'][com_value] = 0.04;
		$ratings['CMB Bundle 2(Kapo 6 Months)'][com_value] = 0.04;
		$ratings['CMB Bundle 3(Kapo 12 Months)'][com_value] = 0.03;
		$ratings['CRBT Activation'][com_value] = 0.02;
		$ratings['CRBT Activation and Download'][com_value] = 0.02;
		$ratings['CRBT Download'][com_value] = 0.02;
		//Off the airtime ie 390 - 225K
		$ratings['3GWiFi Router'][com_value] = 0.08;
		
		$ratings['Modem'][com_type] = 'flat';
		$ratings['Airtime'][com_type] = 'percent';
		$ratings['Phone [Chali]'][com_type] = 'percent';
		$ratings['Phone [DaboLine]'][com_type] = 'percent';
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
		$ratings['3GWiFi Router'][com_type] = 'percent';
		
		if($ratings[$product][com_type] == 'percent'){
			$commission = $count * $unit_charge * $ratings[$product][com_value];
		}else{
			$commission = $count * $ratings[$product][com_value];
		}
		
		return $commission;
	}
	
	function summarise($details,$ratings){		
		//print_r(array_keys($details)); exit("");
		/*
		foreach($details[agents] as $agent=>$agent_row){
			foreach($agent_row as $product=>$product_data){
				foreach($product_data as $service_charge=>$sales_num){
					$summary[agents][$agent][$product][$service_charge][num] += $sales_num;
					$summary[agents][$agent][$product][$service_charge][value] += ($sales_num * $service_charge);
					
					if($summary[agents][$agent][$product][$service_charge][num] <= $ratings[$product][$service_charge][10]){
						$summary[agents][$agent][$product][$service_charge][rates][10] = $summary[agents][$agent][$product][$service_charge][num];
					}else{
						//THIS SCENARIO SHALL NEVER HAPPEN SINCE WE NOW HAVE A FLAT RATE FOR ALL SALES HENCE THE BIG NO 999999
						$summary[agents][$agent][$product][$service_charge][rates][10] = $ratings[$product][$service_charge][10];
						$summary[agents][$agent][$product][$service_charge][rates][20] = ($summary[agents][$agent][$product][$service_charge][num] - $ratings[$product][$service_charge][10]);
					}
					
					$summary[agents][$agent][$product][$service_charge][com] = ($summary[agents][$agent][$product][$service_charge][rates][10] * ($service_charge/1.18) * 0.1) + ($summary[agents][$agent][$product][$service_charge][rates][20] * ($service_charge/1.18) * 0.2);
					
					//Totals
					$summary[Totals][commission][$agent] += $summary[agents][$agent][$product][$service_charge][com];
					$summary[Totals][sales_values][$agent] += ($sales_num * $service_charge);
				}
			}
		}
		*/
		
		foreach($details[agents] as $agent=>$agent_row){
			foreach($agent_row as $product=>$product_data){
				foreach($product_data as $service_charge=>$sales_num){
					$summary[agents][$agent][$product][$service_charge][num] += $sales_num;
					$summary[agents][$agent][$product][$service_charge][value] += ($sales_num * $service_charge);
					
					$summary[agents][$agent][$product][$service_charge][com] = calc_commission($product, $summary[agents][$agent][$product][$service_charge][num], $service_charge/1.18);
					
					//Totals
					$summary[Totals][commission][$agent] += $summary[agents][$agent][$product][$service_charge][com];
					$summary[Totals][sales_values][$agent] += ($sales_num * $service_charge);
				}
			}
		}
		
		return $summary;
	}
	
	switch($report_type){
		case 'detail':
			$report[detail] = details($counts);
			break;
		case 'both':
			$report[detail] = details($counts);
			$report[summary] = summarise($details=$report[detail],$ratings);
			$report[summary_brief] = $report[summary];
			break;
		case 'summary':
			$report[summary] = summarise($details=details($counts),$ratings);
			break;
		case 'summary_brief':
		default:
			$report[summary_brief] = summarise($details=details($counts),$ratings);
			break;
	}
	
	if(isset($flag)){
		return $report;
	}else{ 
		return display_upsale_crossale_commission_report($report); 
	}
}

function display_upsale_crossale_commission_report($report){
	
	if($report[NO_DATA]){ return "There is No data to match your filters"; }
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	
	if($report[summary_brief]){
		$html .= '
			<tr>
				<th>SUMMARY - BRIEF</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
					<tr>
						<th>AGENT</th>
						<th>SALES VALUES</th>
						<th>COMMISSION</th>
					</tr>
		';
		
		foreach($report[summary_brief][agents] as $agent=>$agent_data){
			$html .= '
					<tr>
						<td class="text_values">'.$agent.'</td>
						<td class="values">'.number_format($report[summary_brief][Totals][sales_values][$agent],0).'</td>
						<td class="values">'.number_format($report[summary_brief][Totals][commission][$agent],2).'</td>
					</tr>
			';
		}
		
		$html .= '
				</table>
				<td>
			</tr>
		';
	}
	
	if(($report[summary_brief]) and (($report[summary_detail]) or ($report[detail]))){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if($report[summary]){
		$html .= '
			<tr>
				<th>SUMMARY</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<th>AGENT</th>
						<th>PRODUCT</th>
						<th>CHARGE</th>
						<th>SALES NUMBERS</th>
						<th>SALES VALUES</th>
						<th>10% SALES NUMBERS</th>
						<th>20% SALES NUMBERS</th>
						<th>COMMISSION</th>
					</tr>
		';
		
		foreach($report[summary][agents] as $agent=>$agent_data){
			foreach($agent_data as $product=>$product_data){
				foreach($product_data as $service_charge=>$service_charge_data){
					$html .= '
					<tr>
						<td class="text_values">'.$agent.'</td>
						<td class="text_values">'.$product.'</td>
						<td class="values"><!--'.number_format(($service_charge/1.18),2).'--> '.number_format($service_charge,0).'</td>
						<td class="values">'.number_format($service_charge_data[num],0).'</td>
						<td class="values">'.number_format($service_charge_data[value],0).'</td>
						<td class="values">'.number_format($service_charge_data[rates][10],0).'</td>
						<td class="values">'.number_format($service_charge_data[rates][20],0).'</td>
						<td class="values">'.number_format($service_charge_data[com],2).'</td>
					</tr>
					';
				}
			}
			$html .= '
					<tr id="totals">
						<td colspan="4" class="text_values">'.$agent.'</td>
						<td class="values">'.number_format($report[summary][Totals][sales_values][$agent],2).'</td>
						<td colspan="3" class="values">'.number_format($report[summary][Totals][commission][$agent],2).'</td>
					</tr>
			';
		}
		
		$html .= '
				</table>
				<td>
			</tr>
		';
	}
	
	if(($report[summary]) and ($report[detail])){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if($report[detail]){
		$html .= '
			<tr>
				<th>DETAILS</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
					<tr>
						<th></th>
						<th>DATE</th>
						<th>AGENT</th>
						<th>PRODUCT</th>
						<th>CHARGE</th>
						<th>SALES NUMBERS</th>
						<th>SALES VALUES</th>
					</tr>
		';
		
		foreach($report[detail][rows] as $row){
			$html .= '
				<tr>
					<td class="text_values">'.++$ww.'</td>
					<td class="text_values">'.$row[activation_date].'</td>
					<td class="text_values">'.$row[agent].'</td>
					<td class="text_values">'.$row[Product_sold].'</td>
					<td class="values">'.number_format($row[service_charge],0).'</td>
					<td class="values">'.number_format($row[sales_num],0).'</td>
					<td class="values">'.number_format($row[sales_value],0).'</td>
				</tr>
			';
		}
		
		$html .= '
				</table>
				<td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}

error_reporting(E_ERROR);
?>