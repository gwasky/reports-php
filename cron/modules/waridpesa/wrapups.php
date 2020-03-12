<?php
function generate_warid_pesa_wrapup_summary($date){

	if($date==''){ return show_gsm_wrapup_summary(FALSE);}
	
	//custom_query::select_db('reportscrm');
	$myquery = new custom_query();
	
	$query = "
		select
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat = 'Warid Pesa' and reportsphonecalls.createdon between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat = 'Warid Pesa' and reportsphonecalls.createdon between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_month_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat = 'Warid Pesa' and reportsphonecalls.createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat = 'Warid Pesa' and reportsphonecalls.createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_wrapups
	";
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA wrap up summary - Base information ... \n";
	
	$wrapup[bases] = $myquery->single($query,'ccba02.reportscrm');
	$wrapup[bases][yesterday_CPC] = $wrapup[bases][yesterday_wrapups]/$_REQUEST[bases][subbases][day];
	$wrapup[bases][months_CPC] = $wrapup[bases][months_wrapups]/$_REQUEST[bases][subbases][month];
	$wrapup[bases][years_CPC] = $wrapup[bases][years_wrapups]/$_REQUEST[bases][subbases][year];
	
	$_REQUEST[bases][wrapups][yesterday_wrapups] = $wrapup[bases][yesterday_wrapups];
	$_REQUEST[bases][wrapups][months_wrapups] = $wrapup[bases][months_wrapups];
	$_REQUEST[bases][wrapups][years_wrapups] = $wrapup[bases][years_wrapups];
	
	//Include service types and Subject type info in the base array
	warid_pesa_wrapup_stats_bytype_add_baseinfo($wrapup[bases],$date);
	
	$wrapup[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query,'ccba02.reportscrm');
	$wrapup[bases][this_month] = $result[this_month];
	$wrapup[bases][this_year] = substr($date,0,4);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA wrap up summary - Inquiries ... \n";
	
	//Inquiries
	$wrapup[data_sets][inquiries][day] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='day',$type='Inquiry'),
												$total_wrapups = $wrapup[bases][yesterday_wrapups]
											);
	
	$wrapup[data_sets][inquiries][month] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='month',$type='Inquiry'),
												$total_wrapups = $wrapup[bases][last_month_wrapups]
											);
	
	$wrapup[data_sets][inquiries][year] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='year',$type='Inquiry'),
												$total_wrapups = $wrapup[bases][years_wrapups]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA wrap up summary - Service Requests ... \n";
	
	//Service Requests
	$wrapup[data_sets][service_requests][day] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='day',$service_type='Service Restoration Request'),
												$total_wrapups = $wrapup[bases][yesterday_wrapups]
											);
	
	$wrapup[data_sets][service_requests][month] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='month',$service_type='Service Restoration Request'),
												$total_wrapups = $wrapup[bases][last_month_wrapups]
											);
	
	$wrapup[data_sets][service_requests][year] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='year',$service_type='Service Restoration Request'),
												$total_wrapups = $wrapup[bases][years_wrapups]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA wrap up summary - Negative feed back ... \n";
	
	//Negative Feedback
	$wrapup[data_sets][negative_feedback][day] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='day',$type='Negative Feedback'),
												$total_wrapups = $wrapup[bases][yesterday_wrapups]
											);
	
	$wrapup[data_sets][negative_feedback][month] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='month',$type='Negative Feedback'),
												$total_wrapups = $wrapup[bases][last_month_wrapups]
											);
	
	$wrapup[data_sets][negative_feedback][year] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bytype($date,$period='year',$type='Negative Feedback'),
												$total_wrapups = $wrapup[bases][years_wrapups]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA wrap up summary - Language distribution ... \n";
	
	//Language distributiokn
	$wrapup[data_sets][language][day] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bylang($date,$period='day'),
												$total_wrapups = $wrapup[bases][yesterday_wrapups]
											);
	
	$wrapup[data_sets][language][month] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bylang($date,$period='month'),
												$total_wrapups = $wrapup[bases][last_month_wrapups]
											);
	
	$wrapup[data_sets][language][year] = prep_wrapup_table_data(
												$raw_stats = generate_warid_pesa_wrapup_stats_bylang($date,$period='year'),
												$total_wrapups = $wrapup[bases][years_wrapups]
											);

	
	return show_warid_pesa_wrapup_summary($wrapup);
}

