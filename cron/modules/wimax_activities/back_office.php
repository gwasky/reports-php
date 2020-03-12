<?php

function generate_wimax_back_office_summary($date){
	
	if($date==''){ echo "date is ".$date."<br>"; return show_wimax_operations_summary(FALSE);}
	$db_ref[db] = 'wimax';
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	
	$query = "
		select
			(SELECT count(*) FROM cases where date_entered between DATE_SUB('".$date." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$date." 23:59:59', INTERVAL 3 HOUR) AND deleted = 0) as yesterday_cases_created,
			
			(select count(*) from cases where date_entered between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month) and deleted = 0) as last_months_cases_created,
			
			(select count(*) from cases where date_entered between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' and deleted = 0) as months_cases_created,
			
			(select count(*) from cases where date_entered between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' and deleted = 0) as years_cases_created,
			
			(SELECT count(*) FROM cases INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) INNER JOIN accounts ON (cases.account_id=accounts.id) LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') WHERE cases.deleted = '0' AND accounts.deleted = '0' AND ((cases.date_entered BETWEEN  '".$date." 00:00:00' and  '".$date." 23:59:59') OR (cases_audit.date_created BETWEEN '".$date." 00:00:00' and  '".$date." 23:59:59'))) as yesterday_cases_handled,
			
			(SELECT count(*) FROM cases INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) INNER JOIN accounts ON (cases.account_id=accounts.id) LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND  cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') WHERE cases.deleted = '0' AND accounts.deleted = '0' AND ((cases.date_entered between date_sub('".$date." 00:00:00', interval 1 month) and  date_sub('".$date." 23:59:59', interval 1 month)) OR (cases_audit.date_created between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)))) as yesterday_cases_handled_last_month,
			
			(SELECT count(*) FROM cases INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) INNER JOIN accounts ON (cases.account_id=accounts.id) LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') WHERE cases.deleted = '0' AND accounts.deleted = '0' and ((cases.date_entered between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') OR (cases_audit.date_created between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59'))) as year_cases_handled,
			
			(SELECT count(*) FROM cases INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c) INNER JOIN accounts ON (cases.account_id=accounts.id) LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status') WHERE cases.deleted = '0' AND accounts.deleted = '0' and ((cases.date_entered between '".substr($date,0,7)."-01 00:00:00' and  '".$date." 23:59:59') OR (cases_audit.date_created between '".substr($date,0,7)."-01 00:00:00' and  '".$date." 23:59:59'))) as month_cases_handled
	";
	
	//echo $query; echo "\n...................\n";
		
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax back office summary - Base information ... \n";
	
	$cs_ops[bases] = $myquery->single($query);
	$cs_ops[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$cs_ops[bases][this_month] = $result[this_month];
	$cs_ops[bases][this_year] = substr($date,0,4);

	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating back office summary - Cases Handling - Trouble Tickets... \n";
	
	$cs_ops[data_sets][troubletickets][day] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_cases_stats($date,$period='day',$columns=array('cases_cstm.subject_setting_c as troubleticket'),$limit=5),
												$total = $cs_ops[bases][yesterday_cases_created]
											);
						//print_r($cs_ops[data_sets][troubletickets]);					
	
	$cs_ops[data_sets][troubletickets][month] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_cases_stats($date,$period='month',$columns=array('cases_cstm.subject_setting_c as troubleticket'),$limit=5),
												$total = $cs_ops[bases][months_cases_created]
											);
	
	$cs_ops[data_sets][troubletickets][year] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_cases_stats($date,$period='year',$columns=array('cases_cstm.subject_setting_c as troubleticket'),$limit=5),
												$total = $cs_ops[bases][years_cases_created]
											);
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Back Office summary - Cases Handling - Resolution... \n";
	
	$cs_ops[data_sets][caseresolution][day] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_resolution_stats($date,$period='day'),
												$total = $cs_ops[bases][yesterday_cases_created]
											);
	
	$cs_ops[data_sets][caseresolution][month] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_resolution_stats($date,$period='month'),
												$total = $cs_ops[bases][months_cases_created]
											);
	
	$cs_ops[data_sets][caseresolution][year] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_resolution_stats($date,$period='year'),
												$total = $cs_ops[bases][years_cases_created]
											);
									
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Back Office summary - Cases Handling - Root Causes... \n";
	
	$cs_ops[data_sets][rootcauses][day] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_handling_rootcauses_stats($date,$period='day',$columns=array('cases_cstm.root_cause_c as root_causes'),$limit=5),
												$total = $cs_ops[bases][yesterday_cases_created]
											);
	
	$cs_ops[data_sets][rootcauses][month] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_handling_rootcauses_stats($date,$period='month',$columns=array('cases_cstm.root_cause_c as root_causes'),$limit=5),
												$total = $cs_ops[bases][months_cases_created]
											);
	
	$cs_ops[data_sets][rootcauses][year] = prep_wimaxbackoffice_table_data(
												$raw_stats = generate_wimax_case_handling_rootcauses_stats($date,$period='year',$columns=array('cases_cstm.root_cause_c as root_causes'),$limit=5),
												$total = $cs_ops[bases][years_cases_created]
											);

	return show_wimax_operations_summary($cs_ops);

}

