<?php
function generate_courier_delivery_report($from,$to,$reporttype,$company,$courier){

	$myquery = new custom_query();
	custom_query::select_db('ccba01.waridpesacouriers');
	
	
	function summarise_by_company($rows,$from,$to){
		foreach($rows as $row){
	
			if(strlen($row[delivery_time])==4){
				$row[delivery_time] = '0'.$row[delivery_time];
			}
			
			$report[data][$row[company_name]][$row[entry_date]]['customer_forms'] += $row[customer_forms];	
			$report[data][$row[company_name]][$row[entry_date]]['retailer_forms'] += $row[retailer_forms];
			$report[data][$row[company_name]]['Total Forms']['customer_forms'] += $row[customer_forms];
			$report[data][$row[company_name]]['Total Forms']['retailer_forms'] += $row[retailer_forms];
			
			$report[data]['Total Forms'][$row[entry_date]]['customer_forms'] += $row[customer_forms];	
			$report[data]['Total Forms'][$row[entry_date]]['retailer_forms'] += $row[retailer_forms];
			$report[data]['Total Forms']['Total Forms']['customer_forms'] += $row[customer_forms];
			$report[data]['Total Forms']['Total Forms']['retailer_forms'] += $row[retailer_forms];
			
			$report[company][$row[company_name]] = $row[company_name];
		}
		asort($report[company]);
		
		$report[period][from] = $from;
		$report[period][to] = $to;
		
		$report[title][main] = 'Courier Delivery Report For '.$from.' to '.$to;
		
		
		$thidate = $from;
		while($thidate<=$to){
			$report[dates][] = date( 'm-d' ,strtotime($thidate));
			$report[dates_comparison][] = date( 'Y-m-d' ,strtotime($thidate));
			$thidate = date( 'Y-m-d' ,strtotime ('+1 day', strtotime($thidate)));
		}
		$report[dates][] = 'Total Forms';
		$report[dates_comparison][] = 'Total Forms';
		
		$report[datecolumns] = array('CF','RF');
		
		return $report;
	}
	
	function summarise_by_region($rows){
		foreach($rows as $row){
	
			if(strlen($row[delivery_time])==4){
				$row[delivery_time] = '0'.$row[delivery_time];
			}
			
			$report[data][$row[telecom]][$row[company_region]]['rows'][] = $row;	
			$report[data][$row[telecom]][$row[company_region]]['customer_forms'] += $row[customer_forms];
			$report[data][$row[telecom]][$row[company_region]]['retailer_forms'] += $row[retailer_forms];
			
			$report[telecom_totals][$row[telecom]]['customer_forms'] += $row[customer_forms];
			$report[telecom_totals][$row[telecom]]['retailer_forms'] += $row[retailer_forms];
			
			if($row[customer_billnum]!='' || $row[customer_billnum]!=0){
			++$report[data][$row[telecom]][$row[company_region]]['customer_billnum'][$row[customer_billnum]];
			
			}
			if($row[retailer_billnum]!='' || $row[retailer_billnum]!=0){
			++$report[data][$row[telecom]][$row[company_region]]['retailer_billnum'][$row[retailer_billnum]];
			}
		}
		
		return $report;
	}
	
	//$to = '2012-03-14'; $from = $to;
	if($from == ''){
		$date = date("Y-m-d");
		$default = strtotime ('-1 day', strtotime($date));
		$default = date ( 'Y-m-d' , $default );
		$from = $default;
		$_POST['from'] = $default;
	}
	
	if($to == ''){
		$date = date("Y-m-d");
		$default = strtotime ('-1 day', strtotime($date));
		$default = date ( 'Y-m-d' , $default );
		$to = $default;
		$_POST['to'] = $default;
	}
	
	if($to == $from){
		$period = " deliveries.entry_date = '".$from."'";
	}else{
		$period = " (deliveries.entry_date >= '".$from."' AND deliveries.entry_date <= '".$to."')";
	}
	
	if($company != ''){
		$companycondition = " company.company_name = '".$company."' AND";
	}else{
		$companycondition =""; 
	}
	
	if($courier != ''){
		$couriercondition = " couriers.couriers_name = '".$courier."' AND";
	}else{
		$couriercondition =""; 
	}	
	
	$query = "SELECT
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
WHERE".$companycondition.$couriercondition.$period."
ORDER BY
deliveries.entry_date,
deliveries.telecom
DESC";
	//print $query;
	$rows = $myquery->multiple($query);
	
	if(count($rows) == 0) { 
		return display_courier_delivery_report(array('period'=>$from,'data'=>"NO DATA"));
		return 'NO rec';
	}

	switch($reporttype){
		case 'Delieveries by Company':
			$report[by_company] = summarise_by_company($rows,$from,$to);
			break;
		case 'Delieveries by Region':
			$report[by_region] = summarise_by_region($rows);
			break;
		case 'detail':
			$report[rows] = $myquery->multiple($query);
			break;
		default:
			$_POST[reporttype] = 'Delieveries by Company';
			$report[by_company] = summarise_by_company($rows,$from,$to);
			break;
	}
	
	return display_courier_delivery_report($report);
}

