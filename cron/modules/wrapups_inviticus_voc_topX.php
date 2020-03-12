<?php
function generate_inviticus_voc_topX_wrapups($upto,$top=5,$subject_type_filter){
	
	//echo date('Y-m-d H:i:s')." : entering fx \n";
	
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	//QUERY FOR GETTING YESTERDAY'S TOP TWO COMPLAINTS AND TOP TWO INQUIRIES
	
	$report[top_count] = $top;
	
	//$report[excluded_subjecttypes] = array('Not Applicable','Inward Info');
	
	$report[subjecttypes_order] = array('Negative Feedback','Inquiry','Service Restoration Request');
	$inviticus_subject_types = array('Negative Feedback');
	
	if(!$upto){
		$upto = date('Y-m-d',strtotime("-1 days"));
	}
		
	$from = substr($upto,0,8)."01";
	$to = $upto;
	
	$query = "
		SELECT
			LEFT(reportsphonecalls.createdon,10) AS date_created,
			reportsphonecalls.wrapupcat AS category,
			reportsphonecalls.wrapupsubcat AS sub_category,
			IF(subsubcategory.subject_type IS NULL,'Inquiry',subsubcategory.subject_type) AS subjecttype,
			reportsphonecalls.`subject`,
			count(*) AS num
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subcategory = reportsphonecalls.wrapupsubcat AND subsubcategory.subsubcategory = reportsphonecalls.`subject`)
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
--			AND reportsphonecalls.wrapupsubcat != 'Prank Calls'
		GROUP BY
			date_created, category, sub_category, subjecttype, `subject`
		ORDER BY
			date_created ASC, num DESC
	";
	
	//echo date('Y-m-d H:i:s')." ".str_replace(array("\t","\r\n","\n"),array(""," "," "),$query)." \n";
	
	$wrapups = $myquery->multiple($query,'ccba02.reportscrm');
	
	//echo date('Y-m-d H:i:s')." : Got wrap ups ".count($wrapups)." \n";

	//PREAPRE THE TOP WRAP UPS BY DAY
	foreach($wrapups as $row){
		$report[dates][$row[date_created]] = $row[date_created];
			$report[daily_totals][$row[subjecttype]][$row[date_created]] += $row[num];
		if(count($report[top_wrapups][$row[subjecttype]][$row[date_created]]) < $top){
			$report[top_wrapups][$row[subjecttype]][$row[date_created]][] = array('sub_category'=>$row[sub_category],'subject'=>$row[subject],'num'=>$row[num]);
		}
		
		$report[totals][by_subjecttype_by_date][$row[subjecttype]][$row[date_created]] += $row[num];
		$report[totals][by_date][$row[date_created]] += $row[num];
		if(in_array($row[subjecttype],$inviticus_subject_types)){
			$data[highest_wrapups][inviticus][$row[sub_category]." >> ".$row[subject]] += $row[num];
		}else{
			$data[highest_wrapups][voc][$row[sub_category]." >> ".$row[subject]] += $row[num];
		}
	}
	
	//echo date('Y-m-d H:i:s')." : Preped top wrap ups by day \n";
	
	//ORDER FROM HIGHEST TO LOWEST
	arsort($data[highest_wrapups][inviticus]);
	arsort($data[highest_wrapups][voc]);
	
	//echo date('Y-m-d H:i:s')." : Sorted highest wrap ups \n";
	
	//CLEAR COUNTER
	unset($counter);
	
	//GET THE TOP $top HIGHEST WRAP UPS
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		foreach($project_group_data as $key=>$row){
			if(++$counter[$project_group] > $top) unset($data[highest_wrapups][$project_group][$key],$key,$row);
		}
	}
	//SAVE MEM
	unset($counter,$project_group,$project_group_data);
	
	//print_r($data[highest_wrapups]); exit();
	
	//GET THE TOP $top HIGHEST WRAP UPS BY DAY
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		unset($counter);
		foreach($project_group_data as $subcat_subject=>$row){
			$report[graph_key][$project_group][$subcat_subject] = ++$counter;
			list($subcategory,$subject) = explode(" >> ",$subcat_subject);
			
			foreach($wrapups as $row){
				if($subcategory == $row[sub_category] and $subject == $row[subject]){
					$data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$row[date_created]] = $row[num];
				}
			}
		}
	}
	
	//print_r($data[raw_graph_data]); exit();
	
	//CLEAN GRAPH DATA ADDIING ZEROS WHERE THEY ARE SUPPOSED TO BE
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		foreach($project_group_data as $subcat_subject=>$row){
			foreach($report[dates] as $date){
				$data[graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date] = intval($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date]);
				unset($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date]);
			}
		}
	}
	
	//print_r($data[graph_data]); exit();
	
	//echo "<pre>".print_r($data[graph_data],true)."</pre>";
	
	//SAVE MEM
	unset($wrapups);
	
	$graph_detail['data']=$data[graph_data][inviticus];
	//SAVE MEM
	unset($data[graph_data][inviticus]);
	$graph_detail['title']='Top 5 (COMPLAINTS) Wrap ups Trend MTD';
	$graph_detail['display_title']=true;
	$graph_detail['legend']=true;
	$graph_detail['line_graph']=true;
	$graph_detail['bar_graph']=false;
	$graph_detail['set_data_points']=true;
	$graph_detail['set_data_values']=false;
	$graph_detail['width']=1000;
	$graph_detail['height']=700;
	$my_graph->graph($graph_detail['title'],"MTD ".$upto, $graph_detail);
	custom_query::select_db('graphing');
	$report[graphids][inviticus] = $my_graph->Save();
	
	unset($my_graph->id);
	$graph_detail['data']=$data[graph_data][voc];
	//SAVE MEM
	unset($data[graph_data][voc]);
	$graph_detail['title']='Top 5 (REQUESTS/QUERIES) Wrap ups Trend MTD';
	$graph_detail['display_title']=true;
	$graph_detail['legend']=true;
	$graph_detail['line_graph']=true;
	$graph_detail['bar_graph']=false;
	$graph_detail['set_data_points']=true;
	$graph_detail['set_data_values']=false;
	$graph_detail['width']=1000;
	$graph_detail['height']=700;
	$my_graph->graph($graph_detail['title'],"MTD ".$upto, $graph_detail);
	custom_query::select_db('graphing');
	$report[graphids][voc] = $my_graph->Save();
	
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - $report[start];
	
	return display_inviticus_voc_topX_wrapups($report);
}
	