function generate_wimax_case_resolution_stats($date,$period){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	/*
	$query = "				
SELECT
		count(case when status = 'Assigned' or status = 'Pending Input' then 1 else null end) AS open,
		sum(case when status = 'Assigned' or status = 'Pending Input' then UNIX_TIMESTAMP(cases_audit.date_created) else null end) as open_hours,
		count(case when status = 'Closed' then 1 else null end) AS Closed,
		sum(case when status = 'closed' then UNIX_TIMESTAMP(cases_audit.date_created) else null end) as closed_hours
	FROM
		cases
		INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
		INNER JOIN accounts ON (cases.account_id=accounts.id)
		LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status')
	WHERE 
		accounts.deleted = '0' and
		".wimax_operations_wrapup_stats_period_query($period,$date)."
	";*/
	
	$query = "
			SELECT
				cases.status,
				count(cases.status) as `count`,
				avg((UNIX_TIMESTAMP(cases_audit.date_created) - UNIX_TIMESTAMP(cases.date_entered))/3600) as average_resolution_Hrs
			FROM
				cases
				INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
				INNER JOIN accounts ON (cases.account_id=accounts.id)
				LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed' AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status')
			WHERE 
				cases.deleted = '0' AND
				accounts.deleted = '0' AND
				(
					(".wimax_operations_wrapup_stats_period_query($period,$date).") or
					(".wimax_audit_operations_wrapup_stats_period_query($period,$date).")
				)	
		
		GROUP BY cases.status
		";
		//echo $query;
		//print_r($myquery->single($query));
	return $myquery->multiple($query);
}

function generate_wimax_case_handling_rootcauses_stats($date,$period,$columns,$limit=5){
	
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	$getAlias = explode(" ",$columns[0]);
	
	$query = "
		SELECT
			".list_columns($columns).",
			count(".$getAlias[0].") as `count`
		FROM
			cases INNER JOIN cases_cstm ON cases.id = cases_cstm.id_c
		WHERE
			cases.deleted = '0' AND
			".wimax_operations_wrapup_stats_period_query($period,$date)."
		GROUP BY
			".$getAlias[0]."
		ORDER BY
			`count` DESC
		LIMIT
			".$limit."
	";
	
	//echo $query;
	return $myquery->multiple($query);
}

function wimax_operations_wrapup_stats_period_query($period,$date){
	switch($period){
		case 'year':
			$query .= "	cases.date_entered between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'month':
			$query .= "	cases.date_entered between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'day':
		default:
			$query .= "	cases.date_entered between '".$date." 00:00:00' and '".$date." 23:59:59' ";
			break;
	}
	return $query;
}

function wimax_audit_operations_wrapup_stats_period_query($period,$date){
	switch($period){
		case 'year':
			$query .= "	cases_audit.date_created between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'month':
			$query .= "	cases_audit.date_created between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'day':
		default:
			$query .= "	cases_audit.date_created between '".$date." 00:00:00' and '".$date." 23:59:59' ";
			break;
	}
	return $query;
}


