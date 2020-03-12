<?php
function get_data_complaints_solved_in_x_days($dates, $billing=true, $solved_in){
	$myquery = new custom_query();
	
	$from = $dates[0]; $to = $dates[2];
	
	if($billing == false) { $like_suffix = ' NOT '; }
	
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		FROM
			cases
			inner join cases_cstm on cases.id = cases_cstm.id_c
			inner join cases_audit on (cases.id = cases_audit.parent_id and cases_audit.field_name = 'status' and cases_audit.after_value_string = 'Closed' and cases_audit.before_value_string != 'Closed')
		where
			cases.deleted = 0 AND
			cases.status = 'Closed' AND
			cases_cstm.subject_setting_c ".$like_suffix." LIKE '%bill%' AND
			DATEDIFF(cases_audit.date_created,cases.date_entered) <= ".$solved_in." AND
			cases.date_entered BETWEEN date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour)
	";
	
	return $myquery->single($query);
}

function get_data_complaints($dates, $billing=true){
	$myquery = new custom_query();
	
	$from = $dates[0]; $to = $dates[2];
	
	if($billing == true) { $query_addition = " cases_cstm.subject_setting_c LIKE '%bill%' AND "; }
	
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(date_add(cases.date_entered, interval 3 hour),7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		FROM
			cases
			inner join cases_cstm on cases.id = cases_cstm.id_c
		where
			cases.deleted = 0 AND
			".$query_addition."
			cases.date_entered BETWEEN date_sub('".$from." 00:00:00', interval 3 hour) AND date_sub('".$to." 23:59:59', interval 3 hour)
	";
	
	return $myquery->single($query);
}
?>