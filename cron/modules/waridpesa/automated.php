<?php

function generate_warid_pesa_ussd_summary($date){
	if($date==''){ $ussd = FALSE; return show_warid_pesa_ussd_summary($ussd);}
	$myquery = new custom_query();
	
	custom_query::select_db('reportsussd');
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA USSD summary - Base information ... \n";
	
	$query = "
		SELECT count(*) AS sessions, sum(ussd_log.number_of_tx) AS hits FROM ussd_log WHERE ussd_log.service_code = '*144' AND ussd_log.start_time between '".$date." 00:00:00' AND '".$date." 23:59:59'
	";
	$result = $myquery->single($query);
	$ussd[bases][day][sessions] = $result[sessions];
	$ussd[bases][day][hits] = $result[hits];
	$query = "
		SELECT count(*) AS sessions, sum(ussd_log.number_of_tx) AS hits FROM ussd_log WHERE ussd_log.service_code = '*144' AND  ussd_log.start_time between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)
	";
	$result = $myquery->single($query);
	$ussd[bases][last_month_day][sessions] = $result[sessions];
	$ussd[bases][last_month_day][hits] = $result[hits];
	$query = "
		SELECT count(*) AS sessions, sum(ussd_log.number_of_tx) AS hits FROM ussd_log WHERE ussd_log.service_code = '*144' AND  ussd_log.start_time between '".substr($date,0,7)."-01 00:00:00' AND '".$date." 23:59:59'
	";
	$result = $myquery->single($query);
	$ussd[bases][month][sessions] = $result[sessions];
	$ussd[bases][month][hits] = $result[hits];
	$query = "
		SELECT count(*) AS sessions, sum(ussd_log.number_of_tx) AS hits FROM ussd_log WHERE ussd_log.service_code = '*144' AND  ussd_log.start_time between '".substr($date,0,4)."-01-01 00:00:00' AND '".$date." 23:59:59'
	";
	$result = $myquery->single($query);
	$ussd[bases][year][sessions] = $result[sessions];
	$ussd[bases][year][hits] = $result[hits];
	
	$ussd[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$ussd[bases][this_month] = $result[this_month];
	$ussd[bases][this_year] = substr($date,0,4);

	echo date('Y-m-d H:i:s')." : [".$date."] Generating WARID PESA USSD summary - Period summaries ... \n";
	
	$ussd[data_sets][day] = prep_ussd_table_data(
								$raw_stats = generate_warid_pesa_ussd_stats($date,$period='day'),
								$totals = $ussd[bases][day]
							);
	
	$ussd[data_sets][month] = prep_ussd_table_data(
								$raw_stats = generate_warid_pesa_ussd_stats($date,$period='month'),
								$totals = $ussd[bases][month]
							);
	
	$ussd[data_sets][year] = prep_ussd_table_data(
								$raw_stats = generate_warid_pesa_ussd_stats($date,$period='year'),
								$totals = $ussd[bases][year]
							);
	
	return show_warid_pesa_ussd_summary($ussd);
}


//ALREADY DECLARED
//function prep_ussd_table_data($raw_stats,$totals){
//	foreach($raw_stats as $key=>$row){
//		$session_value = $row[session_count];
//		$row[session_count] = number_format($row[session_count],0);
//		$row['session_%age'] = number_format($session_value*100/$totals[sessions],1);
//		$row[hit_count] = number_format($row[hit_count],0);
//		
//		$stats[] = array('data'=>$row);
//		unset($raw_stats[$key],$key,$row);
//	}
//	
//	return $stats;
//}

function generate_warid_pesa_ussd_stats($date,$period){
	$myquery = new custom_query();
	
		$query = "
		SELECT
			ussd_log.service_code,
			if(ussd_log.end_event='PROCESSED','COMPLETE','INCOMPLETE') AS status,
			count(if(ussd_log.end_event='PROCESSED','COMPLETE','INCOMPLETE')) AS session_count,
			sum(ussd_log.number_of_tx) AS hit_count
		FROM
			ussd_log
		WHERE
			ussd_log.service_code = '*144' AND
	";

	switch($period){
		case 'year':
			$query .= "	ussd_log.start_time between '".substr($date,0,4)."-01-01 00:00:00' AND '".$date." 23:59:59' ";
			break;
		case 'month':
			$query .= "	ussd_log.start_time between '".substr($date,0,7)."-01 00:00:00' AND '".$date." 23:59:59' ";
			break;
		case 'day':
		default:
			$query .= "	ussd_log.start_time between '".$date." 00:00:00' AND '".$date." 23:59:59' ";
			break;
	}
	
	$query .= "
		GROUP BY
			service_code,status
		ORDER BY
			service_code ASC
	";
	
	return $myquery->multiple($query);
}

function show_warid_pesa_ussd_summary($data){
	
	if($data == FALSE){return "NO IVR DATA<br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">USSD SUMMARY</div>
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
				   			Total Sessions : '.number_format($data[bases][day][sessions],0).' <br>
				   			Total Hits : '.number_format($data[bases][day][hits],0).' <br>
							Total Sessions on this date last month : '.number_format($data[bases][last_month_day][sessions],0).' <br>
				   			Total Hits on this date last month : '.number_format($data[bases][last_month_day][hits],0).' '
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
				   			Total Sessions : '.number_format($data[bases][month][sessions],0).' <br>
				   			Total Hits : '.number_format($data[bases][month][hits],0).' '
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
				   			Total Sessions : '.number_format($data[bases][year][sessions],0).' <br>
				   			Total Hits : '.number_format($data[bases][year][hits],0).' '
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