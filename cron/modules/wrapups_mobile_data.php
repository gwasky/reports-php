<?php
	
function generate_yesterday_mobile_data_top_wrapups(){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	/* QUERY FOR GETTING YESTERDAY'S TOP TWO COMPLAINTS AND TOP TWO INQUIRIES */
	$query = "
		(
			SELECT
				subsubcategory.subject_type,
				reportsphonecalls.wrapupsubcat as category,
				reportsphonecalls.subject,
				Count(reportsphonecalls.id) AS wrapupCount
			FROM
				reportsphonecalls
				INNER JOIN subsubcategory ON reportsphonecalls.subject = subsubcategory.subsubcategory AND reportsphonecalls.wrapupsubcat = subsubcategory.subcategory
			WHERE
				reportsphonecalls.wrapupsubcat IN ('GPRS (Phone)','GPRS Data Packages', 'Data 5', 'Data 85', 'Data More','GPRS', 'GSM account details', 'Phone Settings') AND
				subsubcategory.subject_type IN ('Inquiry','Service Restoration Request') AND
				reportsphonecalls.createdon BETWEEN DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL 1 DAY) AND DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59'), INTERVAL 1 DAY)
			GROUP BY
				category, subject
			ORDER BY
				wrapupCount DESC
			LIMIT 2
		)
		UNION
		(
			SELECT
				subsubcategory.subject_type,
				reportsphonecalls.wrapupsubcat as category,
				reportsphonecalls.subject,
				Count(reportsphonecalls.id) AS wrapupCount
			FROM
				reportsphonecalls
				INNER JOIN subsubcategory ON reportsphonecalls.subject = subsubcategory.subsubcategory AND reportsphonecalls.wrapupsubcat = subsubcategory.subcategory
			WHERE
				reportsphonecalls.wrapupsubcat IN ('GPRS (Phone)','GPRS Data Packages', 'Data 5', 'Data 85', 'Data More','GPRS', 'GSM account details', 'Phone Settings') AND
				subsubcategory.subject_type = 'Negative feedback' AND
				reportsphonecalls.createdon BETWEEN DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL 1 DAY) AND DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59'), INTERVAL 1 DAY)
			GROUP BY
				category, subject
			ORDER BY
				wrapupCount DESC
			LIMIT 2
		)
	";
	
	$top_wrapups_list = $myquery->multiple($query, 'ccba02.reportscrm');
	
	return generate_mobile_data_top_wrapups_for_last_30_days($top_wrapups_list);
}

