<?
function generate_offnet_sales_report1($from,$to,$report_type,$user,$flag){

	custom_query::select_db('telesales');

	$myquery = new custom_query();

	exit("here ... ");

	$query = '
		SELECT
			concat(users.first_name," ",users.last_name) AS Agent,
			tel_customer_details.name AS msisdn,
			date(date_add(tel_items_sold.date_entered, interval 3 hour)) AS date_entered,
			tel_customer_details.customer_name,
			tel_items_sold.name AS item_sold,
			tel_items_sold.quantity,
			tel_items_sold.unit_price,
			tel_items_sold.total_price,
			tel_items_sold.actual_pick_date,
			tel_items_sold.airtime_use
		FROM
			tel_customer_details
			Inner Join tel_customel_items_sold_c ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
			Inner Join tel_items_sold ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
			Inner Join users ON tel_customer_details.assigned_user_id = users.id
		WHERE
			tel_customel_items_sold_c.deleted = 0 and 
			tel_customer_details.deleted = 0 and 
			tel_customel_items_sold_c.deleted = 0
	';
	
	if($from){
		$query .= " AND date(date_add(tel_items_sold.date_entered, interval 3 hour)) >= '".$from."' ";
	}else { $query .= " AND date(date_add(tel_items_sold.date_entered, interval 3 hour)) >= '".date('Y-m-d')."'"; }
	
	if($to){
		$query .= " AND date(date_add(tel_items_sold.date_entered, interval 3 hour)) <= '".$to."'";
	}else { $query .= " AND date(date_add(tel_items_sold.date_entered, interval 3 hour)) <= '".date('Y-m-d')."'"; }
	//echo $query;
	
	function summarise_sales($data){
		$item_commissions[Modem] = 0.1;
		$item_commissions[Airtime] = 0.1;
		$item_commissions[Phone] = 0.02;
		foreach($data as $row){
			$summary[products][$row[item_sold]] = $row[item_sold];
			
			++$summary[agents][$row[Agent]][products][$row[item_sold]][counts];
			$summary[agents][$row[Agent]][products][$row[item_sold]][sales] += $row[total_price];
			$summary[agents][$row[Agent]][products][$row[item_sold]][commission] += ($row[total_price] * $item_commissions[$row[item_sold]]/1.18);
			$summary[agents][$row[Agent]][totals][commission] += ($row[total_price] * $item_commissions[$row[item_sold]]/1.18);
			$summary[agents][$row[Agent]][totals][sales] += $row[total_price];
			
			++$summary[Totals][counts][$row[item_sold]];
			$summary[Totals][products][$row[item_sold]][sales] += $row[total_price];
		}
		return $summary;	
	}
	
	function summarise_item($data){
		foreach($data as $row){
			$summary[item][$row[item_sold]] += $row[total_price];
			$summary[airtime_use][$row[airtime_use]] += $row[total_price];
			}
		return $summary;	
	}
	
	function get_call_data($from,$to){
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		$query = 'SELECT
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
			$query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) >= '".$from."'";
				}else { $query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) >= '".date('Y-m-d')."'"; }
				
				if($to){
					$query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) <= '".$to."'";
				}else { $query .= " AND date(date_add(tel_calls.date_entered, interval 3 hour)) <= '".date('Y-m-d')."'"; }
				$calls = $myquery->multiple($query);
			foreach($calls as $row){
					++$data[caller][$row[call_success]];
					++$data[rejection_reasons][$row[reason_for_rejection]];
					++$data[calls_per_day][$row[date_entered]];
					++$data[calls_per_agent][$row[Agent]];
			}
			return $data;
		
	}
	
	$items = $myquery->multiple($query);
	switch($report_type){
		case 'detail':
			$report[rows] = $items;
			break;
		case 'sales_per_agent_per_item':
			$report[sales_summary] = summarise_sales($items);
			break;
		case 'sales_per_item_airtime_use':
		$report[sales_item_summary] = summarise_item($items);
			break;
		case 'calls_analysis':
		$report[calls_analysis] = get_call_data($from,$to);
		//print_r($report[calls_analysis]);
			break;
		default:
			$_POST[report_type] = 'detail';
			$$report[rows] = $items;
			
	}
	if(isset($flag)){ 
		return $report; 
	}else{
		return display_offnet_sales_report($report);
	}
}

