<?php
function generate_cc_xhourly_report($date){
	
	$report[start] = date('Y-m-d H:i:s');
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	if(($date == '') or ($date > date('Y-m-d H:i:s'))){
		$date = date('Y-m-d H:i:s');
	}
	
	$query = "
		select
			(select MAX(subscount.day) as the_date from subscount where subscount.day <= '".substr($date,0,10)."') as last_date,
			(select subscount.active_subs from subscount where `day` = last_date) as last_date_active_subs
	";
	
	$report[bases] = $myquerys->single($query,'ivrperformance');
	$date_before = date_time_add($date,$value=-1,$mysql_interval='DAY');
	
	//TOTALS ON $DATE
	$query = "
		SELECT
			subsubcategory.subject_type,
			count(reportsphonecalls.subject) as `count`
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.createdon BETWEEN '".substr($date,0,10)." 00:00:00' AND '".$date."'
		GROUP BY
			subsubcategory.subject_type
	";
	
	$totals[substr($date,0,10)] = $myquerys->multiple($query,'ccba01.reportscrm');
	
	//TOTALS ON $DATE_BEFORE
	$query = "
		SELECT
			subsubcategory.subject_type,
			count(reportsphonecalls.subject) as `count`
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.createdon BETWEEN '".substr($date_before,0,10)." 00:00:00' AND '".$date_before."'
		GROUP BY
			subsubcategory.subject_type
	";
	
	$totals[substr($date_before,0,10)] = $myquerys->multiple($query,'ccba02.reportscrm');
	
	//SUMMARISING THE TOTALS
	foreach($totals as $total_date=>$date_data){
		foreach($date_data as $row){
			$report[bases][$row[subject_type]][$total_date] +=  $row['count'];
			$report[bases]['Total Wrap ups'][$total_date] +=  $row['count'];
		}
	}
	
	//INQUIRIES
	$query = "
		SELECT 
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(reportsphonecalls.subject) as `count`
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.createdon BETWEEN '".substr($date,0,10)." 00:00:00' AND '".$date."' AND
			subsubcategory.subject_type = 'Inquiry'
		GROUP BY
			category,subject,subsubcategory.subject_type
		ORDER BY
			`count` DESC
		LIMIT 6
	";
	
	//echo $query." \n\n";
	
	$data[INQUIRIES][substr($date,0,10)] = $myquerys->multiple($query,'ccba01.reportscrm');
	
	//COMPLAINTS
	$query = "
		SELECT 
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(reportsphonecalls.subject) as `count`
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.createdon BETWEEN '".substr($date,0,10)." 00:00:00' AND '".$date."' AND
			subsubcategory.subject_type = 'Negative Feedback'
		GROUP BY
			category,subject,subsubcategory.subject_type
		ORDER BY
			`count` DESC
		LIMIT 6
	";
	
	$data[COMPLAINTS][substr($date,0,10)] = $myquerys->multiple($query,'ccba01.reportscrm');
	
	//SERVICE REQUESTS
	$query = "
		SELECT 
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(reportsphonecalls.subject) as `count`
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.createdon BETWEEN '".substr($date,0,10)." 00:00:00' AND '".$date."' AND
			subsubcategory.subject_type = 'Service Restoration Request'
		GROUP BY
			category,subject,subsubcategory.subject_type
		ORDER BY
			`count` DESC
		LIMIT 6
	";
	
	$data['SERVICE (RESTORATION) REQUESTS'][substr($date,0,10)] = $myquerys->multiple($query,'ccba01.reportscrm');
	
	//PREPARE THE REPORT DATA ARRAY
	foreach($data as $report_type=>$report_type_data){
		foreach($report_type_data as $this_date=>$this_date_data){
			foreach($this_date_data as $row){
				$report[category_subjects_pairs][$row[category].$row[subject]] = array('wrapupsubcat'=>$row[category],'subject'=>$row[subject]);
				
				$report[data][$report_type][$row[category].$row[subject]][category] = $row[category];
				$report[data][$report_type][$row[category].$row[subject]][subject] = $row[subject];
				$report[data][$report_type][$row[category].$row[subject]]['count'][$this_date] = $row['count'];
			}
			
			unset($report_type_data[$this_date],$this_date);
		}
		
		unset($data[$report_type],$report_type);
	}
	
	//GET PREVIOUS DAY COUNTS FOR THE SELECTED WRAP UPS
	foreach($report[category_subjects_pairs] as $pair){
		$query = "
			SELECT
				count(reportsphonecalls.subject) as `count`
			FROM 
				reportsphonecalls
	 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				reportsphonecalls.wrapupsubcat = '".$pair[wrapupsubcat]."' AND
				reportsphonecalls.subject = '".$pair[subject]."' AND
				reportsphonecalls.createdon BETWEEN '".substr($date_before,0,10)." 00:00:00' AND '".$date_before."'
		";
		
		$day_before_info = $myquerys->single($query,'ccba02.reportscrm');
		
		
		$report[category_subjects_pairs][$pair[wrapupsubcat].$pair[subject]]['count'] = $day_before_info['count'];
	}
	
	//INSERT PREVIOUS DAY COUNTS IN THE DATA ARRAY
	foreach($report[data] as $report_type=>$report_type_data){
		foreach($report_type_data as $cat_sub_key=>$row){
			$report[data][$report_type][$cat_sub_key]['count'][substr($date_before,0,10)] = $report[category_subjects_pairs][$cat_sub_key]['count'];
		}
	}
	
	$report[dates] = array($date_before,$date);
	//$report[dates] = array($date,$date_before);
	$report[duration] = strtotime(date('Y-m-d H:i:s')) - strtotime($report[start]);
	
	return display_cc_xhourly_report($report);
	
}

