<?php

function generate_cases_report($from, $to, $account_id,$status,$customer_types,$subject_setting,$reporttype){

	custom_query::select_db('wimax');

	$myquery = new custom_query();

	function summarise($rows){
		
		foreach($rows as $row){
			++$summary["Number of Cases created by Month"][substr($row[date_entered],0,7)];
			++$summary["Number of Cases created by Platform"][$row[platform]];
			++$summary["Number of Cases created by Subject setting"][$row[subject_setting]];
			++$summary["Number of Cases created by Agent Created"][$row[created_by]];
			++$summary["Number of Cases created by Queue"][$row[queue_name]];
			++$summary["Number of Cases created by Queue by Subject setting"][$row[queue_name]." >> ".$row[subject_setting]];
			++$summary["Number of Cases created by Date"][substr($row[date_entered],0,10)];
			
			++$summary["Number of Cases created by Account"][$row[name]];
			
			if(intval($row[closed_on]) != ''){
				++$summary["Number of Cases closed by Month"][substr($row[closed_on],0,7)];
				++$summary["Number of Cases closed by Platform"][$row[platform]];
				++$summary["Number of Cases closed by Subject setting"][$row[subject_setting]];
				++$summary["Number of Cases closed by Date"][substr($row[closed_on],0,10)];
				
				$resolution_days = ceil($row[resolution_time]/86400);
				++$summary["Number of Cases closed by Resolution days"][$resolution_days];
				/*echo "Case No ".++$jjj."<br>";
				foreach($summary["Number of Cases closed by Resolution days"] as $resolution_day_value=>$no_of_cases){
					if($resolution_days < $resolution_day_value){
						++$summary["Number of Cases closed by Resolution days"][$resolution_day_value];
						echo "+ ".$resolution_days." to [".$resolution_day_value."] = ".$summary["Number of Cases closed by Resolution days"][$resolution_day_value]."<br>";
					}elseif(($resolution_days > $resolution_day_value) and $summary["Number of Cases closed by Resolution days"][$resolution_days] != ''){
						$summary["Number of Cases closed by Resolution days"][$resolution_days] += $summary["Number of Cases closed by Resolution days"][max(array_keys($summary["Number of Cases closed by Resolution days"]))];
						echo "+ ".$resolution_days." to [".$resolution_day_value."] = ".$summary["Number of Cases closed by Resolution days"][$resolution_day_value]."<br>";
					}else{
						echo "- ".$resolution_days." to [".$resolution_day_value."] = ".$summary["Number of Cases closed by Resolution days"][$resolution_day_value]."<br>";
					}
					
					unset($resolution_day_value,$no_of_cases);
				}
				echo "<hr>";
				*/
								
				++$summary["Number of Cases closed by User"][$row[closed_by]];
				++$summary["Number of Cases closed by Account"][$row[name]];
			}
		}
		
		return $summary;
	}
	
	$query = "
		SELECT 
			left(cases.date_entered,10) as date_entered,
			cases.id,
			cases_cstm.subject_setting_c as subject_setting,
			cases_cstm.subject_group_c as subject_group,
			cases_cstm.calling_num_c as calling_num,
			cases_cstm.case_type_c as case_type,
			cases_cstm.root_cause_c as root_cause,
			cases.case_number,
			cases.resolution,
			cases.description,
			cases.status,
			concat(created_users.first_name,' ',created_users.last_name) as created_by,
			qs_queues_cases_c.date_modified as date_assigned_toqueue,
			qs_queues.name as queue_name,
			accounts.id as account_id,
			accounts.name,
			accounts_cstm.crn_c as account_no,
			accounts_cstm.platform_c as platform,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.download_bandwidth_c as bandwidth,
			accounts_cstm.contact_person_c as contact_person,
			accounts_cstm.contact_person_phone_c as contact_phone,
			accounts_cstm.technical_contact_person_c as tech_person,
			accounts_cstm.technical_contact_phone_c as tech_phone,
			(select cases_audit.date_created from cases_audit where cases_audit.after_value_string = 'Closed' and cases_audit.before_value_string != 'Closed' and cases_audit.parent_id = cases.id and cases_audit.field_name = 'status' order by cases_audit.date_created DESC LIMIT 1) as closed_on,
			(select concat(users.first_name,' ',users.last_name ) from cases_audit inner join users on (cases_audit.created_by = users.id ) where cases_audit.after_value_string = 'Closed' and cases_audit.before_value_string != 'Closed' and cases_audit.parent_id = cases.id and cases_audit.field_name = 'status' order by cases_audit.date_created DESC LIMIT 1) as closed_by
		FROM
 			cases
			INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
			INNER JOIN accounts ON (cases.account_id=accounts.id)
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			INNER JOIN users created_users ON (cases.created_by = created_users.id)
			LEFT OUTER JOIN qs_queues_cases_c ON (cases.id = qs_queues_cases_c.qs_queues_casescases_idb)
			LEFT OUTER JOIN qs_queues ON (qs_queues.id = qs_queues_cases_c.qs_queues_csqs_queues_ida)
		WHERE
			(qs_queues_cases_c.deleted = 0 OR qs_queues_cases_c.deleted IS NULL)  AND
			accounts.deleted = '0' AND
			cases.deleted = '0'
	";

	if($from == ''){
		$from = date('Y-m-d H:i:s');
		$_POST[from] = $from;
	}
	$query .= " AND cases.date_entered >= date_sub('".$from." 00:00:00', interval 3 hour) ";
	
	if($to == ''){
		$to = date('Y-m-d');
		$_POST[to] = $to;
	}
	$query .= " AND cases.date_entered <= date_sub('".$to." 23:59:59', interval 3 hour) ";

	if($account_id){
		$query .= " AND accounts_cstm.crn_c = '".$account_id."' ";
	}
	
	if(($customer_types) && (!in_array('%%',$customer_types))){
		$query .= "AND (";
		foreach($customer_types as $count=>$customer_type){
			$query .= "accounts_cstm.customer_type_c = '".$customer_type."'";
			if(count($customer_types) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ")";
	}
	
	if($subject_setting){
		$query .= " AND cases_cstm.subject_setting_c = '".$subject_setting."' ";
	}
	
	if($status){
		$query .= " AND cases.status = '".$status."' ";
	}
	
	$query .= "
		ORDER BY 
			cases.date_entered DESC 
	";
	
	//echo nl2br($query);
	
	$detail = $myquery->multiple($query);
	
	if(count($detail) == 0) { return display_cases_report('NO DATA'); }
	
	foreach($detail as &$row){
		
		$row[contact_info] = "Admin : ".$row[contact_person]." ".$row[contact_phone];
		if(preg_replace("/[^0-9]/","", $row[tech_phone]) != ""){
			$row[contact_info] .= "<br>Tech : ".$row[tech_person]." ".$row[tech_phone];
		}
		
		if(trim($row[closed_on]) != '') { $row[resolution_time] = (strtotime($row[closed_on]) - strtotime($row[date_entered])); }
	}
	
	switch($reporttype){
		case 'detail':
			$report[detail] = $detail;
			break;
		case 'both':
			$report[detail] = $detail;
			$report[summary] = summarise($detail);
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($detail);
			break;
	}
	
	return display_cases_report($report);
}

function display_cases_report($report){
	if($report == 'NO DATA'){
		return "No data matches the selected filters";
	}
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="80%">
	';
	
	if(count($report[summary]) > 0 ){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARY</th>
			</tr>
		';
		
		foreach($report[summary] as $sub_title=>$sub_title_data){
			
			//EXTRACTING PARAMETER TILES FROM THE TITLE STRING: ie Totals by parameter title by parameter title
			$parameter_title_list = explode(" by ",$sub_title);
			
			$html .= '
			<tr>
				<th>'.$sub_title.'</th>
			</tr>
			<tr>
				<td>
				<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
					<tr>
			';
			
			foreach($parameter_title_list as $kkey=>$parameter_title){
				if($kkey != 0){
					//EXCLUDING THE FIRST COLUMN "TOTALS" IN "Totals by parameter title by parameter title"
					$html .= '
							<th>'.$parameter_title.'</th>
					';
				}
			}
			
			$html .= '
						<th>Value</th>
					</tr>
			';
			foreach($sub_title_data as $parameter_string=>$value){
				$parameter_list = explode(' >> ',$parameter_string);
				$html .= '
					<tr>
				';
				
				foreach($parameter_list as $parameter){
					$html .= '
						<td class="text_values">'.$parameter.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($value,0).'</td>
					</tr>
				';
			}
			$html .= '
				</table>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			';
		}
	}
	
	if(count($report[detail]) > 0 and count($report[summary]) > 0 ){
		$html .= '
			<tr><td height="15"></td></tr>
		';
	}
	
	if(count($report[detail]) > 0){
		$html = '
			<tr>
				<th style="height:20px;">DETAILS</th>
			</tr>
			<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable"> 
			<tr> 
				  <th></th>
				  <th>Case Number</th>
				  <th>Current Queue</th>
				  <th>Account Number</th>
				  <th>Date created</th>
				  <th>Created by</th>
				  <th>Date Assigned to Queue</th>
				  <th>Account Name</th>
				  <th>Platform</th>
				  <th>Customer type</th>
				  <th>Status</th>
				  <th>Bandwidth</th>
				  <th>Subject setting</th>
				  <th>Description</th>
				  <th>Contact Info</th>
				  <th>Resolution</th>
				  <th>Root cause</th>
				  <th>Closed on</th>
				  <th>Closed by</th>
				  <th>Resolution time hh:mm:ss</th>
			</tr>
		';
		
		foreach($report[detail] as $row){
			$html .= '
				<tr>
					<td class="text_values">'.++$i.'</td>
					<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Cases&action=DetailView&record='.$row[id].'" target="_blank">'.$row[case_number].'</a></td>
					<td class="text_values">'.$row[queue_name].'</td>
					<td class="text_values">'.$row[account_no].'</td>
					<td class="text_values">'.$row[date_entered].'</td>
					<td class="text_values">'.$row[created_by].'</td>
					<td class="text_values">'.$row[date_assigned_toqueue].'</td>
					<td class="wrap_text"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[account_id].'" target="_blank">'.$row[name].'</a></td>
					<td class="text_values">'.$row[platform].'</td>
					<td class="text_values">'.$row[customer_type].'</td>
					<td class="text_values">'.$row[status].'</td>
					<td class="text_values">'.$row[bandwidth].'</td>
					<td class="text_values">'.$row[subject_setting].'</td>
					<td class="wrap_text">'.$row[description].'</td>
					<td class="wrap_text">'.$row[contact_info].'</td>
					<td class="wrap_text">'.$row[resolution].'</td>
					<td class="text_values">'.$row[root_cause].'</td>
					<td class="text_values">'.$row[closed_on].'</td>
					<td class="text_values">'.$row[closed_by].'</td>
					<td class="values">'.sec_to_time($row[resolution_time]).'</td>
				</tr>
			';
		}
		
		foreach($report[totals] as $total){
			$html .= '
				<tr><td colspan="15" height="10">&nbsp;</td></tr>
				<tr><td colspan="15">
				<table width="100%" border="0" cellpadding="2" cellspacing="0"> 
					<tr> 
			';
			
			foreach($total as $title=>$value){
				$html .= '<th>'.$title.'</th>';
			}
			
			$html .= '
					</tr>
					<tr>
					';
			
			foreach($total as $values){
				$html .= '<td class="values">'.accounts_format($values).'</td>';
			}
			
			$html .= '
					</tr>
				</table>
				</td></tr>
			';
		}
		
		$html . '
			</table>
			</td>
			</tr>
		';
	}
	
	$html . '
		</table>
	';
	
	return $html;
}

?>