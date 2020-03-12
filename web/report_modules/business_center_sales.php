<?

function generate_sales_report($from,$to,$report_type,$business_centre,$items){
	
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	$sales_query = "
		SELECT
			bc_sales.entry_date,
			bc_item_groups.group_name as grouping,
			bc_items.item_name,
			bc_items.cost as unit_cost,
			bc_items.price as unit_price,
			(bc_sales.amount/bc_sales.qty) AS calc_price,
			bc_sales.qty as quantity,
			bc_sales.amount,
			if(bc_item_groups.group_name NOT IN ('COLLECTIONS','WARID PESA CASHFLOWS','RENTALS'),(bc_sales.qty * bc_items.cost),bc_sales.amount) as amount_cost,
			if(bc_item_groups.group_name NOT IN ('COLLECTIONS','WARID PESA CASHFLOWS','RENTALS'),amount - (if(bc_item_groups.group_name NOT IN ('COLLECTIONS','WARID PESA CASHFLOWS','RENTALS'),(bc_sales.qty * bc_items.cost),bc_sales.amount)), 0) AS sales_margin,
			bc_names.`name` AS business_centre,
			bc_regions.region_name
		FROM
			bc_sales
			INNER JOIN bc_items ON bc_sales.item_id = bc_items.id
			INNER JOIN bc_names ON bc_sales.business_centre_id = bc_names.id
			INNER JOIN bc_regions ON bc_names.region_id = bc_regions.id
			INNER JOIN bc_item_groups ON bc_items.group_id = bc_item_groups.id
		WHERE
	";
	
	if(!$from){
		$_POST[from] = date('Y-m-01');
		$from = $_POST[from];
	}
	
	if(!$to){
		$_POST[to] = date('Y-m-d',strtotime("-1 days"));
		$to = $_POST[to];
	}
	
	$sales_query .= "
		bc_sales.entry_date BETWEEN '".$from."' AND '".$to."'
	";
	
	if($business_centre != ''){
		$sales_query .= "
			AND bc_names.`name` = '".$business_centre."'
		";
	}
	
	if(count($items) > 0 and !in_array('',$items)){
		$sql_in_bracket = "(";
		foreach($items as $item_key=>$item){
			$sql_in_bracket .= "'$item'";
			
			if($item_key + 1 < count($items)) { $sql_in_bracket .= ","; }
		}
		$sql_in_bracket .= ")";
		$sales_query .= "
			AND bc_items.item_name IN ".$sql_in_bracket."
		";
	}

	function summarise($entries){
		foreach($entries as &$row){
			
			$summary['Total by Grouping'][$row[grouping]] += $row[amount];
			if(!in_array($row[grouping],array('COLLECTIONS','WARID PESA CASHFLOWS','RENTALS'))){
				$summary['Items by Unit Cost'][$row[item_name]] = $row[unit_cost];
			}
			$summary['Sales by Month by Grouping'][substr($row[entry_date],0,7)." >> ".$row[grouping]] += $row[amount];
			$summary['Sales by Month by Grouping by Business Centre'][substr($row[entry_date],0,7)." >> ".$row[grouping]." >> ".$row[business_centre]] += $row[amount];
			
			$summary['Sales by Region'][$row[region]] += $row[amount];
			$summary['Sales by Business Centre'][$row[business_centre]] += $row[amount];
			$summary['Sales by Month'][substr($row[entry_date],0,7)] += $row[amount];
			$summary['Sales Margin by Month'][substr($row[entry_date],0,7)] += $row[sales_margin];
			$summary['Sales by Month by Business Centre'][substr($row[entry_date],0,7)." >> ".$row[business_centre]] += $row[amount];
			$summary['Sales Margin by Month by Business Centre'][substr($row[entry_date],0,7)." >> ".$row[business_centre]] += $row[sales_margin];
			$summary['Sales by Date'][$row[entry_date]] += $row[amount];
			
			//3 COLUMN REPORTS
			$summary['Quantity by Category by Item'][$row[grouping].' >> '.$row[item_name]] += $row[quantity];
			$summary['Sales by Category by Item'][$row[grouping].' >> '.$row[item_name]] += $row[amount];
			$summary['Quantity Margin by Category by Item'][$row[grouping].' >> '.$row[item_name]] += $row[quantity];
			$summary['Sales Margin by Category by Item'][$row[grouping].' >> '.$row[item_name]] += $row[sales_margin];
			$summary['Sales by Date by Business Centre'][$row[entry_date].' >> '.$row[business_centre]] += $row[amount];
			
			//4COLUMN REPORTS
			$summary['Sales by Date by Business Centre by Category by Item'][$row[entry_date].' >> '.$row[business_centre].' >> '.$row[grouping].' >> '.$row[item_name]] += $row[amount];
			$summary['Quantity by Date by Business Centre by Category by Item'][$row[entry_date].' >> '.$row[business_centre].' >> '.$row[grouping].' >> '.$row[item_name]] += $row[quantity];
			
			/*
			if($row[item] != 'Withdrawal' and $row[item] != 'Deposit'){
				$summary['Total sales by Region'][$row[region]] += $row[amount];
				$summary['Total sales by Business Centre'][$row[business_centre]] += $row[amount];
				$summary['Total sales by Month'][substr($row[entry_date],0,7)] += $row[amount];
				$summary['Total sales by Month by Business Centre'][substr($row[entry_date],0,7)." >> ".$row[business_centre]] += $row[amount];
				$summary['Total sales by Date'][$row[entry_date]] += $row[amount];
				$summary['Total sales by Category'][$row[category]] += $row[amount];
				
				//3 COLUMN REPORTS
				$summary['Total sales by Category by Item'][$row[category].' >> '.$row[item]] += $row[amount];
				$summary['Total sales by Date by Business Centre'][$row[entry_date].' >> '.$row[business_centre]] += $row[amount];
			}else{
				if($row[item] == 'Bill Payment'){
					//WIMAX AND POSTPAID BILLS
					
				}else{
					//WARID PESA
					$summary['Total Warid Pesa cash flows by Direction'][$row[item]] += $row[amount];
					
					//3 COLUMNS
					$summary['Total Warid Pesa cash flows by Direction by Business Centre'][$row[item].' >> '.$row[business_centre]] += $row[amount];
					
					//4 COLUMNS
					$summary['Total Warid Pesa cash flows by Direction by Business Centre by Month'][$row[item].' >> '.$row[business_centre].' >> '.substr($row[entry_date],0,7)] += $row[amount];
					$summary['Total Warid Pesa cash flows by Direction by Business Centre by Date'][$row[item].' >> '.$row[business_centre].' >> '.$row[entry_date]] += $row[amount];
				}
			}
			*/
		}
		
		return $summary;	
	}
	
	//echo "<pre>".$sales_query."<hr>"; //exit();
	
	$entries = $myquery->multiple($sales_query);
	
	if(count($entries) == 0) { return display_sales_report('NO DATA'); }
	
	switch($report_type){
		case 'detail':
			$report[detail] = $entries;
			break;
		case 'both':
			$report[detail] = $entries;
			$report[summary] = summarise($entries);
			break;
		case 'summary':
		default:
			$_POST[report_type] = 'summary';
			$report[summary] = summarise($entries);
			break;
	}
			
	return display_sales_report($report);
}