function display_cc_xhourly_report($report){
	
	//print_r($report[dates]); echo "<br><br>";
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<th>CALL CENTRE REGULAR REPORT AT '.substr($report[dates][0],-8,-3).'</th>
			</tr>
			<tr>
				<tD style="height:10px;"></tD>
			</tr>
			<tr>
				<tD style="height:10px;" align="center">This report shows top 6 Inquiry, Complaint and Service request counts between Midnight and '.substr($report[dates][1],-8,-3).' for '.substr($report[dates][1],0,10).' with the their corresponding counts on '.substr($report[dates][0],0,10).'</tD>
			</tr>
			<tr>
				<tD style="height:10px;"></tD>
			</tr>
			<tr>
				<tD>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr>
	';

	foreach($report[data] as $report_type=>$report_data){
		$html .= '
							<tD><table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<th align="center">'.$report_type.'</th>
							</tr>
							<tr>
								<tD><table width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<th style="font-size:11px;">#</th>
										<th style="font-size:11px;">Category</th>
										<th style="font-size:11px;">Subject</th>
		';
		
		foreach($report[dates] as $date){
			$html .= '
										<th style="font-size:11px;">'.substr($date,0,10).'</th>
			';
		}
		
		foreach($report_data as $row){
			$html .= '
									<tr class="'.row_style(++$i).'">
										<td class="values">'.$i.'</td>
										<td class="text_values">'.$row[category].'</td>
										<td class="text_values" height="28" style="height:28px;">'.wordwrap($row[subject],50,"<br>").'</td>
			';
			
			foreach($report[dates] as $date){
				$html .= '
										<td class="values">'.number_format($row['count'][substr($date,0,10)],0).'</td>
				';
			}
			
			$html .= '
									</tr>
			';
		}
		unset($i);

		$html .= '
									</tr>
								</table></tD>
								<!--SPACER TD NEXT-->
								<td style="width:10px;" width="10"></td>
							</tr>
							</table></tD>
		';
	}
	
	$html .= '
						</tr>
					</table>
				</tD>
			</tr>
			<tr>
				<tD style="height:10px;"></tD>
			</tr>
			<tr>
				<tD>
					<!--CPC : Using Last 90 days active as at '.$report[bases][last_date].' : '.number_format($report[bases][last_date_active_subs],0).'<br><br>-->
					Total Number of Inquiries <br>
					'.substr($report[dates][0],0,10).' = '.number_format($report[bases][Inquiry][substr($report[dates][0],0,10)],0).'<br>
					'.substr($report[dates][1],0,10).' = '.number_format($report[bases][Inquiry][substr($report[dates][1],0,10)],0).'<br><br>
					
					Total Number of Complaints <br>
					'.substr($report[dates][0],0,10).' = '.number_format($report[bases]['Negative Feedback'][substr($report[dates][0],0,10)],0).' <br> 
					'.substr($report[dates][1],0,10).' = '.number_format($report[bases]['Negative Feedback'][substr($report[dates][1],0,10)],0).' <br><br>
					
					Total Number of Service (Restoration) Requests <br>
					'.substr($report[dates][0],0,10).' = '.number_format($report[bases]['Service Restoration Request'][substr($report[dates][0],0,10)],0).' <br>
					'.substr($report[dates][1],0,10).' = '.number_format($report[bases]['Service Restoration Request'][substr($report[dates][1],0,10)],0).' <br><br>
					
					Total Number of Wrap ups<br>
					'.substr($report[dates][0],0,10).' = '.number_format($report[bases]['Total Wrap ups'][substr($report[dates][0],0,10)],0).' <br>
					'.substr($report[dates][1],0,10).' = '.number_format($report[bases]['Total Wrap ups'][substr($report[dates][1],0,10)],0).' <br><br>
					
					Report Duration = '.sec_to_time($report[duration]).'
				</tD>
			</tr>
		</table>
	';

	return $html;
}

?>