<?php

function generate_warid_pesa_gsm_ivr_summary($date){
	if($date==''){ $ivr = FALSE; return show_warid_pesa_gsm_ivr_summary($ivr);}
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
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA IVR summary - Base information ... \n";
	
	$ivr[bases] = $myquery->single($query);
	
	$_REQUEST[bases][subbases][day] = $ivr[bases][last_date_active_subs];
	$_REQUEST[bases][subbases][month] = $ivr[bases][this_month_active_subs];
	$_REQUEST[bases][subbases][year] = $ivr[bases][this_year_active_subs];
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA IVR summary - Service Levels ... \n";
	
	$ivr[bases][service_levels][day] = calculate_warid_pesa_servicelevels($date,$period='day');
	$ivr[bases][service_levels][month] = calculate_warid_pesa_servicelevels($date,$period='month');
	$ivr[bases][service_levels][year] = calculate_warid_pesa_servicelevels($date,$period='year');
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA IVR summary - Period summaries ... \n";
	
	$ivr[data_sets][day] = prep_ivr_table_data(
								$raw_stats = generate_warid_pesa_calls_stats($date,$period='day'),
								$active_subs = $ivr[bases][last_date_active_subs]
							);
	
	$ivr[data_sets][month] = prep_ivr_table_data(
								$raw_stats = generate_warid_pesa_calls_stats($date,$period='month'),
								$active_subs = $ivr[bases][this_month_active_subs]
							);
	
	$ivr[data_sets][year] = prep_ivr_table_data(
								$raw_stats = generate_warid_pesa_calls_stats($date,$period='year'),
								$active_subs = $ivr[bases][this_year_active_subs]
							);
	
	return show_warid_pesa_gsm_ivr_summary($ivr);
}

//ALREADY DECLARED
//function prep_ivr_table_data($raw_stats,$active_subs){
//	foreach($raw_stats as $key=>$row){
		
//		if($row[call_status] == 'Received'){
//			$raw_stats[$key][html_id] = 'totals';
//		}else{
//			if($row[call_status] == 'Abandon'){ $row[call_status] = 'Abandoned';}
//			$raw_stats[$key][html_id] = '';
//		}
		
//		$row[CPC] = number_format($row[No_of_calls]/$active_subs,5);
//		$row[No_of_calls] = number_format($row[No_of_calls],0);
		//echo $row[No_of_calls]." => ".$active_subs." => ".$row[cpc]."<br>";
		
//		$stats[] = array('data'=>$row);
//		unset($raw_stats[$key],$key,$row);
//	}
	
//	return $stats;
//}

function calculate_warid_pesa_servicelevels($date_to,$period){
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
			queue.que = 'MobileMoney' AND
			queue.entrydate between '".$date_from."' AND '".$date_to."' AND
			calldetail.status !=  'Handled'
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

function generate_warid_pesa_calls_stats($date,$period){
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			calldetail.status as call_status, 
			sum(calldetail.calls) as No_of_calls 
		FROM 
			calldetail
			Inner Join queue ON queue.id = calldetail.id_c 
		WHERE
			queue.que = 'MobileMoney' AND
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
	
	custom_query::select_db('ivrperformance');
	return $myquery->multiple($query);
}

function show_warid_pesa_gsm_ivr_summary($data){
	
	if($data == FALSE){return "NO IVR DATA<br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CALL SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][day],
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
				   'rows'=>$data[data_sets][month],
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
				   'rows'=>$data[data_sets][year],
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