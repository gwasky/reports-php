<?php

function generate_gsm_ivr_summary($date){
	if($date==''){ $ivr = FALSE; return show_gsm_ivr_summary($ivr);}
	$myquery = new custom_query();
	
	custom_query::select_db('ccba02.ivrperformance');
	
	/*
	$query = "
		select
			(select MAX(subscount.day) as the_date from subscount) as last_date,
			(select subscount.active_subs from subscount where `day` = last_date) as last_date_active_subs,
			(select date_format(last_date,'%M')) as this_month,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,7) = left(last_date,7)) as this_month_active_subs,
			(select date_format(last_date,'%Y')) as this_year,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,4) = left(last_date,4)) as this_year_active_subs
	";
	*/
	
	$query = "
		select
			(select MAX(subscount.day) as the_date from subscount) as last_date,
			(select subscount.active_subs from subscount where `day` = last_date) as last_date_active_subs,
			(select date_format('".$date."','%M')) as this_month,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,7) = left(last_date,7)) as this_month_active_subs,
			(select date_format('".$date."','%Y')) as this_year,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,4) = left(last_date,4)) as this_year_active_subs,
			(SELECT count(asterisk_cdrs.id) FROM asterisk_cdrs WHERE asterisk_cdrs.date_entered BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59') AS day_total_ivr,
			(SELECT count(asterisk_cdrs.id) FROM asterisk_cdrs WHERE asterisk_cdrs.date_entered BETWEEN DATE_SUB('".$date." 00:00:00', INTERVAL 1 MONTH) AND DATE_SUB('".$date." 23:59:59', INTERVAL 1 MONTH)) AS last_month_total_ivr,
			(SELECT count(asterisk_cdrs.id) FROM asterisk_cdrs WHERE asterisk_cdrs.date_entered BETWEEN '".substr($date,0,7)."-01 00:00:00' AND '".$date." 23:59:59') AS month_total_ivr,
			(SELECT count(asterisk_cdrs.id) FROM asterisk_cdrs WHERE asterisk_cdrs.date_entered BETWEEN '".substr($date,0,4)."-01-01 00:00:00' AND '".$date." 23:59:59') AS year_total_ivr,
			(SELECT count(*) FROM asterisk_cdrs INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value WHERE asterisk_cdrs.date_entered BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' AND asterisk_cdrs.last_option_group = 'IVR' AND asterisk_translations.name LIKE '100%') as day_100_ivr,
			(SELECT count(*) FROM asterisk_cdrs INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value WHERE asterisk_cdrs.date_entered BETWEEN DATE_SUB('".$date." 00:00:00', INTERVAL 1 MONTH) AND DATE_SUB('".$date." 23:59:59', INTERVAL 1 MONTH) AND asterisk_cdrs.last_option_group = 'IVR' AND asterisk_translations.name LIKE '100%') as last_month_100_ivr,
			(SELECT count(*) FROM asterisk_cdrs INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value WHERE asterisk_cdrs.date_entered BETWEEN '".substr($date,0,7)."-01 00:00:00' AND '".$date." 23:59:59' AND asterisk_cdrs.last_option_group = 'IVR' AND asterisk_translations.name LIKE '100%') as month_100_ivr,
			(SELECT count(*) FROM asterisk_cdrs INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value WHERE asterisk_cdrs.date_entered BETWEEN '".substr($date,0,4)."-01-01 00:00:00' AND '".$date." 23:59:59' AND asterisk_cdrs.last_option_group = 'IVR' AND asterisk_translations.name LIKE '100%') as year_100_ivr

	";
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - Base information ... \n";
	
	$ivr[bases] = $myquery->single($query,'ccba02.ivrperformance');
	
	$_REQUEST[bases][subbases][day] = $ivr[bases][last_date_active_subs];
	$_REQUEST[bases][subbases][month] = $ivr[bases][this_month_active_subs];
	$_REQUEST[bases][subbases][year] = $ivr[bases][this_year_active_subs];
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - Queued Call Distribution Service Levels ... \n";
	
	$ivr[bases][service_levels][day] = calculate_servicelevels($date,$period='day');
	$ivr[bases][service_levels][month] = calculate_servicelevels($date,$period='month');
	$ivr[bases][service_levels][year] = calculate_servicelevels($date,$period='year');
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - IVR Traffic summary ... \n";
	
	$ivr[ivr_traffic][day] = prep_ivr_table_data(
								$raw_stats = generate_ivrtraffic_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[ivr_traffic][month] = prep_ivr_table_data(
								$raw_stats = generate_ivrtraffic_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[ivr_traffic][year] = prep_ivr_table_data(
								$raw_stats = generate_ivrtraffic_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - IVR Info Choices ... \n";
	
	$ivr[ivr_info_choices][day] = prep_ivr_table_data(
								$raw_stats = generate_ivr_info_choice_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[ivr_info_choices][month] = prep_ivr_table_data(
								$raw_stats = generate_ivr_info_choice_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[ivr_info_choices][year] = prep_ivr_table_data(
								$raw_stats = generate_ivr_info_choice_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - IVR Activation Choices ... \n";
	
	$ivr[ivr_activation_choices][day] = prep_ivr_table_data(
								$raw_stats = generate_ivr_activation_choice_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[ivr_activation_choices][month] = prep_ivr_table_data(
								$raw_stats = generate_ivr_activation_choice_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[ivr_activation_choices][year] = prep_ivr_table_data(
								$raw_stats = generate_ivr_activation_choice_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - IVR Language distribution ... \n";
	
	$ivr[ivr_choice_language][day] = prep_ivr_table_data(
								$raw_stats = generate_ivrlanguage_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[ivr_choice_language][month] = prep_ivr_table_data(
								$raw_stats = generate_ivrlanguage_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[ivr_choice_language][year] = prep_ivr_table_data(
								$raw_stats = generate_ivrlanguage_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Call Handling summary - Queued Call Distribution Numbers... \n";
	
	$ivr[call_handling][day] = prep_ivr_table_data(
								$raw_stats = generate_calls_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[call_handling][month] = prep_ivr_table_data(
								$raw_stats = generate_calls_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[call_handling][year] = prep_ivr_table_data(
								$raw_stats = generate_calls_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	return show_gsm_ivr_summary($ivr);
}

function prep_ivr_table_data($raw_stats,$active_subs){
	foreach($raw_stats as $key=>$row){
		
		if($row[call_status] != ''){
			if($row[call_status] == 'Received'){
				$row[call_status] = 'Offered';
				$raw_stats[$key][html_id] = 'totals';
			}else{
				if($row[call_status] == 'Abandon'){ $row[call_status] = 'Abandoned';}
				$raw_stats[$key][html_id] = '';
			}
		}elseif($row[option_name] != ''){
			//IVR 100 CHOICES
			//IS THERE ANYTHING SPECIAL WE WANNA DO?
		}elseif($row[option_language] != ''){
			if($row[option_language] == 'Unknown_language'){
				$row[option_language] = 'No Language';
			}
		}elseif($row[group] != ''){
			//IVR TRAFFIC
			if($row[group] == 'IVR') $row[group] = 'Stayed in IVR';
			if($row[group] == 'Agent') $row[group] = 'Queued to Agent';
		}
		
		$row[CPC] = number_format($row[No_of_calls]/$active_subs,5);
		$row[No_of_calls] = number_format($row[No_of_calls],0);
		//echo $row[No_of_calls]." => ".$active_subs." => ".$row[cpc]."<br>";
		
		$stats[] = array('data'=>$row);
		unset($raw_stats[$key],$key,$row);
	}
	
	return $stats;
}

function calculate_servicelevels($date_to,$period){
	$myquery = new custom_query();
	
	custom_query::select_db('ivrperformance');
	
	switch($period){
		case 'day':
			$date_from = $date_to;
			break;
		case 'month':
			$date_from = substr($date_to,0,7)."-01";
			break;
		case 'year':
			$date_from = substr($date_to,0,4)."-01-01";
			break;
	}
	
	$query = "
		SELECT
			queue.entrydate, queue.que, queue.servicelevel as sl, calldetail.status as call_status, calldetail.calls
		FROM
			calldetail Inner Join queue ON queue.id = calldetail.id_c
		WHERE 
			queue.entrydate between '".$date_from."' and '".$date_to."' and calldetail.status !=  'Handled'
	";
	
	$rows = $myquery->multiple($query);
	
	//PUTTING EACH QUE ON THE SAME ROWS
	foreach($rows as $row){
		$set[$row[entrydate].$row[que]][sl] = $row[servicelevel];
		$set[$row[entrydate].$row[que]][$row[call_status]] = $row[calls];
		
		if(($set[$row[entrydate].$row[que]][Abandon] != '') and ($set[$row[entrydate].$row[que]][Received] != '')){
			//METHOD 1: ABANDONED FRACTION X ABANDONED NUMBER
			//$aggregates[numerator] += ($set[$row[entrydate].$row[que]][Abandon]/$set[$row[entrydate].$row[que]][Received] * $set[$row[entrydate].$row[que]][Abandon]) * $row[sl];
			//$aggregates[denominator] += ($set[$row[entrydate].$row[que]][Abandon]/$set[$row[entrydate].$row[que]][Received] * $set[$row[entrydate].$row[que]][Abandon]);
			
			//METHOD 2: RECEIVED CALLS
			$aggregates[numerator] += ($set[$row[entrydate].$row[que]][Received] * $row[sl]);
			$aggregates[denominator] += $set[$row[entrydate].$row[que]][Received];
		}
	}
	
	return number_format($aggregates[numerator]/$aggregates[denominator],1)." %";
}

function generate_calls_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			calldetail.status as call_status, 
			sum(calldetail.calls) as No_of_calls 
		FROM 
			calldetail
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE
		";
		
	switch($period){
		case 'year':
			$query .= "	left(queue.entrydate,4) = left('".$date."',4) ";
			break;
		case 'month':
			$query .= "	left(queue.entrydate,7) = left('".$date."',7) ";
			break;
		case 'day':
		default:
			$query .= "	queue.entrydate = '".$date."' ";
			break;
	}
	
	$query .= "
		GROUP BY
			calldetail.status
	";
	
	return $myquery->multiple($query,'ivrperformance');
}

function generate_ivr_info_choice_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			SUBSTRING_INDEX(asterisk_translations.name, ': ', -1) AS option_name,
			count(*) AS No_of_calls
		FROM
			asterisk_cdrs
			INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value
		WHERE
			asterisk_cdrs.date_entered BETWEEN ";

	switch($period){
		case 'day':
			$query .= "	'".$date." 00:00:00' ";
			break;
		case 'month':
			$query .= "	'".substr($date,0,7)."-01 00:00:00' ";
			break;
		case 'year':
		default:
			$query .= "	'".substr($date,0,4)."-01-01 00:00:00' ";
			break;
	}
	
	$query .= " AND '".$date." 23:59:59' AND
			asterisk_cdrs.last_option_group = 'IVR' AND
			asterisk_translations.name LIKE '100%' AND
			asterisk_translations.`name` LIKE '%info%'
		GROUP BY
			option_name
		ORDER BY
			No_of_calls DESC
		LIMIT 6
	";
	
	return $myquery->multiple($query,'ivrperformance');
}

function generate_ivr_activation_choice_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			SUBSTRING_INDEX(asterisk_translations.name, ': ', -1) AS option_name,
			count(*) AS No_of_calls
		FROM
			asterisk_cdrs
			INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value
		WHERE
			asterisk_cdrs.date_entered BETWEEN ";

	switch($period){
		case 'day':
			$query .= "	'".$date." 00:00:00' ";
			break;
		case 'month':
			$query .= "	'".substr($date,0,7)."-01 00:00:00' ";
			break;
		case 'year':
		default:
			$query .= "	'".substr($date,0,4)."-01-01 00:00:00' ";
			break;
	}
	
	$query .= " AND '".$date." 23:59:59' AND
			asterisk_cdrs.last_option_group = 'IVR' AND
			asterisk_translations.name LIKE '100%' AND
			asterisk_translations.`name` LIKE '%activation%' AND asterisk_translations.`name` NOT LIKE '%Info/Activation selection Menu%'
		GROUP BY
			option_name
		ORDER BY
			No_of_calls DESC
		LIMIT 5
	";
	
	return $myquery->multiple($query,'ivrperformance');
}

function generate_ivrlanguage_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			SUBSTRING_INDEX(SUBSTRING_INDEX(asterisk_translations.name, ' ', 2),' ',-1) as option_language,
			count(*) AS No_of_calls
		FROM
			asterisk_cdrs
			INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value
		WHERE
			asterisk_cdrs.date_entered BETWEEN ";
		
	switch($period){
		case 'day':
			$query .= "	'".$date." 00:00:00' ";
			break;
		case 'month':
			$query .= "	'".substr($date,0,7)."-01 00:00:00' ";
			break;
		case 'year':
		default:
			$query .= "	'".substr($date,0,4)."-01-01 00:00:00' ";
			break;
	}
	
	$query .= " AND '".$date." 23:59:59' AND
			asterisk_cdrs.last_option_group = 'IVR' AND
			asterisk_translations.name LIKE '100%'
		GROUP BY
			option_language
		ORDER BY
			No_of_calls DESC
		LIMIT 6
	";
	
	return $myquery->multiple($query,'ivrperformance');
}

function generate_ivrtraffic_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT
			IF(
				asterisk_cdrs.last_option_group NOT IN ('IVR','Agent'),
				'Others',
				asterisk_cdrs.last_option_group
			) AS `group`,
			IF(
				asterisk_cdrs.last_option_group IN ('IVR','Agent'),
				SUBSTRING_INDEX(asterisk_translations.name,' ',1),
				asterisk_cdrs.last_option_group
			) AS sub_group,
			count(*) as No_of_calls
		FROM
			asterisk_cdrs
			LEFT OUTER Join asterisk_translations ON asterisk_translations.option_value = asterisk_cdrs.last_option_value
		WHERE
			asterisk_cdrs.date_entered BETWEEN ";

	switch($period){
		case 'day':
			$query .= "	'".$date." 00:00:00' ";
			break;
		case 'month':
			$query .= "	'".substr($date,0,7)."-01 00:00:00' ";
			break;
		case 'year':
		default:
			$query .= "	'".substr($date,0,4)."-01-01 00:00:00' ";
			break;
	}
	
	$query .= " AND '".$date." 23:59:59'
		GROUP BY
			`group`,sub_group
		ORDER BY
			FIND_IN_SET( `group`, 'IVR,Agent,Others'), No_of_calls DESC
		LIMIT 7
	";
	
	//echo $query; exit();
	
	return $myquery->multiple($query,'ccba02.ivrperformance');
}

function show_gsm_ivr_summary($data){
	
	if($data == FALSE){return "NO IVR DATA<br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>

		<div class="category_head">IVR TRAFFIC</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[ivr_traffic][day],
				   'notes'=>'
				   			Total calls all helplines : '.number_format($data[bases][day_total_ivr],0).'<br>
							Total calls all helplines last month on this date : '.number_format($data[bases][last_month_total_ivr],0).' <br>
				   			Active subscribers on '.$data[bases][last_date].' : '.number_format($data[bases][last_date_active_subs],0).' <br><br>
							Unknown_code = No of calls where the customer did not select any IVR option and the IVR system dropped the call'
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[ivr_traffic][month],
				   'notes'=>'
				   			Total calls all helplines : '.number_format($data[bases][month_total_ivr],0).'<br><br>
							Unknown_code = No of calls where the customer did not select any IVR option and the IVR system dropped the call'
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" id="data_td" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[ivr_traffic][year],
				   'notes'=>'
				   			Total calls all helplines : '.number_format($data[bases][year_total_ivr],0).'<br><br>
							Unknown_code = No of calls where the customer did not select any IVR option and the IVR system dropped the call'
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
		
		<div class="category_head">100 IVR SERVICE INFORMATION CHOICE SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[ivr_info_choices][day],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][day_100_ivr],0).'<br>
							Total callers in IVR (100) with valid option last month on this date : '.number_format($data[bases][last_month_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[ivr_info_choices][month],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][month_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" id="data_td" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[ivr_info_choices][year],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][year_100_ivr],0).' '
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
	
		<div class="category_head">100 IVR SERVICE ACTIVATION CHOICE SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[ivr_activation_choices][day],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][day_100_ivr],0).'<br>
							Total callers in IVR (100) with valid option last month on this date : '.number_format($data[bases][last_month_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[ivr_activation_choices][month],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][month_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" id="data_td" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[ivr_activation_choices][year],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][year_100_ivr],0).' '
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
		
		<div class="category_head">IVR CHOICE LANGUAGE SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[ivr_choice_language][day],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][day_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[ivr_choice_language][month],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][month_100_ivr],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" id="data_td" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[ivr_choice_language][year],
				   'notes'=>'
				   			Total callers in IVR (100) with valid option : '.number_format($data[bases][year_100_ivr],0).' '
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

		<div class="category_head">QUEUED CALL SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[call_handling][day],
				   'notes'=>'
				   			Average service level on '.$_REQUEST[use_date].' : '.$data[bases][service_levels][day].' <br>
				   			Active subscribers on '.$data[bases][last_date].' : '.number_format($data[bases][last_date_active_subs],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[call_handling][month],
				   'notes'=>'
				   			Average service level : '.$data[bases][service_levels][month].' <br>
				   			Average number of active subscribers : '.number_format($data[bases][this_month_active_subs],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" id="data_td" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[call_handling][year],
				   'notes'=>'
				   			Average service level : '.$data[bases][service_levels][year].' <br>
				   			Average number of active subscribers : '.number_format($data[bases][this_year_active_subs],0).''
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