function display_inviticus_voc_topX_wrapups($report){
	
	$mail = '
		<table border="0" cellpadding="0" cellspacing="0" width="'.(count($report[dates]) * 360).'">
	';
	
	$mail .= '
							<th align="centre">Date</th>
			';
		foreach($report[dates] as $date){
			$mail .= '
							<th colspan="2" align="centre">'.$date.'</th>
			';
		}
		
		$mail .= '
						</tr>
						<!--<tr>
							<th width="300"></th>-->
		';
		
		/*foreach($report[dates] as $date){
			$mail .= '
							<th width="300">Issue</th>
							<th width="30">No</th>
							<!--<th width="30">%age</th>-->
			';
		}
		
		$mail .= '
						</tr>
		';*/
		
	foreach($report[subjecttypes_order] as $subject_type){
		
		$subject_type_data = $report[top_wrapups][$subject_type];
		
		//if(in_array($subject_type,$report[excluded_subjecttypes])){ continue; }
		
		/*$mail .= '
			<tr><th>Top '.$report[top_count].' '.tx_subject_type($subject_type).' wrap ups</th></tr>
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
		';*/
		
		
		
		$mail .= '
						<tr>
			';
			$mail .= '<td rowspan="5" valign="middle" align="center" style="border:1px dashed black; border-top:0px;">TOP 5 '.tx_subject_type($subject_type).'</td>';
		$row_index = 0;
		while(intval($row_index) <= ($report[top_count] - 1)){
			
			foreach($report[dates] as $date){
				if(count($subject_type_data[$date][$row_index]) > 0){
					$row = $subject_type_data[$date][$row_index];
				
					$row['%age'] = $row[num]*100/$report[totals][by_date][$date];
					$mail .= '
						<td class="text_values">'.$row[sub_category].' >> '.$row[subject].'</td>
						<td class="values">'.number_format($row[num],0).'</td>
						<!--<td class="values">'.number_format($row['%age'],1).' %</td>-->
					';
				}else{
					$mail .= '
						<td colspan="4"></td>
					';
				}
			}
			
			$mail .= '
						</tr>
			';
			
			++$row_index;
		}
		$mail .= '
						<tr><th colspan="'.((count($report[dates])*2)+1).'" height="3"></th></tr>
			';
	}
	
	$mail .= '
		</table><hr>
	';
	
	//---------------------------- Totals ----------------------------
	$mail .= '
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>';
	
	$mail .= '<th align="centre">Date</th>';
		foreach($report[dates] as $date){
			$mail .= '
							<th align="centre">'.$date.'</th>
			';
		}
		
		$mail .= '</tr>';
	foreach($report[subjecttypes_order] as $subject_type){
		$mail .= '<tr><td align="centre" class="text_values">'.tx_subject_type($subject_type).'</td>';
		foreach($report[dates] as $date){
			$mail .= '<td class="values">'.number_format($report[daily_totals][$subject_type][$date],0).'</td>';
		}
		$mail .= '</tr>';
	}
	$mail .= '</table>';
	//---------------------------- Totals ----------------------------
	
	
	$html .= '
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>'.display_generic_graph($graph_id = $report[graphids][inviticus],$with_td=TRUE).'</tr>
			<tr><td height="10"></td></tr>
			<tr>
				<th>GRAPH KEY</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="300">
						<tr>
							<th>KEY</th>
							<th>Representation</th>
						</tr>
	';
	
	foreach($report[graph_key][inviticus] as $subcat_subject=>$graph_key){
		$html .= '
					<tr>
						<td class="values">'.$graph_key.'</td>
						<td class="text_values">'.$subcat_subject.'</td>
					</tr>
		';
	}
	
	$html .= '
					</table>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
			<tr>'.display_generic_graph($graph_id = $report[graphids][voc],$with_td=TRUE).'</tr>
			<tr><td height="10"></td></tr>
			<tr>
				<th>GRAPH KEY</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="300">
						<tr>
							<th>KEY</th>
							<th>Representation</th>
						</tr>
	';
	
	foreach($report[graph_key][voc] as $subcat_subject=>$graph_key){
		$html .= '
					<tr>
						<td class="values">'.$graph_key.'</td>
						<td class="text_values">'.$subcat_subject.'</td>
					</tr>
		';
	}
	
	$html .= '
					</table>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
			<tr><td height="10">Report took '.$report[duration].' seconds</td></tr>
		</table>
	';
	
	return array('html'=>$html,'attach'=>$mail);
}

