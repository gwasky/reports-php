<?php

function generate_upsell_report($from,$to,$report_type,$user,$msisdn,$service_charge,$product_type,$activation_from,$activation_to){

	custom_query::select_db('telesales');

	$myquery = new custom_query();
	
	$_POST[report_type] = $report_type;
	
	$query = "
		SELECT
			up_upsell.customer_name,
			up_upsell.name AS msisdn,
			up_upsell.email AS contact_no,
			users.user_name AS username,
			users.phone_work AS IPT_no,
			up_product_interest.service_charge,
			up_product_interest.name AS Product_sold,
			up_product_interest.id AS product_id,
			up_product_interest.activation_date,
			up_product_interest.expiry_date,
			up_product_interest.status,
			up_product_interest.description,
			(date_add(up_product_interest.date_entered, interval 3 hour)) as sale_date
		FROM 
			up_upsell
			Inner Join up_upsell_uuct_interest_c ON up_upsell.id = up_upsell_uuct_interest_c.up_upsell_47e3_upsell_ida
			Inner Join up_product_interest ON up_upsell_uuct_interest_c.up_upsell_87d5nterest_idb = up_product_interest.id
			Inner Join users ON up_product_interest.assigned_user_id = users.id
			INNER JOIN up_upsell_cstm ON up_upsell.id = up_upsell_cstm.id_c

		WHERE 
			up_upsell.deleted = 0 
			AND up_upsell_uuct_interest_c.deleted = 0
	";
	
	if($from){
		$query .= " AND up_call_up.date_entered.date_entered >= date_sub('".$from." 00:00:00', interval 3 hour)";
		$set_condition = TRUE;
	}
	
	if($to){
		$query .= " AND up_call_up.date_entered.date_entered <= date_sub('".$to." 00:00:00', interval 3 hour)";
		$set_condition = TRUE;
	}
	
	if($user != 'All Agents' && $user!=''){ 
		$query .=" AND users.user_name = '".$user."'";
		$set_condition = TRUE;
	}
	if($service_charge == '>0'){ 
		$query .=" AND up_product_interest.service_charge > 0 ";
		$set_condition = TRUE;
	}
	if($service_charge != '>0' && $service_charge !=''){ 
		$query .=" AND up_product_interest.service_charge = '".$service_charge."'";
		$set_condition = TRUE;
	}
	if( $product_type != ''){
		$query .=" AND up_product_interest.name = '".$product_type."'";
		$set_condition = TRUE;
	}
	if($msisdn!=''){ 
		$query .=" AND up_upsell.name = '".$msisdn."'";
		$set_condition = TRUE;
	}
	
	if($activation_from){
		$query .= " AND up_product_interest.activation_date >= '".$activation_from."' ";
		$set_condition = TRUE;
	}
	
	if($activation_to){
		$query .= " AND up_product_interest.activation_date <= '".$activation_to."' ";
		$set_condition = TRUE;
	}
	
	if($set_condition != TRUE){
		$query .= " LIMIT 500; ";
	}
	
	//echo nl2br($query);
	
	function get_upsell_call_data($from,$to){
		//THIS FUNCTION MAY NOT BE BEING CALLED AT ALL
		custom_query::select_db('telesales');
		$myquery = new custom_query();
		$query = "
			SELECT
				concat(users.first_name,' ',users.last_name) AS Agent,
				up_call_up.name,
				up_upsell.name AS msisdn,
				up_upsell.email AS contact_no,
				users.user_name AS username,
				users.phone_work AS IPT_no,
				LEFT(date_add(up_call_up.date_entered, interval 3 hour),10) as date_entered,
				up_upsell.name,
				up_call_up.call_success
			FROM
				up_upsell
				Inner Join up_upsell_up_call_up_c ON up_upsell.id = up_upsell_up_call_up_c.up_upsell_e0eb_upsell_ida
				Inner Join up_call_up ON up_upsell_up_call_up_c.up_upsell_09c6call_up_idb = up_call_up.id
				Inner Join users ON up_upsell.assigned_user_id = users.id
				INNER JOIN up_upsell_cstm ON up_upsell.id = up_upsell_cstm.id_c
			WHERE
				up_call_up.deleted = 0 AND up_upsell.deleted=0
				AND up_call_up.date_entered between date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour)
		";
		
		$calls = $myquery->multiple($query);
		
		//echo nl2br($query);
		
		foreach($calls as $row){
				++$data[caller][$row[call_success]];
				++$data[calls_per_agent][$row[Agent]];
				++$data[calls_per_day][$row[date_entered]];
		}
		
		return $data;
	}
	
	//calls (BI Extract)
	function get_upsell_call_data_for_bi($from,$to){
		custom_query::select_db('telesales');
		$myquery = new custom_query();
 		/*
		$query = '
			SELECT
				up_upsell.customer_name,
				up_call_up.name as Product_sold,
				up_upsell.name AS msisdn,
				up_upsell.email AS contact_no,
				users.user_name AS username,
				users.phone_work AS IPT_no, 
				date(date_add(up_call_up.date_entered, interval 3 hour)) as sale_date
			FROM
				up_upsell
				Inner Join up_upsell_up_call_up_c ON up_upsell.id = up_upsell_up_call_up_c.up_upsell_e0eb_upsell_ida
				Inner Join up_call_up ON up_upsell_up_call_up_c.up_upsell_09c6call_up_idb = up_call_up.id
				Inner Join users ON up_upsell.assigned_user_id = users.id 
				INNER JOIN up_upsell_cstm ON up_upsell.id = up_upsell_cstm.id_c
			WHERE up_upsell.deleted=0 AND up_call_up.deleted=0
		';
		*/
		$query = "
			SELECT
				up_upsell.customer_name,
				up_call_up.name as Product_sold,
				up_upsell.name AS msisdn,
				if(up_call_up.name = 'GPRS', up_upsell.email, up_upsell.name) AS contact_no,
				-- up_upsell.email AS contact_no,
				users.user_name AS username,
				users.phone_work AS IPT_no, 
				LEFT(date_add(up_call_up.date_entered, interval 3 hour),10) as sale_date
			FROM
				up_upsell
				Inner Join up_upsell_up_call_up_c ON up_upsell.id = up_upsell_up_call_up_c.up_upsell_e0eb_upsell_ida
				Inner Join up_call_up ON up_upsell_up_call_up_c.up_upsell_09c6call_up_idb = up_call_up.id
				Inner Join users ON up_upsell.assigned_user_id = users.id 
				INNER JOIN up_upsell_cstm ON up_upsell.id = up_upsell_cstm.id_c
			WHERE
				up_upsell.deleted=0 AND up_call_up.deleted=0
				AND up_call_up.date_entered between date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour) 
			ORDER BY sale_date asc
		";

		//echo nl2br($query);
		
		$calls = $myquery->multiple($query);
		//print_r($calls);
		return $calls;
	}
	
	function sales_item_summry($rows){
		foreach($rows as $row){
			if($row[service_charge] > 0){
				//BY MONTH
				$summary[substr($row[activation_date],0,7)][$row[Product_sold]] += $row[service_charge];
			}
		}
		
		return $summary;
	}
	
	switch($report_type){
		
		case 'call_analysis':
			$report[calls_analysis] = get_upsell_call_data($from,$to);
			break;
		case 'sales_item_summary':
			$report[sales_item_summary] = sales_item_summry($myquery->multiple($query));
			break;
		case 'calls (BI Extract)':
			$report[bi_calls] = get_upsell_call_data_for_bi($from,$to);
			break;
		case 'detail':
		default:
			$_POST[report_type] = 'detail';
			$report[rows] = $myquery->multiple($query);
			break;
	}
		
	return display_upsell_report($report);
}

