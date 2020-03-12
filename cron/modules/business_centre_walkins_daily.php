<?php
function generate_business_centre_walkins_daily($date){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	if($date == '') $date = date('Y-m-d', strtotime("-1 days"));
	
	$query = "
		SELECT
			LEFT(bc_traffic.traffic_date_start,10) AS date_entered,
			bc_cameras.name AS camera_name,
			bc_names.name AS centre_name,
			(bc_traffic.traffic_in + bc_traffic.traffic_out)/2 AS walkins
		FROM
			bc_cameras
			Inner Join bc_traffic ON bc_cameras.id = bc_traffic.camera_id
			Inner Join bc_names ON bc_names.id = bc_cameras.bc_name_id
		WHERE
			bc_traffic.traffic_date_start BETWEEN DATE_SUB('".$date." 00:00:00', INTERVAL 30 DAY) AND '".$date." 23:59:59'
		ORDER BY
			date_entered ASC
	";
	
	$traffic = $myquery->multiple($query,'ccba02.businesssales');
	
	if(count($traffic) == 0){ exit('NO DATA'); }
	
	foreach($traffic as $row){
		$report[dates][$row[date_entered]] = $row[date_entered];
		$report[cameras][$row[camera_name]] = $row[camera_name];
		$report[centres][$row[centre_name]] = $row[centre_name];
		
		$report[graph][$row[centre_name]][$row[date_entered]] += $row[walkins];
		
		$report[table]['Traffic by Camera'][$row[date_entered]][$row[camera_name]] = $row[walkins];
		$report[table]['Traffic by Centre'][$row[date_entered]][$row[centre_name]] += $row[walkins];
	}
	
	//STANDARDISING GRAPH DATA ADDING ZEROS WHERE THEY ARE SUPPOSED TO BE
	foreach($report[centres] as $centre){
		foreach($report[dates] as $entry_date){
			$graph_data[$centre][$entry_date] = round($report[graph][$centre][$entry_date]);
			foreach($report[cameras] as $camera){
				$table_data['Traffic by Camera'][$entry_date][$camera] = round($report[table]['Traffic by Camera'][$entry_date][$camera]);
				$table_data['Traffic by Centre'][$entry_date][$centre] = round($report[table]['Traffic by Centre'][$entry_date][$centre]);
			}
		}
	}
	
	$report[graph] = $graph_data; unset($graph_data);
	$report[table] = $table_data; unset($table_data);
	
	$graph_detail[data]=$report[graph];
	$graph_detail[title]='Business Centre Walkins '.date_format(date_create($date),'F Y');
	$graph_detail[display_title]=false;
	$graph_detail[legend]=true;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=false;
	$graph_detail[width]=850;
	$graph_detail[height]=600;
	
	$my_graph->graph($graph_detail[title],"30 days before ".$date, $graph_detail);
	custom_query::select_db('graphing');
	$report[graph_id] = $my_graph->Save();
	
	return display_business_centre_walkins_daily($report);
}

function display_business_centre_walkins_daily($report){
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<TH>BUSINESS CENTRE WALK INS FOR THE LAST 30 DAYS</TH>
			</tr>
			<tr>
				'.display_generic_graph($report[graph_id],TRUE).'
			</tr>
			<tr>
				<TD height="20"></TD>
			</tr>
			<tr>
				<TD>
				<table width="850" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th width="33%">DATES</th>
	';
	
	//OTHER VALUES OF HEADING ROW
	foreach($report[centres] as $centre){
		$html .= '
						<th width="33%">'.$centre.'</th>
		';
	}
	
	$html .= '
					</tr>
	';
	
	//DATA ROWS
	foreach($report[dates] as $entry_date){
		$html .= '
					<tr>
						<td class="text_values">'.$entry_date.'</td>
		';
		
		foreach($report[centres] as $centre){
			$html .= '
						<td class="values">'.number_format($report[table]['Traffic by Centre'][$entry_date][$centre],0).'</td>
			';
		}
		
		$html .= '
					</tr>
		';
	}
	
	$html .= '
				</table>
				</TD>
			</tr>
		</table>
	';
	
	return $html;
}
?>