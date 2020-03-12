<?php

function generate_accounts_summary($date){
	if($date==''){ echo "date is ".$date."<br>"; return show_wimax_accounts_summary(FALSE);}
	$db_ref[db] = 'wimax';
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	
	$query = "SELECT count(*) as total_accounts from accounts where accounts.deleted =0 ";
	$accounts[base] = $myquery->single($query);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Accounts summary - Yesterday information ... \n\n";
	
	$query = "
			SELECT (SELECT count(*) from accounts where date_entered between '".$date." 00:00:00' and '".$date." 20:59:59' and deleted = 0) as Accounts_Created,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Active' and date_created between '".$date." 00:00:00' and '".$date." 20:59:59') as Newly_Activated_Accounts,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Churned' and date_created between '".$date." 00:00:00' and '".$date." 20:59:59') as Churned_Accounts";
	//echo $query;
	$accounts[yesterday] = $myquery->single($query);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Accounts summary - Month information ... \n\n";
	
	$query = "
			SELECT (
					SELECT count(*) from accounts where date_entered between '".substr($date,0,7)."-01 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Accounts_Created,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Active' and date_created between '".substr($date,0,7)."-01 00:00:00' and '".$date." 20:59:59') as Newly_Activated_Accounts,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Churned' and date_created between '".substr($date,0,7)."-01 00:00:00' and '".$date." 20:59:59') as Churned_Accounts";
	
	$accounts[month] = $myquery->single($query);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Accounts summary - Year information ... \n\n";
	
	$query = "
			SELECT (
					SELECT count(*) from accounts where date_entered between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 20:59:59' and deleted = 0) as Accounts_Created,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Active'	and date_created between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 20:59:59') as Newly_Activated_Accounts,
					(SELECT count(*) from cn_contracts_audit WHERE field_name = 'INITIALDATE_Churned'	and date_created between '".substr($date,0,7)."-01-01 00:00:00' and '".$date." 20:59:59') as Churned_Accounts";
	
	$accounts[year] = $myquery->single($query);
	//print_r($accounts);
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$accounts[bases][this_month] = $result[this_month];
	$accounts[bases][this_year] = substr($date,0,4);


	$accounts[totalaccounts] = $accounts[base][total_accounts];
	$accounts[accountsplatforms] = prep_accounts_platform_table_data(generate_accounts_by_platform(),$total = $accounts[totalaccounts]);
	$accounts[accountsqueues] = prep_accounts_platform_table_data(generate_accounts_by_queue(),$total = $accounts[totalaccounts]);
	$accounts[accountsbwstatus] = prep_accounts_platform_table_data(generate_accounts_by_bandwidth_status(),$total = $accounts[totalaccounts]);
	$accounts[accountsequipmentstatus] = prep_accounts_platform_table_data(generate_accounts_by_equipment_status(),$total = $accounts[totalaccounts]);
	$accounts[accountsdregstatus] = prep_accounts_platform_table_data(generate_accounts_by_dreg_status(),$total = $accounts[totalaccounts]);
	$accounts[accountsdhosstatus] = prep_accounts_platform_table_data(generate_accounts_by_dhos_status(),$total = $accounts[totalaccounts]);
	$accounts[accountsmhosstatus] = prep_accounts_platform_table_data(generate_accounts_by_mhos_status(),$total = $accounts[totalaccounts]);
	$accounts[accountswhosstatus] = prep_accounts_platform_table_data(generate_accounts_by_whos_status(),$total = $accounts[totalaccounts]);
	//echo 'total accounts '.$accounts[totalaccounts];
	//print_r($accounts[accountsbwstatus]);
	return show_wimax_accounts_summary($accounts);
}