function display_courier_delivery_report($report){
	
	if(count($report[by_company])>0){
	$report = $report[by_company];
	
	$numcols = count($report[dates]);
	$headerspan = ($numcols*2) + 3;
	
		$html .='<table cellspacing="0" cellpadding="0" border="0">';
		
		$html .='<tr>';
		$html .='<th colspan="'.$headerspan.'" style="align:center">'.$report[title][main].'</th>';
		$html .='</tr>';
		
		$html .='<tr>';
		$html .='<th>&nbsp;</th>';
		foreach($report[dates] as $date){
			$html .= '<th colspan="'.count($report[datecolumns]).'">'.$date.'</th>';
		}
		$html .='</tr>';
		
		$html .='<tr>';
		$html .='
					<th>Companies</th>
		';
		
		foreach($report[dates] as $date){
			foreach($report[datecolumns] as $datecolumn){
				$html .='
					<th width="30">'.$datecolumn.'</th>
				';
			}
		}
		
		$html .='</tr>';
		
		
		
		$counter = 0;
		foreach($report[company] as $company){
		
			$colorclass = ($counter & 1)? "white" : "blue";
	
			$html .='<tr>';
			$html .='<td class="text_values">'.$company.'</td>';
		
				foreach($report[dates_comparison] as $generaldate){
					
						$customer_forms = $report[data][$company][$generaldate]['customer_forms'];
						$retailer_forms = $report[data][$company][$generaldate]['retailer_forms'];
						
						if($customer_forms=='') $customer_forms = '0';
						if($retailer_forms=='') $retailer_forms = '0';
						
						$html .= '<td class="values '.$colorclass.'">'.number_format($customer_forms,0).'</td>';
						$html .= '<td class="values '.$colorclass.'">'.number_format($retailer_forms,0).'</td>';
	
				}
				$html .='</tr>';
				
			$counter++;
		}
		
		$html .='<tr>';
		$html .='<td class="text_values  grand_titles">Grand Total</td>';
		
		foreach($report[dates_comparison] as $generaldate){
				$total_customer_forms = $report[data]['Total Forms'][$generaldate]['customer_forms'];
				$total_retailer_forms = $report[data]['Total Forms'][$generaldate]['retailer_forms'];
				
				if($total_customer_forms=='') $total_customer_forms = '0';
				if($total_retailer_forms=='') $total_retailer_forms = '0';
				
				$html .= '<td class="values grand_titles">'.number_format($total_customer_forms,0).'</td>';
				$html .= '<td class="values grand_titles">'.number_format($total_retailer_forms,0).'</td>';
		}
	
		$html .='</tr>';
		
		$html .='</table>';
	}
	
	
	//By region
	if(count($report[by_region])>0){
	
		$report = $report[by_region];
		
		$html .='<table cellspacing="0" cellpadding="0" width="100%">';
		foreach($report[telecom_totals] as $telecom => $telecomdata){
		 	$html .='<tr height="28">
						<td class="text_values">Customer Forms</td>
						<td class="values">'.$report[telecom_totals][$telecom]['customer_forms'].'</td>
					</tr>
					<tr height="28">
						<td class="text_values">Retailer Forms</td>
						<td class="values">'.$report[telecom_totals][$telecom]['retailer_forms'].'</td>
					</tr>';
		}
		$html .='</table>';
		
		$html = '
			<table cellspacing="0" cellpadding="0" width="100%">
			  <tr height="28">
				<th height="28" colspan="10" align="center" style="texalign:center;">Distributor List SIM Registration Courier Delivery Report</th>
			  </tr>
			  <tr height="21">
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
	
		foreach($report[data] as $telecom=>$telecom_data){
			foreach($telecom_data as $region=>$region_data){
			//echo '<pre>'.print_r($report[data][$telecom][$region]['rows'], true).'</pre>';
				$html .= '
					<!--REGION-->
					<tr>
						<th colspan="10">'.$region.'</th>
					</tr>
		
		
				';
				
				
				$counter = 0;
				$regions_data = $report[data][$telecom][$region]['rows'];
				foreach($regions_data as $row){
				
					if($telecom == 'Warid'){ $colorclass = ($counter & 1)? "white" : "blue"; }
					if($telecom == 'Airtel'){ $colorclass = ($counter & 1)? "white" : "faintred"; }
					
					$html .= '<tr>';
					$html .= '<td class="values '.$colorclass.'">'.++$ii.'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[telecom].'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[company_name].'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[company_location].'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[customer_billnum].'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[retailer_billnum].'</td>';
					$html .= '<td class="values '.$colorclass.'">'.number_format($row[customer_forms],0).'</td>';
					$html .= '<td class="values '.$colorclass.'">'.number_format($row[retailer_forms],0).'</td>';
					$html .= '<td class="text_values '.$colorclass.'">'.$row[couriers_name].'</td>';
					$html .= '<td class="values '.$colorclass.'">'.$row[delivery_time].'</td>';
					$html .= '</tr>';
					$counter++;
				}
				$customer_forms = $report[data][$telecom][$region]['customer_forms'] ? $report[data][$telecom][$region]['customer_forms'] : '0';
				$retailer_forms = $report[data][$telecom][$region]['retailer_forms'] ? $report[data][$telecom][$region]['retailer_forms'] : '0';
				$customer_bills = count($report[data][$telecom][$region]['customer_billnum']);
				$retailer_bills = count($report[data][$telecom][$region]['retailer_billnum']);
				$html .= '<tr>';
				$html .= '<td class="text_values grand_titles">Total/Count:</td>';
				$html .= '<td class="text_values grand_titles"></td>';
				$html .= '<td class="text_values grand_titles"></td>';
				$html .= '<td class="text_values grand_titles"></td>';
				$html .= '<td class="text_values grand_titles">'.number_format($customer_bills,0).'</td>';
				$html .= '<td class="text_values grand_titles">'.number_format($retailer_bills,0).'</td>';
				$html .= '<td class="values grand_titles">'.number_format($customer_forms,0).'</td>';
				$html .= '<td class="values grand_titles">'.number_format($retailer_forms,0).'</td>';
				$html .= '<td class="text_values grand_titles"></td>';
				$html .= '<td class="values grand_titles"></td>';
				$html .= '</tr>';
			}	
			
		}
		$html .='</table>';
	}
	
	
	if(count($report[rows])>0){
			$html .= '<table cellspacing="0" cellpadding="0" class="sortable">';
			$html .= '<tr height="28">';
			$html .= '<th>#</th>';
			$html .= '<th>Telecom</th>';
			$html .= '<th>Company Name</th>';
			$html .= '<th>Customer Bill No.</th>';
			$html .= '<th>Retailer Bill No.</th>';
			$html .= '<th>Customer Forms</th>';
			$html .= '<th>Retailer Forms</th>';
			$html .= '<th>Couriers Name</th>';
			$html .= '<th>Delivery Time</th>';
			$html .= '<th>Entry Date</th>';
			$html .= '</tr>';
			
		$counter = 1;	
		foreach($report[rows] as $row){
			
			if($row['telecom'] == 'Warid'){ $colorclass = ($counter & 1)? "white" : "blue"; }
			if($row['telecom'] == 'Airtel'){ $colorclass = "faintred"; }
			
			$html .= '<tr>';
			$html .= '<td class="text_values '.$colorclass.'">'.++$ii.'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['telecom'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['company_name'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['customer_billnum'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['retailer_billnum'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.number_format($row['customer_forms'],0).'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.number_format($row['retailer_forms'],0).'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['couriers_name'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['delivery_time'].'</td>';
			$html .= '<td class="text_values '.$colorclass.'">'.$row['entry_date'].'</td>';
			$html .= '</tr>';
			$counter++;
		}
		$html .='</table>';
	
	}
	
	return $html;
}
?>