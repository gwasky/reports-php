<?php
function generate_vas_call_drivers($upto,$top=5,$subject_type_filter){
	
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
			-- AND reportsphonecalls.wrapupsubcat != 'Prank Calls'
			AND reportsphonecalls.wrapupsubcat
			IN
			(
			'176 Magic Voice',
			'Balance Share',
			'Beera ko',
			'Blue cube subscriptions',
			'Call Me back service',
			'Chatzone',
			'Directory Info',
			'Gamezone',
			'Horoscope 09-09-2009 Broadcast',
			'Horoscopes',
			'Logo/Wall paper',
			'Love Subscriptions',
			'Missed Call Alert',
			'Mozook',
			'Music Zone',
			'Over 2 U Songs',
			'Paka/Mooo SMS',
			'Quran/Bible',
			'Ringback Tunes',
			'Ringback Tunes - Names',
			'SMS',
			'Ssenga',
			'Talking SMS',
			'True African Subscriptions',
			'Univeristy Admissions',
			'UWA',
			'Voice Chat',
			'Voice mail',
			'Voice SMS',
			'Warid Content subscription',
			'Warid Info Alerts 146',
			'Warid info Services',
			'Warid Inspirational Zone',
			'Warid Katale(Classifieds on mobile)',
			'Warid Kyezza',
			'Warid Menu',
			'Warid Mobi Ads',
			'World Cup 2010',
			'Call me'
			)
		GROUP BY
			date_created, category, sub_category, subjecttype, `subject`
		ORDER BY
			date_created ASC, num DESC
	";
	
	//echo nl2br($query);
	
	//echo date('Y-m-d H:i:s')." ".str_replace(array("\t","\r\n","\n"),array(""," "," "),$query)." \n";
	
	$wrapups = $myquery->multiple($query,'ccba02.reportscrm');
	
	//echo date('Y-m-d H:i:s')." : Got wrap ups ".count($wrapups)." \n";

	$MTD = 'MTD '.date('F', strtotime($to)).' '.date('y', strtotime($to));
	$report[dates][$MTD] = $MTD;
	
	//PREAPRE THE TOP WRAP UPS BY DAY	
	foreach($wrapups as $row){
		$report[dates][$row[date_created]] = $row[date_created];
		$monthly_totals[$row[subjecttype]][$row[category]][$row[sub_category]][$row[subject]] += $row[num];
		
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
	
	
	arsort($please_sort);

	foreach($please_sort as $key => $wrapup_count){
		$sorted_wrapups = explode('>>>', $key);
		$newarray_ref[] = array('subjecttype'=>$sorted_wrapups[0], 'sub_category'=>$sorted_wrapups[1], 'subject'=>$sorted_wrapups[2], 'num'=>$wrapup_count);

	}
	unset($please_sort);
	//exit('<pre>'.print_r($newarray_ref, true).'</pre>');
	foreach($newarray_ref as $key => $monthdata){
		$report[top_wrapups][$monthdata['subjecttype']][$MTD][] = array('sub_category'=>$monthdata['sub_category'],'subject'=>$monthdata['subject'],'num'=>$monthdata['num']);
	}
	unset($newarray_ref);
	
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
	
	//exit(max($report[dates]));
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
	/*foreach($data[highest_wrapups] as $project_group=>$project_group_data){
		foreach($project_group_data as $subcat_subject=>$row){
			foreach($report[dates] as $date){
				$data[graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date] = intval($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date]);
				unset($data[raw_graph_data][$project_group][$report[graph_key][$project_group][$subcat_subject]][$date]);
			}
		}
	}*/
	
	//print_r($report[dates]); exit();
	
	//echo "<pre>".print_r($data[graph_data],true)."</pre>";
	
	//SAVE MEM
	unset($wrapups);
	
	
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - $report[start];
	
	return display_vas_call_drivers($report);
}
	
function display_vas_call_drivers($report){
	
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
		</table>
	';
	
	$MTD = max($report[dates]);
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="360">
			<tr>
				<th align="centre">Date</th>
				<th colspan="2" align="centre">'.$MTD.'</th>
			</tr>
		';
	
	foreach($report[subjecttypes_order] as $subject_type){
		
		$subject_type_data = $report[top_wrapups][$subject_type];
		
		$html .= '<tr><td rowspan="5" valign="middle" align="center" style="border:1px dashed black; border-top:0px;">TOP 5 '.tx_subject_type($subject_type).'</td>';
		
		$row_index = 0;
		while(intval($row_index) <= ($report[top_count] - 1)){
			
			$row = $subject_type_data[$MTD][$row_index];
			
			//exit(PrintR($row));
			
			if(count($subject_type_data[$date][$row_index]) > 0){
			$html .= '
							<td class="text_values">'.$row[sub_category].' >> '.$row[subject].'</td>
							<td class="values">'.number_format($row[num],0).'</td>
						';
			$html .= '
							</tr>
				';
			}else{
					$mail .= '
						<td colspan="4"></td>
					';
			}
		++$row_index;
		}
		$html .= '
						<tr><th colspan="3" height="3"></th></tr>
			';
		
	}
	
	$html .= '
		</table>
	';
	
	return array('html'=>$html,'attach'=>$mail);
}

?>