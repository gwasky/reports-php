<?
function generate_cca_attendance_by_data_team_supervisor($from, $to, $agents, $shifts, $supervisors, $teams){
	
	custom_query::select_db('attendance');
	$myquery = new custom_query();
	//require_once('config.attendance.php');
	$query = "
		SELECT 
		  cc_supervisor.name as supervisor_name,
		  cc_teams.name as team_name,
		  cc_cca.name as cca_name,
		  cc_attendance_cstm.checkout_time_c as checkout_time,
		  cc_attendance.name as attendance_date,
		  cc_attendance.reasons_for_not_completing,
		  cc_attendance.shift,
		  cc_attendance.reasons_for_not_attending,
		  cc_attendance.attendance_status,
  		  if(cc_attendance.attendance_status in ('Didnt Attend'),cc_attendance.attendance_status,cc_attendance.shift_completion_status) as shift_completion_status
		FROM
		 cc_teams
		 INNER JOIN cc_supervisor ON (cc_teams.cc_supervisor_id_c=cc_supervisor.id)
		 INNER JOIN cc_teams_cc_cca_c ON (cc_teams_cc_cca_c.cc_teams_ca453c_teams_ida=cc_teams.id)
		 INNER JOIN cc_cca ON (cc_cca.id=cc_teams_cc_cca_c.cc_teams_cd298acc_cca_idb)
		 INNER JOIN cc_cca_cc_attendance_c ON (cc_cca_cc_attendance_c.cc_cca_cc_3168ecc_cca_ida=cc_cca.id)
		 INNER JOIN cc_attendance ON (cc_cca_cc_attendance_c.cc_cca_cc_940eendance_idb=cc_attendance.id)
		 INNER JOIN cc_attendance_cstm ON (cc_attendance.id=cc_attendance_cstm.id_c)
		WHERE
		  cc_attendance.deleted = 0 AND
		  cc_cca.deleted = 0 AND
		  cc_teams_cc_cca_c.deleted = 0 AND
		  cc_cca_cc_attendance_c.deleted = 0
	";
	
	if($from){
		$query .= " AND cc_attendance.name >= '".$from."'";
	}else{
		$query .= " AND cc_attendance.name >= '".date('Y-m-')."01"."'";
		$_POST[from] = date('Y-m-')."01";
	}
	
	if($to){
		$query .= " AND cc_attendance.name <= '".$to."'";
	}else{
		$query .= " AND cc_attendance.name <= '".date('Y-m-d')."'";
		$_POST[to] = date('Y-m-d');
	}
	
	if($supervisors){
		$query .= " AND cc_supervisor.name = '".$supervisors."'";
	}
	
	
	if(($agents) && (!in_array('%%',$agents))){
		$query .= "AND (";
		foreach($agents as $count=>$agent){
			$query .= "cc_cca.name = '".$agent."'";
			if(count($agents) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ")";
	}
	
	if($teams){
		$query .= " AND cc_teams.name = '".$teams."'";
	}
	
	$query .= " ORDER BY cc_attendance.name,cc_attendance.shift,cc_teams.name ASC ";
	
	//echo $query."<br>";
	
	$records = $myquery->multiple($query);
	
	return display_cca_attendance_by_data_team_supervisor($records);
}

function display_cca_attendance_by_data_team_supervisor($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th></th>
				<th>Date</th>
				<th>Shift</th>
				<th>Team</th>
				<th>Agent</th>
				<th>Supervisor</th>
				<th>Attended?</th>
				<th>Completed?</th>
				<th>Check Out</th>
				<th>Reason for not attending</th>
				<th>Reason for not completing</th>
			</tr>
	';
	
	foreach($report as $row){
		$html .= '
			<tr>
				<td class="values">'.++$i.'</td>
				<td class="text_values">'.$row[attendance_date].'</td>
				<td class="text_values">'.$row[shift].'</td>
				<td class="text_values">'.$row[team_name].'</td>
				<td class="text_values">'.$row[cca_name].'</td>
				<td class="text_values">'.$row[supervisor_name].'</td>
				<td class="text_values">'.$row[attendance_status].'</td>
				<td class="text_values">'.$row[shift_completion_status].'</td>
				<td class="text_values">'.$row[checkout_time].'</td>
				<td class="wrap_text">'.$row[reasons_for_not_attending].'</td>
				<td class="wrap_text">'.$row[reasons_for_not_completing].'</td>
			</tr>
		';
	}
	
	//close tr td and table
	$html .= '
		</table>
	';
	
	return $html;
}
?>