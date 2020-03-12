<?php
date_default_timezone_set('Africa/Nairobi');

function generate_wpesacourier_report($from,$to){
	
	$myquery = new custom_query();
	custom_query::select_db('ccba01.waridpesacouriers'); 
	
	//IF NO DATE SET, GET YESTO'S DATA
	if($from == '') { $from = date('Y-m-d', strtotime("-1 days")); }
	if($to == ''){ $to = $from; }
	
	$query = "
		SELECT
			deliveries.deliveriesid,
			deliveries.telecom,
			company.company_region,
			company.company_name,
			company.company_location,
			bills.customer_billnum,
			bills.retailer_billnum,
			deliveries.customer_forms,
			deliveries.retailer_forms,
			deliveries.delivery_time,
			couriers.couriers_name,
			deliveries.entry_date,
			company.companyid,
			bills.billsid,
			couriers.couriersid,
			deliveries.enteredby
		FROM
			company
			INNER JOIN bills ON company.companyid = bills.companyid
			INNER JOIN deliveries ON bills.deliveriesid = deliveries.deliveriesid
			INNER JOIN couriers ON deliveries.couriersid = couriers.couriersid
		WHERE
			deliveries.entry_date BETWEEN '".$from."' AND '".$to."'
	";
	
	$rows = $myquery->multiple($query);
	
	if(count($rows) == 0) { return display_wpesacourier_report(array('period'=>$from,'data'=>"NO DATA")); }

	foreach($rows as $row){

		if(strlen($row[delivery_time])==4){
			$row[delivery_time] = '0'.$row[delivery_time];
		}
		
		$report[data][$row[telecom]][$row[company_region]][] = $row;
		$report[totals][$row[telecom]][$row[company_region]][customer_forms] += $row[customer_forms];
		$report[totals][$row[telecom]][$row[company_region]][retailer_forms] += $row[retailer_forms];	
	}
	
	foreach($report[totals] as $telecom => $telecomdata){
		foreach($telecomdata as $company_region=>$forms){
			$report[totals][$telecom][grand_total][customer_forms] += $forms[customer_forms];
			$report[totals][$telecom][grand_total][retailer_forms] += $forms[retailer_forms];
			$report[total_all][customer_forms] += $forms[customer_forms];
			$report[total_all][retailer_forms] += $forms[retailer_forms];
		}
	}

	//exit(print_r($rows));
	
	
	//MONTHLY TOTALS
	$first_of_month = date("Y-m-d", strtotime(date("Y",strtotime($to)).'-'.date("m",strtotime($to)).'-01'));
	$query_monthly = "
		SELECT
			deliveries.deliveriesid,
			deliveries.telecom,
			deliveries.kyc_type,
			company.company_region,
			company.company_name,
			company.company_location,
			bills.customer_billnum,
			bills.retailer_billnum,
			deliveries.customer_forms,
			deliveries.retailer_forms,
			deliveries.delivery_time,
			couriers.couriers_name,
			deliveries.entry_date,
			company.companyid,
			bills.billsid,
			couriers.couriersid,
			deliveries.enteredby
		FROM
			company
			INNER JOIN bills ON company.companyid = bills.companyid
			INNER JOIN deliveries ON bills.deliveriesid = deliveries.deliveriesid
			INNER JOIN couriers ON deliveries.couriersid = couriers.couriersid
		WHERE
			deliveries.entry_date BETWEEN '".$first_of_month."' AND '".$to."'
	";
	
	$rows_monthly = $myquery->multiple($query_monthly);
	
	foreach($rows_monthly as $row_monthly){
		$report[monthly_totals][$row_monthly[telecom]][$row_monthly[company_region]][customer_forms] += $row_monthly[customer_forms];
		$report[monthly_totals][$row_monthly[telecom]][$row_monthly[company_region]][retailer_forms] += $row_monthly[retailer_forms];
		$report[monthly_totals][$row_monthly[telecom]][$row_monthly[company_region]][kyc_type][$row_monthly[kyc_type]] += $row_monthly[customer_forms];	
		$report[monthly_totals][$row_monthly[telecom]][$row_monthly[company_region]][kyc_type][$row_monthly[kyc_type]] += $row_monthly[retailer_forms];
		
		$report[kyc_types][$row_monthly[kyc_type]] = $row_monthly[kyc_type];
	}
	
	//exit('<pre>'.print_r($report[kyc_types],true).'</pre>');
	
	foreach($report[monthly_totals] as $telecom => $telecomdata){
		foreach($telecomdata as $region_monthy=>$forms_monthly){
		
			$report[monthly_grand_total][$telecom][customer_forms] += $forms_monthly[customer_forms];
			$report[monthly_grand_total][$telecom][retailer_forms] += $forms_monthly[retailer_forms];
			$report[monthly_grand_totals][customer_forms] += $forms_monthly[customer_forms];
			$report[monthly_grand_totals][retailer_forms] += $forms_monthly[retailer_forms];
			$report[monthly_total_forms] += $forms_monthly[customer_forms];
			$report[monthly_total_forms] += $forms_monthly[retailer_forms];
			
			foreach($forms_monthly[kyc_type] as $kyc_type => $kyc_count){
				$report[monthly_grand_total][$telecom][kyc_type][$kyc_type] += $kyc_count;
				$report[monthly_grand_totals][kyc_type][$kyc_type] += $kyc_count;
			}
		}
	}
	
	$kyc_type_includes = array('Undefined', 'Normal - KYC', 'E - KYC');
	foreach($report[monthly_grand_total] as $telecom => $telecom_data){
		foreach($telecom_data as $kyc => $kyc_types){
			if($kyc == 'kyc_type'){
				foreach($kyc_types as $kyc_type => $kyc_type_num){
					//echo 'Num: ['.$kyc_type_num.'] Devided by ['.$report[monthly_grand_totals][kyc_type][$kyc_type].']<br>';
					$report[monthly_per][$telecom][kyc_type][$kyc_type] = ($kyc_type_num/$report[monthly_grand_totals][kyc_type][$kyc_type]*100);
				}
			}
		}
	}
	
	foreach($report[monthly_grand_totals] as $kyc => $kyc_types){
		if($kyc == 'kyc_type'){
			foreach($kyc_types as $kyc_type => $kyc_type_num){
			//echo 'Num: ['.$kyc_type_num.'] Devided by ['.$report[monthly_total_forms].']<br>';
				$report[monthly_per][kyc_type][$kyc_type] = ($kyc_type_num/$report[monthly_total_forms]*100);
			}
		}
	}
	
	$report[first_of_month] = $first_of_month;
	$report[period] = $to;
	
	//echo '<pre>'.print_r($report[monthly_per],true).'</pre>';
	
	return display_wpesacourier_report($report);
}

