<?php

function generate_sr_report($upto='', $reporttype){

	$report[start] = strtotime(date('Y-m-d H:i:s'));
	$myquery = new custom_query();
	
	$subjecttypes_includes = "'Negative Feedback','Service Restoration Request'";
	
	if(!$upto){
		$upto = date('Y-m-d',strtotime("-1 days"));
	}
		
	$from = substr($upto,0,8)."01";
	$to = $upto;
	
	$query = "
		SELECT
			LEFT(reportsphonecalls.createdon,10) AS date_created,
			IF(subsubcategory.subject_type IS NULL,'Inquiry',subsubcategory.subject_type) AS subjecttype,
			count(*) AS num
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subcategory = reportsphonecalls.wrapupsubcat
			AND subsubcategory.subsubcategory = reportsphonecalls.`subject`)
		WHERE
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			AND subsubcategory.subject_type IN (".$subjecttypes_includes.")
		GROUP BY
			date_created, subjecttype
		ORDER BY
			date_created ASC, num DESC
	";
	//echo nl2br($query);
	$wrapups = $myquery->multiple($query,'ccba02.reportscrm');
	//echo PrintR($wrapups);
	//error_reporting(E_ALL);
	$query = "
		SELECT
			left(cases_audit.date_created,10) as modification_date,
			cases.status,
			count(cases.status) as num,
			avg((UNIX_TIMESTAMP(cases_audit.date_created) - UNIX_TIMESTAMP(cases.date_entered))/3600) as average_resolution_Hrs
		FROM
			cases
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
			INNER JOIN accounts ON (cases.account_id=accounts.id)
			LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id
			AND (
					(cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed') OR 
					(cases_audit.after_value_string = 'Duplicate' AND cases_audit.before_value_string != 'Duplicate') OR 
					(cases_audit.after_value_string = 'Rejected' AND cases_audit.before_value_string != 'Rejected')
					)
			AND cases_audit.field_name = 'status')
		WHERE 
			cases.deleted = '0' AND
			accounts.deleted = '0' AND
			cases_audit.date_created BETWEEN DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR) AND DATE_SUB('".$to." 23:59:59',INTERVAL -3 HOUR)
			AND cases.status != 'Assigned'
		GROUP BY
		modification_date,
		cases.status
	";
	//echo nl2br($query);
	$wimax_modified_cases = $myquery->multiple($query,'wimax');
	//echo PrintR($wimax_modified_cases);
	
	$query = "
			SELECT
				left(cases.date_entered,10) as created_date,
				count(cases.id) as num
			FROM
				cases
			WHERE
				cases.date_entered BETWEEN DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR) AND DATE_SUB('".$to." 23:59:59',INTERVAL -3 HOUR)
			GROUP BY
			created_date
			";
	//echo nl2br($query);
	$wimax_cases_created = $myquery->multiple($query,'wimax');
	
	
	$query = "
			SELECT
				date_closed,
				status,
				count(status) as num,
				avg(resolution_hours) as average_resolution_Hrs
			FROM
			(
			SELECT
				left(caseresolution.actualend,10) as date_closed,
				if(caseresolution.casenum is not null,'Closed', reportscrm.`status`) as status,
				if(caseresolution.casenum is not null,(UNIX_TIMESTAMP(caseresolution.actualend) - UNIX_TIMESTAMP(reportscrm.createdon))/3600,'') as resolution_hours
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				caseresolution.actualend BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			) as tablee
			GROUP BY
				date_closed,
				status
			";
			//echo nl2br($query);
	$gsm_cases_closed = $myquery->multiple($query,'ccba01.reportscrm');
	
	$query = "
			SELECT
				left(reportscrm.createdon,10) as date_created,
				count(reportscrm.casenum) as num
			FROM
				reportscrm
			WHERE
				reportscrm.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			GROUP BY
				date_created
			";
			//echo nl2br($query);
	$gsm_cases_created = $myquery->multiple($query,'ccba01.reportscrm');
	
	$query = "
			SELECT
				left(reportscrm.createdon,10) as date_closed,
				count(DISTINCT caseresolution.casenum) as num
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				caseresolution.actualend BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			AND
				reportscrm.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			GROUP BY
				date_closed
			";
			//echo nl2br($query);
	$gsm_cases_created_and_closed_daily = $myquery->multiple($query,'ccba01.reportscrm');
	
	$query = "SELECT
					modification_date,
					created_date,
					num
				FROM
				(
				SELECT
					left(cases_audit.date_created,10) as modification_date,
					left(cases.date_entered,10) as created_date,
					count(cases.date_entered) as num
				FROM
					cases
					INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
					INNER JOIN accounts ON (cases.account_id=accounts.id)
					LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id
					AND (
							(cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed') OR 
							(cases_audit.after_value_string = 'Duplicate' AND cases_audit.before_value_string != 'Duplicate') OR 
							(cases_audit.after_value_string = 'Rejected' AND cases_audit.before_value_string != 'Rejected')
							)
					AND cases_audit.field_name = 'status')
				WHERE 
					cases.deleted = '0' AND
					accounts.deleted = '0' AND
				(	
					cases_audit.date_created BETWEEN DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR) AND DATE_SUB('".$to." 23:59:59',INTERVAL -3 HOUR) AND
					cases.date_entered BETWEEN DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR) AND DATE_SUB('".$to." 23:59:59',INTERVAL -3 HOUR)
				)
					AND cases.status != 'Assigned'
				GROUP BY
				modification_date,
				created_date
				) as tablee
				WHERE
				modification_date = created_date
				";