function prep_accounts_platform_table_data($raw_stats,$total){
	
	$no_of_rows = count($raw_stats);
	$next_row_key = $no_of_rows;
	
	foreach($raw_stats as $key=>$row){
		unset($skip);
		$row_key_list = array_keys($row);
		//print_r($row_key_list);
			if(in_array('platform',$row_key_list)){
				if(trim($row[platform]) == '') { $skip = TRUE; }
				$row['Platform'] = $row[platform];
				$row['number_of_accounts'] = $row['counts']; 
				unset($row['counts'],$row[platform]);
				$row['%age'] = number_format($row['number_of_accounts']*100/$total,1);
				$row['number_of_accounts'] = number_format($row['number_of_accounts'],0);
				}
			elseif(in_array('queue',$row_key_list)){
				if(trim($row[queue]) == '') { $skip = TRUE; }
				$row['Queue'] = $row[queue];
				$row['number_of_accounts'] = $row['counts']; 
				unset($row['counts'],$row[queue]);
				$row['%age'] = number_format($row['number_of_accounts']*100/$total,1);
				$row['number_of_accounts'] = number_format($row['number_of_accounts'],0);
				}
			elseif(in_array('bandwidth_package',$row_key_list)){
				if(trim($row[bandwidth_package]) == '') { $skip = TRUE; }
				$row['Bandwidth Package'] = $row[bandwidth_package];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract]; 
				$row['TOTAL'] = (int)$row[active]+(int)$row[inactive]+(int)$row[dummy]+(int)$row[trial]+(int)$row[trial]+(int)$row[no_Contract];
				
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				unset($row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract],$row[bandwidth_package]);
			}
			elseif(in_array('cpe_type',$row_key_list)){
				if(trim($row[cpe_type]) == '') { $skip = TRUE; }
				$row['Equipment'] = $row[cpe_type];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract]; 
				$row['TOTAL'] =  $row[active]+$row[inactive]+$row[dummy]+$row[trial]+$row[trial]+$row[no_Contract];
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				unset($row[cpe_type],$row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract]);
			}
			elseif(in_array('dreg',$row_key_list)){
				if(trim($row[dreg]) == '') { $skip = TRUE; }
				$row['DREG Package'] = $row[dreg];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract];
				$row['TOTAL'] =  $row[active]+$row[inactive]+$row[dummy]+$row[trial]+$row[trial]+$row[no_Contract];
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				
				unset($row[dreg],$row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract]);
			}
			elseif(in_array('dhos',$row_key_list)){
				if(trim($row[dhos]) == '') { $skip = TRUE; }
				$row['DHOS Package'] = $row[dhos];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract];
				$row['TOTAL'] =  $row[active]+$row[inactive]+$row[dummy]+$row[trial]+$row[trial]+$row[no_Contract];
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				unset($row[dhos],$row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract]);
			}
			elseif(in_array('mhos',$row_key_list)){
				if(trim($row[mhos]) == '') { $skip = TRUE; }
				$row['MHOS Package'] = $row[mhos];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract];
				$row['TOTAL'] =  $row[active]+$row[inactive]+$row[dummy]+$row[trial]+$row[trial]+$row[no_Contract];
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				unset($row[mhos],$row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract]);
			}
			elseif(in_array('whos',$row_key_list)){
				if(trim($row[whos]) == '') { $skip = TRUE; }
				$row['WHOS Package'] = $row[whos];
				$row['Active'] = (int)$row[active]; 
				$row['Inactive'] = (int)$row[inactive]; 
				$row['Dummy'] = (int)$row[dummy]; 
				$row['Trial'] = (int)$row[trial]; 
				$row['No Contract'] = (int)$row[no_Contract];
				$row['TOTAL'] =  $row[active]+$row[inactive]+$row[dummy]+$row[trial]+$row[trial]+$row[no_Contract];
				//Summing up the column values to form a totals row
				if($key < $next_row_key){
					$raw_stats[$next_row_key][bandwidth_package] = 'TOTALS';
					 
					$raw_stats[$next_row_key][active] += $row[active];
					$raw_stats[$next_row_key][inactive] += $row[inactive];
					$raw_stats[$next_row_key][dummy] += $row[dummy];
					$raw_stats[$next_row_key][trial] += $row[trial];
					$raw_stats[$next_row_key][no_Contract] += $row[no_Contract];
					$raw_stats[$next_row_key][Total] += $row[TOTAL];
				}
				unset($row[whos],$row[active],$row[inactive],$row[dummy],$row[trial],$row[no_Contract]);
			}
			else{ echo "uncatered for scenario in unconverted accounts opps ... <br>"; }
		
		if(!$skip) {
			$stats[] = array('data'=>$row);
		}
		unset($raw_stats[$key],$key,$row);	
	}
	//Adding the totals Row if it was populated
	if(is_array($raw_stats[$next_row_key])){
		$stats[] = array('data'=>$raw_stats[$next_row_key]);		
	}
	
	//print_r($stats);
	return $stats;
}

function generate_accounts_by_queue(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			qs_queues.name as queue,
			count(qs_queues.name) as counts
		FROM
			qs_queues
			Inner Join qs_queues_accounts_c ON qs_queues.id = qs_queues_accounts_c.qs_queues_asqs_queues_ida
			Inner Join accounts ON qs_queues_accounts_c.qs_queues_atsaccounts_idb = accounts.id
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c 
		WHERE
			accounts.deleted = 0 and qs_queues_accounts_c.deleted = 0
		GROUP BY
			queue
		ORDER BY 
			counts DESC
	
	";
	//echo $query;
	return $myquery->multiple($query);
}

