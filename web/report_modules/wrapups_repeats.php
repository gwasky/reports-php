<?php
function generate_repeat_cca_wrapups($from,$to,$interval = 1,$agents,$cat,$sub_cat,$subject,$msisdn,$report_type){
	$report[start_time] = date('Y-m-d H:i:s');
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	//Listo of agents
	if(count($agents) == 0){
	}else{
	}
	
	if(intval($interval) <= 0){
		$period[days] = 1;
		$period[sec] = $period[days] * 24 * 3600;
	}else{
		$period[days] = $interval;
		$period[sec] = $period[days] *  24 * 3600;
	}
	//$period[sec] = 0.25 * 24 * 3600;
	
	$_POST[interval] = $period[days];
	$period[start] = $from;

	
	if($from==''){
		$_POST[from] = date('Y-m-d',strtotime("- 1 days"));
		$from = $_POST[from];	
	}
	
	if($to==''){
		$_POST[to] = date('Y-m-d',strtotime("- 1 days"));
		$to = $_POST[to];
	}
	
	if($msisdn != ''){
		$msisdn_query = " AND reportsphonecalls.phonenumber = '".$msisdn."' ";
	}
	
	/*
	$agents_query_list = "('";
	foreach($agents as $agent){
		++$i;
		$agents_query_list .= $agent[agent];
		if($i < count($agents)){
			$agents_query_list .= "','";
		}
	}
	$agents_query_list .= "')";
	*/
	
	$totals_query = "
		SELECT
			LEFT(reportsphonecalls.createdon,10) as createdon,
			count(*) as num
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon between  '".$from." 00:00:00' and '".$to." 23:59:59'
			".$msisdn_query."
		GROUP BY
			reportsphonecalls.createdon
	";
	
	//GET TOTAL WRAP UPS BY DAY
	$total_wrapups = $myquery->multiple($totals_query,'ccba02.reportscrm');
	
	foreach($total_wrapups as $row){
		$totals[wrapups_by_date][$row[createdon]] += $row[num];
		$totals[wrapups_by_month][substr($row[createdon],0,7)] += $row[num];
	}
	
	$wrapup_query = "
		SELECT
			reportsphonecalls.phonenumber as msisdn,
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(*) as num
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon between DATE_SUB('".$from." 00:00:00', INTERVAL 1 DAY) AND '".$to." 23:59:59'
			".$msisdn_query."
		GROUP BY
			reportsphonecalls.phonenumber, reportsphonecalls.wrapupsubcat, reportsphonecalls.subject
		HAVING
			num > 1
	";
	
	//GET THE INDIVIDUAL WRAP UPS SUBJECTS, CATEOGRIES AND NUMBERS WITH REPEAT WRAP UPS
	$repeated_wrapups = $myquery->multiple($wrapup_query,'ccba02.reportscrm');
	
	//echo $wrapup_query.'<br><hr>Number is '.count($repeated_wrapups)."<hr>";
	
	//GET THE INDIVIDUAL WRAP UPS THAT WERE REPEATED
	foreach($repeated_wrapups as $wrapup){
		/*
		$query = "
			SELECT
				reportsphonecalls.createdon,
				LOWER(reportsphonecalls.createdby) AS agent,
				wrapupcall_type.`name` AS caller_type
			FROM
				reportsphonecalls
				LEFT OUTER JOIN wrapupcall_type ON reportsphonecalls.wrapupcall_type = wrapupcall_type.id
			WHERE
				reportsphonecalls.createdon between  '".$from." 00:00:00' and '".$to." 23:59:59' AND
				reportsphonecalls.phonenumber = '".$wrapup[msisdn]."' AND
				reportsphonecalls.wrapupsubcat = '".$wrapup[category]."' AND
				reportsphonecalls.subject = '".$wrapup[subject]."'
			ORDER BY
				reportsphonecalls.createdon ASC
		";
		*/
		
		$query = "
			SELECT
				reportsphonecalls.createdon,
				LOWER(reportsphonecalls.createdby) AS agent,
				wrapupcall_type.`name` AS caller_type
			FROM
				reportsphonecalls
				LEFT OUTER JOIN wrapupcall_type ON reportsphonecalls.wrapupcall_type = wrapupcall_type.id
			WHERE
				reportsphonecalls.createdon between DATE_SUB('".$from." 00:00:00', INTERVAL 1 DAY) and '".$to." 23:59:59' AND
				reportsphonecalls.phonenumber = '".$wrapup[msisdn]."' AND
				reportsphonecalls.wrapupsubcat = '".$wrapup[category]."' AND
				reportsphonecalls.subject = '".$wrapup[subject]."'
			ORDER BY
				reportsphonecalls.createdon ASC
		";
		
		$repeat_wrapups = $myquery->multiple($query,'ccba02.reportscrm');
		
		//exit($query.'<br><hr>Number is '.count($repeat_wrapups));
		
		foreach($repeat_wrapups as $key=>$row){
			//++$uuuu;
			$row[agent] = ucwords($repeat_wrapups[$key][agent]);
			
			//GETTING REPEAT CALLS BY AGENT
			if(
				strtotime($row[createdon]) >= strtotime($from.' 00:00:00') or								//THIS WRAP UP IS WITHIN THE FROM TO PERIOD
				(																							//THIS WRAP UP IS THE LAST ONE BEFORE THE
				 	strtotime($repeat_wrapups[$key][createdon]) < strtotime($from.' 00:00:00') and			//FROM VALUE SELECTED IN THE FILTERS
					is_array($repeat_wrapups[$key+1]) and													//EG
					strtotime($repeat_wrapups[$key+1][createdon]) >= strtotime($from.' 00:00:00')			//22:00:00 FOLLOWED BY 02:00:00
				)
			){
				if($data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_agent] == ''){
					//echo $uuuu." Recording first agent ".$wrapup[msisdn]." - ".print_r($row,true)." <br>";
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_agent] = $row[agent];
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents][] = $row[agent];
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_time] = $row[createdon];
				}else{
					//echo $uuuu." Apending agent repeat call ".$wrapup[msisdn]." - ".print_r($row,true)." <br>";
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents][] = $row[agent];
				}
			}else{
				//echo $uuuu." Discarding wrap up ".$wrapup[msisdn]." - ".print_r($row,true)." <br>";
			}
			
			if($key > 0 and strtotime($row[createdon]) >= strtotime($from.' 00:00:00')){
				//echo $uuuu." Apending repeat call counts ".$wrapup[msisdn]." - ".print_r($row,true)." <br>";
				//YOU CAN ONLY SUBTRACT THIS ONE FROM THE PREVIOUS ONE IF THERE IS A PREVIOUS ONE 
				$row[previous_calltime] = $repeat_wrapups[$key-1][createdon];
				$row[repeat_duration] = strtotime($row[createdon]) - strtotime($repeat_wrapups[$key-1][createdon]);
				
				if($row[repeat_duration] <= $period[sec]){
					//PERIOD DIFF <= 24 HOURS
					++$report[summary]['Repeat Wrapups by Month by Total Wrapups'][substr($row[createdon],0,7).">>>".$totals[wrapups_by_month][substr($row[createdon],0,7)]];
					++$report[summary]['Repeat Wrapups by Date by Total Wrapups'][substr($row[createdon],0,10).">>>".$totals[wrapups_by_date][substr($row[createdon],0,10)]];
					//++$report[summary]['Repeat Wrapups by Category'][$wrapup[category]];
					++$report[summary]['Repeat Wrapups by Caller Type'][$row[caller_type]];
					++$report[summary]['Repeat Wrapups by Category by Subject'][$wrapup[category].">>>".$wrapup[subject]];
					++$report[summary]['Repeat Wrapups by Month by Category by Subject'][substr($row[createdon],0,7).">>>".$wrapup[category].">>>".$wrapup[subject]];
					++$report[summary]['Repeat Wrapups by Date by Category by Subject'][substr($row[createdon],0,10).">>>".$wrapup[category].">>>".$wrapup[subject]];
					++$report[summary]['Repeat Wrapups by MSISDN'][$wrapup[msisdn]];
					++$report[summary]['Repeat Wrapups by Category by Subject by MSISDN'][$wrapup[category].">>>".$wrapup[subject].">>>".$wrapup[msisdn]];
					
					$this_first_agent = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_agent];
					$this_first_time = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_time];
					
					$data_agents = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents];
					
					foreach($data_agents as $data_agent){
						++$report[summary]['Repeat Wrapups by Agent'][$data_agent];
						//++$report[summary]['Repeat Wrapups by Agent by Category by Subject'][$data_agent.">>>".$wrapup[category].">>>".$wrapup[subject]];
						//++$report[summary]['Repeat Wrapups by Date by Agent by Category by Subject'][substr($this_first_time,0,10).">>>".$data_agent.">>>".$wrapup[category].">>>".$wrapup[subject]];
					}
					
					//DETAIL OF THE REPORT 
					$report_row[first_agent] = $this_first_agent;
					$report_row[createdon] = $row[createdon];
					$report_row[category] = $wrapup[category];
					$report_row[subject] = $wrapup[subject];
					$report_row[agent] = $row[agent];
					$report_row[msisdn] = $wrapup[msisdn];
					$report_row[caller_type] = $row[caller_type];
					$report_row[previous_calltime] = $row[previous_calltime];
					$report_row[repeat_duration] = $row[repeat_duration];
					
					$report[rows][] = $report_row;
				}else{
					unset($data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]]);
					//echo $uuuu."Apending - discard on repeat call counts ".$wrapup[msisdn]." - ".print_r($row,true)." <br>";
				}
			}
		}
	}
	
	//echo "rEPORT TYPE ".$report_type." <HR> ".print_r($_POST,true)."<hr>";
	
	switch($report_type){
		case 'both':
			//DO NOTHING
			break;
		case 'detail':
			unset($report[summary]);
			break;
		case 'summary':
		default:
			$_POST[report_type] = 'summary';
			unset($report[rows]);
			break;
	}
	
	$report[end_time] = date('Y-m-d H:i:s');
	$report[duration] = strtotime($report[end_time]) - strtotime($report[start_time]);
	return display_repeat_cca_wrapups($report);
}

