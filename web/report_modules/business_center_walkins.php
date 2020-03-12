<?

function generate_walkins_report($from,$to,$reporttype,$camera_id='',$business_centre_id=''){
	
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	if(!$from){
		$from = date('Y-m-d',strtotime("-1 days"));
	}
	$_POST[from] = $from;
	$from .= ' 00:00:00';
	
	if(!$to){
		$to = date('Y-m-d',strtotime("-1 days"));
	}
	$_POST[to] = $to;
	$to .= ' 23:59:59';
	
	
	$query = "
		SELECT
			LEFT(bc_traffic.traffic_date_start,10) AS date_entered,
			bc_cameras.name as camera_name,
			bc_traffic.traffic_in,
			bc_traffic.traffic_out,
			bc_names.name as business_centre_name,
			(bc_traffic.traffic_in + bc_traffic.traffic_out)/2 as avg_traffic
		FROM
			bc_cameras
			Inner Join bc_traffic ON bc_cameras.id = bc_traffic.camera_id
			Inner Join bc_names ON bc_names.id = bc_cameras.bc_name_id
		WHERE
			bc_traffic.traffic_date_start BETWEEN '".$from."' AND '".$to."'
	";
	
	if($camera_id != ''){
		$query .= " AND bc_cameras.id = '".$camera_id."' ";
	}
	
	if($business_centre_id != ''){
		$query .= " AND bc_names.id = '".$business_centre_id."' ";
	}
	
	$query .= "
		ORDER BY
			date_entered ASC
	";

	function summarise($entries){
		foreach($entries as $row){
			
			$row[month] = substr($row[date_entered],0,7);
			
			$summary['Avg Traffic by Month'][$row[month]] += $row[avg_traffic];
			$summary['Avg Traffic by Business Centre'][$row[business_centre_name]] += $row[avg_traffic];
			$summary['Avg Traffic by Month by Business Centre'][$row[month]." >> ".$row[business_centre_name]] += $row[avg_traffic];
			
			$summary['Avg Traffic by Business Centre by Camera'][$row[business_centre_name]." >> ".$row[camera_name]] += $row[avg_traffic];
			
			$summary['Avg Traffic by Date'][$row[date_entered]] += $row[avg_traffic];
			$summary['Avg Traffic by Date by Business Centre'][$row[date_entered]." >> ".$row[business_centre_name]] += $row[avg_traffic];
		}
		
		return $summary;	
	}
	
	//echo $query."<hr>"; exit();
	
	$entries = $myquery->multiple($query);
	
	if(count($entries) == 0) { return display_sales_report('NO DATA'); }
	
	switch($reporttype){
		case 'detail':
			$report[detail] = $entries;
			break;
		case 'both':
			$report[detail] = $entries;
			$report[summary] = summarise($entries);
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($entries);
			break;
	}
			
	return display_walkins_report($report);
}

function display_walkins_report($report){
	
	if($report == 'NO DATA'){ return 'No Data matches your filter selections'; }
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="80%">
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
						<td class="values">'.number_format($value,0).'</td>
					</tr>
				';
			}
			$html .= '
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
	
	//echo '<textarea rows="20" cols="50">'.print_r($report[detail],true).'</textarea>';
	
	if($report[detail]){
		$html .= '
			<tr>
				<th style="height:20px;">DETAILS</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>Business Centre</th>
						<th>Camera</th>
						<th>IN</th>
						<th>OUT</th>
						<th>AVG</th>
					</tr>
				
		';
		
		foreach($report[detail] as $row){
			$html .= '
					<tr>
						<td class="text_values">'.++$ii.'</td>
						<td class="text_values">'.$row[date_entered].'</td>
						<td class="text_values">'.$row[business_centre_name].'</td>
						<td class="text_values">'.$row[camera_name].'</td>
						<td class="values">'.number_format($row[traffic_in],0).'</td>
						<td class="values">'.number_format($row[traffic_out],0).'</td>
						<td class="values">'.number_format($row[avg_traffic],0).'</td>
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

?>