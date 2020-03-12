<?
function generate_site_surveys($cpe_type,$from,$to,$test_results,$assigned_to,$status){
	
	custom_query::select_db('wimax');

	$myquery = new custom_query();
	$query = "
	SELECT
		leads.first_name as fname, 
		leads.last_name as lname, 
		sv_sitesurvey.coordinates as longitude,
		sv_sitesurvey.primary_base as primary_base,
		sv_sitesurvey.secondary_base,
		sv_sitesurvey.primary_base_loc_id,
		sv_sitesurvey.primary_base_loc_id,
		sv_sitesurvey.site_survey_status as site_survey_status,
		sv_sitesurvey_cstm.lattitude_c as latitude,
		sv_sitesurvey.secondary_base_loc_id,
		sv_sitesurvey.signal_strength,
		sv_sitesurvey.date_entered as date_entered,
		sv_sitesurvey.terrain_type as terrain_type,
		sv_sitesurvey.overall_test_result as overall_test_result,
		sv_sitesurvey.engineer_rec as engineer_rec
		FROM
		leads
		Inner Join leads_cstm ON leads.id = leads_cstm.id_c
		Inner Join leads_sv_sitesurvey_c ON leads.id = leads_sv_sitesurvey_c.leads_sv_siurveyleads_ida
		Inner Join sv_sitesurvey ON leads_sv_sitesurvey_c.leads_sv_sisitesurvey_idb = sv_sitesurvey.id
		Inner Join sv_sitesurvey_cstm ON sv_sitesurvey.id = sv_sitesurvey_cstm.id_c
		WHERE 
		leads.deleted = '0'
		AND sv_sitesurvey.deleted = '0'
		AND leads_sv_sitesurvey_c.deleted = '0'
	";
	if($from){ $query .= " AND sv_sitesurvey.date_entered >= '".$from."'";}else{ $query .= " AND sv_sitesurvey.date_entered >= '".date('Y-m-')."01'";}
	if($to){ $query .= " AND sv_sitesurvey.date_entered <= '".$to."'";}else{ $query .= " AND sv_sitesurvey.date_entered <= '".date('Y-m-d')."'";}
	if($cpe_type){ $query .= " AND accounts_cstm.cpe_type_c = '".$cpe_type."'";}
	if($test_results){ $query .= " AND sv_sitesurvey.overall_test_result = '".$test_results."'";}
	if($status){ $query .= " AND sv_sitesurvey.site_survey_status= '".$status."'";}
	
	$site_surveys = $myquery->multiple($query);
	
	return display_site_survey_hmtl($site_surveys);
}

function display_site_survey_hmtl($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%"> 
		<tr> 
			  <th></th>
			  <th>Lead Name</th>
			  <th>Primary Base</th>
			  <th>Overall Test Result</th>
			  <th>CPE Type</th>
			  <th>Site Survey Status</th>
			  <th>Terrain Type</th>
			  <th>Assigned To</th>
			  <th>Latitude</th>
			  <th>Longitude</th>
			  <th>Date Created</th>
			  <th>Engineers Resomendation</th>
		</tr>
	';
	foreach($report as $row)
	{
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="wrap_text_task">'.$row[fname].' '.$row[lname].'</td>
				<td class="text_values">'.$row[primary_base].'</td>
				<td class="wrap_text_task">'.$row[overall_test_result].'</td>
				<td class="text_values">'.$row[cpe_type].'</td>
				<td class="text_values">'.$row[site_survey_status].'</td>
				<td class="text_values">'.$row[terrain_type].'</td>
				<td class="text_values">'.$row[''].'</td>
				<td class="wrap_text_task">'.$row[latitude].'</td>
				<td class="text_values">'.$row[longitude].'</td>
				<td class="text_values">'.$row[date_entered].'</td>
				<td class="wrap_text_task">'.$row[engineer_rec].'</td>
			</tr>';
	
	}
	return $html;
}
?>