function generate_accounts_by_platform(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.platform_c as platform,
			count(accounts_cstm.platform_c) as counts
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join cn_contracts ON accounts_cstm.id_c = cn_contracts.account where
			cn_contracts.status = 'Active'  and accounts.deleted = 0
		GROUP BY
			platform 
		ORDER BY 
			counts DESC
	";
	return $myquery->multiple($query);
}

function generate_accounts_by_bandwidth_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.download_bandwidth_c as bandwidth_package,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1 else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join cn_contracts ON accounts_cstm.id_c = cn_contracts.account
		WHERE
			accounts.deleted = 0
		GROUP BY
			bandwidth_package 
		ORDER BY 
			active DESC
	";
	
	return $myquery->multiple($query);
}

function generate_accounts_by_equipment_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.cpe_type_c as cpe_type,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1 else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c = accounts_cn_contracts_c.accounts_cntsaccounts_ida
			Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
		WHERE 
			accounts.deleted = 0 and
			accounts_cn_contracts_c.deleted=0 
		GROUP BY 
			cpe_type
	";
	return $myquery->multiple($query);
}


function generate_accounts_by_dreg_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.package_domain_registration_c as dreg,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1 else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c = accounts_cn_contracts_c.accounts_cntsaccounts_ida
			Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
		WHERE
			accounts.deleted = 0 and
			accounts_cn_contracts_c.deleted=0 and
			accounts_cstm.package_domain_registration_c != ''
		GROUP BY 
			dreg
	";
	
	return $myquery->multiple($query);
}

function generate_accounts_by_dhos_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.package_web_hosting_c as dhos,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1  else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c= accounts_cn_contracts_c.accounts_cntsaccounts_ida
			Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
		WHERE
			accounts.deleted = 0 and 
			accounts_cn_contracts_c.deleted=0 and 
			accounts_cstm.package_web_hosting_c != ''
		GROUP BY
			dhos
	";
	
	return $myquery->multiple($query);
}

function generate_accounts_by_mhos_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.package_mail_hosting_c as mhos,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1  else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c= accounts_cn_contracts_c.accounts_cntsaccounts_ida
			Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
		WHERE
			accounts.deleted = 0 and
			accounts_cn_contracts_c.deleted=0 and
			accounts_cstm.package_mail_hosting_c != ''
		GROUP BY
			mhos
	";
	
	return $myquery->multiple($query);
}


function generate_accounts_by_whos_status(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			accounts_cstm.package_web_hosting_c as whos,
			count(case when cn_contracts.status = 'Active' then 1 else null end) as active,
			count(case when cn_contracts.status = 'Inactive' then 1  else null end) as inactive,
			count(case when cn_contracts.status = 'Dummy' then 1 else null end) as dummy,
			count(case when cn_contracts.status = 'Trial' then 1 else null end) as trial,
			count(case when cn_contracts.status = '' then 1 else null end) as no_Contract
		FROM
			accounts
			Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
			Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c= accounts_cn_contracts_c.accounts_cntsaccounts_ida
			Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
			Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
		WHERE
			accounts.deleted = 0 and 
			accounts_cn_contracts_c.deleted=0 and 
			accounts_cstm.package_web_hosting_c != ''
		GROUP BY
			whos
	";
	
	return $myquery->multiple($query);
}

function show_wimax_accounts_summary($data){
	
	if($data == FALSE){return "NO ACCOUNT DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	

	//Day
	$table = array(
				   'title'=>'Yesterday :'.$_REQUEST[use_date],
				   'rows'=>$data[yesterday],
				   'notes'=>'
				   			Total Number of Accounts: '.number_format($data[totalaccounts],0).' <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>';
		
	//MONTH
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[month],
				   'notes'=>'
				   			Total Number of Accounts: '.number_format($data[totalaccounts],0).' <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>';
	
	//YEAR
	$table = array(
				   'title'=>'Year of '.$data[bases][this_year],
				   'rows'=>$data[year],
				   'notes'=>'
				   			Total Number of Accounts: '.number_format($data[totalaccounts],0).' <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
		</tr>
			</table>
		</div>
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY PLATFORM</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsplatforms],
				   'notes'=>'Total number of Accounts : '.number_format($data[totalaccounts],0).'  <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
		
		
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY QUEUE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsqueues],
				   'notes'=>'Total number of Accounts : '.number_format($data[totalaccounts],0).'  <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY BANDWIDTH AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsbwstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY EQUIPMENT AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsequipmentstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY DOMAIN REGISTRATION AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsdregstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY DOMAIN HOSTING AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsdhosstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY MAIL HOSTING AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountsmhosstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table= '',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">NO OF ACCOUNTS BY WEB HOSTING AND STATUS</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[accountswhosstatus]
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table= '',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	';
	
	return $html;
}
?>