function display_upsell_report($report){
	if(count($report[bi_calls])>0){
		$html = '
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable"> 
			<tr> 
				<th></th>
				<!--<th>CUSTOMER NAME</th>-->
				<th>IPT NO</th>
				<th>MSISDN</th>
				<th>CONTACT NO</th>
				<th>USERNAME</th>
				<th>SERVICE CHARGE</th>
				<th>SERVICE</th>
				<th>ACTIVATION DATE</th>
				<th>EXPIRY DATE</th>
				<th>STATUS</th>
				<th>DESCRIPTION</th>
				<th>SALE DATE</th>
			</tr>
		';
		
		foreach($report[bi_calls] as $row){
			list($no1,$no2)= explode("/",$row[contact_no]);
			if(isset($no1)){ $contactNo = $no1;} else {$contactNo=$row[contact_no];} 
			if(substr($contactNo,0,3)!='256' && strlen($contactNo)==9){
				$contactNo1 = stripslashes(ereg_replace("[^A-Za-z0-9]", "", '256'.$contactNo));
				$contactNo = $contactNo1;
			}elseif(substr($contactNo,0,1)!='2' && strlen($contactNo)==10){
				$contactNo1 = stripslashes(ereg_replace("[^A-Za-z0-9]", "", '256'.substr($contactNo,1,9)));
				$contactNo = $contactNo1;
			}
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<!--<td class="text_values">'.$row[customer_name].'</td>-->
					<td class="values">'.$row[IPT_no].'</td>
					<td class="text_values">'.$row[msisdn].'</td>
					<td class="text_values">'.$contactNo.'</td>
					<td class="text_values">'.$row[username].'</td>
					<td class="text_values">'.$row[service_charge].'</td>
					<td class="text_values">'.$row[Product_sold].'</td>
					<td class="text_values">'.$row[activation_date].'</td>
					<td class="text_values">'.$row[expiry_date].'</td>
					<td class="text_values">'.$row[status].'</td>
					<td class="text_values">'.$row[description].'</td>
					<td class="text_values">'.$row[sale_date].'</td>
				</tr>
			';
		}
		
		$html . '
			</table>
		';
	}
	
	if(count($report[rows])>0){
		$html = '
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable"> 
			<tr> 
				<th></th>
				<th>CUSTOMER NAME</th>
				<th>IPT NO</th>
				<th>MSISDN</th>
				<th>CONTACT NO</th>
				<th>USERNAME</th>
				<th>SERVICE CHARGE</th>
				<th>SERVICE</th>';
				if($_POST[excel] != ''){
					$html .= '<th>PD ENTRY ID</th>';
				}
		$html .= '
				<th>ACTIVATION DATE</th>
				<th>EXPIRY DATE</th>
				<th>STATUS</th>
				<th>DESCRIPTION</th>
				<th>SALE DATE</th>
			</tr>
		';
		
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="values">'.$row[IPT_no].'</td>
					<td class="text_values">'.$row[msisdn].'</td>
					<td class="text_values">'.$row[contact_no].'</td>
					<td class="text_values">'.$row[username].'</td>
					<td class="text_values">'.$row[service_charge].'</td>
					<td class="text_values">'.$row[Product_sold].'</td>';
					if($_POST[excel] != ''){
						$html .= '<td class="text_values">'.$row[product_id].'</td>';
					}
			$html .= '
					<td class="text_values">'.$row[activation_date].'</td>
					<td class="text_values">'.$row[expiry_date].'</td>
					<td class="text_values">'.$row[status].'</td>
					<td class="text_values">'.$row[description].'</td>
					<td class="text_values">'.$row[sale_date].'</td>
				</tr>
			';
		}
		
		$html . '
			</table>
		';
	}
	
	if(count($report[calls_analysis])>0){
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
						  <th colspan="2" align="center">CALLS PER AGENT SUMMARY</th>
					</tr>
					<tr> 
						  <th></th><th>AGENT</th><th>NUMBER OF CALLS</th>
					</tr>
				';
			//print_r($report[sales_item_summary][item]);
			$totalcalls = '';
			foreach($report[calls_analysis][calls_per_agent] as $agent=>$count){
				$i = 0;
				$html .= '
					<tr> 
						<td class="values">'.++$i.'</td><th>'.$agent.'</th><td class="values">'.number_format($count,0).'</td>
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
			';
	}
	
	if(count($report[sales_item_summary])>0){
		$html .= '
			<table border="0" cellpadding="2" cellspacing="0"> 
			<tr> 
				 <th align="center">SALES BY ITEM SUMMARY</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="2" cellspacing="0">
					<tr> 
						<th>Month</th>
						<th>Item</th>
						<th>Sales Value</th>
					</tr>
		';
		foreach($report[sales_item_summary] as $period=>$period_data){
			foreach($period_data as $product=>$sales_value){
				$html .= '
					<tr> 
						<td class="text_values">'.$period.'</td>
						<td class="text_values">'.$product.'</td>
						<td class="values">'.number_format($sales_value,0).'</td>
					</tr>
				';
			}
		}
		
		$html .= '
					</table>
				</td>
			</tr>
			</table>
		';
	}
	return $html;
}

?>