function display_repeat_cca_wrapups($report){

	//print_r($report);

	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<tr>
				<td class="text_values">Report took '.sec_to_time($report[duration]).' to run</td>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
	';
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th>SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			//SORT FROM HIGHEST TO LOWSEST
			//arsort($summary_data);
			
			$column_titles = explode(" by ",strtolower($summary_heading));
			//REMOVE THE FIRST ELEMENT OF THE ARRAY IE THE PART THAT SAYS "Repeat Wrapups by"
			array_shift($column_titles);
			
			$html .= '
			<tr>
				<th>'.$summary_heading.'</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
						<tr>
							<th>#</th>
			';
			
			foreach($column_titles as $column_title){
				$html .= '
							<th>'.ucwords(strtolower($column_title)).'</th>
				';
			}
			
			$html .= '
							<th>No of repeat Wrapups</th>
						</tr>
			';
			
			foreach($summary_data as $row_key=>$row_value){
				$column_values = explode(">>>",$row_key);
				
				$html .= '
						<tr>
							<td class="values">'.++$i.'</td>
				';
			
				foreach($column_values as $column_value){
					if(is_numeric($column_value) and strlen($column_value) != 12 and substr($column_value,0,3) != '256'){
						$column_value = number_format($column_value,0);
						$class = "values";
					}else{
						$class = "text_values";
					}
					
					$html .= '
							<td class="'.$class.'">'.$column_value.'</td>
					';
				}
			
				$html .= '
							<td class="values">'.number_format($row_value,0).'</td>
						</tr>
				';
			}
			unset($i);
			
			$html .= '
					</table>
				</td>
			</tr>
			';
			
			//ADDING A SPACER BETWEEN SUMMARY TABLES
			if(++$summary_table_count != count($report[summary])){
				$html .= '
			<tr>
				<td style="height:10px;"></td>
			</tr>
				';
			}
		}
	}
	
	if(count($report[summary]) > 0 and count($report[rows]) > 0){
		$html .= '
			<tr>
				<td style="height:15px;"></td>
			</tr>
		';
	}
	
	if(count($report[rows]) > 0){
		$html .= '
			<tr>
				<th>DETAILS</th>
			</tr>
		';
		$html .= '
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
						<tr>
							<th>#</th>
							<th>Date</th>
							<th>First Agent</th>
							<th>Category</th>
							<th>Subject</th>
							<th>Caller Type</th>
							<th>MSISDN</th>
							<th>Agent</th>
							<th>Interval</th>
						</tr>
		';
		
		unset($row);
		
		foreach($report[rows] as $row){
			$html .= '
						<tr>
							<td class="values">'.++$j.'</td>
							<td class="values">'.$row[createdon].'</td>
							<td class="text_values">'.$row[first_agent].'</td>
							<td class="text_values">'.$row[category].'</td>
							<td class="text_values">'.$row[subject].'</td>
							<td class="text_values">'.$row[caller_type].'</td>
							<td class="values">'.$row[msisdn].'</td>
							<td class="text_values">'.ucwords(strtolower($row[agent])).'</td>
							<td class="values">'.sec_to_time($row[repeat_duration]).'</td>
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