//function prep_wrapup_table_data($raw_stats,$total_wrapups){
	
//	foreach($raw_stats as $key=>$row){
		
//		unset($skip);
		//$row['%age'] = number_format($row[No_of_wrapups]*100/$total_wrapups,1);
//		$row['count'] = number_format($row['count'],0);
//		if($row[subject] != '') $row[subject] = wordwrap($row[subject],50,"<br>");
//		if($row[language] != ''){
//			if($row[language] == 'Undetermined (Dropped/Silent Calls)'){
				//skip this because it is not a language ...
//				$skip = TRUE;
//			}
//			$row[language] = wordwrap($row[language],50,"<br>");
//		}
		
		//to skip un necessary rows
//		if(!$skip) { $stats[] = array('data'=>$row);}
//		unset($raw_stats[$key],$key,$row);
//	}
	
//	return $stats;
//}

function generate_warid_pesa_wrapup_stats_bytype($date,$period,$type){

	$myquery = new custom_query();
	//custom_query::select_db('reportscrm');
	
	$query = "
		SELECT 
			reportsphonecalls.wrapupsubcat as category,
			reportsphonecalls.subject,
			count(reportsphonecalls.subject) as `count`,
			round(count(reportsphonecalls.subject)/".number_format($_REQUEST[bases][subbases][$period],2,'.','').",6) as CPC
		FROM 
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			reportsphonecalls.wrapupsubcat = 'Warid Pesa' AND
			".wrapup_stats_bytype_period_query($period,$date)."
			".wrapup_stats_bytype_type_query($type)."
			AND reportsphonecalls.wrapupsubcat != 'Prank Calls'
		GROUP BY
			category,subject,subsubcategory.subject_type
		ORDER BY
			count(reportsphonecalls.subject) DESC
		LIMIT 6
	";
	
	//echo $query."\n\n\n";
	
	return $myquery->multiple($query,'ccba02.reportscrm');
}

function generate_warid_pesa_wrapup_stats_bylang($date,$period='day'){

	$myquery = new custom_query();
	//custom_query::select_db('reportscrm');
	
	$query = "
		SELECT 
			reportsphonecalls.language as language,
			count(reportsphonecalls.language) as count
		FROM 
			reportsphonecalls
		WHERE
			reportsphonecalls.wrapupsubcat = 'Warid Pesa' AND
			".wrapup_stats_bytype_period_query($period,$date)."
		GROUP BY
			language
		ORDER BY
			count(reportsphonecalls.language) DESC
		LIMIT 6
	";
	
	return $myquery->multiple($query,'ccba02.reportscrm');
}