function display_offnet_sales_report1($report){
//print_r($report[sales_summary][items]); echo "<br>";
//print_r($report[sales_summary][agents]); echo "<br>";
	if($report[rows]){
		$html = '
			<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
			<tr> 
				  <th></th>
				  <th>AGENT</th>
				  <th>MSISDN</th>
				  <th>SALE DATE</th>
				  <th>CUSTOMER NAME</th>
				  <th>ITEM SOLD</th>
				 <th>QUANTITY</th>
				  <th>UNIT PRICE</th>
				  <th>TOTAL PRICE</th>
				  <th>ACTUAL PICK UP DATE</th>
				  <th>AIRTIME USE</th>
			</tr>
		';
		
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="text_values">'.$row[msisdn].'</td>
					<td class="text_values">'.$row[date_entered].'</td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="text_values">'.$row[item_sold].'</td>
					<td class="text_values">'.$row[quantity].'</td>
					<td class="text_values">'.$row[unit_price].'</td>
					<td class="text_values">'.$row[total_price].'</td>
					<td class="text_values">'.$row[actual_pick_date].'</td>
					<td class="text_values">'.$row[airtime_use].'</td>
				</tr>
			';
		}
		
		$html . '
			</table>
		';
	}
	else if(count($report[sales_summary])>0){
	//print_r($report[sales_summary][agents]);
		$html = '
			<table border="0" cellpadding="3" cellspacing="0" width="100%"> 
			<tr> 
				  <th>AGENT</th>';
		foreach($report[sales_summary][products] as $product){
			$html .= '
				<th>'.$product.' Counts</th>
				<th>'.$product.' Sales</th>
				<th>'.$product.' Commission</th>
			';
		}
		$html .= '
				<th>Total Sales</th>
				<th>Total Commission</th>
			</tr>
		';
			foreach($report[sales_summary][agents] as $agent=>$agent_row){
				$html .= '
					<tr>
						<th>'.$agent.'</th>';
						foreach($report[sales_summary][products] as $product){
							$html .= '
								<td class="values">'.number_format($agent_row[products][$product][counts],0).'</td>
								<td class="values">'.number_format($agent_row[products][$product][sales],0).'</td>
								<td class="values">'.number_format($agent_row[products][$product][commission],1).'</td>
							'; 
						}
				$html .= '
						<td class="values">'.number_format($agent_row[totals][sales],0).'</td>
						<td class="values">'.number_format($agent_row[totals][commission],1).'</td>
					</tr>
				';
			}
			$html .='
				</table>
			';
	}
	else if(count($report[sales_item_summary])>0){
		$html = '
			<table border="0" cellpadding="2" cellspacing="0" width="50%" style="float:left"> 
			<tr> 
				  <th colspan="2" align="center">TOTAL ITEMS SOLD SUMMARY</th>
			</tr>
			<tr> 
				  <th>ITEM SOLD</th><th>TOTAL PRICE</th>
			</tr>';
			//print_r($report[sales_item_summary][item]);
			foreach($report[sales_item_summary][item] as $item=>$price){
					$html .= '<tr> 
								<td class="values">'.$item.'</td><td class="values">'.number_format($price,0).'</td>
								</tr>';
			}
		
				$html .=' </table>
			<table border="0" cellpadding="2" cellspacing="0" width="50%" class="sortable" style="float:left"> 
			<tr> 
				  <th colspan="2" align="center">AIRTIME USE SUMMARY</th>
			</tr>
			<tr> 
				  <th>AIRTIME USE</th><th>TOTAL PRICE</th>
			</tr>';
			//print_r($report[sales_item_summary][item]);
			foreach($report[sales_item_summary][airtime_use] as $item=>$price){
					$html .= '<tr> 
								<td class="values">'.$item.'</td><td class="values">'.number_format($price,0).'</td>
								</tr>';
			}
			$html .=' </table>';
			
	}
	
	else if(count($report[calls_analysis])>0){
		$html = '
			<table border="0" cellpadding="2" cellspacing="0" width="30%" class="sortable" style="float:left"> 
			<tr> 
				  <th colspan="2" align="center">CALL SUCCESS SUMMARY</th>
			</tr>
			<tr> 
				  <th>CALL STATUS</th><th>TOTAL COUNT</th>
			</tr>';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			foreach($report[calls_analysis][caller] as $call_status=>$count){
				$html .= '
					<tr> 
						<th>'.$call_status.'</th><td class="values">'.number_format($count,0).'</td>
					</tr>
				';
				$totalcalls += $count;
			}
			
			$html .='
				<tr id="totals"> 
						<th>Total</th><td class="values">'.number_format($totalcalls,0).'</td>
					</tr>
				</table>
				<table border="0" cellpadding="2" cellspacing="0" width="30%" class="sortable" style="float:left"> 
					<tr> 
						  <th colspan="2" align="center">REASONS FOR REJECTION SUMMARY</th>
					</tr>
					<tr> 
						  <th>REASON FOR REJECTION</th><th>TOTAL COUNT</th>
					</tr>
				';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			foreach($report[calls_analysis][rejection_reasons] as $reason=>$count){
				$html .= '
					<tr> 
						<th>'.$reason.'</th><td class="values">'.number_format($count,0).'</td>
					</tr>
					
				';
				$totalcalls += $count;
			}
			$html .='
					<tr id="totals"> 
						<th>Total</th><td class="values">'.number_format($totalcalls,0).'</td>
					</tr>
				</table>
				<table border="0" cellpadding="2" cellspacing="0" width="30%" class="sortable" style="float:left"> 
				<tr> 
					  <th colspan="2" align="center">CALLS PER DATE SUMMARY</th>
				</tr>
				<tr> 
					  <th>Date</th><th>TOTAL COUNT</th>
				</tr>
			';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			foreach($report[calls_analysis][calls_per_day] as $date=>$count){
				$html .= '
					<tr> 
						<th>'.$date.'</th><td class="values">'.number_format($count,0).'</td>
					</tr>
				';
				$totalcalls += $count;
			}
			$html .='
					<tr id="totals"> 
						<th>Total</th><td class="values">'.number_format($totalcalls,0).'</td>
					</tr>
				</table>
				<table border="0" cellpadding="2" cellspacing="0" width="30%" class="sortable" style="float:left"> 
				<tr> 
					  <th colspan="2" align="center">CALLS AGENT SUMMARY</th>
				</tr>
				<tr> 
					  <th></th><th>AGENT</th><th>TOTAL CALLS</th>
				</tr>
			';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			arsort($report[calls_analysis][calls_per_agent]);
			foreach($report[calls_analysis][calls_per_agent] as $agent=>$count){
			$i = 0;
				$html .= '
					<tr> 
						<td class="values">'.++$i.'</td><th>'.$agent.'</th><td class="values">'.number_format($count,0).'</td>
					</tr>';
					$totalcalls += $count;
			}
			$html .='
					<tr id="totals"> 
						<th>Total</th><td class="values">'.number_format($totalcalls,0).'</td>
					</tr>
				</table>
			';
	}
	return $html;
}
?>