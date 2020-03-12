<?php
ini_set('memory_limit','1028M');
function generate_repeat_cca_wrapups($to,$interval = 1,$agents,$cat,$sub_cat,$subject){
	$report[start_time] = date('Y-m-d H:i:s');
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	$report[use_date] = $to;
	
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
	$period[start] = $to;

	if($to==''){
		$_POST[to] = date('Y-m-d',strtotime("- 1 days"));
		$to = $_POST[to];
	}
	
	$from = substr($to,0,7)."-01";
	
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
	
	$query = "
		SELECT
			wrapup_repeats.date_entered,
			wrapup_repeats.repeat_wrapups,
			wrapup_repeats.total_wrapups
		FROM
			wrapup_repeats
		WHERE
			wrapup_repeats.date_entered between  '".$from."' and '".$to."'
	";
	
	//echo date('Y-m-d H:i:s')." Getting totals \n";

	//GET TOTAL WRAP UPS BY DAY
	$totals_by_day = $myquery->multiple($query,'ccba02.ivrperformance');
	
	foreach($totals_by_day as $row){
		
		$totals[repeat_wrapups_by_date][$row[date_entered]] += $row[repeat_wrapups];
		$totals[repeat_wrapups_by_month][substr($row[date_entered],0,7)] += $row[repeat_wrapups];
		$totals[wrapups_by_date][$row[date_entered]] += $row[total_wrapups];
		$totals[wrapups_by_month][substr($row[date_entered],0,7)] += $row[total_wrapups];
		
		$report[summary]['Repeat Wrapups by Date by Total Wrapups'][$row[date_entered].">>>".$row[total_wrapups]] = $row[repeat_wrapups];
	}
	
	$report[totals] = $totals;
	$report[totals]['FCR%_MTD'] = 100 - ($report[totals][repeat_wrapups_by_month][substr($to,0,7)] *100/$report[totals][wrapups_by_month][substr($to,0,7)]);
	
	$wrapup_query = "
		SELECT
			reportsphonecalls.phonenumber as msisdn,
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(*) as num
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon between '".$from." 00:00:00' and '".$to." 23:59:59'
		GROUP BY
			reportsphonecalls.phonenumber, reportsphonecalls.wrapupsubcat, reportsphonecalls.subject
		HAVING
			num > 1
	";
	
	//echo date('Y-m-d H:i:s')." Getting Repeats";
	
	//GET THE INDIVIDUAL WRAP UPS SUBJECTS, CATEOGRIES AND NUMBERS WITH REPEAT WRAP UPS
	$repeated_wrapups = $myquery->multiple($wrapup_query,'ccba02.reportscrm');
	
	//echo " = [".count($repeated_wrapups)."]\n";
	
	if(count($totals_by_day) == 0 or count($repeated_wrapups) == 0){
		echo "Either Wrap up Totals [".count($totals_by_day)."] or repeated Wrap ups [".count($repeated_wrapups)."] returned zero rows \nExiting ... \n";
		exit();
	}

	//exit($wrapup_query.'<hr><hr>Number is '.count($repeated_wrapups));
	//echo date('Y-m-d H:i:s')." Getting the indidual Wrap ups that were repeated from ".count($repeated_wrapups)." \n";
	
	//GET THE INDIVIDUAL WRAP UPS THAT WERE REPEATED
	foreach($repeated_wrapups as $wrapup){
		
		$query = '
			SELECT
				reportsphonecalls.createdon,
				LOWER(reportsphonecalls.createdby) AS agent,
				wrapupcall_type.`name` AS caller_type
			FROM
				reportsphonecalls
				LEFT OUTER JOIN wrapupcall_type ON reportsphonecalls.wrapupcall_type = wrapupcall_type.id
			WHERE
				reportsphonecalls.createdon between DATE_SUB("'.$from.' 00:00:00", INTERVAL 1 DAY) and "'.$to.' 23:59:59" AND
				reportsphonecalls.phonenumber = "'.$wrapup[msisdn].'" AND
				reportsphonecalls.wrapupsubcat = "'.$wrapup[category].'" AND
				reportsphonecalls.subject = "'.$wrapup[subject].'"
			ORDER BY
				reportsphonecalls.createdon ASC
		';
		
		//echo date('Y-m-d H:i:s')." Processing ".++$ii."/".count($repeated_wrapups)." : ".$wrapup[category]." : ".$wrapup[subject]." : ".$wrapup[msisdn];
		
		$repeat_wrapups = $myquery->multiple($query,'ccba02.reportscrm');
		
		//echo " = [".count($repeat_wrapups)."]\n";
		
		//exit($query.'<hr><hr>Number is '.count($repeat_wrapups));
		
		foreach($repeat_wrapups as $key=>$row){
			//echo date('Y-m-d H:i:s')." ".$wrapup[category]." : ".$wrapup[subject]." : ".$wrapup[msisdn]." =>> ".$row[createdon].": ".$row[agent]."\n";
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
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_agent] = $row[agent];
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents][] = $row[agent];
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_time] = $row[createdon];
				}else{
					$data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents][] = $row[agent];
				}
			}
			
			if($key > 0 and strtotime($row[createdon]) >= strtotime($from.' 00:00:00')){
				//YOU CAN ONLY SUBTRACT THIS ONE FROM THE PREVIOUS ONE IF THERE IS A PREVIOUS ONE
				$row[repeat_duration] = strtotime($row[createdon]) - strtotime($repeat_wrapups[$key-1][createdon]);
				
				//echo date('Y-m-d H:i:s')." Duration = [".$row[repeat_duration]."]";
				
				if($row[repeat_duration] <= $period[sec]){
					//echo " repeat! ";
					$dates[substr($row[createdon],0,10)] = substr($row[createdon],0,10);
					//PERIOD DIFF <= 24 HOURS

					//++$summary['Repeat Wrapups by Category'][$wrapup[category]];
					++$summary['Repeat Wrapups by Category'][$wrapup[category]];
					++$summary['Repeat Wrapups by Caller Type'][$row[caller_type]];
					//++$summary['Repeat Wrapups by Month by Category by Subject'][substr($row[createdon],0,7).">>>".$wrapup[category].">>>".$wrapup[subject]];
					//++$summary['Repeat Wrapups by Date by Category by Subject'][substr($row[createdon],0,10).">>>".$wrapup[category].">>>".$wrapup[subject]];
					//++$summary['Repeat Wrapups by MSISDN'][$wrapup[msisdn]];
					
					$this_first_agent = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_agent];
					$this_first_time = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][first_time];
					
					$data_agents = $data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]][agents];
					
					foreach($data_agents as $data_agent){
						++$summary['Repeat Wrapups by Agent'][$data_agent];
						//++$summary['Repeat Wrapups by Agent by Category by Subject'][$data_agent.">>>".$wrapup[category].">>>".$wrapup[subject]];
						//++$summary['Repeat Wrapups by Date by Agent by Category by Subject'][substr($this_first_time,0,10).">>>".$data_agent.">>>".$wrapup[category].">>>".$wrapup[subject]];
					}
					
					//DETAIL OF THE REPORT 
					/*
					$report_row[first_agent] = $this_first_agent;
					$report_row[createdon] = $row[createdon];
					$report_row[category] = $wrapup[category];
					$report_row[subject] = $wrapup[subject];
					$report_row[agent] = $row[agent];
					$report_row[msisdn] = $wrapup[msisdn];
					$report_row[repeat_duration] = $row[repeat_duration];
					
					$report[rows][] = $report_row;
					*/
				}else{
					//echo " NOT repeat! ";
					unset($data[$wrapup[msisdn]][$wrapup[category]][$wrapup[subject]]);
				}
				
				//echo "\n";
			}else{
				//echo date('Y-m-d H:i:s')." Key [".$key."] is not > 0 or Created on [".strtotime($row[createdon])."] < From [".strtotime($from.' 00:00:00')."]\n";
			}
		}
		
		//sleep(4);
	}
	
	sort($dates);
	
	foreach($summary as $key=>$key_data){
		//SORT FROM HIGHEST TO LOWSEST
		arsort($key_data);
		$report[summary][$key] = $key_data;
		
		unset($summary[$key],$key);
	}
	
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - strtotime($report[start_time]);
	return display_repeat_cca_wrapups($report);
}

