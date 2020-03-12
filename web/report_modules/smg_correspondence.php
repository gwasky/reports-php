<?
function generate_correnspondence($from,$to,$report_type,$categories,$subjects,$agents,$msisdns,$wrap_up_sources){
	
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	
	custom_query::select_db('reportscrm');

	$myquery = new custom_query();
	
	$query = "
		SELECT
			correspondance.category,
			correspondance.subcategory as subject,
			wrapup_source_type.`name` AS source_name,
			correspondance.createdon,
			correspondance.createdby as agent,
			correspondance.statusreason,
			correspondance.msisdn,
			correspondance.emailadd
		FROM
			correspondance
			LEFT OUTER JOIN wrapup_source_type ON correspondance.source = wrapup_source_type.id
		WHERE
			wrapup_source_type.`status` = 'Active' AND
	";
//--		wrapup_source_type.`name` != 'SMS' AND
		
	if(!$from){
		$_POST[from] = date('Y-m-d');
		$from = $_POST[from];
	}
	
	$period[start] = $from;
	
	if(!$to){
		$_POST[to] = date('Y-m-d');
		$to = $_POST[to];
	}
	$query .= " correspondance.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
	";
	
	if(count($categories) > 0 and !in_array('',$categories)){
		$query .= " AND (";
		foreach($categories as $count=>$category){
			$query .= " correspondance.category = '".$category."'";
			if(count($categories) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ") ";
	}
	
	if(count($wrap_up_sources) > 0 and !in_array('',$wrap_up_sources)){
		$query .= " AND (";
		foreach($wrap_up_sources as $count=>$wrap_up_source){
			$query .= " wrapup_source_type.`name` = '".$wrap_up_source."'";
			if(count($wrap_up_sources) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ") ";
	}
	
	if(count($subjects) > 0 and !in_array('',$subjects)){
		$query .= " AND (";
		foreach($subjects as $count=>$subject){
			$query .= " correspondance.subcategory = '".$subject."'";
			if(count($subjects) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ") ";
	}
	
	if(count($agents) > 0 && (!in_array('',$agents))){
		$query .= "AND (";
		foreach($agents as $count=>$agent){
			$query .= " correspondance.createdby = '".$agent."'";
			if(count($agents) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ")";
		unset($count);
	}
	
	if(trim($msisdns) != ''){
		$msisdns = explode(",",$msisdns);
		$query .= " AND correspondance.msisdn IN (";
		foreach($msisdns as $count=>$msisdn){
			$query .= "'".trim($msisdn)."'";
			if(count($msisdns) > $count+1){
				$query .= ",";
			}
		}
		$query .= ")";
		unset($count);
	}
	
	function summarise($entries, $agent_list){
		
		foreach($entries as $row){
			++$summary['Numbers by Source'][$row[source_name]];
			++$summary['Numbers by Month'][substr($row[createdon],0,7)];
			++$summary['Numbers by Agent'][$agent_list[$row[agent_key]]];
			++$summary['Numbers by Agent by Month'][$agent_list[$row[agent_key]]." >> ".substr($row[createdon],0,7)];
			++$summary['Numbers by Date'][substr($row[createdon],0,10)];
			++$summary['Numbers by Date by Source'][substr($row[createdon],0,10)." >> ".$row[source_name]];
			++$summary['Numbers by Date by Agent'][substr($row[createdon],0,10)." >> ".$agent_list[$row[agent_key]]];
			++$summary['Numbers by Category'][$row[category]];
			++$summary['Numbers by Category by Subject'][$row[category]." >> ".$row[subject]];
		}
		
		return $summary;
	}
	
	//echo nl2br($query)."<br>";
	$rows = $myquery->multiple($query);
	
	foreach($rows as &$row){
		if(str_replace(array(" "),"",$row[agent]) == '') { $row[agent] = 'None'; }
		$row[agent_key] = str_replace(array(" "),"",strtolower($row[agent]));
		
		if($report[agent_list][$row[agent_key]] == ''){
			$report[agent_list][$row[agent_key]] = $row[agent];
		}
	}
	
	switch($report_type){
		case 'detail':
			$report[rows] = $rows;
			break;
		case 'both':
			$report[rows] = $rows;
			$report[summary] = summarise($rows, $report[agent_list]);
			break;
		case 'summary':
			default:
			$report[summary] = summarise($rows, $report[agent_list]);
	}
	
	$report[stop] = strtotime(date('Y-m-d H:i:s'));
	
	return display_correnspondence($report);
}

function display_correnspondence($report){

	//print_r($report);
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="80%">
			<tr>
				<td>Report took ['.($report[stop] - $report[start]).'] seconds to run</td>
			</tr>
			<!--class="sortable"-->
	';
	
	if(count($report[rows]) > 0){
		$html = '
		<tr>
		<td style="height:20px;">DETAILS</td>
		</tr>
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th></th>
				<th>Date</th>
				<!--<th>Time</th>-->
				<th>Source</th>
				<th>Category</th>
				<th>Subject</th>
				<th>Email</th>
				<th>MSISDN</th>
				<th>Agent</th>
			</tr>
		';
		
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="values">'.substr($row[createdon],0,10).'</td>
					<!--<td class="values">'.substr($row[createdon],-8).'</td>-->
					<td class="values">'.$row[source_name].'</td>
					<td class="text_values">'.ucfirst($row[category]).'</td>
					<td class="text_values">'.ucfirst($row[subject]).'</td>
					<td class="text_values">'.strtolower($row[emailadd]).'</td>
					<td class="values">'.$row[msisdn].'</td>
					<td class="text_values">'.ucfirst($row[agent]).'</td>
				</tr>
			';
		}
		$html .= '
		</table>
		</td></tr>
		';
	}

	//if we have both reports let us space them by a row
	if((count($report[rows]) > 0) &&(count($report[summary]) > 0)){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			$html .= '
				<tr>
					<td style="font-size:15px;">'.$summary_heading.'</td>
				</tr>
				<tr>
					<td>
					<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
						<tr>
							<th>#</th>
			';
			
			//Titles
			$columns = explode(" by ",$summary_heading);
			$first_col = array_shift($columns);
			
			foreach($columns as $column){
				$html .= '
							<th>'.$column.'</th>
				';
			}
			
			$html .= '
					<th>'.$first_col.'</th>
			';
			
			$html .= '
						</tr>
			';
			
			//row
			unset($row_counter);
			foreach($summary_data as $parameter_string=>$parameter_string_value){
				
				$html .= '
						<tr>
							<td class="values">'.++$row_counter.'</td>
				';
				
				$parameters = array();
				$parameters = explode(" >> ",$parameter_string);
				
				foreach($parameters as $parameter){
					$html .= '
							<td class="'; if(!is_numeric($parameter)){ $html .= 'text_'; } $html .= 'values">'; 
								if(!is_numeric($parameter)){ $html .= $parameter; }else{ $html .= number_format($parameter,0); } $html .= '
							</td>
					';
				}
				$html .= '
							<td class="values">'.number_format($parameter_string_value,0).'</td>
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
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>