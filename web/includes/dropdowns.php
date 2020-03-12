<?php

function dropdown($label, $name, $onchange_call, $selected, $options, $class='select', $size='1', $multiple){
	
	if(!is_array($selected)){
		$selected = array($selected);
	}
	
	$html = '
		<label>'.$label.' : <br><select name="'.$name.'" id="'.$name.'" class="'.$class.'" size="'.$size.'" 
	';
	
	if($multiple){
		$html .= ' multiple="multiple" ';
	}
	
	if($onchange_call){
		$html .= ' onchange="'.$onchange_call.'" ';
	}
	
	$html .= '>
		<option value=""'; if(in_array('',$selected)){ $html .= ' selected="selected" ';} $html .= '>Select a '.$label.'</option>
	';
	
	//print_r($options); echo '<br>';
	
	foreach($options as $value=>$lable){
		$html .=  '<option value="'.$value.'"'; if(in_array($value,$selected)){ $html .= ' selected="selected" ';} $html .= '>'.$lable.'</option>';
	}
	
	$html .= '</select></label>';
	
	return $html;
}

function get_wrapup_source_options(){
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT wrapup_source_type.`name` as source_name FROM wrapup_source_type WHERE wrapup_source_type.`status` = 'Active'
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		//echo "key is ".$key."<br>";
		$newlist[$row[source_name]] = $row[source_name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_category_options(){
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT cat_id, category FROM category where cat_status = 'active'
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		//echo "key is ".$key."<br>";
		$newlist[$row[category]]=$row[category];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_datagroup_options(){
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			DISTINCT
			ps_products_cstm.product_grouping_c as db_datagroup,
			IF(ps_products_cstm.product_grouping_c = 'Service','Bandwidth',ps_products_cstm.product_grouping_c)  as datagroup
		FROM
			ps_products
			LEFT OUTER JOIN ps_products_cstm ON ps_products_cstm.id_c = ps_products.id
		WHERE
			ps_products.deleted = 0
		ORDER BY
			datagroup ASC
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[db_datagroup]] = $row[datagroup];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_dataproduct_options(){
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			IF(ps_products.type = 'Goods','One time','Regular') as product_type,
			ps_products_cstm.product_grouping_c as product_group,
			ps_products.`name`
		FROM
			ps_products
			LEFT OUTER JOIN ps_products_cstm ON ps_products_cstm.id_c = ps_products.id
		WHERE
			ps_products.deleted = 0
		ORDER BY
			ps_products.type,
			ps_products_cstm.product_grouping_c,
			ps_products_cstm.billing_currency_c,
			ps_products.`name` ASC
	";
	
	$list = $myquery->multiple($query);
	
	//print_r($list);
	
	foreach($list as $key=>$row){
		$newlist[$row[name]] = $row[product_type].' - '.$row[product_group].' - '.$row[name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_subcategory_options($cat_id){
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT category.category,subcategory.subcategory FROM subcategory inner join category on subcategory.cat_id=category.cat_id  where ";
	
	if($cat_id){
		$query .= " subcategory.cat_id = '".$cat_id."' and ";
	}
	
	$query .= "	subcategory.sub_cat_status = 'active' order by category.category,subcategory.subcategory asc ";
	
	//echo "<br>".$query."<br>";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[subcategory]] = '['.$row[category].'] '.$row[subcategory];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_wrapup_options($cat_id,$subcategory){
	
	custom_query::select_db('ccba01.reportscrm');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			category.category,
			subsubcategory.subcategory,
			subsubcategory.subsubcategory
		FROM
			subsubcategory
			Inner Join category ON subsubcategory.cat_id = category.cat_id
		WHERE
			subsubcategory.id != '0'
	";

	if($cat_id){
		$query .= " and subsubcategory.cat_id = '".$cat_id."'  ";
	}

	if($subcategory){
		$query .= " and subsubcategory.subcategory = '".$subcategory."'  ";
	}
	
	$query .= "
--			subsubcategory.subject_status = 'active' 
		order by 
			category.category,subsubcategory.subcategory,subsubcategory.subsubcategory asc ";
	
	//echo "<br>".nl2br($query)."<br>";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[subsubcategory]]='['.$row[category].'] '.'['.$row[subcategory].'] '.$row[subsubcategory];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_business_centre_options(){
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	$query = "
		SELECT
			bc_names.`name`
		FROM
			bc_names
		WHERE 
			bc_names.`name` != ''
		ORDER BY
			bc_names.`name` ASC
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[name]]=$row[name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_business_centre_sales_item_options(){
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	$query = "
		SELECT DISTINCT
			bc_item_groups.group_name,
			bc_items.item_name
		FROM
			bc_sales
			INNER JOIN bc_items ON bc_items.id = bc_sales.item_id
			INNER JOIN bc_item_groups ON bc_item_groups.id = bc_items.group_id
		WHERE
			bc_sales.entry_date > '2012-09-01'
		ORDER BY
			group_name,item_name
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[item_name]]=$row[group_name]." - ".$row[item_name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_business_centre_options_with_ids(){
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	$query = "
		SELECT
			bc_names.id,
			bc_names.name
		FROM
			bc_names
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[id]]=$row[name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_business_centre_camera_options_with_ids(){
	custom_query::select_db('businesssales');
	$myquery = new custom_query();
	
	$query = "
	SELECT
		bc_cameras.id,
		bc_cameras.name
	FROM
		bc_cameras
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[id]]=$row[name];
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function get_agents_options(){
	
	custom_query::select_db('cs');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			concat(emp_FNAME,' ',emp_LNAME) as agent
		FROM
			employees
		WHERE
			emp_SEC = 'Retention' or 
			emp_POS = 'Student'
		ORDER BY
			TRIM(concat(emp_FNAME,' ',emp_LNAME)) ASC
	";
		
	//echo "<br>".$query."<br>";
	
	$list = $myquery->multiple($query);
	
	foreach($list as $key=>$row){
		$newlist[$row[agent]]=ucwords(strtolower($row[agent]));
		unset($list[$key],$key);
	}
	
	return $newlist;
}

function display_smt_user_dropdown($selected, $style){
	
	custom_query::select_db('wimax');
	$style = 'select';
	$myquery = new custom_query();
	
	$query = "
		SELECT
			id,
			users.first_name,
			users.last_name,
			department,
			STATUS
		FROM
			wimax.users 
		WHERE 
			users.department IN ('Customer Care CS SMT','Bill Delivery') AND
			users.status = 'Active'
	";
	
	$team = $myquery->multiple($query);
	$html = '<label class="'.$style.'"> SMT Team Member <select name="smt_user[]" size="3" multiple="multiple"  id="smt_user[]" class="'.$style.'">
			<option value="%%" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>WHOLE TEAM</option>
			';
	foreach($team as $user){
		$html .= '
		<option value="'.$user[id].'" '; if($selected==$user[id]){$html .= 'selected="selected"';} $html .= '>'.$user[first_name]." ".$user[last_name].'</option>';
	}
	$html .= '</select></label>';
	return $html;
}

function display_equipment_type_dropdown($selected, $style){
	
	custom_query::select_db('wimax');
	$style = 'select';
	$myquery = new custom_query();
	
	$query = "
		SELECT
	distinct accounts_cstm.cpe_type_c
	FROM
	accounts
	Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c where  accounts_cstm.cpe_type_c != ''

	";
	$equip = $myquery->multiple($query);
	$html = '<label class="'.$style.'"> Equipment Type <select name="equip_type[]" size="3" multiple="multiple"  id="equip_type[]" class="'.$style.'">
			<option value="" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>ALL EQUIP</option>
			';
	foreach($equip as $row){
		$html .= '
		<option value="'.$row[cpe_type_c].'" '; if($selected == $row[cpe_type_c]){$html .= 'selected="selected"';} $html .= '>'.$row[cpe_type_c].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_casestatus_dropdown($status){

	require_once('config.wimax.php');

	$myquery = new custom_query();
	
	$statuses = $myquery->multiple("select distinct status as statuses from cases order by status asc");
	
	$html = '<label> Select case status <select name="case_status" size="1" id="case_status" class="select">';
	$html .= '<option value="" ';if($status == ''){ $html .= 'selected="selected"'; } $html .= '>ANY STATUS</option>';
	foreach($statuses as $value){
		$html .= '<option value="'.$value[statuses].'" ';if($status == $value[statuses]){ $html .= 'selected="selected"'; } $html .= '>'.$value[statuses].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_lead_dropdown($select_leads){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct first_name as fname,last_name as lname  from leads where leads.deleted = 0 order by status asc";
	$leads_list = $myquery->multiple($query);
	$html = '<div class="search" id="leads"><label> Leads <select name="leads" size="1" id="leads" class="select">';
	if($select_leads == ''){
		$html .= '<option value="" selected="selected">ALL LEADS</option>';
	}else{
		$html .= '<option value="">tttt</option>';
	}
	foreach($leads_list as $lead){
		if($select_leads == $lead[fname].''.$lead[lname]){
			$html .= '<option value="'.$lead[fname].' '.$lead[lname].'" selected="selected">'.$lead[fname].' '.$lead[lname].'</option>';
		}else{
			$html .= '<option value="'.$lead[fname].' '.$lead[lname].'">'.$lead[fname].' '.$lead[lname].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function display_subject_setting_dropdown($subject_setting){

	require_once('config.wimax.php');

	$myquery = new custom_query();
	
	$subject_settings = $myquery->multiple("select distinct cases_cstm.subject_setting_c as subject_settings from cases_cstm order by subject_setting_c asc");
	
	$html = '<label> Select subject setting <select name="subject_setting" size="1" id="subject_setting" class="select">';
	$html .= '<option value="" ';if($subject_setting == ''){ $html .= 'selected="selected"'; } $html .= '>ANY SUBJECT SETTING</option>';
	foreach($subject_settings as $value){
		$html .= '<option value="'.$value[subject_settings].'" ';if($subject_setting == $value[subject_settings]){ $html .= 'selected="selected"'; } $html .= '>'.$value[subject_settings].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function supervisor_drop_down($select_supervisor){
	require_once('config.attendance.php');
	$myquery = new custom_query();
	$query = "select distinct name from cc_supervisor";
	$supervisors = $myquery->multiple($query);
	$html = '<select name="supervisors">';
	if($select_supervisor == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Please Select Supervisors</option>';
	}
	foreach($supervisors as $supervisor){
		if($select_supervisor == $supervisor[name]){
			$html .= '<option value="'.$supervisor[name].'" selected="selected">'.$supervisor[name].'</option>';
		}else{
			$html .= '<option value="'.$supervisor[name].'">'.$supervisor[name].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function task_type_drop_down($select_task_types){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct task_type_c as task_type from tasks_cstm";
	$task_types = $myquery->multiple($query);
	$html = '<label>Task Type </label><select name="task_type" class="select">';
	if($select_task_type == ''){
		$html .= '<option value="" selected="selected">ALL TASK TYPES</option>';
	}else{
		$html .= '<option value="">Select Task Type</option>';
	}
	foreach($task_types as $task_type){
		if($select_task_types == $task_type[task_type]){
			$html .= '<option value="'.$task_type[name].'" selected="selected">'.$task_type[task_type].'</option>';
		}else{
			$html .= '<option value="'.$task_type[task_type].'">'.$task_type[task_type].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function test_result_drop_down($select_test_results){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct overall_test_result as result from sv_sitesurvey where overall_test_result != '' ";
	$test_results = $myquery->multiple($query);
	$html = '<label>Test Result </label><select name="test_results" class="select">';
	if($select_test_results == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Select</option>';
	}
	foreach($test_results as $test_result){
		if($select_test_results == $test_result[result]){
			$html .= '<option value="'.$test_result[result].'" selected="selected">'.$test_result[result].'</option>';
		}else{
			$html .= '<option value="'.$test_result[result].'">'.$test_result[result].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function cpe_type_drop_down($select_cpe_types){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct cpe_type_c as cpe_type from accounts_cstm where cpe_type_c != '' ";
	$cpe_types = $myquery->multiple($query);
	$html = '<label>CPE Type</label><select name="cpe_type" class="select">';
	if($select_cpe_types == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Select</option>';
	}
	foreach($cpe_types as $cpe_type){
		if($select_cpe_types == $cpe_type[cpe_type]){
			$html .= '<option value="'.$cpe_type[cpe_type].'" selected="selected">'.$cpe_type[cpe_type].'</option>';
		}else{
			$html .= '<option value="'.$cpe_type[cpe_type].'">'.$cpe_type[cpe_type].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function ss_status_drop_down($select_ss_status){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct site_survey_status as status from sv_sitesurvey where site_survey_status != '' ";
	$ss_statuses = $myquery->multiple($query);
	$html = '<label>Site Survey Status</label><select name="status" class="select">';
	if($select_ss_status == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Select</option>';
	}
	foreach($ss_statuses as $ss_status){
		if($select_ss_status == $ss_status[status]){
			$html .= '<option value="'.$ss_status[status].'" selected="selected">'.$ss_status[status].'</option>';
		}else{
			$html .= '<option value="'.$ss_status[status].'">'.$ss_status[status].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}
 
function team_drop_down($select_team){
	require_once('config.attendance.php');
	$myquery = new custom_query();
	$query = "select distinct name from cc_teams";
	$teams = $myquery->multiple($query);
	$html = '<select name="teams">';
	if($select_team == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Please Select Teams</option>';
	}
	foreach($teams as $team){
		if($select_team == $team[name]){
			$html .= '<option value="'.$team[name].'" selected="selected">'.$team[name].'</option>';
		}else{
			$html .= '<option value="'.$team[name].'">'.$team[name].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function shift_drop_down($select_shift){
	require_once('config.attendance.php');
	$myquery = new custom_query();
	$query = "select distinct shift from shifts";
	$shifts = $myquery->multiple($query);
	$html = '<select name="shifts">';
	if($select_shift == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">Please Select Shift</option>';
	}
	foreach($shifts as $shift){
		if($select_shift == $shift[shift]){
			$html .= '<option value="'.$shift[shift].'" selected="selected">'.$shift[shift].'</option>';
		}else{
			$html .= '<option value="'.$shift[shift].'">'.$shift[shift].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function dislay_blanks($report){	$count = count($report);
	$html .= '<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align = "center">No data falls within the given date ranges '.$report[0].' and '.$report[$count-1].'</td></tr>
	';
	return $html;
}

function answers_dropdown($selected){
	$answers = array('1'=>'Poor','2'=>'Only Ok','3'=>'Very Nice');
	$html = '
		<label>Answers : 
		<select class="select" multiple="multiple" name="answers[]" id="answers[]" size="3">';
    	foreach($answers as $k => $v)
		{
			$html .= '<option value="'.$k.'"';
			if(in_array($k,$selected)){ $html .= ' selected=selected '; }
			$html .= '>'.$v.'</option>';
		}
      $html .= '</select></label>';
	
	return $html;
}

function franchises_dropdown($franchise_ids){
	custom_query::select_db(franchise);
	//require_once('config.franchise.php');
	$myquery = new custom_query();
	$query = "SELECT code,code_num,name FROM locations";
	$franchises = $myquery->multiple($query);
	$html = '
		<label>Franchises : 
		<select class="select" multiple="multiple" name="franchises[]" id="franchises[]" size="5">';
	foreach ($franchises as $franchise){	
		$html .= '<option value="'.$franchise[code_num].'"';
		if(in_array($franchise[code_num],$franchise_ids)){
			$html .= ' selected=selected ';
		}
		$html .= '>'.$franchise[name].' ['.$franchise[code].']</option>';
	}
	$html .= '
		</select>
		</label>';
	return $html;
}

function display_crm_user_dropdown($crm_user_id){

	require_once('config.wimax.php');

	$myquery = new custom_query();
	
	$crm_users = $myquery->multiple("select id,user_name, concat(concat(first_name,' '), last_name) as crm_user from users where deleted=0 and (first_name != '' or last_name != '') order by crm_user");
	
	$html = '<label> Select User <select name="crm_user_id" size="1" id="crm_user_id" class="select">';
	$html .= '<option value="" ';if($crm_user_id == ''){ $html .= 'selected="selected"'; } $html .= '>ALL USERS</option>';
	foreach($crm_users as $user){
		$html .= '<option value="'.$user[id].'" ';if($crm_user_id == $user[id]){ $html .= 'selected="selected"'; } $html .= '>'.$user[crm_user].' ('.$user[user_name].')</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_broadband_status_dropdown($status){

	require_once('config.wimax.php');

	$myquery = new custom_query();
	
	$statuses = $myquery->multiple("select distinct status as statuses from cn_contracts order by status asc");
	
	$html = '<label> Select bandwidth status <select name="broadband_status" size="1" id="broadband_status" class="select">';
	$html .= '<option value="" ';if($status == ''){ $html .= 'selected="selected"'; } $html .= '>ANY STATUS</option>';
	$html .= '<option value="NO CONTRACT" >No Contract</option>';
	foreach($statuses as $value){
		$html .= '<option value="'.$value[statuses].'" ';if($status == $value[statuses]){ $html .= 'selected="selected"'; } $html .= '>'.$value[statuses].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_taskstatus_dropdown($select_status){
	require_once('config.wimax.php');
	$myquery = new custom_query();
	$query = "select distinct status as statuses from tasks order by status asc";
	$status_list = $myquery->multiple($query);
	$html = '<label> Status <select name="task_status" size="1" id="task_status" class="select">';
	if($select_status == ''){
		$html .= '<option value="" selected="selected"></option>';
	}else{
		$html .= '<option value="">tttt</option>';
	}
	foreach($status_list as $status){
		if($select_status == $status[statuses]){
			$html .= '<option value="'.$status[statuses].'" selected="selected">'.$status[statuses].'</option>';
		}else{
			$html .= '<option value="'.$status[statuses].'">'.$status[statuses].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function display_customer_type_dropdown($selected, $style){
	
	custom_query::select_db('wimax');
	
	$style = 'select';
	$myquery = new custom_query();
	
	$query = "
			select distinct customer_type_c as customer_type
			from accounts_cstm
			inner join accounts on (accounts.id = accounts_cstm.id_c)
			where accounts.deleted = 0 and
			customer_type_c != ''
			";
	$customer_types = $myquery->multiple($query);
	$html = '<label class="'.$style.'"> Customer Type <select name="customer_types[]" size="3" multiple="multiple"  id="customer_type[]" class="'.$style.'">
			<option value="%%" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>ALL TYPES</option>
			';
	foreach($customer_types as $customer_type){
		$html .= '
		<option value="'.$customer_type[customer_type].'" '; if($selected==$customer_type[customer_type]){$html .= 'selected="selected"';} $html .= '>'.$customer_type[customer_type].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_ivr_queue_dropdown($selected, $style){
	
	if($style == ''){
		$style = 'select';
	}
	
	$html = '
		<label class="'.$style.'"> IVR Queues 
			<select name="queues[]" size="3" multiple="multiple"  id="queues[]" class="'.$style.'">
				<option value="%%" '; if(count($selected)==0){$html .= 'selected="selected"';} $html .= '>ALL QUEUES</option>
				<option value="Wimax" '; if(in_array('Wimax',$selected)){$html .= 'selected="selected"';} $html .= '>Wimax</option>
				<option value="Prepaid" '; if(in_array('Prepaid',$selected)){$html .= 'selected="selected"';} $html .= '>Prepaid</option>
				<option value="Postpaid" '; if(in_array('Postpaid',$selected)){$html .= 'selected="selected"';} $html .= '>Postpaid</option>
				<option value="NonWarid" '; if(in_array('NonWarid',$selected)){$html .= 'selected="selected"';} $html .= '>NonWarid</option>
				<option value="Franchise" '; if(in_array('Franchise',$selected)){$html .= 'selected="selected"';} $html .= '>Franchise</option>
				<option value="MobileMoney" '; if(in_array('MobileMoney',$selected)){$html .= 'selected="selected"';} $html .= '>MobileMoney</option>
			</select>
		</label>';
	
	return $html;
}

function display_accounts_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$account_query = "
		SELECT
			accounts.name, 
			accounts_cstm.crn_c as account_id 
		FROM
			accounts 
			INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id) 
		WHERE 
			accounts_cstm.mem_id_c  != '' and  
			accounts.deleted = '0' 
		ORDER BY 
			name asc
	";
	
	$accounts_list = $myquery->multiple($account_query);
	
	$html = '<label> Account <select name="account_id" size="1" id="account_id" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[account_id].'" ';
		if($selected == $account[account_id]){
			$html .= 'selected="selected"';
		} $html .= '>'.$account[name].'</option>';
	}
	$html .= '
		</select></label>
	';
	
	return $html;
}

function display_status_dropdown($selected){
	$myquery = new custom_query();
	
	$statuses = $myquery->multiple("select distinct `status` from cn_contracts");
	
	$html = '<label> Select CURRENT Status <select name="cn_status" size="1" id="cn_status" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ANY STATUS</option>';
	$html .= '<option value="blank" >No Contract</option>';
	foreach($statuses as $status){
		$html .= '<option value="'.$status[status].'" ';
		if($selected == $status[status]){
			$html .= 'selected="selected"';
		} $html .= '>'.$status[status].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_field_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$acc_query = "
		SELECT 
		  distinct
		  accounts_audit.field_name
		FROM
		 accounts_audit,cn_contracts_audit
		WHERE
		 accounts_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	$conts_query = "
		SELECT 
		  distinct
		  cn_contracts_audit.field_name
		FROM
		 cn_contracts_audit
		WHERE
		 cn_contracts_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	$tr_trials_query = "
		SELECT 
		  distinct
		  tr_trials_audit.field_name
		FROM
		 tr_trials_audit
		WHERE
		 tr_trials_audit.field_name NOT LIKE '%assigned_user_id%'
	";
	$cases_query = "
		SELECT 
		  distinct
		  cases_audit.field_name
		FROM
		 cases_audit
		WHERE
		 cases.field_name NOT LIKE '%assigned_user_id%'
	";
	
	$acc_fields = $myquery->multiple($acc_query);
	$cont_fields = $myquery->multiple($conts_query);
	$tr_trials_fields = $myquery->multiple($tr_trials_query);
	$cases_fields = $myquery->multiple($cases_query);
	
	$fields = array();
	foreach($acc_fields as $row){
		$row[module] = 'Account';
		array_push($fields,$row);
	}
	foreach($cont_fields as $row){
		$row[module] = 'Contract';
		array_push($fields,$row);
	}
	foreach($tr_trials_fields as $row){
		$row[module] = 'Service Trials';
		array_push($fields,$row);
	}
	foreach($cases_fields as $row){
		$row[module] = 'Cases';
		array_push($fields,$row);
	}
	
	$html = '<label> Select Field <select name="field_name" size="1" id="field_name" class="select">';
	$html .= '<option value="" ';if($selected == ''){ $html .= 'selected="selected"'; } $html .= '>ALL FEILDS</option>';
	foreach($fields as $field){
		$html .= '<option value="'.$field[field_name].'" ';if($selected == $field[field_name]){ $html .= 'selected="selected"'; } $html .= '>'.$field[module].' '.display_label($field[field_name]).'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_aging_period_dropdown($selected){

	$html = '
	<label>Aging Period:
		<select name="from" id="from" class="select">
			<!--
			<option value="1"'; if($selected == 1){$html .= 'selected="selected"';} $html .= '>1 month</option>
			<option value="2"'; if($selected == 2){$html .= 'selected="selected"';} $html .= '>2 months</option>
			<option value="3"'; if($selected == 3){$html .= 'selected="selected"';} $html .= '>3 months</option>
			<option value="4"'; if($selected == 4){$html .= 'selected="selected"';} $html .= '>4 months</option>
			<option value="5"'; if($selected == 5){$html .= 'selected="selected"';} $html .= '>5 months</option>
			<option value="6"'; if($selected == 6){$html .= 'selected="selected"';} $html .= '>6 months and above</option>
			<option value="7"'; if($selected == 7){$html .= 'selected="selected"';} $html .= '>7 months</option>
			<option value="8"'; if($selected == 8){$html .= 'selected="selected"';} $html .= '>8 months</option>
			<option value="9"'; if($selected == 9){$html .= 'selected="selected"';} $html .= '>9 months</option>
			<option value="10"'; if($selected == 10){$html .= 'selected="selected"';} $html .= '>10 months</option>
			<option value="11"'; if($selected == 11){$html .= 'selected="selected"';} $html .= '>11 months</option>
			<option value="12"'; if($selected == 12){$html .= 'selected="selected"';} $html .= '>12 months & above</option>
			-->
			<option value="1"'; if($selected == 1){$html .= 'selected="selected"';} $html .= '>1 month back</option>
			<option value="2"'; if($selected == 2){$html .= 'selected="selected"';} $html .= '>2 months back</option>
			<option value="3"'; if($selected == 3){$html .= 'selected="selected"';} $html .= '>3 months back</option>
			<option value="4"'; if($selected == 4){$html .= 'selected="selected"';} $html .= '>4 months back</option>
			<option value="3"'; if($selected == 5){$html .= 'selected="selected"';} $html .= '>5 months back</option>
			<option value="4"'; if($selected == 6){$html .= 'selected="selected"';} $html .= '>6 months back</option>
			<option value="5"'; if($selected == 7){$html .= 'selected="selected"';} $html .= '>7 - 12 months back</option>
			<option value="13"'; if($selected == 13){$html .= 'selected="selected"';} $html .= '>Beyond 12 Months</option>
		</select>
	</label>
	';
	
	return $html;
}

function display_wrapups_sms_csat_evaluation_dropdown($selected){

	$html = '
		<label>CSAT Evaluation:
			<select name="csat_evaluation_answer" id="csat_evaluation_answer" class="select">
				<option value=""'; if($selected == ''){$html .= 'selected="selected"';} $html .= '>ALL</option>
				<option value="y%"'; if($selected == 'y%'){$html .= 'selected="selected"';} $html .= '>GOOD</option>
				<option value="n%"'; if($selected == 'n%'){$html .= 'selected="selected"';} $html .= '>BAD</option>
			</select>
		</label>
	';
	
	return $html;
}

function display_products_dropdown($grouping){
	$myquery = new custom_query();
	
	if($grouping == ''){
		$query = "SELECT ps_products.name FROM ps_products WHERE deleted = '0' order by name asc";
	}else{
		$query = "SELECT ps_products.name FROM ps_products INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c) WHERE (ps_products_cstm.product_grouping_c = 'Service') and ps_products.deleted != 1 order by name asc";
	}
	
	$products_list = $myquery->multiple($query);
	
	$html = '<label> Select Product <select name="product" size="1" id="product" class="select">';
	$html .= '<option value="" selected="selected">ALL PRODUCTS</option>';
	foreach($products_list as $product){
		$html .= '<option value="'.$product[name].'">'.$product[name].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_gsm_case_subject_setting_dropdown($subject_setting){

	custom_query::select_db('reportscrm');

	$myquery = new custom_query();
	
	$subject_settings = $myquery->multiple("SELECT distinct troubleticket as subject_setting FROM reportscrm ORDER BY troubleticket ASC");
	
	$html = '<label> Select Subject setting <select name="subject_settings[]" size="5" multiple="multiple" id="subject_settings[]" class="select">';
	$html .= '<option value="" ';if($subject_setting == ''){ $html .= 'selected="selected"'; } $html .= '>ALL</option>';
	foreach($subject_settings as $value){
		if($value[subject_setting] != ''){
			$html .= '<option value="'.$value[subject_setting].'" ';if(in_array($value[subject_setting],$subject_setting)){ $html .= 'selected="selected"'; } $html .= '>'.$value[subject_setting].'</option>';
		}
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_report_type_dropdown($report_type){
	
	$html = '<label> Select Report Type <select name="report_type" size="1" id="report_type" class="select">';
	$html .= '<option value="summary_brief" ';if(($report_type == '')||($report_type == 'summary_brief')){ $html .= 'selected="selected"'; } $html .= '>Summary (Brief)</option>';
	$html .= '<option value="summary" ';if($report_type == 'summary'){ $html .= 'selected="selected"'; } $html .= '>Summary</option>';
	$html .= '<option value="both" ';if($report_type == 'both'){ $html .= 'selected="selected"'; } $html .= '>Both (ALL)</option>';
	$html .= '<option value="detail" ';if($report_type == 'detail'){ $html .= 'selected="selected"'; } $html .= '>Detail only</option>';
	$html .= '</select></label>';
	
	return $html;
}

function display_upsell_product_type_dropdown($product_type){
$html = '<label> Select Product Type <select name="product_type" size="1" id="product_type" class="select">';
	$html .= '<option value="" ';if(($report_type == '')||($report_type == 'All Pdts')){ $html .= 'selected="selected"'; } $html .= '>All Pdts</option>';
	$html .= '<option value="CRBT Activation" ';if($report_type == 'CRBT Activation'){ $html .= 'selected="selected"'; } $html .= '>CRBT Activation</option>';
	$html .= '<option value="CRBT Download" ';if($report_type == 'CRBT Download'){ $html .= 'selected="selected"'; } $html .= '>CRBT Download</option>';
	$html .= '<option value="GPRS" ';if($report_type == 'GPRS'){ $html .= 'selected="selected"'; } $html .= '>GPRS</option>';
	$html .= '</select></label>';
	
	return $html;
}


function display_ivr_report_type_dropdown($report_type){
	
	$html = '<label> Select Report Type <select name="report_type" size="1" id="report_type" class="select">';
	$html .= '<option value="summary" ';if(($report_type == '')||($report_type == 'summary')){ $html .= 'selected="selected"'; } $html .= '>Overall Summary</option>';
	$html .= '<option value="monthly summary" ';if($report_type == 'monthly summary'){ $html .= 'selected="selected"'; } $html .= '>By Month</option>';
	$html .= '<option value="daily summary" ';if($report_type == 'daily summary'){ $html .= 'selected="selected"'; } $html .= '>By day</option>';
	$html .= '</select></label>';
	
	return $html;
}

function display_telesales_report_type_dropdown($report_type){
	
	$html = '<label> Select Report Type <select name="report_type" size="1" id="report_type" class="select">';
	$html .= '<option value="detail" ';if(($report_type == '')||($report_type == 'detail')){ $html .= 'selected="selected"'; } $html .= '>Detail</option>';
	$html .= '<option value="sales_per_agent_per_item" ';if($report_type == 'sales_per_agent_per_item'){ $html .= 'selected="selected"'; } $html .= '>Sales Per Agent Per Item</option>';
	$html .= '<option value="sales_per_item_airtime_use" ';if($report_type == 'sales_per_item_airtime_use'){ $html .= 'selected="selected"'; } $html .= '>Sales Per Item/Airtime Use</option>';
	$html .= '<option value="calls_analysis" ';if($report_type == 'calls_analysis'){ $html .= 'selected="selected"'; } $html .= '>Calls Analysis</option>';
	$html .= '<option value="sold_warid_numbers" ';if($report_type == 'sold_warid_numbers'){ $html .= 'selected="selected"'; } $html .= '>WARID NUMBERS</option>';
	$html .= '</select></label>';
	
	return $html;
}

function display_perfomance_dropdown($perfomance_report_type){
	
	$html = '<label> Select Report Type <select name="perfomance_report_type" size="1" id="perfomance_report_type" class="select">';
	$html .= '<option value="Gross Sales" ';
		if(($perfomance_report_type == '')||($perfomance_report_type == 'sales')){ 
				$html .= 'selected="selected"'; 
			} 
				$html .= '>Gross Sales</option>';
	$html .= '<option value="Commission" ';
		if($perfomance_report_type == 'commission'){ 
		$html .= 'selected="selected"'; 
		} 
			$html .= '>Commission</option>';
			$html .= '</select></label>';
	
	return $html;
}

function display_items_dropdown($item_type){
	
	$html = '<label> Item Sold 
	<select name="item_sold" size="1" id="item_sold" class="select">';
	$html .= '<option value=""';if($item_type == ''){ $html .= 'selected="selected"'; } $html .= '>All Items</option>';
	$html .= '<option value="Phone [Chali]" ';if($item_type == 'Phone [Chali]'){ $html .= 'selected="selected"'; } $html .= '>Phone [Chali]</option>';
	$html .= '<option value="Phone" ';if($item_type == 'Phone'){ $html .= 'selected="selected"'; } $html .= '>Phone</option>';
	$html .= '<option value="Phone [DaboLine]" ';if($item_type == 'Phone [DaboLine]'){ $html .= 'selected="selected"'; } $html .= '>Phone [DaboLine]</option>';
	$html .= '<option value="Modem" ';if($item_type == 'Modem'){ $html .= 'selected="selected"'; } $html .= '>Modem</option>';
	$html .= '<option value="Airtime" ';if($item_type == 'Airtime'){ $html .= 'selected="selected"'; } $html .= '>Airtime</option>';
	$html .= '</select></label>';
	return $html;
}


function display_upsell_report_type_dropdown($report_type){
	
	$html = '<label> Report Type <select name="report_type" size="1" id="report_type" class="select">';
	$html .= '<option value="detail" ';if(($report_type == '')||($report_type == 'detail')){ $html .= 'selected="selected"'; } $html .= '>Detail</option>';
	$html .= '<option value="call_analysis" ';if($report_type == 'call_analysis'){ $html .= 'selected="selected"'; } $html .= '>Call Analysis</option>';
	$html .= '<option value="calls (BI Extract)" ';if($report_type == 'calls (BI Extract)'){ $html .= 'selected="selected"'; } $html .= '>Calls (BI Extract)</option>';
	$html .= '<option value="sales_item_summary" ';if($report_type == 'sales_item_summary'){ $html .= 'selected="selected"'; } $html .= '>Sales by Item Summry</option>';
	$html .= '</select></label>';
	
	return $html;
}


function display_related_object_dropdown($related_object){
	
	$html = '<label> Related Object <select name="relatedto" id="relatedto" class="select" onchange="javascript:objectdiv(this.value)">';
	$html .= '<option value="" selected="selected">Select Object</option>';
	$html .= '<option value="Accounts">Accounts</option>';
	$html .= '<option value="Leads">Leads</option>';
	$html .= '</select></label>';
	
	return $html;
}

function display_cca_agent_dropdown($selected, $style){
	
	custom_query::select_db('attendance');
	
	$style = 'select';
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			cc_cca.name
		FROM
			cc_cca
		WHERE
			cc_cca.deleted = 0
		ORDER BY 
			cc_cca.name aSC
			";
			
	$agents = $myquery->multiple($query);
	
	$html = '
		<label class="'.$style.'">
			Select Agents <select name="agents[]" size="3" multiple="multiple"  id="agents[]" class="'.$style.'">
			<option value="%%" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>ALL AGENTS</option>
			';
			
	foreach($agents as $agent){
		$html .= '
			<option value="'.$agent[name].'" '; if(in_array($agent[name],$selected)){$html .= 'selected="selected"';} $html .= '>'.$agent[name].'</option>';
	}
	
	$html .= '
		</select></label>
	';
	
	return $html;
}

function display_parent_accounts_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
		/*
	if($_GET['report']=='broadband_account_status'){ $query .= "accounts_cstm.crn_c as parent_acc "; }
	if($_GET['report']=='broad_band_cases'){ $query .= "accounts_cstm.crn_c as parent_acc "; }
	else{ $query .= "accounts_cstm.mem_id_c as parent_acc "; }
	*/
	
	$query = "
		SELECT 
			accounts.name,
			trim(accounts_cstm.mem_id_c) as parent_acc
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
		WHERE
			accounts_cstm.mem_id_c  != '' AND
			accounts.deleted = 0
		GROUP BY
			parent_acc
		ORDER BY
			name asc
	";
	
	$accounts_list = $myquery->multiple($query);
	
	$html = '<label> Select Account <select name="account_id" size="1" id="account_id" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[parent_acc].'" ';
		if($selected == $account[parent_acc]){
			$html .= 'selected="selected"';
		} $html .= '>'.$account[name].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_all_accounts_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$query = "
		SELECT 
			accounts.name, 
			accounts_cstm.crn_c as account_id 
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
		WHERE 
			trim(accounts_cstm.crn_c) != '' AND
			accounts.deleted = 0
		ORDER BY
			name asc
	";
	
	$accounts_list = $myquery->multiple($query);
	
	$html = '<label> Select Account <select name="account_id" size="1" id="account_id" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		if(trim($account[name]) == '') { $account[name] = 'BLANK ACCOUNT NAME'; }
		$html .= '<option value="'.$account[account_id].'" ';
		if($selected == $account[account_id]){
			$html .= 'selected="selected"';
		} $html .= '>'.$account[name].' - '.$account[account_id].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_queue_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$queues = $myquery->multiple("
		SELECT 
		  qs_queues.id,
		  qs_queues.name
		FROM
		 qs_queues
		WHERE
		qs_queues.deleted = '0'
	");
	
	$html = '<label> Select queue <select multiple="multiple" name="queues[]" size="4" id="queues[]" class="select">';
	$html .= '<option value="" '; if(count($selected)==0){$html .= 'selected="selected"';} $html .= '>ANY QUEUE</option>';
	foreach($queues as $queue){
		$html .= '<option value="'.$queue[id].'" ';
		if(in_array($queue[id],$selected)){
			$html .= 'selected="selected"';
		} $html .= '>'.$queue[name].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_platform_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$platforms = $myquery->multiple("select distinct platform_c as platform from accounts_cstm");
	
	$html = '<label> Select Platform <select name="platform" size="1" id="platform" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ANY PLATFORM</option>';
	foreach($platforms as $platform){
		$html .= '<option value="'.$platform[platform].'" ';
		if($selected == $platform[platform]){
			$html .= 'selected="selected"';
		} $html .= '>'.$platform[platform].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_status_leads_dropdown($selected){
	
	custom_query::select_db('wimax');
	
	$myquery = new custom_query();
	
	$statuses = $myquery->multiple("select distinct `status` from leads");
	
	$html = '<label> Select CURRENT Status <select name="leads_status" size="1" id="leads_status" class="select">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ANY STATUS</option>';
	$html .= '<option value="blank" >No Contract</option>';
	foreach($statuses as $status){
		$html .= '<option value="'.$status[status].'" ';
		if($selected == $status[status]){
			$html .= 'selected="selected"';
		} $html .= '>'.$status[status].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_crbt_subjects_dropdown($selected, $style){
	
	custom_query::select_db('reportscrm');
	
	$style = 'select';
	$myquery = new custom_query();
	
	$query = "
		SELECT subsubcategory as crbt_subject FROM `subsubcategory` WHERE `cat_id` = '2' AND `subcategory` = 'Ringback Tunes' ORDER BY `subcategory`
			";
	$crbt_subjects = $myquery->multiple($query);
	$html = '<label class="'.$style.'"> CRBT Wrap Up <select name="crbt_subjects[]" size="3" multiple="multiple"  id="crbt_subject[]" class="'.$style.'">
			<option value="%%" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>ALL SUBJECCTS</option>
			';
	foreach($crbt_subjects as $crbt_subject){
		$html .= '
		<option value="'.$crbt_subject[crbt_subject].'" '; if(in_array($crbt_subject[crbt_subject],$selected)	){$html .= 'selected="selected"';} $html .= '>'.$crbt_subject[crbt_subject].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_subject_type_dropdown($selected){

	$html = '
	<label>Subject Type: <select name="subject_type" id="subject_type" class="select">
	<option value="" '; if($selected == ""){$html .= 'selected="selected"';} $html .= '>All Types</option>
	<option value="Negative Feedback" '; if($selected == "Negative Feedback"){$html .= 'selected="selected"';} $html .= '>Complaints</option>
	<option value="Inquiry" '; if($selected == "Inquiry"){$html .= 'selected="selected"';} $html .= '>Inquiries</option>
	</select>
	</label>
	';
	
	return $html;
}

function standard_billing_textbox($entered){
	$valuez = 354;
	$html = '<label class="textbox">Target Value<input class="textbox" name="target_value" id="target_value" type="text"';
	if($entered)
	{
		$html .='value="'.$entered.'"';
	}else 
	{
		 $html .='value="354"';
	}
	$html .= '/></label>';
	return $html;
}

function measure_options_dropdown($selected){
	$measures = array(
					'>=' => 'Greater Than or Equal to',
					'<' => 'Less Than',
					'=' => 'Equal To'
					);
	$html = '<select name="measure_options" class="select">';
    foreach($measures as $key=>$value){
        if($selected == $key){
            $html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
        }else{
            $html .= '<option value="'.$key.'">'.$value.'</option>';
        }
    }
    $html .= '</select>';
    
    return $html;
}

function display_telesale_dropdown(){
	custom_query::select_db('telesales');
	$myquery = new custom_query();
	$query = "SELECT
				users.user_name as agent
				FROM
				users
			 ";
	 $agents = $myquery->multiple($query);
	$html = '<select name="agent" id="agent" class="select">';
	$html .= '<option value="" selected="selected">All Agents</option>';
	foreach($agents as $row){
		$html .= '<option value="'.$row[agent].'">'.$row[agent].'</option>';
	}
	$html .= '</select>';
	
	return $html;
}

function display_ussd_service_code_dropdown($selected){
	$service_codes = array(
						 ''=>'ALL',
						 '*100'=>'*100#',
						 '*157'=>'*157#',
				         '*144'=>'*144#',
						 '*163'=>'*163#'
					);
	
	$html = '<label>Service code: <select name="ussd_service_code" id="ussd_service_code" class="select">';
	foreach($service_codes as $code_value=>$code_label){
		$html .= '<option value="'.$code_value.'"';
		if($selected == $code_value){
			 $html .= ' selected="selected"';
		} 
		$html .= '>'.$code_label.'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_ussd_complete_state_dropdown($selected){
	$complete_states = array(
						   ''=>'All',
						   'PROCESSED'=>'Complete',
						   'NOT PROCESSED'=>'Incomplete'
						   );
	
	$html = '<label>Completion: <select name="complete_state" id="complete_state" class="select">';
	foreach($complete_states as $state_value=>$state_label){
		$html .= '<option value="'.$state_value.'"';
		if($selected == $state_value){
			 $html .= ' selected="selected"';
		} 
		$html .= '>'.$state_label.'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_period_dropdown($selected){
	$periods = array(
					   'monthly'=>'Monthly',
					   'daily'=>'Daily',
					   'agent_perfomance'=>'Agent Perfomance',
					);
	
	$html = '<label>Period Grouping: <select name="period_grouping" id="period_grouping" class="select">';
	foreach($periods as $period_value=>$period_label){
		$html .= '<option value="'.$period_value.'"';
		if($selected == $period_value){
			 $html .= ' selected="selected"';
		} 
		$html .= '>'.$period_label.'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}



function wrap_up_no_dropdown($selected){
	$types = array(
						//''=>'',
						'Summary'=>'Summary',
					   	'raw'=>'Raw Data'
					   
					);
	$html = '<label>Report Type: <select name="reporttype" id="reporttype" class="select">';
    foreach($types as $key=>$value){
        if($selected == $key){
            $html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
        }else{
            $html .= '<option value="'.$key.'">'.$value.'</option>';
        }
    }
    $html .= '</select>';
    
    return $html;
}

function customer_knowledge_dropdown($selected){
	$types = array(
					   'summary'=>'Summary',
					   'detail'=>'Detailed call list',
					   'leads'=>'Leads',
					   'std_info'=>'Student Info'
					);
	$html = '<label>Report Type: <select name="reporttype" id="reporttype" class="select">';
    foreach($types as $key=>$value){
        if($selected == $key){
            $html .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
        }else{
            $html .= '<option value="'.$key.'">'.$value.'</option>';
        }
    }
    $html .= '</select>';
    
    return $html;
}

function display_bc_report_type_dropdown($report_type){
	
	$html = '<label> Select Report Type <select name="report_type" size="1" id="report_type" class="select">';
	$html .= '<option value="ByItemCat" ';if($report_type == 'ByItemCat'){ $html .= 'selected="selected"'; } $html .= '>By Item Cat</option>';
	$html .= '<option value="By_Item" ';if($report_type == 'By_Item'){ $html .= 'selected="selected"'; } $html .= '>By Item</option>';
	$html .= '<option value="By_Region" ';if($report_type == 'By_Region'){ $html .= 'selected="selected"'; } $html .= '>By Region</option>';
	$html .= '</select></label>';
	
	return $html;
}


 function displayDaysToExpire_box($chosen){
           
            $html = '<input type="text" id="expiry_days" name="expiry_days"';
            if($chosen){
                $html .='value="'.$chosen.'"';
            } else{
                $html .='value=7';
            }
            $html .='>';
            return $html;
        }

/*function display_subcategory_dropdown(){
	custom_query::select_db('reportscrm');
	$myquery = new custom_query();
	$query = "SELECT
				subcategory.subcategory
				FROM
				subcategory 
				where subcategory.sub_cat_status = 'active'
				order by subcategory ASC
			 ";
			//echo $query 
	 $subcategories = $myquery->multiple($query);
	$html = '<select name="subcategory" id="subcategory" class="select" onChange="autoSubmit();">';
	$html .= '<option value="" selected="selected">All SubCategories</option>';
	foreach($subcategories as $row){
		$html .= '<option value="'.$row[subcategory].'">'.$row[subcategory].'</option>';
	}
	$html .= '</select>';
	
	return $html;
}*/


function display_subcategory_dropdown($select_subcat){
	custom_query::select_db('reportscrm');
	$myquery = new custom_query();
	$query = "SELECT
				subcategory.subcategory
				FROM
				subcategory 
				where subcategory.sub_cat_status = 'active'
				order by subcategory ASC
			 ";
	$subcategories = $myquery->multiple($query);
	$html = '<label> Wrap-up Category <select name="subcategory" id="subcategory" class="select" onChange="autoSubmit();">';
	$html .= '<option value="" selected="selected">All SubCategories</option>';
	foreach($subcategories as $row){
		if($select_subcat == $row[subcategory]){
			$html .= '<option value="'.$row[subcategory].'" selected="selected">'.$row[subcategory].'</option>';
		}else{
			$html .= '<option value="'.$row[subcategory].'">'.$row[subcategory].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function display_subject_dropdown($select_subject){
	custom_query::select_db('reportscrm');
	$myquery = new custom_query();
	$query = "SELECT
					subsubcategory.subsubcategory as subject
					FROM
					subsubcategory where subsubcategory.subject_status = 'active' 
					and subsubcategory.subcategory = '$_POST[subcategory]'
			";
	$subjects = $myquery->multiple($query);
	$html = '<select name="subject" id="subject" class="select" onChange="autoSubmit();">';
	$html .= '<option value="" selected="selected">Select Subjects</option>';
	foreach($subjects as $row){
	if($select_subject == $row[subject]){
			$html .= '<option value="'.$row[subject].'" selected="selected">'.$row[subject].'</option>';
		}else{
			$html .= '<option value="'.$row[subject].'">'.$row[subject].'</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
					
}

function display_sms_feedback_agents($selected){
	custom_query::select_db('smsfeedback');
	$myquery = new custom_query();
	
	$query = "
		select
			distinct (last_modified_by) as modified_by
		from
			feedback
		where
			last_modified_by != ''
		order by
			last_modified_by ASC
	";
	$agents = $myquery->multiple($query);
	foreach($agents as $id=>$row){
		if($agents[str_replace(array(" "),"",strtolower($row[modified_by]))] == ''){
			$agents[str_replace(array(" "),"",strtolower($row[modified_by]))] = $row[modified_by];
			
			$clean_agents[$row[modified_by]] = $row[modified_by];
		}
		
		unset($agents[$id],$row);
	}
	unset($agents);
	
	return dropdown($label='Handled by', $name='last_modified_by', $onchange_call, $selected, $options=$clean_agents, $class='select', $size=5, $multiple=true);
	
}

function display_sms_feedback_status($selected){
	
	$statuses = array(
			'Activity created'=>'Activity created',
			'Batch Resolution'=>'Batch Resolution',
			'Case created'=>'Case created',
			'Duplicate'=>'Duplicate',
			'Irrelevant'=>'Irrelevant',
			'Read'=>'Read',
			'Unread'=>'Unread'
	);
	
	return dropdown($label='Status', $name='status', $onchange_call, $selected, $options=$statuses, $class='select', $size=1, $multiple);
}

function display_repeatcalls_datatype_dropdown($selected){
	
	$types = array(
		'Repeat Callers by Agent'=>'Repeat Callers by Agent',
		'Repeat Callers by MSISDN'=>'Repeat Callers by MSISDN',
	);
	
	return dropdown($label='Data Type', $name='repeat_calls_data_type', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_ivr_choices_last_option_group($selected){
	
	custom_query::select_db('ivrperformance');
	$myquery = new custom_query();
	
	$query = "
		SELECT
			DISTINCT(asterisk_cdrs.last_option_group) as last_option
		FROM
			asterisk_cdrs
--		WHERE
--			asterisk_cdrs.date_entered
	";
	
	$lastoptions = $myquery->multiple($query);
	
	foreach($lastoptions as $row){
	$group[$row['last_option']] = $row['last_option'];
	}
	
	return dropdown($label='Last option groups', $name='last_option_groups', $onchange_call, $selected, $options=$group, $class='select', $size=5, $multiple=true);
}

function display_wrapup_caller_groups($selected){
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			wrapupcall_type.id,
			wrapupcall_type.name
		FROM
			wrapupcall_type
		WHERE
			wrapupcall_type.status = 'Active'
	";
	
	$list = $myquery->multiple($query,'ccba02.reportscrm');
	
	foreach($list as $row){
		$caller_group_options[$row[id]] = $row[name];
	}
	
	return dropdown($label='Caller groups', $name='caller_groups[]', $onchange_call, $selected, $options=$caller_group_options, $class='select', $size=4, $multiple=true);
}


function display_courier_report_companies($selected){

	custom_query::select_db('ccba01.waridpesacouriers');
	$myquery = new custom_query();
	
	$query = "
		SELECT * FROM company WHERE company_status = '1'
	";
	
	$companies = $myquery->multiple($query);
	
	foreach($companies as $row){
	$company_options[$row['company_name']] = $row['company_name'];
	}
	
	return dropdown($label='Company', $name='company', $onchange_call, $selected, $options=$company_options, $class='select', $size=5, $multiple=false);
}

function display_courier_report_courier($selected){

	custom_query::select_db('ccba01.waridpesacouriers');
	$myquery = new custom_query();
	
	$query = "
		SELECT * FROM couriers WHERE couriers_status = '1'
	";
	
	$couriers = $myquery->multiple($query);
	
	foreach($couriers as $row){
	$courier_options[$row['couriers_name']] = $row['couriers_name'];
	}
	
	return dropdown($label='Courier', $name='courier', $onchange_call, $selected, $options=$courier_options, $class='select', $size=5, $multiple=false);
}

function show_wimax_site_dropdown($selected){
	
	$wimax_site_options =array (
		'To be filled in later' => 'To be filled in later',
		'FIBRE ACCESS' => 'NO DAP (FIBRE)',
		'INDOOR_CPE' => 'INDOOR (MOBILE) CPE',
		'CRANE_CHAMBERS-MDKP1002-AP01002'=>'CRANE CHAMBERS',
		'CRESTED TOWER-MDKP1007-AP01007'=>'CRESTED TOWERS',
		'EMKAHOUSE-MDKP1011-AP01011'=>'EMKA HOUSE',
		'ELITE_APT-MDKP1014-AP01014'=>'ELITE APARTMENTS',
		'KIBULI-MDKP1016-AP01016'=>'KIBULI',
		'NAKASERO_RD-MDKP1020-AP01020'=>'NAKASERO_RD',
		'MENGO_KISENYI-MDKP1022-AP01022'=>'MENGO KISENYI',
		'NAMUWONGO-MDKP1024-AP01024'=>'NAMUWONGO',
		'MENGO-MDKP1029-AP01029'=>'MENGO',
		'NSAMBYA_WEST-MDKP1031-AP01031'=>'NSAMBYA WEST',
		'KISUGU-MDKP1032-AP01032'=>'KISUGU',
		'NAGURU-MDKP1035-AP01035'=>'NAGURU',
		'RUBAGA_CTDRAL-MDKP1041-AP01041'=>'RUBAGA CATHEDRAL',
		'NDEEBA-MDKP1042-AP01042'=>'NDEEBA',
		'MAWANDA-MDKP1047-AP01047'=>'MAWANDA',
		'MUYENGA-MDKP1056-AP01056'=>'MUYENGA',
		'KYAMBOGO-MDKP1058-AP01058'=>'KYAMBOGO',
		'KYEBANDO-MDKP1062-AP01062'=>'KYEBANDO',
		'ST_AGUSTINE-MDKP1063-AP01063'=>'ST AGUSTINE',
		'NAMASUBA-MDKP1066-AP01066'=>'NAMASUBA',
		'MASAJAZANA-MDKP1067-AP01067'=>'MASAJAZANA',
		'KIZUNGU-MDKP1068-AP01068'=>'KIZUNGU',
		'BUKASA-MDKP1070-AP01070'=>'BUKASA',
		'MBUYA-MDKP1072-AP01072'=>'MBUYA',
		'KALINABIRI-MDKP1074-AP01074'=>'KALINABIRI',
		'BUNGA-MDKP1082-AP01082'=>'BUNGA',
		'LUZIRA-MDKP1083-AP01083'=>'LUZIRA',
		'BUZIGA-MDKP1093-AP01093'=>'BUZIGA',
		'KIREKA1-MDKP1096-AP01096'=>'KIREKA1',
		'NASUTI-MDMU2203-AP02203'=>'NASUTI',
		'JINJA NILE SOURCE-MDJN2302-AP02302'=>'JINJA NILE SOURCE'
	);
	
	return dropdown($label='Wimax Site', $name='wimax_site', $onchange_call, $selected, $options=$wimax_site_options, $class='select', $size=2, $multiple=false);
}

function translate_wimax_site($input){
	
		$wimax_site_options = array (
		'To be filled in later' => 'To be filled in later',
		'FIBRE ACCESS' => 'NO DAP (FIBRE)',
		'INDOOR_CPE' => 'INDOOR (MOBILE) CPE',
		'CRANE_CHAMBERS-MDKP1002-AP01002'=>'CRANE CHAMBERS',
		'CRESTED TOWER-MDKP1007-AP01007'=>'CRESTED TOWERS',
		'EMKAHOUSE-MDKP1011-AP01011'=>'EMKA HOUSE',
		'ELITE_APT-MDKP1014-AP01014'=>'ELITE APARTMENTS',
		'KIBULI-MDKP1016-AP01016'=>'KIBULI',
		'NAKASERO_RD-MDKP1020-AP01020'=>'NAKASERO_RD',
		'MENGO_KISENYI-MDKP1022-AP01022'=>'MENGO KISENYI',
		'NAMUWONGO-MDKP1024-AP01024'=>'NAMUWONGO',
		'MENGO-MDKP1029-AP01029'=>'MENGO',
		'NSAMBYA_WEST-MDKP1031-AP01031'=>'NSAMBYA WEST',
		'KISUGU-MDKP1032-AP01032'=>'KISUGU',
		'NAGURU-MDKP1035-AP01035'=>'NAGURU',
		'RUBAGA_CTDRAL-MDKP1041-AP01041'=>'RUBAGA CATHEDRAL',
		'NDEEBA-MDKP1042-AP01042'=>'NDEEBA',
		'MAWANDA-MDKP1047-AP01047'=>'MAWANDA',
		'MUYENGA-MDKP1056-AP01056'=>'MUYENGA',
		'KYAMBOGO-MDKP1058-AP01058'=>'KYAMBOGO',
		'KYEBANDO-MDKP1062-AP01062'=>'KYEBANDO',
		'ST_AGUSTINE-MDKP1063-AP01063'=>'ST AGUSTINE',
		'NAMASUBA-MDKP1066-AP01066'=>'NAMASUBA',
		'MASAJAZANA-MDKP1067-AP01067'=>'MASAJAZANA',
		'KIZUNGU-MDKP1068-AP01068'=>'KIZUNGU',
		'BUKASA-MDKP1070-AP01070'=>'BUKASA',
		'MBUYA-MDKP1072-AP01072'=>'MBUYA',
		'KALINABIRI-MDKP1074-AP01074'=>'KALINABIRI',
		'BUNGA-MDKP1082-AP01082'=>'BUNGA',
		'LUZIRA-MDKP1083-AP01083'=>'LUZIRA',
		'BUZIGA-MDKP1093-AP01093'=>'BUZIGA',
		'KIREKA1-MDKP1096-AP01096'=>'KIREKA1',
		'NASUTI-MDMU2203-AP02203'=>'NASUTI',
		'JINJA NILE SOURCE-MDJN2302-AP02302'=>'JINJA NILE SOURCE',
		//REVERSE
		'To be filled in later' => 'To be filled in later',
		'NO DAP (FIBRE)' => 'FIBRE ACCESS',
		'INDOOR (MOBILE) CPE' => 'INDOOR_CPE',
		'CRANE CHAMBERS' => 'CRANE_CHAMBERS-MDKP1002-AP01002',
		'CRESTED TOWERS' => 'CRESTED TOWER-MDKP1007-AP01007',
		'EMKA HOUSE' => 'EMKAHOUSE-MDKP1011-AP01011',
		'ELITE APARTMENTS' => 'ELITE_APT-MDKP1014-AP01014',
		'KIBULI' => 'KIBULI-MDKP1016-AP01016',
		'NAKASERO_RD' => 'NAKASERO_RD-MDKP1020-AP01020',
		'MENGO KISENYI' => 'MENGO_KISENYI-MDKP1022-AP01022',
		'NAMUWONGO' => 'NAMUWONGO-MDKP1024-AP01024',
		'MENGO' => 'MENGO-MDKP1029-AP01029',
		'NSAMBYA WEST' => 'NSAMBYA_WEST-MDKP1031-AP01031',
		'KISUGU' => 'KISUGU-MDKP1032-AP01032',
		'NAGURU' => 'NAGURU-MDKP1035-AP01035',
		'RUBAGA CATHEDRAL' => 'RUBAGA_CTDRAL-MDKP1041-AP01041',
		'NDEEBA' => 'NDEEBA-MDKP1042-AP01042',
		'MAWANDA' => 'MAWANDA-MDKP1047-AP01047',
		'MUYENGA' => 'MUYENGA-MDKP1056-AP01056',
		'KYAMBOGO' => 'KYAMBOGO-MDKP1058-AP01058',
		'KYEBANDO' => 'KYEBANDO-MDKP1062-AP01062',
		'ST AGUSTINE' => 'ST_AGUSTINE-MDKP1063-AP01063',
		'NAMASUBA' => 'NAMASUBA-MDKP1066-AP01066',
		'MASAJAZANA' => 'MASAJAZANA-MDKP1067-AP01067',
		'KIZUNGU' => 'KIZUNGU-MDKP1068-AP01068',
		'BUKASA' => 'BUKASA-MDKP1070-AP01070',
		'MBUYA' => 'MBUYA-MDKP1072-AP01072',
		'KALINABIRI' => 'KALINABIRI-MDKP1074-AP01074',
		'BUNGA' => 'BUNGA-MDKP1082-AP01082',
		'LUZIRA' => 'LUZIRA-MDKP1083-AP01083',
		'BUZIGA' => 'BUZIGA-MDKP1093-AP01093',
		'KIREKA1' => 'KIREKA1-MDKP1096-AP01096',
		'NASUTI' => 'NASUTI-MDMU2203-AP02203',
		'JINJA NILE SOURCE' => 'JINJA NILE SOURCE-MDJN2302-AP02302',
	);
		
	return $wimax_site_options[$input];
}

function display_courier_report_type_dropdown($selected){
	
	$types = array(
		'Delieveries by Company'=>'Delieveries by Company',
		'Delieveries by Region'=>'Delieveries by Region',
		'detail'=>'detail'
	);
	
	return dropdown($label='Select Report Type', $name='reporttype', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_telesales_commission_report_type_dropdown($selected){
	$types = array(
		'summary'=>'summary',
		'detail'=>'detail',
		'trend'=>'trend'
	);
	
	return dropdown($label='Select Report Type', $name='report_type', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_standard_report_type_dropdown($selected){
	
	$types = array(
		'summary'=>'summary',
		'detail'=>'detail',
		'both'=>'both'
	);
	
	return dropdown($label='Select Report Type', $name='reporttype', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_wrapups_topx_dropdown($selected){
	
	$types = array(
		'Weekly'=>'Weekly',
		'daily'=>'daily',
		'both'=>'both'
	);
	
	return dropdown($label='Select Report Type', $name='reporttype', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_hvild_hv_ild_upsell_feedback_segement_dropdown($selected){
	
	$types = array(
		'Diamond'=>'Diamond',
		'Platinum'=>'Platinum',
		'Gold'=>'Gold',
		'New Ultra'=>'New Ultra'
	);
	
	return dropdown($label='Select Report Type', $name='hvild_segment', $onchange_call, $selected, $options=$types, $class='select', $size=1, $multiple);
}

function display_wrap_up_data_source_drop_down($selected){
	
	$html = '
	<label class="textbox">Select a period:
	<select name="wrapup_datasource" id="wrapup_datasource" class="select">
	<option value="ccba01.reportscrm"'; if($selected == "ccba01.reportscrm" or $selected == "" ) {$html .= ' selected="selected"'; } $html .= ' >Today\'s data</option>
	<option value="ccba02.reportscrm"'; if($selected == "ccba02.reportscrm") {$html .= ' selected="selected"'; } $html .= ' >Historical data</option>
	</select>
	</label>
	';
	
	return $html;
}

function display_csat_data_source_drop_down($selected){

	$html = '
	<label class="textbox">Select a period:
	<select name="csat_data_source" id="csat_data_source" class="select">
	<option value="ccba01.smsfeedback"'; if($selected == 'ccba01.smsfeedback' or $selected == '') {$html .' selected="selected"'; } $html .= '>Today\'s data</option>
	<option value="ccba02.smsfeedback"'; if($selected == 'ccba02.smsfeedback') {$html .' selected="selected"'; } $html .= '>Historical data</option>
	</select>
	</label>
	';
	
	return $html;
}

function generate_wrapup_dependent_drop_down_javascript($form_name='form1', $subcategory_input_name='subcategory', $subject_input_name='subject'){
	custom_query::select_db('ccba01.reportscrm');
	
	$subcategory_input_name = str_replace(array('[',']',' '),'',$subcategory_input_name);
	$subject_input_name = str_replace(array('[',']',' '),'',$subject_input_name);
	
	$myquery = new custom_query();
	
	$query = "
		SELECT
			category.category,
			subsubcategory.subcategory,
			subsubcategory.subsubcategory AS `subject`
		FROM
			subsubcategory
			LEFT OUTER JOIN category ON subsubcategory.cat_id = category.cat_id
		WHERE
			subsubcategory.subject_status = 'active'
		ORDER BY
			subsubcategory.weight ASC
	";
	
	$list = $myquery->multiple($query);
	
	foreach($list as &$row){
		$categorylist[$row[category]][$row[subcategory]] = $row[subcategory];
		$subcategorylist[$row[subcategory]][$row[subject]] = $row[subject];
		unset($row);
	}
	
	foreach($categorylist as $category=>$subcategories){
		$javascript_category_switch_cases .= '
			case "'.$category.'" : ';
		
		$subcategory_index = 0;
		$javascript_category_switch_cases .= '
				document.'.$form_name.'.'.$subcategory_input_name.'.options['.$subcategory_index.']=new Option("Select a Subcategory",""); ';
		foreach($subcategories as $subcategory){
			$javascript_category_switch_cases .= '
				document.'.$form_name.'.'.$subcategory_input_name.'.options['.++$subcategory_index.']=new Option("'.$subcategory.'","'.$subcategory.'"); ';
		}
		$javascript_category_switch_cases .= '
			break; ';
	}
	
	unset($category,$subcategory);
	foreach($subcategorylist as $subcategory=>$subjects){
		$javascript_subcategory_switch_cases .= '
			case "'.$subcategory.'" : ';
		
		$subject_index = 0;
		$javascript_subcategory_switch_cases .= '
				document.'.$form_name.'.'.$subject_input_name.'.options['.$subject_index.']=new Option("Select a Subject",""); ';
		foreach($subjects as $subject){
			$javascript_subcategory_switch_cases .= '
				document.'.$form_name.'.'.$subject_input_name.'.options['.++$subject_index.']=new Option('.json_encode($subject).','.json_encode($subject).'); ';
		}
		$javascript_subcategory_switch_cases .= '
			break; ';
	}
	
	$JAVASCRIPT .= '
	/*SUB CATEGORY DROPDOWN*/
	function subcategory_dropdown(input_category){
		document.'.$form_name.'.'.$subcategory_input_name.'.options.length = 0;
		document.'.$form_name.'.'.$subject_input_name.'.options.length = 0;
		document.'.$form_name.'.'.$subject_input_name.'.options[0]=new Option("Select a Subject","");
		
		switch(input_category){
			'.$javascript_category_switch_cases.'
		}
		
		return true;
	}
	';
	
	$JAVASCRIPT .= '
	/*SUBJECT DROPDOWN*/
	function subject_dropdown(input_subcategory){
		document.'.$form_name.'.'.$subject_input_name.'.options.length = 0;
		switch(input_subcategory){
			'.$javascript_subcategory_switch_cases.'
		}
		return true;
	}
	';
	
	return $JAVASCRIPT;
}

?>