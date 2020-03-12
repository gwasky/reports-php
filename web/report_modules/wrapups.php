<?
function get_agent_data(){

	$myquery = new custom_query();
	custom_query::select_db('ccba01.cs');
	$query = "
			SELECT
				*
			FROM
				employees
			WHERE
				employees.emp_POS = 'CSA'
			";
	//print nl2br($query);
	$agent_result = $myquery->multiple($query,'cs');
	
	foreach($agent_result as $employee){
		$agentsdata[$employee[emp_FNAME].' '.$employee[emp_LNAME]] = array(
						'agent_id' => $employee[emp_ID],
						'agent_name' => $employee[emp_FNAME].' '.$employee[emp_LNAME],
						'agent_loginid' => $employee[emp_NUM]
						);
	}
	
	return $agentsdata;
}
	
function generate_wrapups($from,$to,$report_type,$categories,$subcategories,$subjects,$agents,$msisdns,$caller_groups){
	
	$report[start] = strtotime(date('Y-m-d H:i:s'));
	
	custom_query::select_db($_POST[wrapup_datasource]);

	$myquery = new custom_query();
	
	//echo "POST -> ".PrintR($_POST)."<hr>";
	
	//echo "Report call -> $from, $to, ".PrintR($categories).", ".PrintR($subcategories).", ".PrintR($subjects).", ".PrintR($agents).", ".PrintR($caller_groups)." <hr>";
	
	$wrapup_query = "
		SELECT
			reportsphonecalls.createdon,
			reportsphonecalls.subject,
			reportsphonecalls.createdby as agent,
			reportsphonecalls.phonenumber as number,
			reportsphonecalls.customername,
			reportsphonecalls.wrapupcat as category,
			reportsphonecalls.wrapupsubcat as sub_category,
			reportsphonecalls.language as language,
			reportsphonecalls.description as description,
			reportsphonecalls.district,
			LEFT(reportsphonecalls.custloc,(LOCATE('~',reportsphonecalls.custloc) - 1)) as town,
			RIGHT(reportsphonecalls.custloc,LENGTH(reportsphonecalls.custloc) - LOCATE('~',reportsphonecalls.custloc)) as landmark,
			wrapupcall_type.name as caller_group,
			subsubcategory.subject_type as type,
			reportsphonecalls.description
		FROM
			reportsphonecalls
 			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			LEFT OUTER JOIN wrapupcall_type ON wrapupcall_type.id = reportsphonecalls.wrapupcall_type
		WHERE
	";
	
	//INNER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		
	if($from){
		$wrapup_query .= " reportsphonecalls.createdon >= '".$from." 00:00:00' ";
	}else{
		$_POST[from] = date('Y-m-d');
		$from = $_POST[from];
		$wrapup_query .= " reportsphonecalls.createdon >= '".$from." 00:00:00' ";
	}
	$period[start] = $from;
	
	if($to){
		$wrapup_query .= " and reportsphonecalls.createdon <= '".$to." 23:59:59' ";
	}else{
		$_POST[to] = date('Y-m-d');
		$to = $_POST[to];
		$wrapup_query .= " and reportsphonecalls.createdon <= '".$to." 23:59:59' ";
	}
	
	$categories = array($categories);
	if(count($categories) > 0 and !in_array('',$categories)){
		$wrapup_query .= " AND (";
		foreach($categories as $count=>$category){
			$wrapup_query .= "reportsphonecalls.wrapupcat = '".$category."'";
			if(count($categories) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ") ";
	}
	
	if(count($caller_groups) > 0 and !in_array('',$caller_groups)){
		$wrapup_query .= " AND (";
		foreach($caller_groups as $count=>$caller_group){
			$wrapup_query .= " reportsphonecalls.wrapupcall_type = '".$caller_group."' ";
			if(count($caller_groups) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ") ";
	}
	
	$subcategories = array($subcategories);
	if(count($subcategories) > 0 and !in_array('',$subcategories)){
		$wrapup_query .= " AND (";
		foreach($subcategories as $count=>$subcategory){
			$wrapup_query .= "reportsphonecalls.wrapupsubcat = '".$subcategory."'";
			if(count($subcategories) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ") ";
	}
	
	$subjects = array($subjects);
	if(count($subjects) > 0 and !in_array('',$subjects)){
		$wrapup_query .= " AND (";
		foreach($subjects as $count=>$subject){
			$wrapup_query .= "reportsphonecalls.subject = '".$subject."'";
			if(count($subjects) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ") ";
	}
	
	if(count($agents) > 0 && (!in_array('',$agents))){
		$wrapup_query .= "AND (";
		foreach($agents as $count=>$agent){
			$wrapup_query .= " reportsphonecalls.createdby = '".$agent."'";
			if(count($agents) > $count+1){
				$wrapup_query .= " OR ";
			}
		}
		$wrapup_query .= ")";
		unset($count);
	}
	
	if(trim($msisdns) != ''){
		$msisdns = explode(",",$msisdns);
		$wrapup_query .= " AND reportsphonecalls.phonenumber IN (";
		foreach($msisdns as $count=>$msisdn){
			$wrapup_query .= "'".trim($msisdn)."'";
			if(count($msisdns) > $count+1){
				$wrapup_query .= ",";
			}
		}
		$wrapup_query .= ")";
		unset($count);
	}
	
	//$wrapup_query .= " limit 100 ";
	
	function summarise($from,$to,$categories,$subcategories,$subjects,$agents,$caller_groups){
		$myquery = new custom_query();
		
		//echo "Summarise -> $from, $to, ".PrintR($categories).", ".PrintR($subcategories).", ".PrintR($subjects).", ".PrintR($agents).", ".PrintR($caller_groups)." <hr>";
		
		if($from){
			$from_query = " reportsphonecalls.createdon >= '".$from." 00:00:00' ";
		}
		
		if($to){
			$to_query = " and reportsphonecalls.createdon <= '".$to." 23:59:59' ";
		}
		
		if(count($categories) > 0 and !in_array('',$categories)){
			$category_query .= " AND (";
			foreach($categories as $count=>$category){
				$category_query .= "reportsphonecalls.wrapupcat = '".$category."'";
				if(count($categories) > $count+1){
					$category_query .= " OR ";
				}
			}
			$category_query .= ") ";
		}
		
		if(count($subcategories) > 0 and !in_array('',$subcategories)){
			$subcategory_query .= " AND (";
			foreach($subcategories as $count=>$subcategory){
				$subcategory_query .= "reportsphonecalls.wrapupsubcat = '".$subcategory."'";
				if(count($subcategories) > $count+1){
					$subcategory_query .= " OR ";
				}
			}
			$subcategory_query .= ") ";
		}
		
		if(count($caller_groups) > 0 and !in_array('',$caller_groups)){
			$caller_group_query .= " AND (";
			foreach($caller_groups as $count=>$caller_group){
				$caller_group_query .= " reportsphonecalls.wrapupcall_type = '".$caller_group."' ";
				if(count($caller_groups) > $count+1){
					$caller_group_query .= " OR ";
				}
			}
			$caller_group_query .= ") ";
		}
		
		if(count($subjects) > 0 and !in_array('',$subjects)){
			$subject_query .= " AND (";
			foreach($subjects as $count=>$subject){
				$subject_query .= "reportsphonecalls.subject = '".$subject."'";
				if(count($subjects) > $count+1){
					$subject_query .= " OR ";
				}
			}
			$subject_query .= ") ";
		}
		
		if(count($agents) > 0 and !in_array('',$agents)){
			$agent_query .= " AND (";
			foreach($agents as $count=>$agent){
				$agent_query .= "reportsphonecalls.createdby = '".$agent."'";
				if(count($agents) > $count+1){
					$agent_query .= " OR ";
				}
			}
			$agent_query .= ") ";
		}
		
		$query = "
			SELECT
				left(reportsphonecalls.createdon,7) as `Month`,
				count(*) as number
			FROM
				reportsphonecalls
			WHERE
		".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				`Month`
			order by
				`Month`
		";
		$summary['Number of Wrapups by Month'] = $myquery->multiple($query);
		
		//echo 'Number of Wrapups by date -> <br>'.$query."<br>"; exit();
		
		$query = "
			SELECT
				left(reportsphonecalls.createdon,10) as `Date`,
				count(*) as number
			FROM
				reportsphonecalls
			WHERE
		".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				`Date`
			order by
				`Date`
		";
		$summary['Number of Wrapups by date'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by date -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				trim(reportsphonecalls.createdby) as Agent,
				count(reportsphonecalls.createdby) as number
			FROM
				reportsphonecalls
			WHERE
		".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				Agent
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Agent'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Agent -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				wrapupcall_type.name as `Caller group`,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN wrapupcall_type ON wrapupcall_type.id = reportsphonecalls.wrapupcall_type
			WHERE
		".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			GROUP BY
				`Caller group`
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Caller Group'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Agent -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				reportsphonecalls.wrapupcat as `Category`,
				reportsphonecalls.wrapupsubcat as `Sub category`,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				`Category`,`Sub category`
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Sub category by Category'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Sub category by Category -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				if(subsubcategory.subject_type is null,'Inquiry',subsubcategory.subject_type) as Type,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				Type
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Wrapup Type'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Wrapup Type -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				if(subsubcategory.subject_type is null,'Inquiry',subsubcategory.subject_type) as Type,
				reportsphonecalls.wrapupsubcat as `Sub category`,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				Type,reportsphonecalls.wrapupsubcat
			ORDER BY
				Type,`Sub category`,number DESC
		";
		$summary['Number of Wrapups by Sub category by Wrapup Type'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Category by Wrapup Type -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				if(subsubcategory.subject_type is null,'Inquiry',subsubcategory.subject_type) as Type,
				reportsphonecalls.subject,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				Type,concat(reportsphonecalls.subject,reportsphonecalls.wrapupsubcat)
			ORDER BY
				Type,number DESC
		";
		$summary['Number of Wrapups by Subject by Wrapup Type'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Subject by Wrapup Type -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				reportsphonecalls.wrapupsubcat as `Sub category`,
				reportsphonecalls.subject as `Wrap up`,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				`Sub category`,`Wrap up`
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Wrapup by Category'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Wrapup by Category -> <br>'.$query."<br>";
		
		$query = "
			SELECT
				wrapupcall_type.name as `Caller group`,
				reportsphonecalls.wrapupsubcat as `Sub category`,
				reportsphonecalls.subject as `Wrap up`,
				count(*) as number
			FROM
				reportsphonecalls
				LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
				LEFT OUTER JOIN wrapupcall_type ON wrapupcall_type.id = reportsphonecalls.wrapupcall_type
			WHERE
				".$from_query.$to_query.$category_query.$subcategory_query.$subject_query.$agent_query.$caller_group_query."
			group by
				`Caller group`,`Sub category`,`Wrap up`
			ORDER BY
				number DESC
		";
		$summary['Number of Wrapups by Caller group by Wrapup by Category'] = $myquery->multiple($query);
		//echo 'Number of Wrapups by Wrapup by Category -> <br>'.$query."<br>";

		
		return $summary;
	}
	
	//echo nl2br($wrapup_query)."<br>";
	
	switch($report_type){
		case 'detail':
			$wrapups = $myquery->multiple($wrapup_query);
			$report[rows] = $wrapups;
			break;
		case 'both':
			$wrapups = $myquery->multiple($wrapup_query);
			$report[rows] = $wrapups;
			$report[summary] = summarise($from,$to,$categories,$subcategories,$subjects,$agents,$caller_groups);
			break;
		case 'summary':
			default:
			$report[summary] = summarise($from,$to,$categories,$subcategories,$subjects,$agents,$caller_groups);
	}
	
	$report[stop] = strtotime(date('Y-m-d H:i:s'));
	
	return display_wrapups($report);
}

function display_wrapups($report){
	
	$agents_data = get_agent_data();
	//print_r($report);
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td>Report took ['.($report[stop] - $report[start]).'] seconds to run</td>
			</tr>
			<!--class="sortable"-->
	';
	
	if(count($report[rows]) > 0){
		$html = '
		<tr>
		<td style="height:20px;">DETAILS</td>
		</tr>
		<tr>
		<td>
		<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
				<th></th>
				<th>Date</th>
				<th>Time</th>
				<th>Phone Number</th>
				<th>Customer Name</th>
				<th>Caller Group</th>
				<th>Language</th>
				<th>Category</th>
				<th>Sub Category</th>
				<th>Subject</th>
				<th>Subject Type</th>
				<th>Description</th>
				<th>Customer District</th>
				<th>Customer Town</th>
				<th>Customer Landmark</th>
				<th>Description</th>
				<th>Agent</th>
				<th>Login ID</th>
			</tr>
		';
		
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class="values">'.++$i.'</td>
					<td class="values">'.substr($row[createdon],0,10).'</td>
					<td class="values">'.substr($row[createdon],-8).'</td>
					<td class="values">'.$row[number].'</td>
					<td class="text_values">'.ucwords($row[customername]).'</td>
					<td class="text_values">'.$row[caller_group].'</td>
					<td class="text_values">'.$row[language].'</td>
					<td class="text_values">'.ucfirst($row[category]).'</td>
					<td class="text_values">'.ucfirst($row[sub_category]).'</td>
					<td class="text_values">'.ucfirst($row[subject]).'</td>
					<td class="text_values">'.ucfirst($row[type]).'</td>
					<td class="text_values">'.ucfirst($row[description]).'</td>
					<td class="text_values">'.ucfirst($row[district]).'</td>
					<td class="text_values">'.ucfirst($row[town]).'</td>
					<td class="text_values">'.ucfirst($row[landmark]).'</td>
					<td class="text_values">'.ucfirst($row[description]).'</td>
					<td class="text_values">'.ucfirst($row[agent]).'</td>
					<td class="text_values">'.$agents_data[$row[agent]]['agent_loginid'].'</td>
				</tr>
			';
		}
		$html .= '
		</table>
		</td></tr>
		';
	}

	//if we have both reports let us space them by a row
	if((count($report[rows]) > 0) &&(count($report[summary]) > 0)){
		$html .= '
			<tr>
				<td style="height:20px;"></td>
			</tr>
		';
	}
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th colspan="2" style="height:20px;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			$html .= '
				<tr>
					<td>
					<tr>
						<th colspan="2">'.$summary_heading.'</th>
					</tr>
					<td>
					<tr>
					<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
						<tr>
			';
			
			//Titles
			if($summary_heading == 'Number of Wrapups by Agent'){
				$html .= '<th>Login ID</th>';
				}
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
				if($summary_heading == 'Number of Wrapups by Agent'){
				$html .= '<td class="text_values">'.$agents_data[$row[Agent]]['agent_loginid'].'</td>';
				}
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
	
	$html .= '
		</table>
	';
	
	$html .= '<script> reconcileDateRange("'.$_POST[wrapup_datasource].'"); </script>';
	
	return $html;
}

?>