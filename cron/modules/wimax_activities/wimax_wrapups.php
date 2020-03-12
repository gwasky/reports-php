<?php

function generate_wimax_wrapup_summary($date){
	$myquery = new custom_query();
	custom_query::select_db('ivrperformance');
	$query = "
		select
			(select MAX(subscount.day) as the_date from subscount) as last_date,
			(select subscount.active_subs from subscount where `day` = last_date) as last_date_active_subs,
			(select date_format(date(now()),'%M')) as this_month,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,7) = left(last_date,7)) as this_month_active_subs,
			(select date_format(date(now()),'%Y')) as this_year,
			(select avg(subscount.active_subs) from subscount where left(subscount.`day`,4) = left(last_date,4)) as this_year_active_subs
	";

	echo date('Y-m-d H:i:s')." : [".$date."] Generating IVR summary - Base information ... \n";
	
	$ivr[bases] = $myquery->single($query,'ivrperformance');
	
	$_REQUEST[bases][subbases][day] = $ivr[bases][last_date_active_subs];
	$_REQUEST[bases][subbases][month] = $ivr[bases][this_month_active_subs];
	$_REQUEST[bases][subbases][year] = $ivr[bases][this_year_active_subs];
	
	if($date==''){ return show_wimax_wrapup_summary(FALSE);}
	//custom_query::select_db('reportscrm');
	
	$query = "
		select
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages') AND reportsphonecalls.createdon between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages') AND  reportsphonecalls.createdon between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_month_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages') AND  reportsphonecalls.createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_wrapups,
			(SELECT count(*) as Number FROM reportsphonecalls WHERE  wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages') AND reportsphonecalls.createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_wrapups
	";
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WIMAX wrap up summary - Base information ... \n";
	
	$wrapup[bases] = $myquery->single($query,'ccba02.reportscrm');
	$wrapup[bases][yesterday_CPC] = $wrapup[bases][yesterday_wrapups]/$_REQUEST[bases][subbases][day];
	$wrapup[bases][months_CPC] = $wrapup[bases][months_wrapups]/$_REQUEST[bases][subbases][month];
	$wrapup[bases][years_CPC] = $wrapup[bases][years_wrapups]/$_REQUEST[bases][subbases][year];
	
	$_REQUEST[bases][wrapups][yesterday_wrapups] = $wrapup[bases][yesterday_wrapups];
	$_REQUEST[bases][wrapups][months_wrapups] = $wrapup[bases][months_wrapups];
	$_REQUEST[bases][wrapups][years_wrapups] = $wrapup[bases][years_wrapups];

	//Include service types and Subject type info in the base array
	wimax_wrapup_stats_bytype_add_baseinfo($wrapup[bases],$date);
	
	$wrapup[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query,'ccba02.reportscrm');
	$wrapup[bases][this_month] = $result[this_month];
	$wrapup[bases][this_year] = substr($date,0,4);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax wrap up summary - Inquiries ... \n";
	
	//Inquiries
	$wrapup[data_sets][inquiries][day] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='day',$type='Inquiry'),$total_wrapups = $wrapup[bases][yesterday_wrapups]);
	
	$wrapup[data_sets][inquiries][month] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='month',$type='Inquiry'),$total_wrapups = $wrapup[bases][last_month_wrapups]);
	
	$wrapup[data_sets][inquiries][year] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='year',$type='Inquiry'),$total_wrapups = $wrapup[bases][years_wrapups]);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax wrap up summary - Service Requests ... \n";
	
	//Service Requests
	$wrapup[data_sets][service_requests][day] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='day',$service_type='Service Restoration Request'),$total_wrapups = $wrapup[bases][yesterday_wrapups]);
	
	$wrapup[data_sets][service_requests][month] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='month',$service_type='Service Restoration Request'),$total_wrapups = $wrapup[bases][last_month_wrapups]);
	
	$wrapup[data_sets][service_requests][year] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='year',$service_type='Service Restoration Request'),$total_wrapups = $wrapup[bases][years_wrapups]);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax wrap up summary - Negative feed back ... \n";
	
	//Negative Feedback
	$wrapup[data_sets][negative_feedback][day] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='day',$type='Negative Feedback'),$total_wrapups = $wrapup[bases][yesterday_wrapups]);
	
	$wrapup[data_sets][negative_feedback][month] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='month',$type='Negative Feedback'),$total_wrapups = $wrapup[bases][last_month_wrapups]);
	
	$wrapup[data_sets][negative_feedback][year] = prep_wimax_wrapup_table_data(
												$raw_stats = generate_wimax_wrapup_stats_bytype($date,$period='year',$type='Negative Feedback'),$total_wrapups = $wrapup[bases][years_wrapups]);
	
	return show_wimax_wrapup_summary($wrapup);
}

function prep_wimax_wrapup_table_data($raw_stats,$total_wrapups){
	foreach($raw_stats as $key=>$row){
		unset($skip);
		$row['count'] = number_format($row['count'],0);
		if($row[subject] != '') $row[subject] = wordwrap($row[subject],50,"<br>");
		if(!$skip) { $stats[] = array('data'=>$row);}
		unset($raw_stats[$key],$key,$row);
	}
	return $stats;
}

function wimax_wrapup_stats_bytype_period_query($period,$date){
	switch($period){
		case 'year':
			$query .= "	reportsphonecalls.createdon between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'month':
			$query .= "	reportsphonecalls.createdon between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'day':
		default:
			$query .= "	reportsphonecalls.createdon between '".$date." 00:00:00' and '".$date." 23:59:59' ";
			break;
	}
	return $query;
}

function generate_wimax_wrapup_stats_bytype($date,$period,$type){

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
			".wrapup_stats_bytype_period_query($period,$date)."
			".wrapup_stats_bytype_type_query($type)." AND reportsphonecalls.wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages')
		GROUP BY
			category,subject,subsubcategory.subject_type
		ORDER BY
			count(reportsphonecalls.subject) DESC
		LIMIT 5
	";
	
	//echo $query;
	return $myquery->multiple($query,'ccba02.reportscrm');
}

function wimax_wrapup_stats_bytype_add_baseinfo(&$base,$date,$period=''){
	
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
				".wimax_wrapup_stats_bytype_period_query($period,$date)." AND reportsphonecalls.wrapupsubcat IN ('Wimax','GPRS (Phone)','GPRS Data Packages')
			group by
				subject_type
		";
		
		$additional_base = $myquery->multiple($query,'ccba02.reportscrm');
			
		foreach($additional_base as $row){
			if(in_array($row[name],array('Service Restoration Request','Negative Feedback','Inquiry'))){
				$base[$period."_".$row[name]] = $row[num];
			}
		}
	}
}

function show_wimax_wrapup_summary($data){
	
	if($data == FALSE){return "NO WRAP UP DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">FIRST CALL RESOLUTION SUMMARY - INQUIRIES (Top 3)</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][inquiries][day],
				   'notes'=>'
				   			Total wrap ups : '.number_format($data[bases][yesterday_wrapups],0).', CPC = '.number_format($data[bases][yesterday_CPC],5).' <br>
							Total wrap ups last month on this date : '.number_format($data[bases][last_month_wrapups],0).' '
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
	';
	
	$html .= '
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
	
	//Month
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
	';
	
	$html .= '
				</tr>
			</table>
		</div>
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
		</div>';
	
	return $html;
}

?>