function display_sales_report($report){
	
	if($report == 'NO DATA'){ return 'No Data matches your filter selections'; }
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0">
	';
	
	if($report[summary]){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARY</th>
			</tr>
		';
		
		foreach($report[summary] as $sub_title=>$sub_title_data){
			
			//EXTRACTING PARAMETER TILES FROM THE TITLE STRING: ie Totals by parameter title by parameter title
			$parameter_title_list = explode(" by ",$sub_title);
			
			$html .= '
			<tr>
				<th width="500px;">'.$sub_title.'</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable">
					<tr>
			';
			
			foreach($parameter_title_list as $kkey=>$parameter_title){
				if($kkey != 0){
					//EXCLUDING THE "Totals" IN "Totals by parameter title by parameter title"
					$html .= '
							<th>'.$parameter_title.'</th>
					';
				}
			}
			
			$html .= '
						<th>Value</th>
					</tr>
			';
			$running_total = 0;
			foreach($sub_title_data as $parameter_string=>$value){
				$parameter_list = explode(' >> ',$parameter_string);
				$html .= '
					<tr>
				';
				
				foreach($parameter_list as $parameter){
					$html .= '
						<td class="text_values">'.$parameter.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($value,bc_sales_no_of_decimals($value,3)).'</td>
					</tr>
				';
				
				$running_total += $value; 
			}
			$html .= '
					<tr id="totals">
						<td class="text_values" colspan="'.count($parameter_list).'">TOTAL</td>
						<td class="values">'.number_format($running_total,bc_sales_no_of_decimals($value,3)).'</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
			';
		}
	}
	
	if($report[summary] and $report[detail]){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if($report[detail]){
		$html .= '
			<tr>
				<th style="height:20px;">DETAILS</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="1" cellspacing="0" class="sortable">
					<tr>
						<th>Date</th>
						<th>Item Grouping</th>
						<th>Item</th>
						<th>Item Cost</th>
						<th>Set Price</th>
						<th>Avg Price</th>
						<th>Quantity</th>
						<th>Amount</th>
						<th>Total Cost</th>
						<th>Margin</th>
						<th>Business Centre</th>
						<th>Region</th>
					</tr>
				
		';
		
		foreach($report[detail] as $row){
			if(round($row[unit_price],2) != round($row[calc_price],2) and (!in_array($row[grouping],array('COLLECTIONS','WARID PESA CASHFLOWS','RENTALS')))){
				$tr_style = 'class="flagged"';
			}else{
				unset($tr_style);
			}
			$html .= '
					<tr '.$tr_style.'>
						<td class="text_values">'.$row[entry_date].'</td>
						<td class="text_values">'.$row[grouping].'</td>
						<td class="text_values">'.$row[item_name].'</td>
						<td class="values">'.number_format($row[unit_cost],4).'</td>
						<td class="values">'.number_format($row[unit_price],2).'</td>
						<td class="values">'.number_format($row[calc_price],2).'</td>
						<td class="values">'.number_format($row[quantity],0).'</td>
						<td class="values">'.number_format($row[amount],0).'</td>
						<td class="values">'.number_format($row[amount_cost],2).'</td>
						<td class="values">'.number_format($row[sales_margin],2).'</td>
						<td class="text_values">'.$row[business_centre].'</td>
						<td class="text_values">'.$row[region_name].'</td>
					</tr>
			';
		}
		
		$html .= '
				</table>
				</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';

	return $html;
}

function bc_sales_no_of_decimals($float,$boundary){
	
	$dec = $float - intval($float);
	$decs = substr($dec,2,strlen($dec) - 2);
	$no = strlen((string)$decs);
	
	if($no < $boundary){
		$no = 0;
	}elseif($no == $boundary){
		$no = $boundary;
	}else{
		$no = 0;
	}

	return $no;
}

?>