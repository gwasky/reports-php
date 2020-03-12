<?
function generate_task_list($relatedto, $from, $to,$accounts,$leads,$status,$task_type){
	custom_query::select_db('wimax');
	
	if($from == ''){ $from = date('Y-m-d'); }
	if($to == ''){ $to = date('Y-m-d'); }
	
	$myquery = new custom_query();
	if($relatedto == 'Accounts'){
		$query = "
			SELECT
				tasks.name as task_name,
				tasks.status as status,
				tasks.parent_id,
				tasks.parent_type as parent_type,
				DATE_ADD(tasks.date_entered, INTERVAL 3 HOUR) as date_entered,
				accounts.name AS related_to,
				accounts.id as id,
				users.user_name,
				tasks_cstm.task_type_c as task_type,
				tasks_cstm.down_grade_start_c as start_date,
				tasks.description as description
			FROM
				tasks
				Inner Join tasks_cstm ON tasks.id = tasks_cstm.id_c
				Inner Join accounts ON tasks.parent_id = accounts.id
				Inner Join accounts_cstm On accounts.id = accounts_cstm.id_c
				left Join users ON tasks.id = users.id
			WHERE
				accounts.deleted = 0 AND
				tasks.date_entered BETWEEN DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR)
			";
				if($status){$query .= " AND tasks.status = '".$status."'";}
				if($relatedto){ $query .= " AND tasks.parent_type = '".$relatedto."'";}
				if($accounts){ $query .= " AND accounts_cstm.mem_id_c = '".$accounts."'";}
				if($task_type){ $query .= " AND tasks_cstm.task_type_c = '".$task_type."'";}
		}elseif($relatedto == 'Leads'){
			$query = "
				SELECT
					tasks.name as task_name,
					tasks.status as status,
					tasks.parent_id,
					tasks.parent_type as parent_type,
					DATE_ADD(tasks.date_entered, INTERVAL 3 HOUR) as date_entered,
					concat(leads.first_name,' ',leads.last_name) AS name,
					leads.id,
					users.user_name,
					tasks_cstm.task_type_c as task_type,
					tasks_cstm.down_grade_start_c as start_date,
					tasks.description as description
				FROM
					tasks
					Inner Join tasks_cstm ON tasks.id = tasks_cstm.id_c
					Inner Join leads ON tasks.parent_id = leads.id
					left Join users ON tasks.id = users.id
				WHERE
					leads.deleted = 0 AND
				tasks.date_entered BETWEEN DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR)
			";
			if($status){$query .= " AND tasks.status = '".$status."'";}
			if($relatedto == 'Leads'){$query .= " AND tasks.parent_type = '".$relatedto."'";}
			if($task_type){ $query .= " AND tasks_cstm.task_type_c = '".$task_type."'";}
			if($leads){
			$lead_names = explode(' ',$leads);
			//var_dump($lead_names );
			$query .= " AND leads.first_name LIKE '%".$lead_names[0]."%'";
			$query.=" OR leads.last_name LIKE '".$lead_names[1]."'";}
		}else{
			$query = "
				SELECT
					tasks.name as task_name,
					tasks.status as status,
					tasks.parent_id,
					tasks.parent_type as parent_type,
					DATE_ADD(tasks.date_entered, INTERVAL 3 HOUR) as date_entered,
					accounts.name AS related_to,
					accounts.id as id,
					users.user_name,
					tasks_cstm.task_type_c as task_type,
					tasks_cstm.down_grade_start_c as start_date,
					tasks.description as description
				FROM
					tasks
					Inner Join tasks_cstm ON tasks.id = tasks_cstm.id_c
					Inner Join accounts ON tasks.parent_id = accounts.id
					Inner Join accounts_cstm On accounts.id = accounts_cstm.id_c
					left Join users ON tasks.id = users.id
				WHERE
					accounts.deleted = 0 AND
					tasks.date_entered BETWEEN DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR)
			";
			if($status){$query .= " AND tasks.status = '".$status."'";}
			if($task_type){ $query .= " AND tasks_cstm.task_type_c = '".$task_type."'";}
			
		$query .= "
			union
		";
		
		$query .= "
			SELECT
				tasks.name as task_name,
				tasks.status as status,
				tasks.parent_id,
				tasks.parent_type as parent_type,
				DATE_ADD(tasks.date_entered, INTERVAL 3 HOUR) as date_entered,
				concat(leads.first_name,' ',leads.last_name) AS name,
				leads.id,
				users.user_name,
				tasks_cstm.task_type_c as task_type,
				tasks_cstm.down_grade_start_c as start_date,
				tasks.description as description
			FROM
				tasks
				Inner Join tasks_cstm ON tasks.id = tasks_cstm.id_c
				Inner Join leads ON tasks.parent_id = leads.id
				left Join users ON tasks.id = users.id
			WHERE
				leads.deleted = 0 AND
				tasks.date_entered BETWEEN DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR)
			";
			if($status){$query .= " AND tasks.status = '".$status."'";}
			if($task_type){ $query .= " AND tasks_cstm.task_type_c = '".$task_type."'";}
		}
		
		//echo nl2br($query)."<br>";
		
		$tasks = $myquery->multiple($query);
		
		if(count($tasks) > 0){
			return display_task_list_hmtl($tasks);
		}else{
			dislay_blanks($report);
		}
}

function display_task_list_hmtl($report){
	
	$url_account = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts';
	$url_lead = 'http://wimaxcrm.waridtel.co.ug/index.php?module=Leads';
	$details = '&action=DetailView&record=';
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%"> 
		<tr>
			<th>Related Object</th>
			<th>Subject</th>
			<th>Task Type</th>
			<th>Related To</th>
			<th>Task Status</th>
			<th>BW Downgrade Start Date</th>
			<th>Description</th>
		</tr>
	';
	foreach($report as $row)
	{
		$html .= '
			<tr>
				<td class="text_values">'.$row[parent_type].'</td>
				<td class="text_values">'.$row[task_name].'</td>
				<td class="wrap_text_task">'.$row[task_type].'</td>
				<td class="wrap_text_task">';
				if($row[parent_type]== 'Accounts') {
					$html .= '<a href="'.$url_account.''.$details.''.$row[id].'">'.$row[related_to].'</a>';
				}else{
					$html .= '<a href="'.$url_lead.''.$details.''.$row[id].'">'.$row[related_to].'</a>';
				}
				$html .='
				</td>
				<td class="text_values">'.$row[status].'</td>
				<td class="text_values">'.$row[start_date].'</td>
				<td class="wrap_text_task">'.$row[description].'</td>
			</tr>';
	
	}
	return $html;
}
?>