function tx_subject_type($input){
	$list = array(
		'Service Restoration Request' => 'REQUESTS',
		'Negative Feedback' => 'COMPLAINTS',
		'Inquiry' => 'QUERIES',
		'Inward Info' => 'Suggestion',
		'Not Applicable' => 'Prank Related',
	);
	
	if($list[$input] == ''){
		return $input;
	}
	
	return $list[$input];
}


function generate_weeks(){
	$numdays = date('t');
	$week = 0;
	$w = 0;
	for($i = 1; $i<=$numdays; $i++){
		++$w;
		if($i<=9){ $d = '0'.$i; }else{ $d = $i; }
		$day = date('Y-m').'-'.$d;
		//echo $i.'/7='.($w%7).'<br>';
		if(($w%7)==1){
			++$week;
			$wk = 'Week '.$week;
		}
		
		if($week > 4){
			$wk = 'Last Few Days';
		}
		
		$weeks[$day] = $wk;
	}
	return $weeks;
}

function generate_inviticus_voc_topX_wrapups_week($upto = false,$top=10,$subject_type_filter){
	
	//echo date('Y-m-d H:i:s')." : entering fx \n";
	
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	//QUERY FOR GETTING YESTERDAY'S TOP TWO COMPLAINTS AND TOP TWO INQUIRIES
	
	$report[top_count] = $top;
	
	$report[week_translation][1] = 'Week 1 Volume';
	$report[week_translation][2] = 'Week 2 Volume';
	$report[week_translation][3] = 'Week 3 Volume';
	$report[week_translation][4] = 'Week 4 Volume';
	$report[week_translation][5] = 'Last Few Days Volume';
	$report[week_translation]['month'] = 'MONTHLY DRIVERS Volume';
	
	//$report[excluded_subjecttypes] = array('Not Applicable','Inward Info');
	
	$report[subjecttypes_order] = array('Negative Feedback','Inquiry','Service Restoration Request');
	$inviticus_subject_types = array('Negative Feedback');
	
	if(!$upto){
		$upto = date('Y-m-d',strtotime("-1 days"));
	}
		
	$from = substr($upto,0,8)."01";
	$to = $upto;
	
	$query = "
		SELECT
			IF(
					DAY(reportsphonecalls.createdon) BETWEEN 1 AND 7,
					'1',
					IF(
						DAY(reportsphonecalls.createdon) BETWEEN 8 AND 14,
						'2',
						IF(
							DAY(reportsphonecalls.createdon) BETWEEN 15 AND 21,
							'3',
							IF(
								DAY(reportsphonecalls.createdon) BETWEEN 22 AND 28,
								'4',
								'5'
							)
						)
					)
			) AS the_week,
			reportsphonecalls.wrapupcat AS category,
			reportsphonecalls.wrapupsubcat AS sub_category,
			IF(subsubcategory.subject_type IS NULL,'Inquiry',subsubcategory.subject_type) AS subjecttype,
			reportsphonecalls.`subject`,
			count(*) AS num
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subcategory = reportsphonecalls.wrapupsubcat AND subsubcategory.subsubcategory = reportsphonecalls.`subject`)
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			-- AND reportsphonecalls.wrapupsubcat != 'Prank Calls'
		GROUP BY
			the_week, category, sub_category, subjecttype, `subject`
		ORDER BY
			the_week ASC, num DESC
	";
	
	//exit(nl2br($query));
	
	$wrapups = $myquery->multiple($query,'ccba02.reportscrm');
	
	//$report[weeks]['month'] = 'month';
	
	//PREAPRE THE TOP WRAP UPS BY DAY
	foreach($wrapups as $row){
		$report[weeks][$row[the_week]] = $row[the_week];
		$monthly_totals[$row[subjecttype]][$row[category]][$row[sub_category]][$row[subject]] += $row[num];
		
		if(count($report[top_wrapups][$row[subjecttype]][$row[the_week]]) < $top){
			$report[top_wrapups][$row[subjecttype]][$row[the_week]][] = array('sub_category'=>$row[sub_category],'subject'=>$row[subject],'num'=>$row[num]);
		}
		
		$report[totals][by_subjecttype_by_week][$row[subjecttype]][$row[the_week]] += $row[num];
		$report[totals][by_subjecttype_by_week][$row[subjecttype]]['month'] += $row[num];
		$report[totals][by_week][$row[the_week]] += $row[num];
		$report[totals][by_week]['month'] += $row[num];
		if(in_array($row[subjecttype],$inviticus_subject_types)){
			$data[highest_wrapups][inviticus][$row[sub_category]." >> ".$row[subject]] += $row[num];
		}else{
			$data[highest_wrapups][voc][$row[sub_category]." >> ".$row[subject]] += $row[num];
		}
	}
	//echo "1<br>".PrintR($data[highest_wrapups]).'<hr>';

	
	foreach($monthly_totals as $subjecttype_month => $subjecttype_monthdata){
		foreach($subjecttype_monthdata as $category_month => $category_monthdata){
			foreach($category_monthdata as $sub_category_month => $sub_categorydata){
				foreach($sub_categorydata as $subject_month => $subjectcount){
					$please_sort[$subjecttype_month.'>>>'.$sub_category_month.'>>>'.$subject_month] += $subjectcount;
					//$sort_ref[$subjecttype_month][$sub_category_month][$subject_month] += $subjectcount;
				}
			}
		}
	}
	//error_reporting(E_ALL);
	//echo "2<br>".PrintR($data[highest_wrapups]).'<hr>';
	
	arsort($please_sort);

	foreach($please_sort as $key => $wrapup_count){
		$sorted_wrapups = explode('>>>', $key);
		$newarray_ref[] = array('subjecttype'=>$sorted_wrapups[0], 'sub_category'=>$sorted_wrapups[1], 'subject'=>$sorted_wrapups[2], 'num'=>$wrapup_count);

	}
	unset($please_sort);
	//exit('<pre>'.print_r($newarray_ref, true).'</pre>');
	foreach($newarray_ref as $key => $monthdata){
		$report[top_wrapups][$monthdata['subjecttype']]['month'][] = array('sub_category'=>$monthdata['sub_category'],'subject'=>$monthdata['subject'],'num'=>$monthdata['num']);
	}
	unset($newarray_ref);
	
	
	//echo "3<br>".PrintR($data[highest_wrapups]).'<hr>';
	
	//ORDER FROM HIGHEST TO LOWEST
	arsort($data[highest_wrapups][inviticus]);
	arsort($data[highest_wrapups][voc]);
	//echo date('Y-m-d H:i:s')." : Sorted highest wrap ups \n";
	
	//CLEAR COUNTER
	unset($counter);
	
	//GET THE TOP $top HIGHEST WRAP UPS
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		foreach($project_group_data as $key=>$row){
			if(++$counter[$project_group] > $top){ unset($data[highest_wrapups][$project_group][$key],$key,$row); }
		}
	}
	//SAVE MEM
	unset($counter,$project_group,$project_group_data);
	
	//print_r($data[highest_wrapups]); exit();

	//GET THE TOP $top HIGHEST WRAP UPS BY DAY
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		unset($counter);
		foreach($project_group_data as $subcat_subject=>$row){
			$report[graph_key][$project_group][$subcat_subject] = ++$counter;
			list($subcategory,$subject) = explode(" >> ",$subcat_subject);
			
			
			foreach($wrapups as $row){
				if($subcategory == $row[sub_category] and $subject == $row[subject]){
					$data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$report[week_translation][$row[the_week]]] = $row[num];
				}
			}
		}
	}
	
	//CLEAN GRAPH DATA ADDIING ZEROS WHERE THEY ARE SUPPOSED TO BE
	foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		foreach($project_group_data as $subcat_subject=>$row){
			foreach($report[weeks] as $week){
				$data[graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$report[week_translation][$week]] = intval($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$report[week_translation][$week]]);
				unset($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$report[week_translation][$week]]);
			}
		}
	}
	
	//echo "<pre>".print_r($data[graph_data],true)."</pre>";
	
	//SAVE MEM
	unset($wrapups);
	
	$graph_detail['data']=$data[graph_data][inviticus];
	//SAVE MEM
	unset($data[graph_data][inviticus]);
	$graph_detail['title']='Top 10 (COMPLAINTS) Wrap ups Trend By Week MTDX';
	$graph_detail['display_title']=true;
	$graph_detail['legend']=true;
	$graph_detail['line_graph']=true;
	$graph_detail['bar_graph']=false;
	$graph_detail['set_data_points']=true;
	$graph_detail['set_data_values']=false;
	$graph_detail['width']=1000;
	$graph_detail['height']=800;
	$my_graph->graph($graph_detail['title'],"MTD ".$upto, $graph_detail);
	custom_query::select_db('graphing');
	$report[graphids][inviticus] = $my_graph->Save();
	
	unset($my_graph->id);
	$graph_detail['data']=$data[graph_data][voc];
	//SAVE MEM
	unset($data[graph_data][voc]);
	$graph_detail['title']='Top 10 (REQUESTS/QUERIES) Wrap ups Trend By Week MTDX';
	$graph_detail['display_title']=true;
	$graph_detail['legend']=true;
	$graph_detail['line_graph']=true;
	$graph_detail['bar_graph']=false;
	$graph_detail['set_data_points']=true;
	$graph_detail['set_data_values']=false;
	$graph_detail['width']=1000;
	$graph_detail['height']=800;
	$my_graph->graph($graph_detail['title'],"MTD ".$upto, $graph_detail);
	custom_query::select_db('graphing');
	$report[graphids][voc] = $my_graph->Save();
	
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - $report[start];
	
	$report[weeks]['month'] = 'month';
	return display_inviticus_voc_topX_wrapups_week($report);
}