function show_warid_pesa_wrapup_summary($data){
	
	if($data == FALSE){return "NO WRAP UP DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">FIRST CALL RESOLUTION SUMMARY - INQUIRIES</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][inquiries][day],
				   'notes'=>'Total wrap ups : '.number_format($data[bases][yesterday_wrapups],0).', CPC = '.number_format($data[bases][yesterday_CPC],5).' <br>
							Total wrap ups last month on this date : '.number_format($data[bases][last_month_wrapups],0).' <br>
							Total Inquiries : '.number_format($data[bases][day_Inquiry],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][inquiries][month],
				   'notes'=>'
				   			Total wrap ups : '.number_format($data[bases][months_wrapups],0).', CPC = '.number_format($data[bases][months_CPC],5).' <br>
							Total Inquiries : '.number_format($data[bases][month_Inquiry],0).' '
					);
	
	$html .= '
		<td class="data_td" width="30%" align="center" valign="top" id="data_td">
		'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
		</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][inquiries][year],
				   'notes'=>'
				   			Total wrap ups : '.number_format($data[bases][years_wrapups],0).'<!--, CPC = '.number_format($data[bases][years_CPC],5).'--> <br>
							Total Inquiries : '.number_format($data[bases][year_Inquiry],0).' '
					);
	
	$html .= '
					<td class="data_td" width="30%" align="center" valign="top">
					'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
					</td>
				</tr>
			</table>
		</div>
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">FIRST CALL RESOLUTION SUMMARY - SERVICE (RESTORATION) REQUESTS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][service_requests][day],
				   'notes'=>'
							Total Service requests : '.number_format($data[bases]['day_Service Restoration Request'],0).' '
				  	);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][service_requests][month],
				   'notes'=>'
							Total Service requests : '.number_format($data[bases]['month_Service Restoration Request'],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][service_requests][year],
				   'notes'=>'
							Total Service requests : '.number_format($data[bases]['year_Service Restoration Request'],0).' '
				   );
	
	$html .= '
					<td class="data_td" width="30%" align="center" valign="top">
					'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
					</td>
				</tr>
			</table>
		</div>

		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">FIRST CALL RESOLUTION SUMMARY - COMPLAINTS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][negative_feedback][day],
				   'notes'=>'
							Total Complaints : '.number_format($data[bases]['day_Negative Feedback'],0).' '
				  	);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][negative_feedback][month],
				   'notes'=>'
							Total Complaints : '.number_format($data[bases]['month_Negative Feedback'],0).' '
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][negative_feedback][year],
				   'notes'=>'
							Total Complaints : '.number_format($data[bases]['year_Negative Feedback'],0).' '
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
		<div class="category_head">FIRST CALL RESOLUTION SUMMARY - LANGUAGE DISTRIBUTION</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][language][day],
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
				   'rows'=>$data[data_sets][language][month],
				   'notes'=>''
				   );
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][language][year],
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
	';
	
	return $html;
}

//USES THE SAME FUNCTION AS GSM ACTIVITIES REPORT HENCE COMMENTED OUT HERE
//function wrapup_stats_bytype_period_query($period,$date){
//	switch($period){
//		case 'year':
//			$query .= "	reportsphonecalls.createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' ";
//			break;
//		case 'month':
//			$query .= "	reportsphonecalls.createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' ";
//			break;
//		case 'day':
//		default:
//			$query .= "	reportsphonecalls.createdon between '".$date." 00:00:00' and '".$date." 23:59:59' ";
//			break;
//	}
//
//	return $query;
//}

//USES THE SAME FUNCTION AS GSM ACTIVITIES REPORT HENCE COMMENTED OUT HERE
//function wrapup_stats_bytype_type_query($type){
//	if($type != ''){ $query = " and subsubcategory.subject_type = '".$type."' "; } return $query;
//}

function warid_pesa_wrapup_stats_bytype_add_baseinfo(&$base,$date,$period=''){
	
	$myquery = new custom_query();
	//custom_query::select_db('reportscrm');
	
	if($period == ''){
		$periods = array('day','month','year');
	}else{
		$periods = array($period);
	}

	foreach($periods as $period){
		$query = "
			SELECT
				subsubcategory.subject_type as name,
				count(*) as num
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				reportsphonecalls.wrapupsubcat = 'Warid Pesa' AND
				".wrapup_stats_bytype_period_query($period,$date)."
			group by
				subject_type
		";
		
		//echo $period." =>> <br>".$query."<br>";
		
		$additional_base = $myquery->multiple($query,'ccba02.reportscrm');
		
		foreach($additional_base as $row){
			if(in_array($row[name],array('Service Restoration Request','Negative Feedback','Inquiry'))){
				$base[$period."_".$row[name]] = $row[num];
			}
		}
		
		//echo "------------------------------------------------------------------------------------<br>";
	}
}

?>