function generate_mobile_data_top_wrapups_for_last_30_days($top_wrapups_list){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	$date = date('Y-m-d', strtotime("-1 days"));
	
	$query_30_day_list = "
		SELECT
			if(subsubcategory.subject_type = 'Service Restoration Request','Inquiry',subsubcategory.subject_type) as subject_type,
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			LEFT(reportsphonecalls.createdon,10) AS date_created,
			Count(reportsphonecalls.id) AS wrapupCount
		FROM
			reportsphonecalls
			INNER JOIN subsubcategory ON reportsphonecalls.subject = subsubcategory.subsubcategory AND reportsphonecalls.wrapupsubcat = subsubcategory.subcategory
		WHERE
			reportsphonecalls.createdon BETWEEN DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL 30 DAY) AND DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59'), INTERVAL 1 DAY) AND
			(";
			foreach($top_wrapups_list as $key => $value){
				$query_30_day_list .= "(reportsphonecalls.wrapupsubcat = '".$value['category']."' AND reportsphonecalls.subject = '".$value['subject']."')";
				if($value != end($top_wrapups_list)){
					$query_30_day_list .= "OR";
				}
			}	
	$query_30_day_list .= "
			)
		GROUP BY
			date_created, reportsphonecalls.subject
		ORDER BY
			date_created ASC
	";	
	
	$generated_30_day_wrapups = $myquery->multiple($query_30_day_list, 'ccba02.reportscrm');
	
	if(count($generated_30_day_wrapups) == 0){ exit('NO DATA'); }
	
	foreach($generated_30_day_wrapups as $generated_30_day_wrapups_key){
		$new_heads[] = $generated_30_day_wrapups_key['subject_type'];
	}
	
	$new_unique_heads = array_merge(array_unique($new_heads));	
	/* CHANGING THE HEADS OF THE ARRAYS */
	foreach($new_unique_heads as $new_unique_heads_key => $new_unique_heads_value){
		foreach($generated_30_day_wrapups as $generated_30_day_wrapups_key){
			if($generated_30_day_wrapups_key['subject_type'] == $new_unique_heads_value){
				$data[$new_unique_heads_value][] = array(
					'category' => $generated_30_day_wrapups_key['category'],
					'subject' => $generated_30_day_wrapups_key['subject'],
					'date_created' => $generated_30_day_wrapups_key['date_created'],	
					'wrapupCount' => $generated_30_day_wrapups_key['wrapupCount']	
				);
			} 
		} 
	}	 
	
	/* For Complaints */
	foreach($data['Negative Feedback'] as $key){/* Sort Data and Store Data */
		$data['complaints']['dates'][$key['date_created']] = $key['date_created'];
		$data['complaints']['subject'][$key['subject']] = $key['subject'];
		$data['complaints']['category'][$key['subject']] = $key['category'];
		$data['complaints']['data_count'][$key['subject']][] = $key['wrapupCount'];	
		$data['complaints']['graph'][$key['subject']][$key['date_created']] += $key['wrapupCount'];		
	}
	
	foreach($data['complaints']['subject'] as $subject){/* Counting Elements and Summing Array */
		$data['complaints']['element_count'][] = count($data['complaints']['graph'][$subject]);
		$data['complaints']['element_total'][] = array_sum($data['complaints']['graph'][$subject]);
		foreach($data['complaints']['dates'] as $dates){
			$graph_data[$subject][$dates] = round($data['complaints']['graph'][$subject][$dates]);
		}
	}	
	
	foreach($data['complaints']['subject'] as $key => $value){
		$data['complaints']['keyed_subject'][] = $value;
	}
	
	$report['complaints']['subject'] = $data['complaints']['subject'];
	$report['complaints']['category'] = $data['complaints']['category'];
	$report['complaints']['graph'] = $graph_data; unset($graph_data);
	$report['complaints']['data_count'] = max($data['complaints']['element_count']);
	$report['complaints']['total'] = $data['complaints']['element_total'];
	$report['complaints']['keyed_subject'] = $data['complaints']['keyed_subject'];
	
	
	$graph_detail['data']=$report['complaints']['graph'];
	$graph_detail['title']='Mobile Data Wrapups (Complaints)';
	$graph_detail['display_title']=false;
	$graph_detail['legend']=true;
	$graph_detail['line_graph']=true;
	$graph_detail['bar_graph']=false;
	$graph_detail['set_data_points']=true;
	$graph_detail['set_data_values']=false;
	$graph_detail['width']=850;
	$graph_detail['height']=600;
	
	/* Graph Data For Complaints */
	$my_graph->graph($graph_detail['title'],"30 days before ".$date, $graph_detail);
	custom_query::select_db('graphing');
	$report['graph_id']['complaints'] = $my_graph->Save();	
	
	/* For Inquiries */
	foreach($data['Inquiry'] as $key){/* Sort Data and Store Data */
		$data['inquiry']['dates'][$key['date_created']] = $key['date_created'];
		$data['inquiry']['subject'][$key['subject']] = $key['subject'];	
		$data['inquiry']['category'][$key['subject']] = $key['category'];	
		$data['inquiry']['data_count'][$key['subject']][] = $key['wrapupCount'];	
		$data['inquiry']['graph'][$key['subject']][$key['date_created']] += $key['wrapupCount'];		
	}
	
	foreach($data['inquiry']['subject'] as $subject){/* Counting Elements and Summing Array */
		$data['inquiry']['element_count'][] = count($data['inquiry']['graph'][$subject]);
		$data['inquiry']['element_total'][] = array_sum($data['inquiry']['graph'][$subject]);
		foreach($data['inquiry']['dates'] as $dates){
			$graph_data[$subject][$dates] = round($data['inquiry']['graph'][$subject][$dates]);
		}
	}	
	
	foreach($data['inquiry']['subject'] as $key => $value){
		$data['inquiry']['keyed_subject'][] = $value;
	}
	
	$report['inquiry']['subject'] = $data['inquiry']['subject'];
	$report['inquiry']['category'] = $data['inquiry']['category'];
	$report['inquiry']['graph'] = $graph_data; unset($graph_data);
	$report['inquiry']['data_count'] = max($data['inquiry']['element_count']);
	$report['inquiry']['total'] = $data['inquiry']['element_total'];
	$report['inquiry']['keyed_subject'] = $data['inquiry']['keyed_subject'];
	
	/* Graph Data For Inquiries */
	$my_graph->id = '';
	$graph_detail['data'] = $report['inquiry']['graph'];
	$graph_detail['title'] = 'Mobile Data Wrapups (Inquiries)';
	$my_graph->graph($title = $graph_detail['title'], "30 days before ".$date, $graph_detail,$type=$graph_detail['type']);
	custom_query::select_db('graphing');
	$report['graph_id']['inquiry'] = $my_graph->Save();	
	
	return 	display_mobile_data_wrapups_report($report, $date);
}

function display_mobile_data_wrapups_report($report, $date){	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<tr>
				<th colspan="3">MOBILE DATA WRAPUPS FOR THE LAST 30 DAYS</th>
			</tr>
			<tr>
				<th colspan="3">TOP TWO INQUIRIES/SERVICE (RESTORATION) REQUESTS FOR '.$date.'</th>
			</tr>
			<tr>
				<th class="text_values">#</th>
				<th class="text_values">SUBJECT</th>
				<th class="text_values">Average Number Of Inquiries/Service (Restoration) Requests Per Day For The Last 30 Days</th>
			</tr>';
			foreach($report['inquiry']['keyed_subject'] as $key => $value){
				$average = $report['inquiry']['total'][$key]/$report['inquiry']['data_count'];
				$html .= '<tr>
							<td class="text_values">'.++$i.'.</td>
							<td class="text_values">'.$report['inquiry']['category'][$value].' - '.$value.'</td>
							<td class="values">'.round($average, 2).'</td>
						</tr>';
			}
	$html .= '			
			<tr>
				<td>'.display_generic_graph($report['graph_id']['inquiry'],TRUE).'</td>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="3" height="20"></td>
			</tr>
			<tr>
				<th colspan="3">TOP TWO COMPLAINTS FOR '.$date.'</th>
			</tr>
			<tr>
				<th class="text_values">#</th>
				<th class="text_values">SUBJECT</th>
				<th class="text_values">Average Number Of Complaints Per Day For The Last 30 Days</th>
			</tr>';
			unset($i);
			foreach($report['complaints']['keyed_subject'] as $key => $value){
				$average = $report['complaints']['total'][$key]/$report['complaints']['data_count'];
				$html .= '<tr>
							<td class="text_values">'.++$i.'.</td>
							<td class="text_values">'.$report['complaints']['category'][$value].' - '.$value.'</td>
							<td class="values">'.round($average, 2).'</td>
						</tr>';
			}
	$html .= '			
			<tr>
				<td>'.display_generic_graph($report['graph_id']['complaints'],TRUE).'</td>
				<td colspan="2"></td>
			</tr>
		</table>
	';
	
	return $html;
}

?>