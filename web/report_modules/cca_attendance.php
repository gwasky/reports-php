<?php

function generate_attendance_report1($report_type, $from, $to){
	
	require_once('config.attendance.php');
	$myquery = new custom_query();
	$dateRangeArray = generateDateRangeArray($from,$to);
	var_dump($dateRangeArray);
	$count = count($dateRangeArray); 
	$i = 0;
	foreach($dateRangeArray as $key=>$value)
	{ 
		while($i<$count){	
	  	$query = "SELECT 
  		cc_supervisor.name,
  		cc_teams.name AS team_name,
  		cc_cca.name AS cca_name,
		cc_attendance.shift as shift,
  		cc_attendance.name AS attendance_date,
  		cc_attendance_cstm.checkin_time_c,
  		cc_attendance_cstm.checkout_time_c
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
  		cc_supervisor.deleted = 0 and cc_attendance.name = '$dateRangeArray[$i]'";
		
		//echo $query;
		
		$Result = $myquery->multiple($query);
		$i++;
		}
		//var_dump($Result);
			
	}
return $html;
}

function generate_attendance_report($from,$to,$shift,$supervisors,$teams){
	require_once('config.attendance.php');
	$myquery = new custom_query();
	if(!$from){ $from = date("Y-m-d", strtotime("-1 day")); }
	if(!$to){ $to = date("Y-m-d", strtotime("-1 day")); }
	$report[daterange] = generateDateRangeArray($from,$to);
	$query = "SELECT 
  		cc_supervisor.name,
  		cc_teams.name AS team_name,
  		cc_cca.name AS agent,
		cc_attendance.shift as shift,
  		cc_attendance.name AS attendance_date,
  		cc_attendance_cstm.checkin_time_c,
  		cc_attendance_cstm.checkout_time_c,
		cc_attendance.attendance_status as status,
		cc_attendance.reasons_for_not_attending as reason
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
  		cc_supervisor.deleted = 0";
		
		if($from){
		$query .= " AND cc_attendance.name >= '".$from."'";
	}

	if($to){
		$query .= "AND cc_attendance.name <='".$to."'";
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
		$attendance_records = $myquery->multiple($query);
		if(isset($attendance_records)){
		foreach($attendance_records as $row)
		{
			$report[data][$row[agent]][$row[attendance_date]][status] = $row[status];
			$report[data][$row[agent]][$row[attendance_date]][shift] = $row[shift];
			$report[data][$row[agent]][$row[attendance_date]][reason] = $row[reason];
			
		}
		//var_dump($report);
		return display_attendance_hmtl($report,$dateRangeArray);}else { return dislay_blanks($report[daterange]);}
}

function display_attendance_hmtl($report){
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%"> 
			<tr  class="sortable">
			<th rowspan="2">Agent Name</th>
	';

	foreach($report[daterange] as $date){
		$html .='
			<th colspan="3">'.$date.'</th>
		';
	}
		
	$html .='
		</tr>
		<tr>
	';
	
	foreach($report[daterange] as $date){
		$html .= '
			<th>status</th>
			<th>shift</th>
			<th>Reasons</th>
		';
	}
	
	$html .='
		</tr>
	';
	
	foreach($report[data] as $agent=>$agent_data){
		$count = 0;
		$html .= '
		<tr>
		<td class="text_values">'.$agent.'</td>
		';
		
		foreach($report[daterange] as $date)
		{
			$html .= '
				<td class="text_values">'.$agent_data[$date][status].'</td>
				<td class="text_values">'.$agent_data[$date][shift].'</td>
				<td class="text_values">'.$agent_data[$date][reason].'</td>
			';
		}
		$html .= '
		</tr>
		';
	}
	
	$html .='
		</table>
	';
	
	return $html;
}

?>