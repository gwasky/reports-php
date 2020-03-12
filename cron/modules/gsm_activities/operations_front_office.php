<?php

function generate_front_office_summary($date){
	$myquery = new custom_query();
	$db_ref[db] = 'fci';
	
	custom_query::select_db($db_ref[db]);
	
	$query = "
		select
			(select count(*) from simswaps where datecreated between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_simswaps,
			(select count(*) from simswaps where datecreated between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_simswaps,
			(select count(*) from simswaps where datecreated between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_simswaps,
			(select count(*) from simswaps where datecreated between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_simswaps,
			
			(select count(*) from scratchcards where datecreated between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_scratchcards,
			(select count(*) from scratchcards where datecreated between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_scratchcards,
			(select count(*) from scratchcards where datecreated between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_scratchcards,
			(select count(*) from scratchcards where datecreated between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_scratchcards,
			
			(select count(*) from subscriber_message where datecreated between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_complaints,
			(select count(*) from subscriber_message where datecreated between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_complaints,
			(select count(*) from subscriber_message where datecreated between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_complaints,
			(select count(*) from subscriber_message where datecreated between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_complaints,
			
			(select count(*) from unblockrequests where datecreated between '".$date." 00:00:00' and '".$date." 23:59:59') as yesterday_unblockrequests,
			(select count(*) from unblockrequests where datecreated between date_sub('".$date." 00:00:00', interval 1 month) and date_sub('".$date." 23:59:59', interval 1 month)) as last_months_unblockrequests,
			(select count(*) from unblockrequests where datecreated between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59') as months_unblockrequests,
			(select count(*) from unblockrequests where datecreated between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59') as years_unblockrequests
	";
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - base information ... \n";
	
	$cs_ops[bases] = $myquery->single($query);
	
	$query = "
		select
			(SELECT CAST(((sum(bc_traffic.traffic_in) + sum(bc_traffic.traffic_out))/2) AS UNSIGNED) AS average_traffic FROM bc_traffic WHERE bc_traffic.traffic_date_start BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59') AS yesterday_bcwalkins,
			(SELECT CAST(((sum(bc_traffic.traffic_in) + sum(bc_traffic.traffic_out))/2) AS UNSIGNED) AS average_traffic FROM bc_traffic WHERE bc_traffic.traffic_date_start BETWEEN date_sub('".$date." 00:00:00', interval 1 month) AND date_sub('".$date." 23:59:59', interval 1 month)) AS last_months_bcwalkins,
			(SELECT CAST(((sum(bc_traffic.traffic_in) + sum(bc_traffic.traffic_out))/2) AS UNSIGNED) AS average_traffic FROM bc_traffic WHERE bc_traffic.traffic_date_start BETWEEN '".substr($date,0,7)."-01 00:00:00' AND '".$date." 23:59:59') AS months_bcwalkins,
			(SELECT CAST(((sum(bc_traffic.traffic_in) + sum(bc_traffic.traffic_out))/2) AS UNSIGNED) AS average_traffic FROM bc_traffic WHERE bc_traffic.traffic_date_start BETWEEN '".substr($date,0,4)."-01-01 00:00:00' AND '".$date." 23:59:59') AS years_bcwalkins
	";
	$result = $myquery->single($query,'ccba02.businesssales');
	foreach($result as $key=>$value){
		$cs_ops[bases][$key] = $value;
	}
	unset($key,$value);
	
	
	$cs_ops[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$cs_ops[bases][this_month] = $result[this_month];
	$cs_ops[bases][this_year] = substr($date,0,4);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - Walkins by Warid Business Centres ... \n";
	
	//WALKINS BY BUSINESS CENTRES
	$cs_ops[data_sets][bc_walkins][day] = prep_frontoffice_table_data(
												$raw_stats = generate_bc_walkin_stats($date,$period='day'),
												$total = $cs_ops[bases][yesterday_bcwalkins]
											);
	
	$cs_ops[data_sets][bc_walkins][month] = prep_frontoffice_table_data(
												$raw_stats = generate_bc_walkin_stats($date,$period='month'),
												$total = $cs_ops[bases][months_bcwalkins]
											);
	
	$cs_ops[data_sets][bc_walkins][year] = prep_frontoffice_table_data(
												$raw_stats = generate_bc_walkin_stats($date,$period='year'),
												$total = $cs_ops[bases][years_bcwalkins]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - Simswaps by franchise ... \n";
	
	//Simswaps by Franhise
	$db_ref[table] = 'simswaps inner join franchises on simswaps.franchiseid = franchises.franchiseid ';
	$cs_ops[data_sets][franchise_simswaps][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('franchises.name'),$db_ref,$date_col='simswaps.datecreated'),
												$total = $cs_ops[bases][yesterday_simswaps]
											);
	
	$cs_ops[data_sets][franchise_simswaps][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('franchises.name'),$db_ref,$date_col='simswaps.datecreated'),
												$total = $cs_ops[bases][months_simswaps]
											);
	
	$cs_ops[data_sets][franchise_simswaps][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('franchises.name'),$db_ref,$date_col='simswaps.datecreated'),
												$total = $cs_ops[bases][years_simswaps]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - Simswap reasons ... \n";
	
	//Simswaps by reasons
	$db_ref[table] = 'simswaps';
	$cs_ops[data_sets][simswap_reasons][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('swapreason'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][yesterday_simswaps]
											);
	
	$cs_ops[data_sets][simswap_reasons][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('swapreason'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][months_simswaps]
											);
	
	$cs_ops[data_sets][simswap_reasons][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('swapreason'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][years_simswaps]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - badly scratched cards by franchise ... \n";
	
	//badly scratched cards by franchise
	$db_ref[table] = ' scratchcards inner join franchises on scratchcards.franchiseid = franchises.franchiseid ';
	$cs_ops[data_sets][franchise_scratchcards][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('franchises.name'),$db_ref,$date_col='scratchcards.datecreated'),
												$total = $cs_ops[bases][yesterday_scratchcards]
											);
	
	$cs_ops[data_sets][franchise_scratchcards][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('franchises.name'),$db_ref,$date_col='scratchcards.datecreated'),
												$total = $cs_ops[bases][months_scratchcards]
											);
	
	$cs_ops[data_sets][franchise_scratchcards][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('franchises.name'),$db_ref,$date_col='scratchcards.datecreated'),
												$total = $cs_ops[bases][years_scratchcards]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - badly scratched cards by status ... \n";
	
	//Badly scratched cards by status
	$db_ref[table] = 'scratchcards';
	$cs_ops[data_sets][scratchcards_status][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][yesterday_scratchcards]
											);
	
	$cs_ops[data_sets][scratchcards_status][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][months_scratchcards]
											);
	
	$cs_ops[data_sets][scratchcards_status][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][years_scratchcards]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - complaints by Franchise ... \n";
	
	//Complaints by franchise
	$db_ref[table] = 'subscriber_message';
	$cs_ops[data_sets][franchise_complaints][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('created_by'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][yesterday_complaints]
											);
	
	$cs_ops[data_sets][franchise_complaints][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('created_by'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][months_complaints]
											);
	
	$cs_ops[data_sets][franchise_complaints][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('created_by'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][years_complaints]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - complaints by status... \n";
	
	//Complaints by status
	$db_ref[table] = 'subscriber_message';
	$cs_ops[data_sets][complaints_status][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][yesterday_complaints]
											);
	
	$cs_ops[data_sets][complaints_status][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][months_complaints]
											);
	
	$cs_ops[data_sets][complaints_status][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][years_complaints]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - Unblock requests by Franchise ... \n";
	
	//Unblock requests by franchise
	$db_ref[table] = ' unblockrequests inner join franchises on unblockrequests.franchiseid = franchises.franchiseid ';
	$cs_ops[data_sets][franchise_unblockrequests][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('franchises.name'),$db_ref,$date_col='unblockrequests.datecreated'),
												$total = $cs_ops[bases][yesterday_unblockrequests]
											);
	
	$cs_ops[data_sets][franchise_unblockrequests][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('franchises.name'),$db_ref,$date_col='unblockrequests.datecreated'),
												$total = $cs_ops[bases][months_unblockrequests]
											);
	
	$cs_ops[data_sets][franchise_unblockrequests][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('franchises.name'),$db_ref,$date_col='unblockrequests.datecreated'),
												$total = $cs_ops[bases][years_unblockrequests]
											);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating front office summary - Unblock requests by status... \n";
	
	//Unblock requests by status
	$db_ref[table] = 'unblockrequests';
	$cs_ops[data_sets][unblockrequests_status][day] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='day',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][yesterday_unblockrequests]
											);
	
	$cs_ops[data_sets][unblockrequests_status][month] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='month',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][months_unblockrequests]
											);
	
	$cs_ops[data_sets][unblockrequests_status][year] = prep_frontoffice_table_data(
												$raw_stats = generate_generic_stats($date,$period='year',$columns=array('status'),$db_ref,$date_col='datecreated'),
												$total = $cs_ops[bases][years_unblockrequests]
											);
	
	return show_front_office_summary($cs_ops);
}

function prep_frontoffice_table_data($raw_stats,$total){
	
	foreach($raw_stats as $key=>$row){
		unset($skip);

		$row_key_list = array_keys($row);
		
		//WORKING ON FRANCHISE DISTRIBUTION FOR SIMSWAPS, SCRATCH CARDS AND UNBLOCK REQUESTS
		if($row[name]){
			$row[Franchise] = ucwords(strtolower(wordwrap(str_replace("- "," ",$row[name]),50,"<br>"))); $row['Count'] = $row['count']; unset($row['count'],$row[name]);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON SIMSWAP REASONS
		elseif(in_array('swapreason',$row_key_list)){
			if(trim($row[swapreason]) == '') $skip = TRUE;
			$row['Swap reason'] = $row[swapreason]; $row['Count'] = $row['count']; unset($row['count'],$row[swapreason]);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON SCRATCH CARD, COMPLAINTS AND UNBLOCK REQUESTS STATUS
		elseif($row[status]){
			$row['%age'] = number_format($row['count']*100/$total,1);
			$row['count'] = number_format($row['count'],0);
		}//WORKING ON COMPLAINTS BY FRANCHISE
		elseif($row[created_by]){
			$row[Franchise] = ucwords(strtolower(wordwrap(str_replace("- "," ",$row[created_by]),50,"<br>"))); $row['Count'] = $row['count']; unset($row['count'],$row[created_by]);
			$row['%age'] = number_format($row['Count']*100/$total,1);
			$row['Count'] = number_format($row['Count'],0);
		}//WORKING ON BUSINESS CENTRE WALKINS
		elseif($row['business_centre']){
			$row['Count'] = number_format($row['average_traffic'],0);
			$row['%age'] = number_format($row['average_traffic']*100/$total,1);
			unset($row['average_traffic']);
		}else{
			echo "uncatered for scenario in front office opps ... <br>";
		}
		
		//to skip un necessary rows
		if(!$skip) { $stats[] = array('data'=>$row); }
		unset($raw_stats[$key],$key,$row);
	}
	
	return $stats;
}

function generate_bc_walkin_stats($date,$period,$limit=5){
	$myquery = new custom_query();
	custom_query::select_db('ccba02.businesssales');
	
	$query = "
		SELECT
			bc_names.`name` AS business_centre,
			CAST(((sum(bc_traffic.traffic_in) + sum(bc_traffic.traffic_out))/2) AS UNSIGNED) AS average_traffic
		FROM
			bc_names
			INNER JOIN bc_cameras ON bc_cameras.bc_name_id = bc_names.id
			INNER JOIN bc_traffic ON bc_traffic.camera_id = bc_cameras.id
		WHERE
			".generic_period_query($period,$date,$column='bc_traffic.traffic_date_start')."
		GROUP BY
			business_centre
		ORDER BY
			average_traffic DESC
		LIMIT
			".$limit."
	";
	
	return $myquery->multiple($query);
}

function show_front_office_summary($data){
	
	if($data == FALSE){return "NO CASE DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">WALKINS - BY WARID BUSINESS CENTRE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//WALKINS BY BUSINESS CENTRES
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][bc_walkins][day],
				   'notes'=>'
				   			Total Walkins : '.number_format($data[bases][yesterday_bcwalkins],0).' <br>
							Total Walkins last month on this date : '.number_format($data[bases][last_months_bcwalkins],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][bc_walkins][month],
				   'notes'=>'
				   			Total Walkins : '.number_format($data[bases][months_bcwalkins],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][bc_walkins][year],
				   'notes'=>'
				   			Total Walkins : '.number_format($data[bases][years_bcwalkins],0).' '
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
		<div class="category_head">SIMSWAPS - BY FRANCHISE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SIMSWAPS BY FRANCHISE
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][franchise_simswaps][day],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][yesterday_simswaps],0).' <br>
							Total requests last month on this date : '.number_format($data[bases][last_months_simswaps],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][franchise_simswaps][month],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][months_simswaps],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][franchise_simswaps][year],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][years_simswaps],0).' '
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
		<div class="category_head">SIMSWAPS - REPORTS BY REASON</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SIMSWAPS BY SWAP REASON
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][simswap_reasons][day],
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
				   'rows'=>$data[data_sets][simswap_reasons][month],
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
				   'rows'=>$data[data_sets][simswap_reasons][year],
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
		<div class="category_head">BADLY SCRATCHCARDS - BY FRANCHISE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SCRATCH CARDS BY FRANCHISE
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][franchise_scratchcards][day],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][yesterday_scratchcards],0).' <br>
							Total requests last month on this date : '.number_format($data[bases][last_months_scratchcards],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][franchise_scratchcards][month],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][months_scratchcards],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][franchise_scratchcards][year],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][years_scratchcards],0).' '
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
		<div class="category_head">BADLY SCRATCHCARDS - REPORTS BY STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//SCRATCH CARDS BY STATUS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][scratchcards_status][day],
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
				   'rows'=>$data[data_sets][scratchcards_status][month],
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
				   'rows'=>$data[data_sets][scratchcards_status][year],
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
		<div class="category_head">COMPLAINTS - BY FRANCHISE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//COMPLAINTS BY FRANCHISE
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][franchise_complaints][day],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][yesterday_complaints],0).' <br>
							Total requests last month on this date : '.number_format($data[bases][last_months_complaints],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][franchise_complaints][month],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][months_complaints],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][franchise_complaints][year],
				   'notes'=>'
				   			Total requests : '.number_format($data[bases][years_complaints],0).' '
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
		<div class="category_head">COMPLAINTS - BY STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//COMPLAINTS BY STATUS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][complaints_status][day],
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
				   'rows'=>$data[data_sets][complaints_status][month],
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
				   'rows'=>$data[data_sets][complaints_status][year],
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
		<div class="category_head">UNBLOCK REQUESTS BY FRANCHISE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//UNBLOCK REQUESTS BY FRANCHISE
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][franchise_unblockrequests][day],
				   'notes'=>'
				   			Total requests created : '.number_format($data[bases][yesterday_unblockrequests],0).' <br>
							Total requests created last month on this date : '.number_format($data[bases][last_months_unblockrequests],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Month
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[data_sets][franchise_unblockrequests][month],
				   'notes'=>'
				   			Total requests created : '.number_format($data[bases][months_unblockrequests],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top" id="data_td">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
	';
	
	//Year
	$table = array(
				   'title'=>'Year '.$data[bases][this_year],
				   'rows'=>$data[data_sets][franchise_unblockrequests][year],
				   'notes'=>'
				   			Total requests created : '.number_format($data[bases][years_unblockrequests],0).' '
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
		<div class="category_head">UNBLOCK REQUESTS BY STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//UNBLOCK REQUESTS BY STATUS
	//Day
	$table = array(
				   'title'=>'On '.$_REQUEST[use_date],
				   'rows'=>$data[data_sets][unblockrequests_status][day],
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
				   'rows'=>$data[data_sets][unblockrequests_status][month],
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
				   'rows'=>$data[data_sets][unblockrequests_status][year],
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
?>