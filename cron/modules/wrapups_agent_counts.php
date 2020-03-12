<?php
function generate_agent_wrapup_counts($date,$days_back,$days_from){
	
	$myquerys = new custom_query();
	
	$from = " concat(date_sub('".$date."', interval ".intval($days_from)." days),' 00:00:00') ";
	
	$query = "
		select
			agent,
			sum(if(category = 'Prank calls',number,NULL)) as 'Prank calls',
			sum(if(category != 'Prank calls',number,NULL)) as 'Non Prank calls',
			sum(number) as total
		from
			(
				SELECT
					left(reportsphonecalls.createdon,10) as entry_date,
					trim(reportsphonecalls.createdby) AS agent,
					count(reportsphonecalls.createdby) AS number,
					if(reportsphonecalls.wrapupcat LIKE '%Unclassified%','Prank calls','Non Prank Calls') AS category
				FROM
					reportsphonecalls
				WHERE
					reportsphonecalls.createdon between concat(date_sub('".$date."', interval ".intval($days_from)." day),' 00:00:00') and concat(date_sub('".$date."', interval ".intval($days_back)." day),' 23:59:59')
				GROUP BY
					entry_date,category,agent
			)as TT
		group by
			agent
		ORDER BY
			`Non Prank calls` DESC
	";
	
	//echo $query.'\n';
	
	//exit();
	
	//custom_query::select_db('reportscrm');
	$agent_calls = $myquerys->multiple($query,'ccba02.reportscrm');
	
	$query = "
		select
			agent,
			sec_to_time(sum(day_seconds)) as duration
		from
			(
				select
					left(reportsphonecalls.createdon,10) as entry_date,
					trim(reportsphonecalls.createdby) AS agent,
					time_to_sec(timediff(max(reportsphonecalls.createdon),min(reportsphonecalls.createdon))) as day_seconds
				from 
					reportsphonecalls 
				where 
					reportsphonecalls.createdon between concat(date_sub('".$date."', interval ".intval($days_from)." day),' 00:00:00') and concat(date_sub('".$date."', interval ".intval($days_back)." day),' 23:59:59')
				GROUP BY
					entry_date,agent
			) as QQ
		GROUP BY
		agent
	";
	
	//echo $query.'\n';
	
	//custom_query::select_db('reportscrm');
	$agent_duration = $myquerys->multiple($query,'ccba02.reportscrm');
	
	foreach($agent_calls as $row){
		$report[data][$row[agent]] = $row;
	}
	
	foreach($agent_duration as $row){
		$report[data][$row[agent]][duration] = $row[duration];
	}
	
	return display_agent_wrapup_counts($report);
}

function display_agent_wrapup_counts($report){
	if($report){
		
		$data=array('rows'=>$report[data],'title'=>'Wrap ups by CCAs');
		$agents_list = get_cc_agent_info();
		//return show_table($data,$padding=2,$spacing=0,$border=0,$align='centre',$width='400px',$table='top_left',$style='border-color:#FFF;');
		
		$html = '
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th>Login ID</th>
					<th>Agent</th>
					<th>Non Prank call wrap ups</th>
					<th>Prank Call wrap ups</th>
					<th>Total Wrap ups</th>
					<th>Wrap up Log in duration</th>
				</tr>
		';
		
		foreach($report[data] as $row){
			$html .= '
				<tr>
					<td class="text_values">'.$agents_list[$row[agent]]['agent_loginid'].'</td>
					<td class="text_values">'.ucwords(strtolower($row[agent])).'</td>
					<td class="values">'.number_format($row["Non Prank calls"],0).'</td>
					<td class="values">'.number_format($row["Prank calls"],0).'</td>
					<td class="values">'.number_format($row[total],0).'</td>
					<td class="values">'.$row[duration].'</td>
				</tr>
			';
		}
		
		$html .= '
			</table>
		';
	}
	
	return $html;
}
?>