$wimax_cases_created_and_closed_daily = $myquery->multiple($query,'wimax');
	
	
	$query = "
			select
				queue.entrydate as thedate,
				calldetail.status,
				sum(calldetail.calls) as num
			from
				queue
				inner join calldetail on calldetail.id_c = queue.id
			where
				queue.entrydate BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			AND
				calldetail.status = 'Handled'
			GROUP BY
			thedate,
			calldetail.status
			";
			//echo nl2br($query);
	$calls = $myquery->multiple($query,'ccba02.ivrperformance');
	
	
	$query = "
			SELECT
				modification_date,
				count(modification_date) as num
			FROM
			(
			SELECT
				left(cases_audit.date_created,10) as modification_date,
				cases.status,
				((UNIX_TIMESTAMP(cases_audit.date_created) - UNIX_TIMESTAMP(cases.date_entered))/3600) as resolution_hours
			FROM
				cases
				INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
				INNER JOIN accounts ON (cases.account_id=accounts.id)
				LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id
				AND (
						(cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed') OR 
						(cases_audit.after_value_string = 'Duplicate' AND cases_audit.before_value_string != 'Duplicate') OR 
						(cases_audit.after_value_string = 'Rejected' AND cases_audit.before_value_string != 'Rejected')
						)
				AND cases_audit.field_name = 'status')
			WHERE 
				cases.deleted = '0' AND
				accounts.deleted = '0' AND
				cases_audit.date_created BETWEEN DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR) AND DATE_SUB('".$to." 23:59:59',INTERVAL -3 HOUR)
				AND cases.status != 'Assigned'
			) as tablee
			WHERE
				tablee.resolution_hours <= 21
			GROUP BY
				modification_date
			";
			//echo nl2br($query);
	$wimax_resolution_hrs = $myquery->multiple($query,'wimax');
	
	$query = "
			SELECT
				date_closed,
				count(date_closed) as num
			FROM
			(
			SELECT
				left(caseresolution.actualend,10) as date_closed,
				if(caseresolution.casenum is not null,'Closed', reportscrm.`status`) as status,
				if(caseresolution.casenum is not null,(UNIX_TIMESTAMP(caseresolution.actualend) - UNIX_TIMESTAMP(reportscrm.createdon))/3600,'') as resolution_hours
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				caseresolution.actualend BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			) as tablee
			WHERE
				tablee.resolution_hours <= 21
			GROUP BY
				date_closed
			";
			//echo nl2br($query);
	$gsm_resolution_hrs = $myquery->multiple($query,'ccba01.reportscrm');
	
	$query = "	
			SELECT
			(
			SELECT
				count(DISTINCT reportscrm.casenum) as num
			FROM
				reportscrm
			WHERE
				reportscrm.createdon < '".$from." 00:00:00'
			) - (
			SELECT
				count(DISTINCT caseresolution.casenum) as num
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				caseresolution.actualend < '".$from." 00:00:00'
			) as open_cases
			";
			//echo nl2br($query);
	$gsm_open_cases = $myquery->multiple($query,'ccba01.reportscrm');
	
	
	$query = "
			SELECT
				(
				SELECT
					count(cases.id) as num
				FROM
					cases
				WHERE
					cases.date_entered < DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR)
				) - (
				SELECT
					count(cases.status) as num
				FROM
					cases
					INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
					INNER JOIN accounts ON (cases.account_id=accounts.id)
					LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id
					AND (
							(cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed') OR 
							(cases_audit.after_value_string = 'Duplicate' AND cases_audit.before_value_string != 'Duplicate') OR 
							(cases_audit.after_value_string = 'Rejected' AND cases_audit.before_value_string != 'Rejected')
							)
					AND cases_audit.field_name = 'status')
				WHERE 
					cases.deleted = '0' AND
					accounts.deleted = '0' AND
					cases_audit.date_created < DATE_SUB('".$from." 00:00:00',INTERVAL -3 HOUR)
					AND cases.status != 'Assigned'
				) as open_cases
			";
			//echo nl2br($query);
	$wimax_open_cases = $myquery->multiple($query,'wimax');
		
	//$report[dates][MTD] = 'MTD';
	
	foreach($calls as $row){
		$report[data]['Calls To CC'][translator($row[status])][$row[thedate]] += $row[num];
		$report[dates][$row[thedate]] = $row[thedate];
	}
	
	foreach($wrapups as $row){
		$report[data]['Wrapups'][translator(trim($row[subjecttype]))][$row[date_created]] += $row[num];
		$report[dates][$row[date_created]] = $row[date_created];
	}
	
	foreach($wimax_cases_created as $row){
		$report[data]['DATA Tickets Created'][translator('Created')][$row[created_date]] += $row[num];
		$report[data]['Totals']['(SR + Complaints + Logged Tickets)'][$row[created_date]] += $row[num];
		//echo $row[created_date].'	Data	'.$row[num].'<br>';
		$report[dates][$row[created_date]] = $row[created_date];
	}
	
	foreach($gsm_cases_created as $row){
		$report[data]['GSM Tickets Created'][translator('Created')][$row[date_created]] += $row[num];
		$report[data]['Totals']['(SR + Complaints + Logged Tickets)'][$row[date_created]] += $row[num];
		//echo $row[date_created].'	GSM	'.$row[num].'<br>';
		//$report[data]['GSM Tickets Created'][translator('Created')]['MTD July 13'] += $row[num];
		$report[dates][$row[date_created]] = $row[date_created];
	}
	
	foreach($gsm_cases_created_and_closed_daily as $row){
		$gsm_cases_created_and_closed_daily_array[$row[date_closed]] = $row[num];
	}
	
	foreach($wimax_cases_created_and_closed_daily as $row){
		$wimax_cases_created_and_closed_daily_array[$row[modification_date]] = $row[num];
	}
	
	foreach($report[dates] as $thedate){
		
		$report[data]['GSM Tickets Open Daily']['Open Tickets daily'][$thedate] = $report[data]['GSM Tickets Created']['Created'][$thedate] - $gsm_cases_created_and_closed_daily_array[$thedate];
		$report[data]['DATA Tickets Open Daily']['Open Tickets daily'][$thedate] += $report[data]['DATA Tickets Created']['Created'][$thedate] - $wimax_cases_created_and_closed_daily_array[$thedate];
		
		//echo 'GSM - '.$report[data]['GSM Tickets Created']['Created'][$thedate].' - '.$gsm_cases_created_and_closed_daily_array[$thedate].'<br>';
		//echo 'Data - '.$report[data]['DATA Tickets Created']['Created'][$thedate].' - '.$wimax_cases_created_and_closed_daily_array[$thedate].'<br>';
	}
	
	foreach($wrapups as $row){
		$report[data]['Totals']['(SR + Complaints + Logged Tickets)'][$row[date_created]] += $row[num];
		//echo $row[date_created].'	Warups	'.$row[num].'<br>';
		
		$wrapups_date_num = $report[data]['Totals']['(SR + Complaints + Logged Tickets)'][$row[date_created]];
		$handled_calls_date_num = $report[data]['Calls To CC']['Total Calls Answered'][$row[date_created]];
		
		$report[data]['Totals']['SRs %age of all calls'][$row[date_created]] = intval($wrapups_date_num * 100/$handled_calls_date_num);
	}
	/*foreach($wimax_cases_created as $row){
		$report[data]['Totals']['Total (SR + Complaints + Tickets Created)'][$row[created_date]] += $row[num];
	}
	foreach($gsm_cases_created as $row){
		$report[data]['Totals']['Total (SR + Complaints + Tickets Created)'][$row[date_created]] += $row[num];
	}*/
	
	foreach($wimax_modified_cases as $row){
		$report[data]['DATA Tickets Closed'][translator(trim($row[status]))][$row[modification_date]] += $row[num];
		$report[dates][$row[modification_date]] = $row[modification_date];
	}
	
	foreach($gsm_cases_closed as $row){
		$report[data]['GSM Tickets Closed'][translator(trim($row[status]))][$row[date_closed]] += $row[num];
		$report[dates][$row[date_closed]] = $row[date_closed];
	}
	
	foreach($wimax_resolution_hrs as $row){
		$report[data]['Data']['Resolved Within 21Hrs - Count'][$row[modification_date]] += $row[num];
	}
	
	foreach($gsm_resolution_hrs as $row){
		$report[data]['GSM']['Resolved Within 21Hrs - Count'][$row[date_closed]] += $row[num];
	}
	
	foreach($report[data] as $source => $source_data){
		foreach($source_data as $sr => $sr_data){
			if($sr == 'Created') $sr = 'Complaints';
			foreach($sr_data as $date => $date_data){
				$report[SR][$sr][$date] += $date_data;
			}
		}
	}
	
	$MTD = 'MTD '.date('F', strtotime(max($report[dates]))).' '.date('y', strtotime(max($report[dates])));
	//$report[dates][$MTD] = $MTD;
	
	foreach($report[dates] as $SR_date){
		foreach($report[SR] as $SR_type => $SR_type_data){
			$date_data = $SR_type_data[$SR_date];
			if($SR_type == 'Resolved Within 21Hrs - Count'){
				$dd[$SR_type] += ($date_data + $report[data]['Wrapups']['Service Request'][$SR_date]);
				//echo $SR_date.' - '.$SR_type.' - '.$date_data.' + '.$report[data]['Wrapups']['Service Request'][$SR_date].' = '.($date_data+$report[data]['Wrapups']['Service Request'][$SR_date]).'<br>';
			}else{
				$dd[$SR_type] += $date_data;
			}
		}
	
	}
	//error_reporting(E_ALL);
	foreach($dd as $SR_type => $mtd_total){
		$report[SR][$SR_type][$MTD] = $mtd_total;
	}
	
	array_unshift($report[dates], $MTD);
	
	$last_date_prev_month = date('Y-m-d',(strtotime('-1 day', strtotime($from))));

	foreach($gsm_open_cases as $row){
		$report[data]['GSM Open Tickets']['Open'][$last_date_prev_month] = $row[open_cases];
	}
	
	foreach($wimax_open_cases as $row){
		$report[data]['DATA Open Tickets']['Open'][$last_date_prev_month] = $row[open_cases];
		$open_tickets_prev_month = $row[open_cases] + $report[data]['GSM Open Tickets']['Open'][$last_date_prev_month];
	}


	unset($report[SR]['Closed Tickets'][$MTD]);
	
	foreach($report[dates] as $date_key){
		//Resolved within 21Hrs includes both Service Requests and complaints.
		$report[SR]['Resolved Within 21Hrs - Count'][$date_key] += $report[data]['Wrapups']['Service Request'][$date_key];
		
		$prev_date = date('Y-m-d',(strtotime('-1 day', strtotime($date_key))));
		
		$report[SR]['Tickets Created'][$date_key] = $report[data]['DATA Tickets Created']['Created'][$date_key] + $report[data]['GSM Tickets Created']['Created'][$date_key];
	
		
		$Resolved_Within_21Hrs_Count = 	$report[SR]['Resolved Within 21Hrs - Count'][$date_key];
		
		$report[SR]['%Age Resolved Within 21Hrs - Count'][$date_key] = ($Resolved_Within_21Hrs_Count/$report[SR]['(SR + Complaints + Logged Tickets)'][$date_key])*100;
		
		$report[SR]['%Age Open Tickets daily'][$date_key] = ($report[SR]['Open Tickets daily'][$date_key]/$report[SR]['(SR + Complaints + Logged Tickets)'][$date_key])*100;

		
		if(date('d', strtotime($date_key)) == '01'){
			$report[SR]['Open Tickets'][$prev_date] = $open_tickets_prev_month;
		}
		$report[SR]['Open Tickets'][$date_key] = ($report[SR]['Tickets Created'][$date_key] + $report[SR]['Open Tickets'][$prev_date]) - $report[SR]['Closed Tickets'][$date_key];
		
		$report[SR]['Open Tickets'][$MTD] = $report[SR]['Open Tickets'][$date_key];
		
		$report[SR]['Closed Tickets'][$MTD] += $report[SR]['Closed Tickets'][$date_key];
		$report[SR]['Tickets Created'][$MTD] += $report[SR]['Tickets Created'][$date_key];
			
	}
	
	unset($report[SR]['%Age Resolved Within 21Hrs - Count'][$MTD]);
	$report[SR]['%Age Resolved Within 21Hrs - Count'][$MTD] = ($report[SR]['Resolved Within 21Hrs - Count'][$MTD]/$report[SR]['(SR + Complaints + Logged Tickets)'][$MTD]) * 100;
	
	unset($report[SR]['SRs %age of all calls'][$MTD]);
	$report[SR]['SRs %age of all calls'][$MTD] = ($report[SR]['(SR + Complaints + Logged Tickets)'][$MTD]/$report[SR]['Total Calls Answered'][$MTD]) * 100;

	$report['%ages'] = array('%Age Resolved Within 21Hrs - Count', 'SRs %age of all calls', '%Age Open Tickets daily');
	//echo max($report[dates]);
	//exit(PrintR($report[SR]['Tickets Created']));
	foreach($report[dates] as $date){
		foreach($report[SR] as $sr_type => $rows){
			$report[mtd][$sr_type] = $rows[$MTD];
		}
	}
	
	$report[SR_display][0]['Total Calls Answered'] = $report[SR]['Total Calls Answered'];
	$report[SR_display][1]['Service Request'] = $report[SR]['Service Request'];
	$report[SR_display][2]['Complaints'] = $report[SR]['Complaints'];
	$report[SR_display][3]['(SR + Complaints + Logged Tickets)'] = $report[SR]['(SR + Complaints + Logged Tickets)'];
	$report[SR_display][4]['SRs %age of all calls'] = $report[SR]['SRs %age of all calls'];
	$report[SR_display][5]['Resolved Within 21Hrs - Count'] = $report[SR]['Resolved Within 21Hrs - Count'];
	$report[SR_display][6]['%Age Resolved Within 21Hrs - Count'] = $report[SR]['%Age Resolved Within 21Hrs - Count'];
	$report[SR_display][7]['Tickets Created'] = $report[SR]['Tickets Created'];
	$report[SR_display][8]['Closed Tickets'] = $report[SR]['Closed Tickets'];
	$report[SR_display][9]['Open Tickets daily'] = $report[SR]['Open Tickets daily'];
	$report[SR_display][10]['%Age Open Tickets daily'] = $report[SR]['%Age Open Tickets daily'];
	$report[SR_display][11]['Open Tickets'] = $report[SR]['Open Tickets'];

	return display_sr_report($report);
}


