<?php
function generate_socialmedia_correspondence_report($date = '',$period='month'){
	
	if($date == ''){$date = date('Y-m-d',strtotime("-1 days"));}
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	$query = "
		SELECT
			LEFT(correspondance.createdon,10) AS date_created,
			correspondance.category,
			correspondance.subcategory as subject,
			correspondance.createdby,
			count(*) AS number
		FROM 
			correspondance
			INNER JOIN wrapup_source_type ON wrapup_source_type.id = correspondance.source
		WHERE
			wrapup_source_type.`name` IN ('Social Media - Facebook','Social Media - Twitter') AND
			correspondance.createdon BETWEEN date_format('".$date."','%Y-%m-01 00:00:00') AND '".$date." 23:59:59'
		GROUP BY 
			date_created,
			correspondance.createdby,
			correspondance.category,
			correspondance.subcategory
		ORDER BY
			date_created DESC
	";
	
	$socialmedia_correspondence = $myquerys->multiple($query,'ccba01.reportscrm');
	if(count($socialmedia_correspondence) == 0){ exit("Exiting due to zero sized query ... \n"); }
	
	$query = "
		SELECT
			DISTINCT
			LEFT(reportscrm.createdon,10) AS date_created
		FROM
			reportscrm
		WHERE
			reportscrm.createdon BETWEEN date_format('".$date."','%Y-%m-01 00:00:00') AND '".$date." 23:59:59'
		ORDER BY
			date_created ASC
	";
	$date_list = $myquerys->multiple($query,'ccba01.reportscrm');
	foreach($date_list as $row){
		$report[dates][str_replace('-','',$row[date_created])] = $row[date_created];
	}
	
	$report[run_date] = $date;
	$report[run_month] = substr($date,0,7);
	foreach($socialmedia_correspondence as $row){
		$report[dates][str_replace('-','',$row[date_created])] = $row[date_created];
		
		if($row[date_created] == $report[run_date]){
			$report[totals][day] += $row[number];
			$report[data][day]['No of Social Media Wrap ups by Date entered'][$row[date_created]] += $row[number];
			$report[data][day]['No of Social Media Wrap ups by User'][$row[createdby]] += $row[number];
			$report[data][day]['No of Social Media Wrap ups by Category by Subject'][$row[category]." >> ".$row[subject]] += $row[number];
		}
		
		if($period == 'month' and substr($row[date_created],0,7) == $report[run_month]){
			$report[totals][month] += $row[number];
			$report[data][month]['No of Social Media Wrap ups by Month entered'][date('F Y',strtotime($row[date_created]))] += $row[number];
			$graph_data['Day count graph']['No of Wrap ups'][$row[date_created]] += $row[number];
			$report[data][month]['No of Social Media Wrap ups by User'][$row[createdby]] += $row[number];
			$report[data][month]['No of Social Media Wrap ups by Category by Subject'][$row[category]." >> ".$row[subject]] += $row[number];
		}
	}
		
	//SORT DATES IN ASCENDING ORDER
	ksort($report[dates]);
	
	//SORT 2 DIMENTIONAL TABULA IN DESCENDING ORDER 
	arsort($report[data][day]['No of Social Media Wrap ups by User']);
	arsort($report[data][month]['No of Social Media Wrap ups by User']);
	arsort($report[data][day]['No of Social Media Wrap ups by Category by Subject']);
	arsort($report[data][month]['No of Social Media Wrap ups by Category by Subject']);
	
	function get_top(&$limit_list,$limit_value = 6){
		$row_counter = 0;
		foreach($limit_list as $row_key=>$row_data){
			if(++$row_counter > $limit_value) { unset($limit_list[$row_key],$row_data); }
		}
		unset($row_key);
	}
	
	get_top($report[data][day]['No of Social Media Wrap ups by Category by Subject']);
	get_top($report[data][month]['No of Social Media Wrap ups by Category by Subject']);
	
	foreach($report[dates] as $this_date){
		foreach($graph_data as $graph_heading=>$graph_heading_data){
			foreach($graph_heading_data as $legend=>$legend_data){
				$report[graph_data][$graph_heading][$legend][$this_date] = intval($legend_data[$this_date]);
			}
		}
	}
	
	$graph_detail[data]=$report[graph_data]['Day count graph'];
	unset($report[graph_data]);
	$graph_detail[title]='No of Social Media Wrap ups by day : '.date_format(date_create($date),'l jS F Y');
	$graph_detail[display_title]=true;
	$graph_detail[legend]=true;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=false;
	$graph_detail[width]=850;
	$graph_detail[height]=600;
	
	$my_graph->graph($graph_detail[title],substr($date,0,7)."-01 - ".$date, $graph_detail);
	custom_query::select_db('graphing');
	$report[graph_id] = $my_graph->Save();
		
	//exit("Count Day is [".count($report[totals][day][status])."] and count Month is [".count($report[totals][month][status])."]\n");
	
	return display_socialmedia_correspondence_report($report);
}

function display_socialmedia_correspondence_report($report){
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0">
	';
	
	if($report[totals][day] > 0){
		$html .= '
			<tr>
				<th style="min-width:500px;">'.date_format(date_create($report[run_date]),'l jS F Y').'</th>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
		';
		
		foreach($report[data][day] as $summary_heading=>$summary_data){
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
			foreach($summary_data as $parameter_string=>$parameter_string_value){
				
				$html .= '
						<tr>
							<td class="values">'.++$row_number.'</td>
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
			
			unset($row_number);
			
			$html .= '
					</table>
					</td>
				</tr>
				<tr>
					<td style="height:10px;"></td>
				</tr>
			';
		}
	}else{
		$html .= '
			<tr>
				<th style="min-width:500px;">Social Media Wrap ups for '.date_format(date_create($report[run_date]),'l jS F Y').'</th>
			</tr>
			<tr>
				<td class="text_values"><strong style="font-size:15px; color:#FF0000;">No Social Media Wrap ups entered</strong></td>
			</tr>
			<tr>
				<td height="20"></td>
			</tr>
		';
	}
	
	if($report[totals][month] > 0 and $report[totals][day] > 0){
		$html .= '
			<tr>
				<td height="20"></td>
			</tr>
		';
	}
	
	if($report[totals][month] > 0){
		$html .= '
			<tr>
				<th style="min-width:500px; font-size:16px;">MONTH : '.date_format(date_create($report[run_date]),'F Y').' TODATE</th>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
		';

		foreach($report[data][month] as $summary_heading=>$summary_data){
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
			foreach($summary_data as $parameter_string=>$parameter_string_value){
				
				$html .= '
						<tr>
							<td class="values">'.++$row_number.'</td>
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
			
			unset($row_number);
			
			$html .= '
					</table>
					</td>
				</tr>
			';
		}
		
		$html .= '
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					'.display_generic_graph($report[graph_id],TRUE).'
				</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>