<?php

function generate_cust_knowledge($use_date){
//echo $reporttype;
 	
	//$use_date = '2011-11-04';
 	$current_month = substr($use_date,0,7);
	$myquery = new custom_query();
	$query = "SELECT
				users.first_name,
				users.last_name,
				users.department as team,
				concat(users.first_name,' ',users.last_name) as full_name,
				sa_product_knowledge.name as msisdn,
				sa_product_knowledge.no_of_complaints,
				sa_product_knowledge.original_complaint,
				sa_product_knowledge.call_status,
				sa_product_knowledge.warid_improve,
				sa_product_knowledge.crbt_awareness,
				sa_product_knowledge.cmb_awareness,
				sa_product_knowledge.kawa_awareness,
				sa_product_knowledge.pakalast_awareness,
				sa_product_knowledge.pakachini_awareness,
				sa_product_knowledge.crbt_lead,
				sa_product_knowledge.pakalast_lead,
				sa_product_knowledge.cmb_lead,
				sa_product_knowledge.kawa_lead,
				sa_product_knowledge.pakachini_lead,
				sa_product_knowledge.warid_improve
				FROM
				sa_product_knowledge
				Inner Join users ON sa_product_knowledge.assigned_user_id = users.id
				WHERE sa_product_knowledge.call_status IN ('Answered','Busy') AND users.status = 'active' AND sa_product_knowledge.modified_user_id != '' AND sa_product_knowledge.modified_user_id != 1 AND 
				date(date_add(sa_product_knowledge.date_modified, interval 3 hour)) = '$use_date'"; 
	
	$MonthlyQuery = "SELECT
				users.user_name,
				count(sa_product_knowledge.name) as msisdncount
				FROM
				sa_product_knowledge
				Inner Join users ON sa_product_knowledge.assigned_user_id = users.id
				WHERE sa_product_knowledge.call_status IN ('Answered','Busy') AND users.status = 'active' AND sa_product_knowledge.modified_user_id != '' AND sa_product_knowledge.modified_user_id != 1 AND 
				left(date(date_add(sa_product_knowledge.date_modified, interval 3 hour)),7) = '$current_month' group by user_name";
				
	//echo $MonthlyQuery;
	custom_query::select_db('survey');
	$entries = $myquery->multiple($query);
	$entriesMonth = $myquery->multiple($MonthlyQuery);
	$query = "select date_format('".$use_date."','%M') as this_month";
	$result = $myquery->single($query);
	$month = $result[this_month];

	foreach($entriesMonth as $row){
		$mthData[counts][$row[user_name]] .= $row[msisdncount];
	}
	
	foreach($entries as $row){
		 $data[heading][$row[call_status]] = $row[call_status];
		++$data[call_status][$row[call_status]];
		++$data[user_calls][$row[full_name]][$row[call_status]];
		++$data[team_perfomance][$row[team]][$row[call_status]];
		
		if($row[kawa_awareness] != ''){
			++$data[product][kawa][$row[kawa_awareness]]; 
		}
		if($row[pakalast_awareness] != ''){
			++$data[product][pakalast][$row[pakalast_awareness]];
		}
		if($row[crbt_awareness] != ''){
			++$data[product][crbt][$row[crbt_awareness]]; 
		}
		if($row[cmb_awareness] != ''){
			++$data[product][cmb][$row[cmb_awareness]]; 
		}
		if($row[pakachini_awareness] != ''){
			++$data[product][pakachini][$row[pakachini_awareness]]; 
		}
		if($row[crbt_lead] != '' && $row[crbt_lead] == 'Yes'){
			++$data[product][crbt][$row[crbt_lead].'Lead']; 
		}
		if($row[pakalast_lead] != '' && $row[pakalast_lead] == 'Yes'){
			++$data[product][pakalast][$row[pakalast_lead].'Lead'];
		}
		if($row[cmb_lead] != '' && $row[cmb_lead] == 'Yes'){
			++$data[product][cmb][$row[cmb_lead].'Lead'];
		}
		if($row[kawa_lead] != '' && $row[kawa_lead] == 'Yes'){
			++$data[product][kawa][$row[kawa_lead].'Lead']; 
		}
		if($row[pakachini_lead] != '' && $row[pakachini_lead] == 'Yes'){
			++$data[product][pakachini][$row[pakachini_lead].'Lead'];
		}
		if($row[warid_improve] != ''){
			$row[warid_improve] = str_replace('^','',$row[warid_improve]);
			$area_array = explode(',',$row[warid_improve]);
			foreach($area_array as $area){
				++$data[warid_improve][$area];
			}
		}
	}
	
	
	return display_cust_knowledge($data,$mthData,$month);
}

 function display_cust_knowledge($report,$mth,$month){
	$html .='
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;
		font-size:9px;
		white-space:nowrap;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	body{
		font-family:Verdana, Geneva, sans-serif;
	}
	
	label,
	.select,
	.textbox{
		font-size:9px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.values{
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		line-height:12px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	tr#totals td{
		font-size:10px;
		font-weight:bold;
		background-color:#CCC;
	}
	</style>
	</head>
	<body>';
	
$html .= '<table border="0" cellpadding="2" cellspacing="0" width="500px">
			<tr>
				<td valign="top">';
	$html .= '		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
						<tr>
							<th colspan="2">'.$month.' Stats </th>
						</tr>
						<tr>
							<td class="text_values">Total Number of Students</td>
							<td class="values">'.count($mth[counts]).'</td>
						</tr>
						<tr>
							<td class="text_values">Total Number of Calls</td>
							<td class="values">'.number_format(array_sum($mth[counts])).'</td>
						</tr>
					</table>
				</td>
			</tr>';
	
	if(count($report)>0){
	//$html .= '<table border="0" cellpadding="2" cellspacing="0" width="500px"><tr>';
	if(count($report[call_status])>0){
		$total = '';
		arsort($report[call_status]);
		$html .= '
					<td valign="top">
						 <table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
						 <tr>
							 <th colspan="2">Call Status</th>
						 </tr>
						 <tr>
							 <th>Call Status</th>
							 <th>Count</th>
						 </tr>';
						 foreach($report[call_status] as $status=>$count){
							$html .= '<tr><td class="text_values">'.$status.'</td>
										  <td class="values">'.number_format($count).'</td></tr>';
							$total += $count;
						 }
						 $html .= '
									<tr id="totals">
										 <td class="text_values">Total</td>
										 <td class="values">'.number_format($total).'</td>
									</tr>
						 </table>
					</td></tr>';
					}
	$html .= '</tr>
			 <tr><td>&nbsp;</td></tr>';
	if(count($report[product])>0){
		$totalYes = '';
		$totalNo = '';
		$totalLead = '';
		$html .= '  
				<tr>
					<!--<td>&nbsp;</td>-->
					<td valign="top">
						 <table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
						 <tr>
							 <th colspan="4">PRODUCT KNOWLEDGE AND LEAD GENERATION STATISTICS</th>
						 </tr>
						 <tr>
							 <th colspan="4"></th>
						 </tr>
						 <tr>
							 <th>PRODUCT</th>
							 <th>Customers that Knew About the Product</th>
							 <th>Customers that Didnt Know About the Product</th>
							 <th>Leads Generated</th>
						 </tr>';
						 foreach($report[product] as $product=>$values){
							$html .= '<tr>
										  <td class="text_values">'.strtoupper($product).'</td>
										  <td class="values">'.number_format($values[Yes]).'</td>
										  <td class="values">'.number_format($values[No]).'</td>
										  <td class="values">'.number_format($values[YesLead]).'</td>
									</tr>';
							$totalYes += $values[Yes];
							$totalNo += $values[No];
							$totalLead += $values[YesLead];
							
						}
						 $html .= '
									<tr id="totals">
										 <td class="text_values">Total</td>
										 <td class="values">'.number_format($totalYes).'</td>
										 <td class="values">'.number_format($totalNo).'</td>
										 <td class="values">'.number_format($totalLead).'</td>
									</tr>
						 </table>
					</td>';
	}
	$html.= '
		</tr>
		<tr>
		<tr><td>&nbsp;</td></tr>';

	if(count($report[warid_improve])>0){
	$total = '';
	arsort($report[rows][warid_improve]);
	$html.= '
		<!--<td>&nbsp;</td>-->
		<td valign="top">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					 <th colspan="2" class="th1">REQUESTED WARID IMPROVEMENTS</th>
				</tr>
				<tr>
					 <th>AREA</th>
					 <th>Count</th>
				</tr>';
				foreach($report[warid_improve] as $area=>$count){
					$html .= '
						<tr>
							<td class="text_values">'.$area.'</td>
							<td class="values">'.number_format($count).'</td>
						</tr>
					';
					$total += $count;
				}
					 
				$html .= '
					<tr id="totals">
						<td class="text_values">Total</td>
						<td class="values">'.number_format($total).'</td>
					</tr>
			</table>
		</td>
	';
	}
	
	if(count($report[heading])>0){
	
	$html.= '
		</tr>
		<tr>
		<tr><td>&nbsp;</td></tr>';
	sort($report[heading]);
	$total = '';
	$html.= '  
				<tr>
					<td valign="top">
						 <table border="0" cellpadding="0" cellspacing="0" width="100%">
						 <tr>
							 <th colspan="5" class="th1">CALLS PER USER</th>
						 </tr>
						 <tr>
							<th></th>
							 <th>AGENT</th>';
							 foreach($report[heading] as $headers){
							 $html .= '<th>'.$headers.'</th>';
					}
						 $html .= '
						  <th>Total</th>
						 </tr>';
						  $p='';
						 foreach($report[user_calls] as $users=>$options_row){
						 ++$p;
							$html .= '<tr class="sortable">	
											<td class="text_values">'.$p.'</td>
											<td class="text_values">'.$users.'</td>
											<td class="values">'.number_format($options_row[Answered]).'</td>
											<td class="values">'.number_format($options_row[Busy]).'</td>';
											$i = $options_row[Busy]; $y =$options_row[Answered];$x=$i+$y;
											$totalAnswered += $options_row[Answered];
											$totalBusy += $options_row[Busy];
											$grandTotal += $x;
									$html .= '<td class="values">'.number_format($x).'</td>
											</tr>';
											
						 }
						 $html .= '
									<tr id="totals">
										<td class="values"></td>
										 <td class="text_values">Total</td>
										 <td class="values">'.number_format($totalAnswered).'</td>
										 <td class="values">'.number_format($totalBusy).'</td>
										 <td class="values">'.number_format($grandTotal).'</td>
									</tr>
						 </table>
					</td>';
	}if(count($report) <= 0)
	{
		$html .= '<tr>
					<td valign="top">
						 <table border="0" cellpadding="0" cellspacing="0" width="100%">
							 <tr>
								 <td>No Activities Yesterday</td>
							</tr>
						</table>
					</td>';
	}
	
	$html.= '
		</tr>
		</table>
	';
	}
		$html .= '</tr>
		
		</table>
		</body>
		</html>';
	
	return $html;
}
		

?>