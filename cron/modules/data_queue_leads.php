<?php
function generate_queue_leads($date,$queue_cs_list,$queue_age=''){
	
	custom_query::select_db('wimax');
	$myquerys = new custom_query();
	
	if(trim($queue_cs_list) != ''){
		
		$queue_array = explode(",",$queue_cs_list);
		
		$queue_cs_list = "'".str_replace(",","','",$queue_cs_list)."'";
		
		$query = "
			SELECT
				qs_queues.id
			FROM
				qs_queues
			WHERE
				qs_queues.name IN (".$queue_cs_list.")
		";
		
		$queue_id_list = $myquerys->multiple($query);
		
		foreach($queue_id_list as $row){
			$queue_id_cs_list .= "'".$row[id]."'";
			if(++$i < count($queue_id_list)) { $queue_id_cs_list .= ","; }
		}
		
		$queue_condition = " AND
			qs_queues.id IN (".$queue_id_cs_list.") ";

		if(in_array('CC Complete Site Surveys',$queue_array)){
			$site_survey_columns = ",
			sv_sitesurvey.site_survey_status,
			leads_sv_sitesurvey_c.leads_sv_sisitesurvey_idb as site_survey_id
			";
			$site_survey_join_query = "
			INNER JOIN leads_sv_sitesurvey_c ON leads.id = leads_sv_sitesurvey_c.leads_sv_siurveyleads_ida
			INNER JOIN sv_sitesurvey ON leads_sv_sitesurvey_c.leads_sv_sisitesurvey_idb = sv_sitesurvey.id
			LEFT OUTER Join accounts ON leads.account_id = accounts.id ";
			
			$site_survey_condition = "
			AND leads_sv_sitesurvey_c.deleted = 0 AND
			sv_sitesurvey.deleted = 0 AND
			(accounts.deleted = 1 or accounts.deleted is null)
			";
			
			$report[queue] = 'CC Complete Site Surveys';
		}
	}
	
	if(trim($date) == ''){ $date = date('Y-m-d H:i:s'); }else{ $date .= " 00:00:00";}
	
	if($queue_age != '') { $queue_age_condition = " AND
			(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date_add(qs_queues_leads_c.date_modified, interval 3 hour)))/(24 * 3600) ".$queue_age." " ;
	}
	
	$report[time_run] = date('H:i:s');
	
	$query = "
		SELECT
			leads.id AS lead_id,
			trim(concat(leads.first_name,' ',leads.last_name)) AS lead_names,
			leads.status,
			leads.description,
			leads_cstm.sales_rep_c,
			(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date_add(qs_queues_leads_c.date_modified, interval 3 hour)))/(24 * 3600) AS queue_age,
			leads_cstm.lead_rejection_reason_c,
			qs_queues.name,
			qs_queues.id AS queue_id".
			$site_survey_columns."
		FROM
			qs_queues
			Inner Join qs_queues_leads_c ON qs_queues_leads_c.qs_queues_lsqs_queues_ida = qs_queues.id
			Inner Join leads ON leads.id = qs_queues_leads_c.qs_queues_leadsleads_idb
			Inner Join leads_cstm ON leads.id = leads_cstm.id_c ".
			$site_survey_join_query."
		WHERE
			qs_queues_leads_c.deleted = 0 AND
			leads.date_entered <= date_add('".$date."', interval -3 hour) AND
			leads.status != 'Converted' AND
			leads.deleted = 0 ".
			$site_survey_condition.
			$queue_condition.
			$queue_age_condition."
		GROUP BY
			LOWER(REPLACE(lead_names,' ',''))
		ORDER BY
			(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date_add(qs_queues_leads_c.date_modified, interval 3 hour)))/(24 * 3600) ASC
	";
	
	//echo $query;
	
	$report[data] = $myquerys->multiple($query);
	
	if(count($report[data]) == 0) { $report[NO_DATA] = TRUE; }
	
	//exit("exiting .... ");
	return display_queue_leads_email($report);
}

function display_queue_leads_email($report){
	
	if($report[NO_DATA]){
		return "There are no Leads .... ";
	}else{
		if($report[queue] == 'CC Complete Site Surveys'){
			$site_survey_title = '<th>SITE SURVEY STATUS</th>';
		}
		$html = '
			<table border="0" cellpadding="2" cellspacing="0" width="700px">
				<tr>
					<th>#</th>
					<th>LEAD NAME</th>
					<!--<th>LEAD STATUS</th>-->
					<th>LEAD NOTES</th>
					<th>SALES REP</th>
					<th>DAYS IN QUEUE</th>
					<th>REJECTION NOTES</th>
					<!--<th>CURRENT QUEUE</th>-->'.
					$site_survey_title.'
				</tr>
		';
		
		foreach($report[data] as $row){
			if($report[queue] == 'CC Complete Site Surveys'){
				$site_survery_tds = '<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=SV_SiteSurvey&action=DetailView&record='.$row[site_survey_id].'" target="_blank">'.$row[site_survey_status].'</a></td>';
			}
			++$i;
			if($i%2 == 0) {$row_class = 'even'; }else{ $row_class = 'odd'; }
			$html .= '
				<tr class="'.$row_class.'">
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Leads&action=DetailView&record='.$row[lead_id].'" target="_blank">'.$i.'</a></td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Leads&action=DetailView&record='.$row[lead_id].'" target="_blank">'.ucwords($row[lead_names]).'</a></td>
					<!--<td class="text_values">'.$row[status].'</td>-->
					<td class="wrap_text">'.$row[description].'</td>
					<td class="text_values">'.$row[sales_rep_c].'</td>
					<td class="values">'.number_format($row[queue_age],0).'</td>
					<td class="wrap_text">'.$row[lead_rejection_reason_c].'</td>
					<!--<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=qs_Queues&action=DetailView&record='.$row[queue_id].'" target="_blank">'.$row[name].'</a></td>-->'.
					$site_survery_tds.'
				</tr>
			';
		}
		
		$html .= '
			</table>
			<table border="0" cellpadding="0" cellspacing="0" width="700px">
				<tr>
					<td height="20px" width="700px"></td>
				</tr>
				<tr>
					<td width="700px">This report was run at '.$report[time_run].'</td>
				</tr>
			</table>
		';
	}

	return $html;
}
?>