<?
function generate_enterprise_sales_report($from,$to,$reporttype){
	
	if(!$from) { $from = date("Y-m-")."01"; }
	if(!$to) { $to = date("Y-m-d", strtotime("-1 days")); }
	
	$_POST[from] = $from;
	$_POST[to] = $to;
	
	custom_query::select_db('wimax');
	$myquery = new custom_query();

	$query = "
		SELECT
			DISTINCT
			leads.id,
			leads.salutation,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as lead_queue,
			leads_cstm.platform_c,
			leads.`status`,
			leads.account_id,
			LEFT(date_add(leads.date_entered, interval 3 hour),10) AS lead_entered_date,
			leads_cstm.sales_rep_c as sales_rep,
			leads_cstm.download_bandwidth_c as lead_bandwidth,
			ps_products.price as lead_product_price,
			sv_sitesurvey.id AS site_survey_id,
			LEFT(date_add(sv_sitesurvey.date_entered, interval 3 hour),10) AS survey_entered_date,
			sv_sitesurvey.site_survey_status,
			sv_sitesurvey.cpe_type,
			LEFT(date_add(leads_audit.date_created, interval 3 hour),10) AS lead_converted_date
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.download_bandwidth_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues.id=qs_queues_leads_c.qs_queues_lsqs_queues_ida)
			LEFT OUTER JOIN leads_audit ON (leads_audit.parent_id = leads.id AND leads_audit.field_name = 'status' AND leads_audit.after_value_string = 'Converted' AND leads_audit.before_value_string != 'Converted' )
			LEFT OUTER JOIN leads_sv_sitesurvey_c ON ( leads.id = leads_sv_sitesurvey_c.leads_sv_siurveyleads_ida )
			LEFT OUTER JOIN sv_sitesurvey ON ( leads_sv_sitesurvey_c.leads_sv_sisitesurvey_idb = sv_sitesurvey.id )
			LEFT OUTER JOIN sv_sitesurvey_cstm ON (sv_sitesurvey.id = sv_sitesurvey_cstm.id_c)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND
			(leads_cstm.shared_packages_c = '' or leads_cstm.shared_packages_c != '') AND
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL) AND
			(sv_sitesurvey.deleted = '0' OR sv_sitesurvey.deleted IS NULL ) AND
			(
			 	(leads.date_entered BETWEEN date_add('".$from." 00:00:00', interval -3 hour) AND date_add('".$to." 23:59:59', interval -3 hour)) OR
				(leads_audit.date_created BETWEEN date_add('".$from." 00:00:00', interval -3 hour) AND date_add('".$to." 23:59:59', interval -3 hour)) OR
				(sv_sitesurvey.date_entered BETWEEN date_add('".$from." 00:00:00', interval -3 hour) AND date_add('".$to." 23:59:59', interval -3 hour))
			)
	";

	//echo nl2br($query)."<hr><br>";

	$data[leads] = $myquery->multiple($query);
	
	$query = "
		SELECT
			accounts.id,
			accounts.name,
			accounts_cstm.crn_c as account_no,
			accounts_cstm.mem_id_c as parent_no,
			LEFT(date_add(accounts.date_entered, interval 3 hour),10) AS date_entered,
			accounts_cstm.platform_c,
			accounts_cstm.download_bandwidth_c as bandwidth,
			accounts_cstm.sales_rep_c as sales_rep,
			cn_contracts.status AS bw_contract_status,
			cn_contracts_audit.after_value_string AS first_activation_date,
			LEFT(date_add(leads.date_entered, interval 3 hour),10) AS lead_created_date
		FROM
			accounts
			INNER JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
			LEFT OUTER JOIN cn_contracts ON accounts.id = cn_contracts.account
			LEFT OUTER JOIN cn_contracts_audit ON (cn_contracts.id = cn_contracts_audit.parent_id AND cn_contracts_audit.field_name = 'INITIALDATE_Active')
			LEFT OUTER JOIN leads ON (accounts.id= leads.account_id)
		WHERE
			accounts.deleted = '0' AND
			(cn_contracts.deleted = '0' OR cn_contracts.deleted IS NULL) AND
			(leads.deleted = '0' OR leads.deleted IS NULL) AND
			(
				accounts.date_entered BETWEEN date_add('".$from." 00:00:00', interval -3 hour) AND date_add('".$to." 23:59:59', interval -3 hour) OR
				cn_contracts_audit.after_value_string BETWEEN '".$from."' AND '".$to."'
			)
	";
	
	$data[accounts] = $myquery->multiple($query);
	
	//echo nl2br($query)."<hr><br>";
	
	foreach($data as $group=>$group_data){
		foreach($group_data as $row_id=>$row){
			if($group == 'leads'){
				
				//LEADS CREATED
				if($from <= $row[lead_entered_date] and $row[lead_entered_date] <= $to){
					$detail[leads_created][$row[id]][date_entered] = $row[lead_entered_date];
					$detail[leads_created][$row[id]][name] = $row[salutation]." ".ucfirst(strtolower($row[first_name]))." ".ucfirst(strtolower($row[last_name]))." [".strtoupper($row[db_lead_name])."]";
					$detail[leads_created][$row[id]][queue] = $row[lead_queue];
					$detail[leads_created][$row[id]][status] = $row[status];
					$detail[leads_created][$row[id]][lead_bandwidth] = $row[lead_bandwidth];
					$detail[leads_created][$row[id]][sales_rep] = $row[sales_rep];
					if($row[status] == 'Converted'){
						$detail[leads_created][$row[id]][interval] = date_diff_days($row[lead_entered_date],$row[lead_converted_date]);
					}else{
						$detail[leads_created][$row[id]][interval] = date_diff_days($row[lead_entered_date],date('Y-m-d'));
					}
				}
				
				//SITE SURVEYS CREATED
				if($row[survey_entered_date] != '' and $from <= $row[survey_entered_date] and $row[survey_entered_date] <= $to){
					$detail[site_surveys_created][$row[site_survey_id]][name] = $row[salutation]." ".ucfirst(strtolower($row[first_name]))." ".ucfirst(strtolower($row[last_name]))." [".strtoupper($row[db_lead_name])."]";
					$detail[site_surveys_created][$row[site_survey_id]][date_entered] = $row[survey_entered_date];
					$detail[site_surveys_created][$row[site_survey_id]][status] = $row[site_survey_status];
					$detail[site_surveys_created][$row[site_survey_id]][sales_rep] = $row[sales_rep];
					if($row[site_survey_status] == 'Approved'){
						$detail[site_surveys_created][$row[site_survey_id]][interval] = date_diff_days($row[lead_entered_date],$row[survey_entered_date]);
					}else{
						$detail[site_surveys_created][$row[site_survey_id]][interval] = date_diff_days($row[lead_entered_date],date('Y-m-d'));
					}
				}
				
				//LEADS CONVERTED
				if($row[lead_converted_date] != '' and $from <= $row[lead_converted_date] and $row[lead_converted_date] <= $to){
					$detail[leads_converted][$row[id]][lead_converted_date] = $row[lead_converted_date];
					$detail[leads_converted][$row[id]][name] = $row[salutation]." ".$row[first_name]." ".$row[last_name]." [".$row[db_lead_name]."]";
					$detail[leads_converted][$row[id]][queue] = $row[lead_queue];
					$detail[leads_converted][$row[id]][status] = $row[status];
					$detail[leads_converted][$row[id]][lead_bandwidth] = $row[lead_bandwidth];
					$detail[leads_converted][$row[id]][sales_rep] = $row[sales_rep];
					$detail[leads_converted][$row[id]][interval] = date_diff_days($row[lead_entered_date],$row[lead_converted_date]);
				}
			}elseif($group == 'accounts'){
				//ACCOUNTS CREATED
				if($from <= $row[date_entered] and $row[date_entered] <= $to){
					$detail[accounts_created][$row[id]][date_entered] = $row[date_entered];
					$detail[accounts_created][$row[id]][parent_no] = $row[parent_no];
					$detail[accounts_created][$row[id]][account_no] = $row[account_no];
					$detail[accounts_created][$row[id]][name] = $row[name];
					$detail[accounts_created][$row[id]][sales_rep] = $row[sales_rep];
					$detail[accounts_created][$row[id]][bandwidth_contract_status] = $row[bandwidth];
					if($row[lead_created_date] != ''){
						$detail[accounts_created][$row[id]][interval] = date_diff_days($row[lead_created_date],$row[date_entered]);
					}else{
						$detail[accounts_created][$row[id]][interval] = 'NO LEAD';
					}
				}
				
				//ACCOUNTS ACTIVATED
				if($row[first_activation_date] != '' and $from <= $row[first_activation_date] and $row[first_activation_date] <= $to){
					$detail[accounts_activated][$row[id]][first_activation_date] = $row[first_activation_date];
					$detail[accounts_activated][$row[id]][parent_no] = $row[parent_no];
					$detail[accounts_activated][$row[id]][account_no] = $row[account_no];
					$detail[accounts_activated][$row[id]][name] = $row[name];
					$detail[accounts_activated][$row[id]][sales_rep] = $row[sales_rep];
					$detail[accounts_activated][$row[id]][bandwidth_contract_status] = $row[bandwidth];
					if($row[lead_created_date] != ''){
						$detail[accounts_activated][$row[id]][interval] = date_diff_days($row[lead_created_date],$row[first_activation_date]);
					}else{
						$detail[accounts_activated][$row[id]][interval] = 'NO LEAD';
					}
				}
			}
			
			unset($group_data[$row_id],$row_id,$row);
		}
		
		unset($data[$group],$group,$group_data);
	}
	
	$report[links][leads_created] = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Leads&return_module=Leads&action=DetailView&record=';
	$report[links][site_surveys_created] = 'http://wimaxcrm.waridtel.co.ug/index.php?module=SV_SiteSurvey&return_module=SV_SiteSurvey&action=DetailView&record=';
	$report[links][leads_converted] = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Leads&return_module=Leads&action=DetailView&record=';
	$report[links][accounts_created] = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&return_module=Accounts&action=DetailView&record=';
	$report[links][accounts_activated] = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&return_module=Accounts&action=DetailView&record=';
	
	//print_r($detail);
	
	function summarise($detail){
		
		$date_keys[leads_created][month_date] = 'date_entered';
		$date_keys[site_surveys_created][month_date] = 'date_entered';
		$date_keys[leads_converted][month_date] = 'lead_converted_date';
		$date_keys[accounts_created][month_date] = 'date_entered';
		$date_keys[accounts_activated][month_date] = 'first_activation_date';
		
		foreach($detail as $report=>$report_data){
			foreach($report_data as $row){
				$months[str_replace(array("-"),'',substr($row[$date_keys[$report][month_date]],0,7))] = substr($row[$date_keys[$report][month_date]],0,7);
				$month = substr($row[$date_keys[$report][month_date]],0,7); 
				
				$data[$report][$row[sales_rep]][$month][qty] += 1;
				$scrap[$report][$row[sales_rep]][$month][interval] += $row[interval];
				$data[$report][$row[sales_rep]][$month][avg_interval] = $scrap[$report][$row[sales_rep]][$month][interval]/$data[$report][$row[sales_rep]][$month][qty];
			}
		}
		
		$summary[data] = $data;
		asort($months);
		$summary[months] = $months;
		$summary[months_titles] = array(
										'qty',
										/*'avg_interval'*/
										);
		
		return $summary;
	}
	
	switch($reporttype){
		case 'detail':
			$report[detail] = $detail;
			break;
		case 'both':
			$report[summary] = summarise($detail);
			$report[detail] = $detail;
			break;
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($detail);
			break;
	}


	return  display_enterprise_sales_report($report);
}
	
