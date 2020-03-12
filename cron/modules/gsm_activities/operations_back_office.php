<?php

function generate_gsm_back_office_summary($date){
	if($date==''){ echo "date is ".$date."<br>"; return show_gsm_operations_summary(FALSE);}
	
	$db_ref[db] = 'ccba01.reportscrm';
	
	custom_query::select_db('ccba01.reportscrm');
	$myquery = new custom_query();
	
	$query = "
		select
			(select count(*) from reportscrm where reportscrm.troubleticket NOT LIKE 'WPesa%' AND createdon between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_cases_created,
			(select count(*) from reportscrm where reportscrm.troubleticket NOT LIKE 'WPesa%' AND createdon between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_cases_created,
			(select count(*) from reportscrm where reportscrm.troubleticket NOT LIKE 'WPesa%' AND createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_cases_created,
			(select count(*) from reportscrm where reportscrm.troubleticket NOT LIKE 'WPesa%' AND createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_cases_created,
			
			(select count(*) from reportscrm LEFT outer join caseresolution on reportscrm.casenum = caseresolution.casenum where reportscrm.troubleticket NOT LIKE 'WPesa%' AND ((caseresolution.actualend between '".$date." 00:00:00' and '".$date." 23:59:59') or (reportscrm.createdon between '".$date." 00:00:00' and '".$date." 23:59:59'))) as yesterday_cases,
			(select count(*) from reportscrm LEFT outer join caseresolution on reportscrm.casenum = caseresolution.casenum where reportscrm.troubleticket NOT LIKE 'WPesa%' AND ((caseresolution.actualend between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month))) or (reportscrm.createdon between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month))) as last_months_cases,
			(select count(*) from reportscrm LEFT outer join caseresolution on reportscrm.casenum = caseresolution.casenum where reportscrm.troubleticket NOT LIKE 'WPesa%' AND ((caseresolution.actualend between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') or (reportscrm.createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59'))) as months_cases,
			(select count(*) from reportscrm LEFT outer join caseresolution on reportscrm.casenum = caseresolution.casenum where reportscrm.troubleticket NOT LIKE 'WPesa%' AND ((caseresolution.actualend between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') or (reportscrm.createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59'))) as years_cases,
			
			(select count(*) from correspondance where correspondance.category != 'Warid Pesa' AND createdon between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_correspondence,
			(select count(*) from correspondance where correspondance.category != 'Warid Pesa' AND createdon between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_correspondence,
			(select count(*) from correspondance where correspondance.category != 'Warid Pesa' AND createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_correspondence,
			(select count(*) from correspondance where correspondance.category != 'Warid Pesa' AND createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_correspondence,
			
			(select count(*) from smsfeedback.feedback where smsfeedback.feedback.datesent between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_smsfeedback,
			(select count(*) from smsfeedback.feedback LEFT OUTER JOIN smsfeedback.feedback_cstm ON smsfeedback.feedback.id = smsfeedback.feedback_cstm.id_c WHERE (smsfeedback.feedback.datesent between '".$date." 00:00:00' and '".$date." 23:59:59' OR smsfeedback.feedback_cstm.modified_on between '".$date." 00:00:00' and '".$date." 23:59:59')) as yesterday_smsfeedback_worked,
			(select count(*) from smsfeedback.feedback where smsfeedback.feedback.datesent between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_smsfeedback,
			(select count(*) from smsfeedback.feedback LEFT OUTER JOIN smsfeedback.feedback_cstm ON smsfeedback.feedback.id = smsfeedback.feedback_cstm.id_c WHERE (smsfeedback.feedback.datesent between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month) OR smsfeedback.feedback_cstm.modified_on between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month))) as last_months_smsfeedback_worked,
			(select count(*) from smsfeedback.feedback where smsfeedback.feedback.datesent between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_smsfeedback,
			(select count(*) from smsfeedback.feedback LEFT OUTER JOIN smsfeedback.feedback_cstm ON smsfeedback.feedback.id = smsfeedback.feedback_cstm.id_c WHERE (smsfeedback.feedback.datesent between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' OR smsfeedback.feedback_cstm.modified_on between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59')) as months_smsfeedback_worked,
			(select count(*) from smsfeedback.feedback where smsfeedback.feedback.datesent between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_smsfeedback,
			(select count(*) from smsfeedback.feedback LEFT OUTER JOIN smsfeedback.feedback_cstm ON smsfeedback.feedback.id = smsfeedback.feedback_cstm.id_c WHERE (smsfeedback.feedback.datesent between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' OR smsfeedback.feedback_cstm.modified_on between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59')) as years_smsfeedback_worked
	";
	
	//echo nl2br($query)."<hr>"; //exit();
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Base information ... \n";
	
	$cs_ops[bases] = $myquery->single($query);

	$query = "
	select
			(select count(*) from smsfeedback.sms_evaluation where smsfeedback.sms_evaluation.date_entered between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_smscsat,
			(select count(*) from smsfeedback.sms_evaluation where smsfeedback.sms_evaluation.date_entered between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_smscsat,
			(select count(*) from smsfeedback.sms_evaluation where smsfeedback.sms_evaluation.date_entered between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_smscsat,
			(select count(*) from smsfeedback.sms_evaluation where smsfeedback.sms_evaluation.date_entered between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_smscsat
	";
	$result = $myquery->single($query,'ccba02.smsfeedback');
	foreach($result as $key=>$value){
		$cs_ops[bases][$key] = $value;
	}
	unset($key,$value);
	
	$cs_ops[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$cs_ops[bases][this_month] = $result[this_month];
	$cs_ops[bases][this_year] = substr($date,0,4);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Cases orgins ... \n";
	
	//Cases by Origin
	$cs_ops[data_sets][case_origins][day] = prep_backoffice_table_data(
												$raw_stats = generate_cases_stats($date,$period='day',$columns=array('caseorigin')),
												$total = $cs_ops[bases][yesterday_cases_created]
											);
	
	$cs_ops[data_sets][case_origins][month] = prep_backoffice_table_data(
												//$raw_stats = generate_cases_stats($date,$period='day',$columns=array('caseorigin')),
												$raw_stats = generate_cases_stats($date,$period='month',$columns=array('caseorigin')),
												$total = $cs_ops[bases][months_cases_created]
											);
	
	$cs_ops[data_sets][case_origins][year] = prep_backoffice_table_data(
												//$raw_stats = generate_cases_stats($date,$period='day',$columns=array('caseorigin')),
												$raw_stats = generate_cases_stats($date,$period='year',$columns=array('caseorigin')),
												$total = $cs_ops[bases][years_cases_created]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Case trouble tickets ... \n";
	
	//Cases by TROUBLE TICKETS
	$cs_ops[data_sets][trouble_tickets][day] = prep_backoffice_table_data(
												$raw_stats = generate_cases_stats($date,$period='day',$columns=array('troubleticket'),$limit=10),
												$total = $cs_ops[bases][yesterday_cases_created]
											);
	
	$cs_ops[data_sets][trouble_tickets][month] = prep_backoffice_table_data(
												//$raw_stats = generate_cases_stats($date,$period='day',$columns=array('troubleticket'),$limit=10),
												$raw_stats = generate_cases_stats($date,$period='month',$columns=array('troubleticket'),$limit=10),
												$total = $cs_ops[bases][months_cases_created]
											);
	
	$cs_ops[data_sets][trouble_tickets][year] = prep_backoffice_table_data(
												//$raw_stats = generate_cases_stats($date,$period='day',$columns=array('troubleticket'),$limit=10),
												$raw_stats = generate_cases_stats($date,$period='year',$columns=array('troubleticket'),$limit=10),
												$total = $cs_ops[bases][years_cases_created]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Case resolution ... \n";
	
	//Cases by CASE RESOLUTION
	$cs_ops[data_sets][case_resolution][day] = prep_backoffice_table_data(
												$raw_stats = generate_case_resolution_stats($date,$period='day'),
												$total = $cs_ops[bases][yesterday_cases]
											);
	
	$cs_ops[data_sets][case_resolution][month] = prep_backoffice_table_data(
												$raw_stats = generate_case_resolution_stats($date,$period='month'),
												$total = $cs_ops[bases][months_cases]
											);
	
	$cs_ops[data_sets][case_resolution][year] = prep_backoffice_table_data(
												$raw_stats = generate_case_resolution_stats($date,$period='year'),
												$total = $cs_ops[bases][years_cases]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Correspondence sources ... \n";
	
	//Correspondence by source
	$cs_ops[data_sets][corres_sources][day] = prep_backoffice_table_data(
												$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('wrapup_source_type.name')),
												$total = $cs_ops[bases][yesterday_correspondence]
											);
	
	$cs_ops[data_sets][corres_sources][month] = prep_backoffice_table_data(
												//$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('source')),
												$raw_stats = generate_correspondence_stats($date,$period='month',$columns=array('wrapup_source_type.name')),
												$total = $cs_ops[bases][months_correspondence]
											);
	
	$cs_ops[data_sets][corres_sources][year] = prep_backoffice_table_data(
												//$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('source')),
												$raw_stats = generate_correspondence_stats($date,$period='year',$columns=array('wrapup_source_type.name')),
												$total = $cs_ops[bases][years_correspondence]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Correspondence categorisation ... \n";
	
	//Correspondence Category and subject
	$cs_ops[data_sets][corres_distribution][day] = prep_backoffice_table_data(
												$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('category','subcategory')),
												$total = $cs_ops[bases][yesterday_correspondence]
											);
	
	$cs_ops[data_sets][corres_distribution][month] = prep_backoffice_table_data(
												//$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('category','subcategory')),
												$raw_stats = generate_correspondence_stats($date,$period='month',$columns=array('category','subcategory')),
												$total = $cs_ops[bases][months_correspondence]
											);
	
	$cs_ops[data_sets][corres_distribution][year] = prep_backoffice_table_data(
												//$raw_stats = generate_correspondence_stats($date,$period='day',$columns=array('category','subcategory')),
												$raw_stats = generate_correspondence_stats($date,$period='year',$columns=array('category','subcategory')),
												$total = $cs_ops[bases][years_correspondence]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - SMS Feedback ... \n";
	
	//SMS Feedback
	//$db_ref[db] = 'smsfeedback';
	//$db_ref[table] = 'smsfeedback.feedback LEFT OUTER JOIN smsfeedback.feedback_cstm ON smsfeedback.feedback.id = smsfeedback.feedback_cstm.id_c';
	//$db_ref[table] = 'feedback_cstm';
	$cs_ops[data_sets][sms_feedback][day] = prep_backoffice_table_data(
												//$raw_stats = generate_generic_stats($date,$period='day',$columns=array('status'),$db_ref,$date_col='datesent'),
												//$total = $cs_ops[bases][yesterday_smsfeedback]
												$raw_stats = generate_sms_feedback_status_stats($date,$period='day',$limit=5),
												$total = $cs_ops[bases][yesterday_smsfeedback_worked]
											);
	
	$cs_ops[data_sets][sms_feedback][month] = prep_backoffice_table_data(
												//$raw_stats = generate_generic_stats($date,$period='month',$columns=array('status'),$db_ref,$date_col='datesent'),
												//$total = $cs_ops[bases][months_smsfeedback]
												$raw_stats = generate_sms_feedback_status_stats($date,$period='month',$limit=5),
												$total = $cs_ops[bases][months_smsfeedback_worked]
											);
	
	$cs_ops[data_sets][sms_feedback][year] = prep_backoffice_table_data(
												//$raw_stats = generate_generic_stats($date,$period='year',$columns=array('status'),$db_ref,$date_col='datesent'),
												//$total = $cs_ops[bases][years_smsfeedback]
												$raw_stats = generate_sms_feedback_status_stats($date,$period='year',$limit=5),
												$total = $cs_ops[bases][years_smsfeedback_worked]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - SMS CSAT ... \n";
	
	//SMS CSAT
	$db_ref[db] = 'ccba02.smsfeedback';
	$db_ref[table] = 'sms_evaluation';
	$cs_ops[data_sets][sms_csat][day] = prep_backoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('LEFT(LOWER(text),1)'),$db_ref,$date_col='date_entered'),
												$total = $cs_ops[bases][yesterday_smscsat]
											);
	
	$cs_ops[data_sets][sms_csat][month] = prep_backoffice_table_data(
												//$raw_stats = generate_generic_stats($date,$period='month',$columns=array('status'),$db_ref,$date_col='date_entered'),
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('LEFT(LOWER(text),1)'),$db_ref,$date_col='date_entered'),
												$total = $cs_ops[bases][months_smscsat]
											);
	
	$cs_ops[data_sets][sms_csat][year] = prep_backoffice_table_data(
												//$raw_stats = generate_generic_stats($date,$period='year',$columns=array('status'),$db_ref,$date_col='date_entered'),
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('LEFT(LOWER(text),1)'),$db_ref,$date_col='date_entered'),
												$total = $cs_ops[bases][years_smscsat]
											);
	
	return show_gsm_back_office_summary($cs_ops);
}

function generate_cases_stats($date,$period,$columns,$limit=5){
	$myquery = new custom_query();
	custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			".list_columns($columns).",
			count(".end($columns).") as `count`
		FROM
			reportscrm
		WHERE
			reportscrm.troubleticket NOT LIKE 'WPesa%' AND
			".generic_period_query($period,$date,$column='reportscrm.createdon')."
		GROUP BY
			".list_columns($columns)."
		ORDER BY
			count(".end($columns).") DESC
		LIMIT
			".$limit."
	";
	
	return $myquery->multiple($query);
}

function generate_case_resolution_stats($date,$period){
	$myquery = new custom_query();
	custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			status,
			count(status) as `count`,
			avg(resolution_hours) as average_resolution_Hrs
		FROM
			(
			SELECT
				if(caseresolution.casenum is null,'Open','Closed') as status,
				if(caseresolution.casenum is not null,(UNIX_TIMESTAMP(caseresolution.actualend) - UNIX_TIMESTAMP(reportscrm.createdon))/3600,'') as resolution_hours
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				reportscrm.troubleticket NOT LIKE 'WPesa%' AND
				(
				(".generic_period_query($period,$date).") or
				(".generic_period_query($period,$date,$column='caseresolution.actualend').")
				)
			) as tablee
		GROUP BY
			status
	";
	
	return $myquery->multiple($query);
}

function generate_correspondence_stats($date,$period,$columns,$limit=5){
	$myquery = new custom_query();
	custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			".list_columns($columns).",
			count(".end($columns).") as `count`
		FROM
			correspondance
			Inner Join wrapup_source_type ON correspondance.source = wrapup_source_type.id
		WHERE
			correspondance.category != 'Warid Pesa' AND
			correspondance.category != 'Prank Calls' AND
			".generic_period_query($period,$date,$column='correspondance.createdon')."
		GROUP BY
			".list_columns($columns)."
		ORDER BY
			count(".end($columns).") DESC
		LIMIT
			".$limit."
	";
	
	//echo $query."<br>";
	
	return $myquery->multiple($query);
}

function generate_sms_feedback_status_stats($date,$period,$limit=5){
	$myquery = new custom_query();
	custom_query::select_db('smsfeedback');
	
	$query = "
		SELECT
			IF(feedback_cstm.`status` IS NULL,feedback.`status`,feedback_cstm.`status`) AS `the_status`,
			count(*) as `count`
		FROM
			feedback
			LEFT OUTER JOIN feedback_cstm ON feedback.id = feedback_cstm.id_c
		WHERE
			(
			 	".generic_period_query($period,$date,$column='feedback.datesent')." OR
				".generic_period_query($period,$date,$column='feedback_cstm.modified_on')."
			)
		GROUP BY
			`the_status`
		ORDER BY
			`count` DESC
		LIMIT
			".$limit."
	";
	
	//echo nl2br($query)."<hr><br><br><br><hr>";
	
	return $myquery->multiple($query);
}

function prep_backoffice_table_data($raw_stats,$total){
	
	foreach($raw_stats as $key=>$row){
		
		//WORKING ON CASE ORIGINS DISTRIBUTION
		if($row[caseorigin]){
			$row['case origin'] = $row[caseorigin]; $row['Count'] = $row['count']; unset($row[caseorigin],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON TROUBLE TICKET DISTRIBUTION
		elseif($row[troubleticket]){
			$row['trouble ticket'] = wordwrap($row[troubleticket],50,"<br>"); $row['Count'] = $row['count']; unset($row[troubleticket],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON CASE RESOLUTION
		elseif($row[status] and (in_array('average_resolution_Hrs',array_keys($row)))){
			$row['%age'] = number_format($row['count']*100/$total,1);
			$row['count'] = number_format($row['count'],0);
			$row[average_resolution_Hrs] = number_format($row[average_resolution_Hrs],2);
		}//WORKING SMS FEEDBACK
		elseif(in_array('the_status',array_keys($row))){
			if($row[the_status] == '') $skip = TRUE;
			$row['status'] = $row[the_status]; $row['Count'] = $row['count']; unset($row[the_status],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON CORRESPONDENCE SOURCES
		elseif($row[name]){
			$row['source'] = $row['name']; $row['Count'] = $row['count']; unset($row['name'],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON CORRESPONDENCE DISTRIBUTION
		elseif(($row[category]) and ($row[subcategory])){
			$row[subject] = wordwrap($row[subcategory],50,"<br>"); $row['Count'] = $row['count'];  unset($row[subcategory],$row['count']);
			//$row['%age'] = number_format($row['Count']*100/$total,0);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON CSAT BY ANSWER
		elseif($row['LEFT(LOWER(text),1)'] != ''){
			if($row['LEFT(LOWER(text),1)'] == 'y'){ $row[evaluation] = 'Positive'; } else { $row[evaluation] = 'Negative'; } $row['Count'] = number_format($row['count'],0);
			$row['%age'] = number_format($row['count']*100/$total,1);
			unset($row['LEFT(LOWER(text),1)'],$row['count']);
		}else{
			echo "uncatered for scenarion in back office opps ... <br>";
		}
		
		if(!$skip) $stats[] = array('data'=>$row);
		unset($raw_stats[$key],$key,$row,$skip);
	}
	
	return $stats;
}

function show_gsm_back_office_summary($data){
	
	if($data == FALSE){return "NO CASE DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CASE HANDLING - ORIGINS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CASE ORIGINS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][case_origins][day],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][yesterday_cases_created],0).' <br>
							Total Cases created last month on this date : '.number_format($data[bases][last_months_cases_created],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][case_origins][month],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][months_cases_created],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][case_origins][year],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][years_cases_created],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CASE HANDLING - TROUBLE TICKETS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CASE TROUBLE TICKETS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][trouble_tickets][day],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][trouble_tickets][month],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][trouble_tickets][year],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CASE HANDLING - RESOLUTION</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CASE RESOLUTION
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][case_resolution][day],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][yesterday_cases_created],0).' <br>
							Total Cases handled: '.number_format($data[bases][yesterday_cases],0).' <br>
							Total Cases created last month on this date : '.number_format($data[bases][last_months_cases_created],0).' <br>
							Total Cases handled last month on this date : '.number_format($data[bases][last_months_cases],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][case_resolution][month],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][months_cases_created],0).' <br>
							Total Cases handled: '.number_format($data[bases][months_cases],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][case_resolution][year],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][years_cases_created],0).' <br>
				   			Total Cases handled: '.number_format($data[bases][years_cases],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CORRESPONDENCE - SOURCES</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CORRESPONDENCE SOURCES
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][corres_sources][day],
				   'notes'=>'
				   			Total Messages : '.number_format($data[bases][yesterday_correspondence],0).' <br>
							Total Cases created last month on this date : '.number_format($data[bases][last_months_correspondence],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][corres_sources][month],
				   'notes'=>'
				   			Total Messages : '.number_format($data[bases][months_correspondence],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][corres_sources][year],
				   'notes'=>'
				   			Total Messages : '.number_format($data[bases][years_correspondence],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CORRESPONDENCE - CATEGORISATION</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CORRESPONDENCE DISTRIBUTION
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][corres_distribution][day],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][corres_distribution][month],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][corres_distribution][year],
				   'notes'=>''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">SMS FEEDBACK - BY STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SMS FEEDBACK - BY STATUS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][sms_feedback][day],
				   'notes'=>'
				   			SMS Received : '.number_format($data[bases][yesterday_smsfeedback],0).' <br>
							Total SMS Received or Worked on : '.number_format($data[bases][yesterday_smsfeedback_worked],0).' <br>
							SMS Received on this date last month: '.number_format($data[bases][last_months_smsfeedback],0).' <br>
							Total SMS Received or Worked on on this date last month: '.number_format($data[bases][last_months_smsfeedback_worked],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][sms_feedback][month],
				   'notes'=>'
				   			SMS Received : '.number_format($data[bases][months_smsfeedback],0).' <br>
							Total SMS Received or Worked on : '.number_format($data[bases][months_smsfeedback_worked],0).''
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][sms_feedback][year],
				   'notes'=>'
				   			SMS Received : '.number_format($data[bases][years_smsfeedback],0).' <br>
							Total SMS Received or Worked on : '.number_format($data[bases][years_smsfeedback_worked],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CUSTOMER SATISFACTION EVALUATION - BY ANSWER</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SMS CSAT - BY ANSWER
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][sms_csat][day],
				   'notes'=>'
				   			Evaluations/Interactions : '.number_format($data[bases][yesterday_smscsat],0).'/'.number_format($_REQUEST[bases][wrapups][yesterday_wrapups],0).' <br>
							Evaluations on this date last month: '.number_format($data[bases][last_months_smscsat],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][sms_csat][month],
				   'notes'=>'
				   			Evaluations/Interactions : '.number_format($data[bases][months_smscsat],0).'/'.number_format($_REQUEST[bases][wrapups][months_wrapups],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][sms_csat][year],
				   'notes'=>'
				   			Evaluations/Interactions : '.number_format($data[bases][years_smscsat],0).'/'.number_format($_REQUEST[bases][wrapups][years_wrapups],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	$html .= '
				</tr>
			</table>
		</div>
	';
	
	return $html;
}
?>