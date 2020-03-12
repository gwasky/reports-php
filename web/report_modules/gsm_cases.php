<?php

function generate_gsm_cases($report_type, $from, $to, $subject_settings, $affected_num, $status){

	custom_query::select_db('reportscrm');

	$myquery = new custom_query();

	/*$query = "
		SELECT 
			date(date_add(createdon, interval 3 hour)) as date_created,
			casenum,
			callmobile,
			numaffected,
			caseorigin,
			description,
			createdby,
			casetype,
			troubleticket,
			status
		FROM
			reportscrm 
		WHERE
	";*/
	
	$query = "
		SELECT 
			reportscrm.createdon as date_created,
			reportscrm.casenum,
			reportscrm.callmobile,
			reportscrm.numaffected,
			reportscrm.caseorigin,
			reportscrm.description,
			reportscrm.createdby,
			reportscrm.casetype,
			reportscrm.troubleticket,
			reportscrm.status,
			if(caseresolution.casenum is null,reportscrm.status,'Closed') as resolved_status
		FROM
			reportscrm
			left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
		WHERE
	";

	if(count($subject_settings)>0 and !in_array('',$subject_settings)){
		$query .= " (";
		foreach($subject_settings as $count=>$subject_setting){
			$query .= "reportscrm.troubleticket = '".$subject_setting."'";
			if(count($subject_settings) > $count+1){
				$query .= " OR ";
			}
		}
		$query .= ") AND ";
	}

	if($affected_num){
		$query .= " reportscrm.numaffected like '%".$affected_num."%' AND ";
	}
	
	if($status){
		$query .= " reportscrm.status = '".$status."' AND ";
	}

	if($from == ''){
		$from = date('Y-m-d');
	}
	$query .= " reportscrm.createdon >= '".$from." 00:00:00' AND ";
	
	if($to == ''){
		$to = date('Y-m-d');
	}
	$query .= " reportscrm.createdon <= '".$to." 23:59:59' ";
	
	function summarise($from, $to, $subject_settings, $affected_num, $status){
		$myquery = new custom_query();
		custom_query::select_db('reportscrm');
		
		if(count($subject_settings)>0 and !in_array('',$subject_settings)){
			$subject_setting_query .= " (";
			foreach($subject_settings as $count=>$subject_setting){
				$subject_setting_query .= 'reportscrm.troubleticket = "'.$subject_setting.'"';
				if(count($subject_settings) > $count+1){
					$subject_setting_query .= " OR ";
				}
			}
			$subject_setting_query .= ") AND ";
		}

	
		if($affected_num){
			$affected_num_query = " reportscrm.numaffected like '%".$affected_num."%' AND ";
		}
		
		if($status){
			$status_query = " reportscrm.status = '".$status."' AND ";
		}
	
		if($from){
			$from_query = " reportscrm.createdon >= '".$from." 00:00:00' AND ";
		}
		
		if($to){
			$to_query = " reportscrm.createdon <= '".$to." 23:59:59' ";
		}
		
		$query = "
			SELECT
				left(reportscrm.createdon,7) as `Month Created`,
				IF(reportscrm.troubleticket LIKE 'WPESA%','Warid Pesa GSM','Other GSM') as Grouping,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Month Created`,Grouping
			ORDER BY
				`Month Created` ASC
		";
		$summary['Number of Cases by Month Created by Grouping'] = $myquery->multiple($query);
		
		$query = "
			SELECT
				reportscrm.troubleticket as `Trouble Ticket`,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Trouble Ticket`
			ORDER BY
				number DESC
		";
		
		$summary['Number of Cases by Trouble Ticket'] = $myquery->multiple($query);
		
		$query = "
			SELECT
				left(reportscrm.createdon,10) as `Date Created`,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Date Created`
			ORDER BY
				number DESC
		";
		
		$summary['Number of Cases by Date Created'] = $myquery->multiple($query);
		
		$query = "
			SELECT
				if(caseresolution.casenum is null,reportscrm.status,'Closed') as `Resolution Status`,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Resolution Status`
			ORDER BY
				number DESC
		";
		$summary['Number of Cases by Resolution Status'] = $myquery->multiple($query);
		
		$query = "
			SELECT
				left(reportscrm.createdon,7) as `Month Created`,
				IF(reportscrm.troubleticket LIKE 'WPESA%','Warid Pesa GSM','Other GSM') as Grouping,
				reportscrm.troubleticket,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Month Created`,Grouping,troubleticket
			ORDER BY
				`Month Created`,Grouping ASC
		";
		$summary['Number of Cases by Month Created, Grouping and Trouble Ticket'] = $myquery->multiple($query);
		
		$query = "
			SELECT
				reportscrm.createdby as `Agent`,
				COUNT(*) AS number
			FROM
				reportscrm
				left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
			WHERE
				".$subject_setting_query.$affected_num_query.$status_query.$from_query.$to_query."
			GROUP BY
				`Agent`
			ORDER BY
				number DESC
		";
		$summary['Number of Cases created by Agent'] = $myquery->multiple($query);

		return $summary;
	}
	
	switch($report_type){
		case 'detail':
			$gsm_cases = $myquery->multiple($query);
			$report[rows] = $gsm_cases;
			break;
		case 'both':
			$gsm_cases = $myquery->multiple($query);
			$report[rows] = $gsm_cases;
			$report[summary] = summarise($from, $to, $subject_settings, $affected_num, $status);
			break;
		case 'summary':
		default:
			$report[summary] = summarise($from, $to, $subject_settings, $affected_num, $status);
	}

	return display_gsm_cases($report);
}

function display_gsm_cases($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable">
	';
	
	if(count($report[rows]) > 0){
		$html .= '
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<th></th>
					<th>Case Number</th>
					<th>Date created</th>
					<th>Number affected</th>
					<th>Calling number</th>
					<th>Case type</th>
					<th>Subject setting</th>
					<th>Description</th>
					<th>Created by</th>
					<th>Status</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class=\'text_values\'>'.++$i.'</td>
					<td class=\'text_values\'>'.$row[casenum].'</td>
					<td class=\'text_values\'>'.$row[date_created].'</td>
					<td class=\'text_values\'>'.$row[numaffected].'</td>
					<td class=\'text_values\'>'.$row[callmobile].'</td>
					<td class=\'text_values\'>'.$row[casetype].'</td>
					<td class=\'text_values\'>'.$row[troubleticket].'</td>
					<td class=\'wrap_text\'>'.$row[description].'</td>
					<td class=\'text_values\'>'.$row[createdby].'</td>
					<td class=\'text_values\'>'.$row[resolved_status].'</td>
				</tr>
			';
		}
		$html .= '
			</table>
			</td>
		</tr>
		';
	}
	
	//if we have both reports let us space them by a row
	if((count($report[rows]) > 0) &&(count($report[summary]) > 0)){
		$html .= '
			<tr>
				<td style=\'height:20px;\'>
				</td>
			</tr>
		';
	}
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			$html .= '
				<tr>
					<th>'.$summary_heading.'</th>
				</tr>
				<tr>
					<td>
					<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
						<tr>
			';
			
			//Titles
			$columns = array_keys($summary_data[0]);
			foreach($columns as $column){
				$html .= '
							<th>'.$column.'</th>
				';
			}
			
			$html .= '
						</tr>
			';
			//row
			foreach($summary_data as $row){
				$html .= '
						<tr>
				';
				foreach($columns as $column){
					$html .= '
							<td class="'; if(!is_numeric($row[$column])){ $html .= 'text_'; } $html .= 'values">'; 
								if(!is_numeric($row[$column])){ $html .= $row[$column]; }else{ $html .= number_format($row[$column],0); } $html .= '
							</td>
					';
				}
				$html .= '
						</tr>
				';
			}
			
			$html .= '
					</table>
					</td>
				</tr>
				<tr>
					<td style="height:10px;"></td>
				</tr>
			';
		}
	}
	
	$html . '
		</table>
	';
	
	return $html;
}

?>