function generate_wimax_cases_stats($date,$period,$columns,$limit=5){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	$getAlias = explode(" ",$columns[0]);
	$query = "
		SELECT
			".list_columns($columns).",
			count(".$getAlias[0].") as `count`
		FROM
			cases INNER JOIN cases_cstm ON cases.id = cases_cstm.id_c
		WHERE
			cases.deleted = '0' AND
			".wimax_operations_wrapup_stats_period_query($period,$date)."
		GROUP BY
			".$getAlias[2]."
		ORDER BY
			`count` DESC
		LIMIT
			".$limit."
	";
//	echo $query;
	return $myquery->multiple($query);
}

function prep_wimaxbackoffice_table_data($raw_stats,$total){
	foreach($raw_stats as $key=>$row){
		//WORKING ON TROUBLE TICKET DISTRIBUTION
		if($row[troubleticket]){
			$chopped = explode("_",$row[troubleticket]);
			$row['trouble ticket'] = wordwrap($chopped[1],50,"<br>"); $row['Count'] = $row['count'];
			 unset($row[troubleticket],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON CASE RESOLUTION RESOLUTION
		elseif($row[status] and (in_array('average_resolution_Hrs',array_keys($row)))){
			$row['%age'] = number_format($row['count']*100/$total,1);
			$row['count'] = number_format($row['count'],0);
			$row[average_resolution_Hrs] = number_format($row[average_resolution_Hrs],2);
		}//WORKING CASE RESOLUTION ROOT CAUSES
		elseif($row[root_causes]){
		//$chopped = explode("_",$row[root_causes]);
			$row['root cause'] = wordwrap($row[root_causes],50,"<br>"); 
			$row['Count'] = $row['count']; 
			unset($row[root_causes],$row['count']);
			$row['%age'] = number_format($row['Count']*100/$total,1);
		}else{
			//print_r($row); echo '........<br>';
			echo "uncatered for scenarion in back office opps ... <br>";
			//exit();
		}
		
		if(!$skip) $stats[] = array('data'=>$row);
		unset($raw_stats[$key],$key,$row,$skip);
	}
	return $stats;
}

function show_wimax_operations_summary($data){

	if($data == FALSE){return "NO CASE DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">CASE HANDLING - TROUBLE TICKETS (Top 5)</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	//CASE HANDLING - TROUBLE TICKETS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][troubletickets][day],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][yesterday_cases_created],0).' <br>
							Total Cases created last month on this date : '.number_format($data[bases][last_months_cases_created],0).' '
					);
	//print_r($table);
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][troubletickets][month],
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
				   'rows'=>$data[data_sets][troubletickets][year],
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
				   'rows'=>$data[data_sets][caseresolution][day],
				   'notes'=>'
				   			Total Cases Created: '.number_format($data[bases][yesterday_cases_created],0).' <br>
							Total Cases Handled: '.number_format($data[bases][yesterday_cases_handled],0).' <br>
							Total Cases Created Last month on this date : '.number_format($data[bases][last_months_cases_created],0).' <br>
							Total Cases Handled Last month on this date : '.number_format($data[bases][yesterday_cases_handled_last_month],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][caseresolution][month],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][months_cases_created],0).' <br>
							Total Cases handled: '.number_format($data[bases][month_cases_handled],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][caseresolution][year],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][years_cases_created],0).' <br>
				   			Total Cases handled: '.number_format($data[bases][year_cases_handled],0).' '
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
		<div class="category_head">CASE HANDLING - ROOT CAUSES (Top 5)</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//ROOT CAUSES
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][rootcauses][day],
				   'notes'=>'
				   			Total Cases created: '.number_format($data[bases][yesterday_cases_created],0).' <br>
							Total Cases created last month on this date : '.number_format($data[bases][last_months_cases_created],0).' '
					);
	//print_r($table);
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][rootcauses][month],
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
				   'rows'=>$data[data_sets][rootcauses][year],
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
		</div>';
		
	
	return $html;

}
?>