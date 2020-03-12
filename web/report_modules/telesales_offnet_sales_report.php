<?
function generate_offnet_sales_report($from,$to,$report_type,$user,$msisdn,$item_sold,$flag){

	custom_query::select_db('telesales');

	//exit("Here ");

	$myquery = new custom_query();

	$query = '
		SELECT
			tel_items_sold.id,
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
			tel_items_sold
			Inner Join tel_customel_items_sold_c ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
			Inner Join tel_customer_details ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
			Inner Join users ON tel_customer_details.assigned_user_id = users.id
		WHERE
			tel_customel_items_sold_c.deleted = 0 and tel_customer_details.deleted = 0 and tel_customel_items_sold_c.deleted = 0
	';
	
	if($from == ''){
		//$query .= " AND date(date_add(tel_items_sold.date_entered, interval 3 hour)) >= '".$from."' ";
		$from = date('Y-m-d');
	}	
	$query .= " AND tel_items_sold.date_entered >= date_sub('".$from." 00:00:00', interval 3 hour) ";
	
	if($to == ''){
		$to = date('Y-m-d');
	}
	$query .= " AND tel_items_sold.date_entered <= date_sub('".$to." 23:59:59', interval 3 hour) ";
	
	if($user != 'All Agents' && $user!=''){ 
		$query .=" AND users.user_name = '".$user."'";
	}
	if($item_sold != ''){ 
		$query .=" AND tel_items_sold.name = '".$item_sold."'";
	}
	if($msisdn!=''){ 
		$query .=" AND tel_customer_details.name = '".$msisdn."'";
	}
	
	$query .= "
		ORDER BY
			tel_items_sold.date_entered ASC
	";
	//print $query;
	//echo nl2br($query)."<hr>";
	
	function summarise_sales($data){
		/*
		$item_commissions[Modem] = 8500;
		$item_commissions[Airtime] = 0.05;
		$item_commissions['Phone [Chali]'] = 0;
		$item_commissions['Phone [DaboLine]'] = 0.01;
		$item_commissions['PCO Phone'] = 0.10;
		$item_commissions['Special Number'] = 0.15;
		*/
		
		function commission($item_row){
			$multipliers[flat]='quantity';
			$multipliers[percentage]='total_price';
			
			$item_commissioning[Modem] = 'flat';
			$item_commissioning[Airtime] = 'percentage';
			$item_commissioning['Phone [Chali]'] = 'percentage';
			$item_commissioning['Phone [DaboLine]'] = 'percentage';
			$item_commissioning['PCO Phone'] = 'percentage';
			$item_commissioning['Special Number'] = 'percentage';
			
			$item_commissions[Modem] = 8500;
			$item_commissions[Airtime] = 0.05;
			$item_commissions['Phone [Chali]'] = 0;
			$item_commissions['Phone [DaboLine]'] = 0.01;
			$item_commissions['PCO Phone'] = 0.1;
			$item_commissions['Special Number'] = 0.1;
			
			//echo "Working on ".$item_row[item_sold]." using ".$multipliers[$item_commissioning[$item_row[item_sold]]]." [".$item_row[$multipliers[$item_commissioning[$item_row[item_sold]]]]."] X [".$item_commissions[$item_row[item_sold]]."]<br>";
			
			$commission = $item_row[$multipliers[$item_commissioning[$item_row[item_sold]]]] * $item_commissions[$item_row[item_sold]];
				
			return $commission;
		}
		
		foreach($data as $row){
			$summary[products][$row[item_sold]] = $row[item_sold];
			
			++$summary[agents][$row[Agent]][products][$row[item_sold]][counts];
			$summary[agents][$row[Agent]][products][$row[item_sold]][sales] += $row[total_price];
			
			//if($row[item_sold] == 'Modem'){
			//	$summary[agents][$row[Agent]][products][$row[item_sold]][commission] += ($row[quantity]* 8500);
			//	$summary[agents][$row[Agent]][totals][commission] += ($row[quantity] * 8500);
			//}else{
			//	$summary[agents][$row[Agent]][products][$row[item_sold]][commission] += ($row[total_price]* $item_commissions[$row[item_sold]]);
			//	$summary[agents][$row[Agent]][totals][commission] += ($row[total_price] * $item_commissions[$row[item_sold]]);
			//}
			
			$summary[agents][$row[Agent]][products][$row[item_sold]][commission] += commission($row);
			$summary[agents][$row[Agent]][totals][commission] += commission($row);
			
			$summary[agents][$row[Agent]][totals][sales] += $row[total_price];			
			++$summary[Totals][counts][$row[item_sold]];
			$summary[Totals][products][$row[item_sold]][sales] += $row[total_price];
		}
		return $summary;	
	}
	
	function summarise_item($data){
		foreach($data as $row){
			$summary[item]['Total Sales by Month'][substr($row[date_entered],0,7)] += $row[total_price];
			//$summary[item]['Total Sales by Date'][substr($row[date_entered],0,10)] += $row[total_price];
			$summary[item]['Total Sales by Item'][$row[item_sold]] += $row[total_price];
			$summary[item]['Total Sales by Month by Item'][substr($row[date_entered],0,7)." >>> ".$row[item_sold]] += $row[total_price];
			
			$summary[item]['Total Sales by Agent'][$row[Agent]] += $row[total_price];
			$summary[item]['Total Sales by Month by Agent'][substr($row[date_entered],0,7)." >>> ".$row[Agent]] += $row[total_price];
			$summary[item]['Total Sales by Month by Agent by Item'][substr($row[date_entered],0,7)." >>> ".$row[Agent]." >>> ".$row[item_sold]] += $row[total_price];
			
			if($row[item_sold] == 'Airtime'){
				$summary[item]['Sales by Airtime use'][$row[airtime_use]] += $row[total_price];
			}
		}
		return $summary;	
	}
	
	function get_call_data($from,$to){
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		
		$query = "
			SELECT
				concat(users.first_name,' ',users.last_name) AS Agent,
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
				tel_customer_details.deleted =  0 AND 
				tel_customels_tel_calls_c.deleted = 0 AND
				tel_calls.deleted = 0 AND 
				tel_calls.date_entered BETWEEN date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour)
		";
			
		$calls = $myquery->multiple($query);
		foreach($calls as $row){
			++$data[caller][$row[call_success]];
			++$data[rejection_reasons][$row[reason_for_rejection]];
			++$data[calls_per_day][$row[date_entered]];
			++$data[calls_per_agent][$row[Agent]];
		}
		
		return $data;
	}
	
	function get_warid_nos($from,$to){
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		
		$query = "
			SELECT
				distinct(tel_warid_number.`name`) AS warid_msisdn,
				tel_warid_number.type_of_no AS msisdn_type,
				CONCAT(assigned_user.first_name,' ',assigned_user.last_name) AS assigned_agent
			FROM
				tel_warid_number
				INNER JOIN tel_customewarid_number_c ON tel_warid_number.id = tel_customewarid_number_c.tel_custom6347_number_idb
				INNER JOIN tel_customer_details ON tel_customer_details.id = tel_customewarid_number_c.tel_customeba1details_ida
				INNER JOIN tel_customel_items_sold_c ON tel_customer_details.id = tel_customel_items_sold_c.tel_custom949fdetails_ida
				INNER JOIN tel_items_sold ON tel_customel_items_sold_c.tel_custom9c71ms_sold_idb = tel_items_sold.id
				LEFT OUTER JOIN users assigned_user ON assigned_user.id = tel_warid_number.assigned_user_id
			WHERE
				tel_warid_number.deleted = 0 AND
				tel_customel_items_sold_c.deleted = 0 AND
				tel_customer_details.deleted = 0 AND
				tel_customewarid_number_c.deleted = 0 AND
				tel_warid_number.deleted = 0 AND
				tel_items_sold.date_entered BETWEEN date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour)
		";
		
		//echo nl2br($query);
		
		return $myquery->multiple($query);
	}
	
	switch($report_type){
		case 'sales_per_agent_per_item':
			$report[sales_summary] = summarise_sales($myquery->multiple($query));
			break;
		case 'sales_per_item_airtime_use':
			$report[sales_item_summary] = summarise_item($myquery->multiple($query));
			break;
		case 'calls_analysis':
			$report[calls_analysis] = get_call_data($from,$to);
			break;
		case 'sold_warid_numbers':
			$report[sold_warid_numbers] = get_warid_nos($from,$to);
			break;
		case 'detail':
		default:
			$_POST[report_type] = 'detail';
			$report[rows] = $myquery->multiple($query);;	
	}
	//print_r($report);
	if($flag != ''){
		return $report; 
	}else{
		return display_offnet_sales_report($report);
	}
}