function display_enterprise_sales_report($report){
	
	$html = '
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
	';
	
	if($report[summary][data]){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARY</th>
			</tr>
		';
		
		foreach($report[summary][data] as $report_name=>$report_data){
			$html .= '
				<tr>
					<th>'.ucwords(str_replace(array('_'),' ',$report_name)).'</th>
				</tr>
				<tr>
					<td>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
					<tr>
						<th></th>
						<th></th>
			';
					
			foreach($report[summary][months] as $month){
				$html .= '
						<th colspan="'.count($report[summary][months_titles]).'">'.$month.'</th>
				';
			}
			$html .= '
					</tr>
					<tr>
						<th>#</th>
						<th>Sales Rep</th>
			';
			foreach($report[summary][months] as $month){
				foreach($report[summary][months_titles] as $month_title){
					$html .= '
						<th>'.ucwords(str_replace(array('_'),' ',$month_title)).'</th>
					';
				}
			}
			$html .= '
					</tr>
			';
			
			foreach($report_data as $sales_rep=>$sales_rep_data){
				$html .= '
					<tr>
						<td class="values">'.++$row_counter.'</td>
						<td class="text_values">'.$sales_rep.'</td>
				';
				
				foreach($report[summary][months] as $month){
					foreach($report[summary][months_titles] as $month_title){
						$html .= '
							<td class="values">'.round($sales_rep_data[$month][$month_title],1).'</td>
						';
					}
				}
				
				$html .= '
					</tr>
				';
			}
			unset($row_counter);
			
			$html .= '
					</table>
					</td>
				</tr>
				<tr>
					<td height="10"></td>
				</tr>
			';
		}
	}
	
	if($report[detail] and $report[summary]){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if($report[detail]){
		$html .= '
			<tr>
				<th style="height:20px;">DETAILS</th>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
		';
		
		foreach($report[detail] as $title=>$title_data){
			$html .= '
			<tr>
				<th>'.ucwords(str_replace(array('_'),' ',$title)).'</th>
			</tr>
			';
			
			$scratchpad = $title_data;
			
			$headings = array_keys(array_shift($scratchpad));
			unset($scratchpad);
			
			$html .= '
			<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
			<tr>
				<th>#</th>
			';
			
			foreach($headings as $heading){
				$html .= '
				<th>'.$heading.'</th>
				';
			}
			
			$html .= '
			</tr>
			';
			
			unset($row_counter);
			foreach($title_data as $row_id=>$row){
				$html .= '
			<tr>
				<td class="values"><a href="'.$report[links][$title].$row_id.'" target="_blank">'.++$row_counter.'</a></td>
				';
				
				foreach($headings as $heading){
					if(is_numeric($row[$heading]) or is_numeric(str_replace(array('-'),'',$row[$heading]))){
						$style_class = "values";
					}else{
						$style_class = "text_values";
					}
					
				$html .= '
				<td class='.$style_class.'>'.$row[$heading].'</td>
				';
				}
				
				$html .= '
			</tr>
				';

			}
			$html .= '
			</table>
			<td>
			</tr>
			<tr>
				<td style="height:5px;"></td>
			</tr>
			';
		}
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}


?>
