<?php
function get_gsm_complaints_solved_in_x_days($dates, $billing=true, $solved_in){
	$myquery = new custom_query();
	
	$from = $dates[0]; $to = $dates[2];
	
	if($billing == false) { $like_suffix = ' NOT '; }
	
	custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			count(if(left(reportscrm.createdon,7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(reportscrm.createdon,7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(reportscrm.createdon,7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		FROM
			reportscrm
			INNER JOIN reportscrm.caseresolution ON (reportscrm.casenum = caseresolution.casenum)
		where
			reportscrm.casetype ".$like_suffix." LIKE '%Customer Complaint (Billing and Invoice Issues)%' AND 
			DATEDIFF(caseresolution.actualend,reportscrm.createdon) <= ".$solved_in." AND
			createdon between '".$from."' AND '".$to."'
	";
	
	return $myquery->single($query);
}

function get_gsm_complaints($dates, $billing=true){
	$myquery = new custom_query();
	
	$from = $dates[0]; $to = $dates[2];
	
	if($billing == true) { $query_addition = " reportscrm.casetype LIKE '%Customer Complaint (Billing and Invoice Issues)%' AND "; }
	
	custom_query::select_db('reportscrm');
	
	$query = "
		SELECT
			count(if(left(reportscrm.createdon,7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(reportscrm.createdon,7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(reportscrm.createdon,7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		FROM
			reportscrm
		where
			".$query_addition."
			createdon between '".$from."' AND '".$to."'
	";
	
	return $myquery->single($query);
}

function get_service_restoration_wrapups($dates){
	$myquery = new custom_query();
	
	$from = $dates[0]; $to = $dates[2];
	
	if($billing == false) { $like_suffix = ' NOT '; }
	
	if(strtotime($dates[1]) >= strtotime('2012-01-01')){
		$db_source = 'ccba02.reportscrm';
	}else{
		$db_source = 'ccba01.reportscrm';
	}
	
	$query = "
		SELECT
			count(if(left(reportsphonecalls.createdon,7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(reportsphonecalls.createdon,7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(reportsphonecalls.createdon,7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON reportsphonecalls.subject = subsubcategory.subsubcategory AND reportsphonecalls.wrapupsubcat = subsubcategory.subcategory
		where
			subsubcategory.subject_type='Service Restoration Request' AND
			reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
	";
	
	return $myquery->single($query,$db_source);
}
?>