function translator($original){
	
	$translation['Service Restoration Request'] = 'Service Request';
	$translation['Negative Feedback'] = 'Complaints';
	$translation['Complaints'] = 'Complaints';
	$translation['Closed'] = 'Closed Tickets';
	$translation['Duplicate'] = 'Closed Tickets';
	$translation['Created'] = 'Created';
	$translation['Handled'] = 'Total Calls Answered';
				
	return $translation[$original];
}

function display_sr_report($report){
	
	if(count($report) > 0){
	
		$mail .= '
			<table border="1" cellpadding="0" cellspacing="0" width="'.(count($report[dates]) * 100).'">
				<tr>
				<th></th>
		';
		foreach($report[dates] as $date){
			
			$mail .= '<th>'.$date.'</th>';
			
		}
		
		$mail .= '</tr>
				';
				
		foreach($report[data] as $sr_source => $sr_source_rows){
/*			$mail .= '<tr>
							<td colspan="'.(count($report[dates])+1).'" class="text_values" style="color:#fff; background:#F20000">'.$sr_source.'</td>
					</tr>';*/
			foreach($sr_source_rows as $sr_status => $rows){
				$mail .= '<!--<tr>
							<td class="text_values" style="color:#fff; background:#FF6666">'.$sr_source.' ['.$sr_status.']</td>';
				
				foreach($report[dates] as $date){
					//exit(PrintR($rows));
					if($rows[$date] == '' ) $rows[$date] = 0;
					$mail .= '<td class="values">'.number_format($rows[$date],0).'</td>';
				
				}
				
				$mail .= '</tr>-->';
			}
			
		}
		
		//$mail .= '<tr><td colspan="'.(count($report[dates])+1).'" class="text_values" style="background:#fff">SR REPORT SUMMARY</td></tr>';
		foreach($report[SR_display]	as $SR_key => $SR){	
			foreach($SR as $sr_type => $rows){
				
				$mail .= '<tr>
							<td class="text_values" style="color:#fff; background:#FF6666">'.$sr_type.'</td>';
				
				foreach($report[dates] as $date){
					//exit(PrintR($rows));
					if($rows[$date] == '' ) $rows[$date] = 0;
					if(in_array($sr_type, $report['%ages'])){ $display_value = number_format($rows[$date],2).'%'; }else{ $display_value = number_format($rows[$date],0); }
					$mail .= '<td class="values">'.$display_value.'</td>';
				
				}
				
				$mail .= '</tr>';
				
			}
		}
		
		$mail .= '
			</table>
		';
		
	}
	
	//Display the HTML
	array_shift($report[dates]);
	$MTD = 'MTD '.date('F', strtotime(max($report[dates]))).' '.date('y', strtotime(max($report[dates])));
	
	$html .= '
			<table border="1" cellpadding="0" cellspacing="0" width="400">
				<tr><th></th><th>'.$MTD.'</th></tr>';
				
	foreach($report[mtd] as $sr_type => $sr_value){
		if($sr_value == '' ) $sr_value = 0;
					if(in_array($sr_type, $report['%ages'])){ $display_value = number_format($sr_value,2).'%'; }else{ $display_value = number_format($sr_value,0); }
		$html .= '<tr><td class="text_values" style="color:#fff; background:#FF6666">'.$sr_type.'</td><td class="values">'.$display_value.'</td></tr>'; 
	}
	$html .= '
			</table>
		';
	
	if($reporttype == 'summary'){
		return $html;
	}elseif($reporttype == 'detail'){
		return $mail;
	}elseif($reporttype == 'both'){
		return $html.'<hr>'.$mail;
	}else{
		return $mail;
	}
	//return array('html'=>$html,'attach'=>$mail);
}
?>