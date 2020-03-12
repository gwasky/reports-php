<?
function generate_cases_handled_by_smt_team($from,$to,$team,$report_type){
		
	custom_query::select_db('wimax');
	$myquery = new custom_query();

	if(!in_array('%%',$team)){
	//echo 'we have selected teams';
	$itemNumber = count($team);
	 $query = "
	 			SELECT
    			DISTINCT cases.name,cases.id,cases.case_number,users.first_name,users.last_name,concat(users.first_name,' ',users.last_name) as user_name,cases_audit.date_created
				FROM
    			wimax.cases
    			INNER JOIN wimax.cases_cstm 
        		ON (cases.id = cases_cstm.id_c)
    			INNER JOIN wimax.users 
        		ON (cases.modified_user_id = users.id)
    			INNER JOIN wimax.cases_audit 
        		ON (cases_audit.parent_id = cases_cstm.id_c)
        		WHERE  users.id IN (";
				foreach($team as $user){
					++$counter;	
					$query .= "'$user'";
					if ($counter < $itemNumber) { $query .=",";}
				}
				$query .= ")";
				if($from){
				$query .= " AND cases_audit.date_created >= '".$from."'";
				}
				if($to){
				$query .= " AND cases_audit.date_created <='".$to."'";
				}
				
			} 
		   else{  
			//echo 'we have not selected teams';
			$query = "
				SELECT DISTINCT
    			cases.name,cases.case_number,users.first_name,users.last_name,concat(users.first_name,' ',users.last_name) as user_name,cases_audit.date_created
				FROM
    			wimax.cases
    			INNER JOIN wimax.cases_cstm 
        		ON (cases.id = cases_cstm.id_c)
    			INNER JOIN wimax.users 
        		ON (cases.modified_user_id = users.id)
    			INNER JOIN wimax.cases_audit 
        		ON (cases_audit.parent_id = cases_cstm.id_c)
        		WHERE  users.department = 'Customer Care CS SMT' AND users.status = 'Active'
				AND cases.deleted = 0
		";
				if($from){
				$query .= " AND cases_audit.date_created >= '".$from."'";
				}
				if($to){
				$query .= " AND cases_audit.date_created <='".$to."'";
				}
				
	}
		$smt_users = $myquery->multiple($query);
		//print_r($smt_users);
		
		function summarise_entries($from,$to)
		{
			custom_query::select_db('wimax');
			$myquery = new custom_query();
			$query = "
					SELECT 
					CONCAT(users.first_name,' ',users.last_name) AS handler_name,COUNT(DISTINCT cases.case_number) AS case_count
				FROM
				wimax.cases
				INNER JOIN wimax.cases_cstm 
				ON (cases.id = cases_cstm.id_c)
				INNER JOIN wimax.users 
				ON (cases.modified_user_id = users.id)
				INNER JOIN wimax.cases_audit 
				ON (cases_audit.parent_id = cases_cstm.id_c)
				WHERE  users.department = 'Customer Care CS SMT' AND users.status = 'Active'
				AND cases.deleted = 0
				
				";
				if($from){
				$query .= " AND cases_audit.date_created >= '".$from."'";}
				if($to){
				$query .= " AND cases_audit.date_created <='".$to."'";}
				
				$query .= " GROUP BY CONCAT(users.first_name,' ',users.last_name)";
				
				$report = $myquery->multiple($query);
				foreach($report as $row)
				{
					$data[Handlers][$row[handler_name]] = $row[case_count];
					$data[Totals][Total] += $row[case_count];
				}
				return $data;
		}
		echo $query;
			switch($report_type){
			case 'detail':
					$report[rows] = $smt_users;
					//var_dump($report[rows]);
					break;
			case 'summary':
					$report[summary] = summarise_entries($from,$to);
					//var_dump($report[summary]);
					break;
			default:
					$_POST[report_type] = 'summary';
					$report[summary] = summarise_entries($from,$to);
			}
		
		return display_cases_handled_by_smt_team_report($report);
}

function display_cases_handled_by_smt_team_report($report){
		$html = '
				<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
				<tr> ';
			if(count($report[rows]) > 0 ){
					$html.= '
					  <th>Case Number</th>
					  <th>Case Name</th>
					  <th>Handler</th>
					  <th>Date Handled</th>
				</tr>
					';
					foreach($report[rows] as $row)
					{
						$html .= '
							<tr>
								<td class="text_values">'.$row[case_number].'</td>
								<td class="text_values">'.$row[name].'</td>
								<td class="text_values">'.$row[first_name].' '.$row[last_name].'</td>
								<td class="text_values">'.$row[date_created].'</td>
							</tr>';
					}
						$html .= '</table>';
			}
			
			if(count($report[summary])>0)
			{
				$html = '
							<table border="0" cellpadding="0" cellspacing="0" width="400px"><tr>
							<th>Handler</th>
							<th>Number of cases Handled</th>
							</tr>';
				foreach($report[summary][Handlers] as $Handler=>$CaseCount)
				{
					$html .= '
					<tr>
						<td class="text_values">'.$Handler.'</td>
						<td class="text_values">'.$CaseCount.'</td>
					</tr>';
				}
				foreach($report[summary][Totals] as $k=>$v)
				{
					$html .= '
					<tr>
						<td class="text_values">'.$k.'</td>
						<td class="text_values">'.$v.'</td>
					</tr>';
				}
					$html .= '</table>';
			}
	return $html;
}
?>