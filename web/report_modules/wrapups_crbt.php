<?
function generate_crbt_wrapups($from,$to,$subjects){
	
	custom_query::select_db('ccba02.reportscrm');
	
	$myquery = new custom_query();
	
	$wrapup_query = "
		SELECT
			left(reportsphonecalls.createdon,10) as createdon,
			reportsphonecalls.subject,
			reportsphonecalls.createdby as agent,
			reportsphonecalls.phonenumber as number,
			reportsphonecalls.wrapupcat as category,
			reportsphonecalls.wrapupsubcat as sub_category,
			reportsphonecalls.crbt_requested,
			reportsphonecalls.language,
			wrapupcall_type.name as caller_group
		FROM
			reportsphonecalls
			LEFT OUTER JOIN wrapupcall_type ON wrapupcall_type.id = reportsphonecalls.wrapupcall_type
		WHERE
			reportsphonecalls.wrapupsubcat = 'Ringback Tunes' AND
	";
	
	if($from == '' or $from == date('Y-m-d')){
		$_POST[from] = date('Y-m-d', strtotime("-1 days"));
		$from = $_POST[from];
	}
	$period[start] = $from;
	
	if($to == '' or $to == date('Y-m-d')){
		$_POST[to] = date('Y-m-d', strtotime("-1 days"));
		$to = $_POST[to];
	}
	
	$wrapup_query .= "
		reportsphonecalls.createdon BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
	";
	
	if(($subjects) && (!in_array('%%',$subjects))){
		$wrapup_query .= "AND (";
		foreach($subjects as $count=>$subject){
			$wrapup_query .= " reportsphonecalls.subject = '".$subject."'";
			if(count($subjects) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ")";
	}
	
	//echo nl2br($wrapup_query)."<hr>";
	
	$crbt_wrapups = $myquery->multiple($wrapup_query);
	
	foreach($crbt_wrapups as $row){
		$crbt_array = explode("||",$row[crbt_requested]);
		$row[crbt_artist] = $crbt_array[0];
		$row[crbt_song] = $crbt_array[1];
		unset($row[crbt_requested],$crbt_array);
		$report[data][] = $row;
	}
	
	return display_crbt_wrapups($report);
}

function display_crbt_wrapups($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0">
		<tr>
		<td>
			<table border="0" cellpadding="2" cellspacing="0" class="sortable">
				<tr>
					<th></th>
					<th>Date</th>
					<th>Agent</th>
					<th>Caller Group</th>
					<th>Sub Category</th>
					<th>Subject</th>
					<th>Phone Number</th>
					<th>Artist</th>
					<th>Song title</th>
				</tr>
	';
	
	foreach($report[data] as $row){
		$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="text_values">'.$row[createdon].'</td>
					<td class="text_values">'.ucfirst($row[agent]).'</td>
					<td class="text_values">'.ucfirst($row[caller_group]).'</td>
					<td class="text_values">'.ucfirst($row[sub_category]).'</td>
					<td class="text_values">'.ucfirst($row[subject]).'</td>
					<td class="values">'.$row[number].'</td>
					<td class="text_values">'.ucfirst($row[crbt_artist]).'</td>
					<td class="text_values">'.ucfirst($row[crbt_song]).'</td>
				</tr>
		';
	}
	
	$html .= '
			</table>
	
		</td></tr>
	</table>
	';
	
	return $html;
}
?>