function display_repeat_cca_wrapups($report){

	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<tr>
				<td>Report took '.sec_to_time($report[duration]).' to run.<hr>A MORE DETAILED REPORT CAN BE VIEWED/EXTRACTED FROM <a href="http://reports.waridtel.co.ug/index.php?report=repeat_cca_wrapups" target="_blank">THE WEBVIEW REPORT</a>.</td>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
			<tr>
				<td style="font-size:15px; font-weight:bold;">'.date('F Y',strtotime($report[use_date])).' Summary : Total Wrap ups '.number_format($report[totals][wrapups_by_month][substr($report[use_date],0,7)],0).' : Total Repeat Wrap ups '.number_format($report[totals][repeat_wrapups_by_month][substr($report[use_date],0,7)],0).' : FCR% '.number_format($report[totals]['FCR%_MTD'],2).'%</td>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
	';
	
	if(count($report[summary]) > 0){
		foreach($report[summary] as $summary_heading=>$summary_data){
			
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
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
						<tr>
							<th>#</th>
							<th>Date</th>
							<th>Time</th>
							<th>Category</th>
							<th>Subject</th>
							<th>MSISDN</th>
							<th>Login ID</th>
							<th>Agent</th>
							<th>Interval</th>
						</tr>
		';
		
		unset($row);
		
		foreach($report[rows] as $row){
			$html .= '
						<tr>
							<td class="values">'.++$j.'</td>
							<td class="values">'.substr($row[createdon],0,10).'</td>
							<td class="values">'.substr($row[createdon],-8).'</td>
							<td class="text_values">'.$row[category].'</td>
							<td class="text_values">'.$row[subject].'</td>
							<td class="values">'.$row[msisdn].'</td>
							<td class="text_values">'.$agents_list[$row[agent]]['agent_loginid'].'</td>
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

function update_wrapup_repeats($from, $to){
	custom_query::select_db('ccba02.reportscrm');
	$mysql[begin] = date('Y-m-d H:i:s');
	
	$myquery = new custom_query();
	$mywrapup_repeats = new wrapup_repeats();
	$mywrapup_repeats_wrapupcall_types = new wrapup_repeats_wrapupcall_types();
	
	if($to == ''){
		$to = date('Y-m-d');
	}
	
	if($from == ''){
		$from = $to;
	}
	
	$mysql[period] = "[".$from." - ".$to."]";
	
	$totals_query = "
		SELECT
			LEFT(reportsphonecalls.createdon,10) as createdon_date,
			count(*) as num
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.createdon between  '".$from." 00:00:00' and '".$to." 23:59:59'
		GROUP BY
			createdon_date
	";
	
	//GET TOTAL WRAP UPS BY DAY
	echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage()." Getting total Wrap ups by day \n";
	$total_wrapups = $myquery->multiple($totals_query,'ccba02.reportscrm');
	
	foreach($total_wrapups as $row){
		$data[$row[createdon_date]][date_entered] = $row[createdon_date];
		$data[$row[createdon_date]][total_wrapups] += $row[num];
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
			reportsphonecalls.createdon between DATE_SUB('".$from." 00:00:00', INTERVAL 1 DAY) and '".$to." 23:59:59'
		GROUP BY
			reportsphonecalls.phonenumber, reportsphonecalls.wrapupsubcat, reportsphonecalls.subject
		HAVING
			num > 1
	";
	
	//GET THE INDIVIDUAL WRAP UPS SUBJECTS, CATEOGRIES AND NUMBERS WITH REPEAT WRAP UPS
	echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage()." Getting Repeated Wrap ups \n";
	$repeated_wrapups = $myquery->multiple($wrapup_query,'ccba02.reportscrm');
	
	if(count($total_wrapups) == 0 or count($repeated_wrapups) == 0){
		$message = "PERIOD => [".$from." - ".$to."]<br>Either Wrap up Totals [".count($total_wrapups)."] or repeated Wrap ups [".count($repeated_wrapups)."] returned zero rows \nExiting ... \n";
		echo $message;
		$mysql['log'] = $message;
		
		echo nl2br(print_r($mysql,true));
		
		return $mysql;
	}
	
	echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage()." Identifying repeat Wrap ups \n";
	
	$repeated_wrapups_count = count($repeated_wrapups);
	foreach($repeated_wrapups as $wrapup){
		
		$query = '
			SELECT
				reportsphonecalls.createdon,
				LOWER(reportsphonecalls.createdby) AS agent,
				reportsphonecalls.wrapupcall_type AS caller_type
			FROM
				reportsphonecalls
			WHERE
				reportsphonecalls.createdon between DATE_SUB("'.$from.' 00:00:00", INTERVAL 1 DAY) and "'.$to.' 23:59:59" AND
				reportsphonecalls.phonenumber = "'.$wrapup[msisdn].'" AND
				reportsphonecalls.wrapupsubcat = "'.$wrapup[category].'" AND
				reportsphonecalls.subject = "'.$wrapup[subject].'"
			ORDER BY
				reportsphonecalls.createdon ASC
		';
		
		$repeat_wrapups = $myquery->multiple($query,'ccba02.reportscrm');
		
		++$wrapup_count;
		$percentage = $wrapup_count*100/$repeated_wrapups_count;
		echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage()." Working on ".number_format($wrapup_count,0)."/".number_format($repeated_wrapups_count,0)." : ".number_format($percentage,2)."%\n";

		
		//exit($query.'<hr><hr>Number is '.count($repeat_wrapups));
		if(count($repeat_wrapups) > 0){
			foreach($repeat_wrapups as $key=>$row){
				if($key > 0 and strtotime($row[createdon]) >= strtotime($from.' 00:00:00')){
					//YOU CAN ONLY SUBTRACT THIS ONE FROM THE PREVIOUS ONE IF THERE IS A PREVIOUS ONE
					
					$row[repeat_duration] = strtotime($row[createdon]) - strtotime($repeat_wrapups[$key-1][createdon]);
					if($row[repeat_duration] <= (24*3600)){
						//PERIOD DIFF <= 24 HOURS
						++$data[substr($row[createdon],0,10)][repeat_wrapups];
						++$wrapupcall_types[substr($row[createdon],0,10)][$row[caller_type]];

					}else{

					}
				}else{

				}
			}
		}else{
			++$mysql[shady_counts][substr($row[createdon],0,10)]["[".$from." - ".$to."]~".$wrapup[category]."~".$wrapup[category]."~".$wrapup[subject]];
			echo date('Y-m-d H:i:s')." - SHADY COUNT [".count($repeat_wrapups)."] => ".list_array($wrapup,'cli')."\n";
			
			sleep(6);
		}
	}
	
	echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage().". Now to save ... \n";
	
	//echo print_r($data,true)."\n";
	custom_query::select_db('ccba02.ivrperformance');
	
	foreach($data as &$row){
		echo date('Y-m-d H:i:s')." - Saving Repeat wrap ups by CCA : [".$from." - ".$to."] : Mem ".show_mem_usage().". Saving the Repeat Wrap ups ".$row[date_entered]." \n";
		/*
		$query = "INSERT INTO wrapup_repeats (date_entered, repeat_wrapups, total_wrapups) VALUES ('".$row[date_entered]."','".$row[repeat_wrapups]."','".$row[total_wrapups]."'); ";
		$result = $myquery->no_row($query,'ccba02.ivrperformance');
		*/
		
		unset($mywrapup_repeats->id);
		$mywrapup_repeats->wrapup_repeats($row[date_entered], $row[repeat_wrapups], $row[total_wrapups]);
		
		$conditions = array(
						array('date_entered','=',$mywrapup_repeats->date_entered)
					);
		
		$db_wrapup_repeats = $mywrapup_repeats->GetList($conditions);
		
		if(count($db_wrapup_repeats) > 0){
			$db_wrapup_repeat = $db_wrapup_repeats[0]; unset($db_wrapup_repeats);
			
			$mywrapup_repeats->id = $db_wrapup_repeat->id; unset($db_wrapup_repeat);
			$id = $mywrapup_repeats->Save();
			
			++$mysql['wrapup_repeats '.$row[date_entered]][updates];
		}else{
			$id = $mywrapup_repeats->SaveNew();
			
			++$mysql['wrapup_repeats '.$row[date_entered]][saves];
		}
		

		
		foreach($wrapupcall_types[$mywrapup_repeats->date_entered] as $wrapupcall_type_id=>$wrapupcall_type_value){
			unset($mywrapup_repeats_wrapupcall_types->id);
			
			$mywrapup_repeats_wrapupcall_types->wrapup_repeats_wrapupcall_types($mywrapup_repeats->id, $wrapupcall_type_id, $wrapupcall_type_value);
			
			$conditions = array(
						array('wrapup_repeat_id','=',$mywrapup_repeats->id),
						array('wrapupcall_type_id','=',$wrapupcall_type_id)
					);
			
			$db_wrapup_repeats_wrapupcall_types = $mywrapup_repeats_wrapupcall_types->GetList($conditions);
			if(count($db_wrapup_repeats_wrapupcall_types) > 0){
				$db_wrapup_repeats_wrapupcall_type = $db_wrapup_repeats_wrapupcall_types[0]; unset($db_wrapup_repeats_wrapupcall_types);
				$mywrapup_repeats_wrapupcall_types->id = $db_wrapup_repeats_wrapupcall_type->id; unset($db_wrapup_repeats_wrapupcall_type);
				
				$mywrapup_repeats_wrapupcall_types->Save();
				++$mysql['wrapup_repeats_wrapupcall_types '.$wrapupcall_type_id][updates];
			}else{
				$mywrapup_repeats_wrapupcall_types->SaveNew();
				++$mysql['wrapup_repeats_wrapupcall_types '.$wrapupcall_type_id][saves];
			}
		}
		
		unset($row);
	}
	
	$mysql['end'] = date('Y-m-d H:i:s');
	$mysql[duration] = sec_to_time(strtotime($mysql['end']) - strtotime($mysql[begin]));
	
	return $mysql;
}

?>