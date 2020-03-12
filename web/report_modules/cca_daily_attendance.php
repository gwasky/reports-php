<?php


function generate_daily_attendance_summary($from,$to,$shift,$supervisors,$teams){
	require_once('config.attendance.php');
	$myquery = new custom_query();
	if(!$from){ $from = date("Y-m-d", strtotime("-1 day")); }
	if(!$to){ $to = date("Y-m-d", strtotime("-1 day")); }
	$query = "SELECT 
  		cc_attendance.name AS attendance_date,
  		COUNT(case when cc_attendance.reasons_for_not_attending = 'Sick' or cc_attendance.reasons_for_not_attending = 'Bereaved' or cc_attendance.reasons_for_not_attending = 'Unknown' or cc_attendance.attendance_status = 'Attended' then 1 else null end) as scheduled_agents,
   		count(case when cc_attendance.reasons_for_not_attending = 'Bereaved' then 1 else null end) AS Bereaved,
   		count(case when cc_attendance.reasons_for_not_attending = 'Sick' then 1 else null end) AS SickNess,
   		count(case when cc_attendance.reasons_for_not_attending = 'Leave' then 1 else null end) AS Annual_Leave,
		count(case when cc_attendance.reasons_for_not_attending = 'Day Off' then 1 else null end) AS day_off,
   		count(case when cc_attendance.attendance_status = 'Didnt Attend' and cc_attendance.reasons_for_not_attending = 'Unknown' then 1 else null end) AS Absconding
		FROM
  		cc_teams
 		INNER JOIN cc_supervisor ON (cc_teams.cc_supervisor_id_c = cc_supervisor.id)
  		INNER JOIN cc_teams_cc_cca_c ON (cc_teams.id = cc_teams_cc_cca_c.cc_teams_ca453c_teams_ida)
  		INNER JOIN cc_cca ON (cc_teams_cc_cca_c.cc_teams_cd298acc_cca_idb = cc_cca.id)
  		INNER JOIN cc_cca_cc_attendance_c ON (cc_cca.id = cc_cca_cc_attendance_c.cc_cca_cc_3168ecc_cca_ida)
  		INNER JOIN cc_attendance ON (cc_cca_cc_attendance_c.cc_cca_cc_940eendance_idb = cc_attendance.id)
  		INNER JOIN cc_attendance_cstm ON (cc_attendance.id = cc_attendance_cstm.id_c)
		WHERE
  		cc_cca_cc_attendance_c.deleted = 0 AND 
  		cc_teams_cc_cca_c.deleted = 0 AND 
  		cc_attendance.deleted = 0 AND 
  		cc_cca.deleted = 0 AND 
  		cc_teams.deleted = 0 AND 
  		cc_supervisor.deleted = 0 ";
		
	if($from){
		$query .= " AND cc_attendance.name >= '".$from."'";
	}
	if($to){
		$query .= " AND cc_attendance.name <='".$to."'";
	}
	if($shift){
		$query .= " AND cc_attendance.shift = '".$shift."'";
	}
	if($supervisors){
		$query .= "  AND cc_supervisor.name = '".$supervisors."'";
	}
	if($teams){
		$query .= "  AND cc_teams.name = '".$teams."'";
	}
	
	$query .=" GROUP BY attendance_date";
	
	$daily_records = $myquery->multiple($query);
	
	return display_daily_report($daily_records);
}

function display_daily_report($report){
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
		<tr> 
			  <th>Date</th>
			  <th>Scheduled Staff Attendance</th>
			  <th>Bereavement</th>
			  <th>Sickness</th>
			  <th>Annual Leave</th>
			  <th>Day Off</th>
			  <th>Absenteeism</th>
		</tr>
	';
	foreach($report as $row)
	{
		$html .= '
			<tr>
				<td class="text_values">'.$row[attendance_date].'</td>
				<td class="text_values">'.$row[scheduled_agents].'</td>
				<td class="text_values">'.$row[Bereaved].'</td>
				<td class="wrap_text">'.$row[SickNess].'</td>
				<td class="text_values">'.$row[Annual_Leave].'</td>
				<td class="text_values">'.$row[day_off].'</td>
				<td class="text_values">'.$row[Absconding].'</td>
			</tr>';
	
	}
	return $html;
}

?>