function display_wpesacourier_report($report){

	if($report[data] == "NO DATA") { return "There is no data to be displayed for ".$report[period]."..."; }
	
	$html .= '
	<table cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="'.(count($report[kyc_types])+4).'">MONTHLY ('.date("Y-m", strtotime($report[first_of_month])).') TOTAL: '.number_format($report[monthly_total_forms],0).'</th>
		</tr>
		<tr>
			<th colspan="2"></th>
			<th colspan="2">TOTALS</th>
			<th colspan="'.count($report[kyc_types]).'">KYC TYPE</th>
		</tr>
		<tr>
			<th>Telecom</th>
			<th>Region</th>
			<th>Total Customer Forms</th>
			<th>Total Retailer Forms</th>
			';
		foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
			$html .= '<th>'.$kyc_type.'</th>';
		}
		$html .= '</tr>';
		
		$html .= '
				<tr>
					<td colspan="2" rowspan="2" class="totals_text_values">Grand Total:</td>
					<td class="totals_values">'.number_format($report[monthly_grand_totals][customer_forms],0).'</td>
					<td class="totals_values">'.number_format($report[monthly_grand_totals][retailer_forms],0).'</td>
				';
		foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
			$html .= '<td class="totals_values">'.number_format($report[monthly_grand_totals][kyc_type][$kyc_type],0).'</td>';
		}
		
		$html .= '
				</tr>
				';	
				
		//------------------------------------------------Percentage--------------------------------------------------------		
		$html .= '
				<tr>
					<td class="totals_text_values_small" colspan="2">Percentage KYC Type(Warid &amp; Airtel) of Total Forms:</td>
				';
		foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
			$html .= '<td class="totals_values">'.number_format($report[monthly_per][kyc_type][$kyc_type],2).'%</td>';
		}
		
		$html .= '
				</tr>
				';
				
	foreach($report[monthly_totals] as $telecom => $telecomdata){
		foreach($telecomdata as $this_region => $r_totals){
		$html .= '
				<tr>
					<td class="text_values">'.$telecom.'</td>
					<td class="text_values">'.$this_region.'</td>
					<td class="values">'.number_format($r_totals[customer_forms],0).'</td>
					<td class="values">'.number_format($r_totals[retailer_forms],0).'</td>
				';
			foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
				$html .= '<td class="values">'.number_format($r_totals[kyc_type][$kyc_type],0).'</td>';
			}
			$html .= '</tr>';
		}
		$html .= '
				<tr>
					<td colspan="2" rowspan="2" class="'.$telecom.'_text_values">'.$telecom.' Total:</td>
					<td class="'.$telecom.'_values">'.number_format($report[monthly_grand_total][$telecom][customer_forms],0).'</td>
					<td class="'.$telecom.'_values">'.number_format($report[monthly_grand_total][$telecom][retailer_forms],0).'</td>
				';
		foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
			$html .= '<td class="'.$telecom.'_values">'.number_format($report[monthly_grand_total][$telecom][kyc_type][$kyc_type],0).'</td>';
		}
		$html .= '</tr>';
		
		//-------------------------------------------Percentages------------------------------------------------------------------
		$html .= '
				<tr>
					<td class="'.$telecom.'_values" colspan="2">Percentage KYC Type('.$telecom.') of Total Forms:</td>
				';
		foreach($report[kyc_types] as $kyc_type_key => $kyc_type){
			$html .= '<td class="'.$telecom.'_values">'.number_format($report[monthly_per][$telecom][kyc_type][$kyc_type],2).'%'.'</td>';
		}
		$html .= '</tr>';
	}
		$html .= '
		</table>
		<hr>';

	$html .= '
		<table cellspacing="0" cellpadding="0">
  <tr height="28">
    <th colspan="10" align="center">Distributor List SIM Registration Courier Delivery Report ('.$report[period].')</th>
  </tr>';
  	$html .= '<tr>';
		$html .= '<td colspan="6" class="totals_text_values">Grand Total:</td>';
		$html .= '<td class="totals_values">'.number_format($report[total_all][customer_forms],0).'</td>';
		$html .= '<td class="totals_values">'.number_format($report[total_all][retailer_forms],0).'</td>';
		$html .= '<td colspan="2" class="totals_text_values">&nbsp;</td>';
	$html .= '</tr>';
	
	foreach($report[data] as $telecom => $telecom_data){
		$html .= '<tr>';
		$html .= '<td colspan="6" class="totals_text_values">'.$telecom.' Total:</td>';
		$html .= '<td class="totals_values">'.number_format($report[totals][$telecom][grand_total][customer_forms],0).'</td>';
		$html .= '<td class="totals_values">'.number_format($report[totals][$telecom][grand_total][retailer_forms],0).'</td>';
		$html .= '<td colspan="2" class="totals_text_values">&nbsp;</td>';
		$html .= '</tr>';
	}
	
  $html .= '<tr height="21">
				<th>S#</th>
				<th>Telecom</th>
				<th>Company/Business Name</th>
				<th>Location</th>
				<th>Customer Bill</th>
				<th>Retailer Bill</th>
				<th>Customer Forms</th>
				<th>Retailer Forms</th>
				<th>Delivered By</th>
				<th>Time</th>
			  </tr>';

	foreach($report[data] as $telecom => $telecom_data){
		foreach($telecom_data as $region=>$region_data){
		
			$html .= '
				<!--REGION-->
				<tr>
					<th colspan="10" class="'.$telecom.'_text_values">'.$region.'</th>
				</tr>
	
	
			';
			
			foreach($region_data as $row){
				$html .= '<tr>';
				$html .= '<td class="values">'.++$ii.'</td>';
				$html .= '<td class="text_values">'.$row[telecom].'</td>';
				$html .= '<td class="text_values">'.$row[company_name].'</td>';
				$html .= '<td class="text_values">'.$row[company_location].'</td>';
				$html .= '<td class="text_values">'.$row[customer_billnum].'</td>';
				$html .= '<td class="text_values">'.$row[retailer_billnum].'</td>';
				$html .= '<td class="values">'.number_format($row[customer_forms],0).'</td>';
				$html .= '<td class="values">'.number_format($row[retailer_forms],0).'</td>';
				$html .= '<td class="text_values">'.$row[couriers_name].'</td>';
				$html .= '<td class="values">'.$row[delivery_time].'</td>';
				$html .= '</tr>';
			}
				$html .= '<tr>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="'.$telecom.'_text_values">Total Forms:</td>';
				$html .= '<td class="'.$telecom.'_values">'.number_format($report[totals][$telecom][$region][customer_forms],0).'</td>';
				$html .= '<td class="'.$telecom.'_values">'.number_format($report[totals][$telecom][$region][retailer_forms],0).'</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '<td class="text_values">&nbsp;</td>';
				$html .= '</tr>';
				$html .= '<tr>
							<td colspan="10" height="1" class="'.$telecom.'_text_values"></td>
						</tr>
						<tr>
							<td colspan="10" height="10"></td>
						</tr>
						';
		}
		
	}
	$html .= '</table>';
	
	//echo '<pre>'.print_r($report[totals], true).'</pre>';
	
	return $html;
}
?>