function display_offnet_sales_report($report){

//print_r($report[sales_summary][items]); echo "<br>";
//print_r($report[sales_summary][agents]); echo "<br>";
	if(count($report[rows])>0){
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
					<td class="values"><a href="http://ccba02.waridtel.co.ug/telesales/index.php?module=tel_items_sold&return_module=tel_items_sold&action=DetailView&record='.$row[id].'" target="_blank">'.++$i.'</a></td>
					<td class="text_values">'.$row[Agent].'</td>
					<td class="values">'.$row[msisdn].'</td>
					<td class="text_values">'.$row[date_entered].'</td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="text_values">'.$row[item_sold].'</td>
					<td class="values">'.number_format($row[quantity],0).'</td>
					<td class="values">'.number_format($row[unit_price],0).'</td>
					<td class="values">'.number_format($row[total_price],0).'</td>
					<td class="values">'.$row[actual_pick_date].'</td>
					<td class="text_values">'.$row[airtime_use].'</td>
				</tr>
			';
		}
		$html . '
			</table>
		';
	}elseif(count($report[sales_summary])>0){
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
	}elseif(count($report[sales_item_summary])> 0){
		$html = '
			<table border="0" cellpadding="0" cellspacing="0" width="60%"> 
			<tr> 
				  <th align="center">ITEM SUMMARIES</th>
			</tr>
			<tr> 
				  <td height="15">.</td>
			</tr>
		';
		
		foreach($report[sales_item_summary][item] as $report_title=>$report_data){
			$html .= '
				<tr> 
				  <th align="center">'.$report_title.'</th>
				</tr>
				<tr><td>
					<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
						<!--HEADINGS-->
						<tr>
			';
			
			$seperate_columns = explode(' by ',$report_title); array_shift($seperate_columns);
			
			
			foreach($seperate_columns as $column){
				$html .= '
							<th>'.$column.'</th>
				';
			}
			
			$html .= '
							<th>Sales</th>
						</tr>
			';
			
			//THE INDIVIDUAL ROWS
			foreach($report_data as $parameters => $value){
				$seperate_parameters = explode(" >>> ",$parameters);
				$html .= '
					<tr>
				';
				
				foreach($seperate_parameters as $parameter){
					$html .= '
						<td class="text_values">'.$parameter.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($value,0).'</td>
					</tr>
				';
			}
			
			$html .= '
					</table>
			';
			
			if(++$report_sales_item_summary_counter < count($report[sales_item_summary][item])){
				$html .= '
					<tr> 
						<td height="10"> </td>
					</tr>
				';
			}
			
			$html .= '
				</td></tr>
			';
		}
		
		$html .='
			</table>
		';	
	}elseif(count($report[calls_analysis])>0){
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
					</tr>';
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
					  <th>AGENT</th><th>TOTAL CALLS</th>
				</tr>
			';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			arsort($report[calls_analysis][calls_per_agent]);
			foreach($report[calls_analysis][calls_per_agent] as $agent=>$count){
				$html .= '
					<tr> 
						<th>'.$agent.'</th><td class="values">'.number_format($count,0).'</td>
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
	
	if(count($report[sold_warid_numbers]) > 0){
		$html .= '
			<table border="0" cellpadding="1" cellspacing="0" class="sortable">
			<tr> 
				  <th align="center">WARID NUMBERS</th>
			</tr>
			<tr> 
				<TD>
				<table border="0" cellpadding="0" cellspacing="0" class="sortable">
					<tr> 
				  		<th>#</th>
						<th>MSISDN</th>
						<th>ASSIGNED AGENT</th>
					</tr> 
		';
		
		foreach($report[sold_warid_numbers] as $row){
			$html .= '
					<tr> 
				  		<td class="values">'.++$ijj.'</td>
						<td class="values">'.$row[warid_msisdn].'</td>
						<td class="text_values">'.$row[msisdn_type].'</td>
						<td class="text_values">'.$row[assigned_agent].'</td>
					</tr> 
			';
		}
		
		
		$html .= '
				</TABLE>
				</TD>
			</tr>
			</TABLE>
		';
		
	}
	
	return $html;
}
?>