function display_inviticus_voc_topX_wrapups_week($report){

	$mail = '
		<table border="1" cellpadding="0" cellspacing="0" width="'.(count($report[dates]) * 360).'">
		<tr>';
	
	unset($report[totals][by_subjecttype_by_week]['Not Applicable']);
	unset($report[totals][by_subjecttype_by_week]['Inward Info']);
	asort($report[totals][by_subjecttype_by_week]);
	
	foreach($report[weeks] as $week){
			$mail .= '
							<th colspan="3" align="centre">'.$report[week_translation][$week].'</th>
			';
		}
		
		$mail .= '
						</tr>
						<tr>
		';
		
	foreach($report[weeks] as $week){
		$week_data['%age'] = $report[totals][by_week][$week]*100/$report[totals][by_week][$week];
		$mail .= '
					<td class="text_values">Total Tagging</td>
					<td class="values">'.number_format($report[totals][by_week][$week],0).'</td>
					<td class="values">'.number_format($week_data['%age'],1).' %</td>
	';
	}
	
	$mail .= '
				</tr>
				';
		
	foreach($report[totals][by_subjecttype_by_week] as $subjecttype => $weekdata){
		$mail .= '
				<tr>
				';
		foreach($report[weeks] as $week){
			
				$weekdata['%age'] = $weekdata[$week]*100/$report[totals][by_week][$week];
				$mail .= '
						<td class="text_values">'.ucwords(strtolower(tx_subject_type($subjecttype))).'</td>
						<td class="values">'.number_format($report[totals][by_subjecttype_by_week][$subjecttype][$week],0).'</td>
						<td class="values">'.number_format($weekdata['%age'],1).' %</td>
				';
		}
		$mail .= '
				</tr>
				';
	}
	
		
	foreach($report[subjecttypes_order] as $subject_type){
		
		$subject_type_data = $report[top_wrapups][$subject_type];
		
		$mail .= '
			<tr><th colspan="'.(count($report[weeks])*3).'">Top '.$report[top_count].' '.tx_subject_type($subject_type).'</th></tr>
		';
		
			
		$row_index = 0;
		while(intval($row_index) <= ($report[top_count] - 1)){
			
			foreach($report[weeks] as $week){
				if(count($subject_type_data[$week][$row_index]) > 0){
					$row = $subject_type_data[$week][$row_index];
				
					$row['%age'] = $row[num]*100/$report[totals][by_week][$week];
					$mail .= '
						<td class="text_values">'.$row[sub_category].' >> '.$row[subject].'</td>
						<td class="values">'.number_format($row[num],0).'</td>
						<td class="values">'.number_format($row['%age'],1).' %</td>
					';
				}else{
					$mail .= '
						<td colspan="3"></td>
					';
				}
			}
			
			$mail .= '
						</tr>
			';
			
			++$row_index;
		}
		
		/*$mail .= '
					</table>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
		';*/
	}
	
	$mail .= '
		</table>
	';
	
	$html .= '
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>'.display_generic_graph($graph_id = $report[graphids][inviticus],$with_td=TRUE).'</tr>
			<tr><td height="10"></td></tr>
			<tr>
				<th>GRAPH KEY</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="300">
						<tr>
							<th>KEY</th>
							<th>Representation</th>
						</tr>
	';
	
	foreach($report[graph_key][inviticus] as $subcat_subject=>$graph_key){
		$html .= '
					<tr>
						<td class="values">'.$graph_key.'</td>
						<td class="text_values">'.$subcat_subject.'</td>
					</tr>
		';
	}
	
	$html .= '
					</table>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
			<tr>'.display_generic_graph($graph_id = $report[graphids][voc],$with_td=TRUE).'</tr>
			<tr><td height="10"></td></tr>
			<tr>
				<th>GRAPH KEY</th>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" width="300">
						<tr>
							<th>KEY</th>
							<th>Representation</th>
						</tr>
	';
	
	foreach($report[graph_key][voc] as $subcat_subject=>$graph_key){
		$html .= '
					<tr>
						<td class="values">'.$graph_key.'</td>
						<td class="text_values">'.$subcat_subject.'</td>
					</tr>
		';
	}
	
	$html .= '
					</table>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
			<tr><td height="10">Report took '.$report[duration].' seconds</td></tr>
		</table>
	';
	
	return array('html'=>$html,'attach'=>$mail);

}
?>