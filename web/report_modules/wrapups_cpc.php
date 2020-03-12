<?
function generate_wrapups_cpc($from,$to,$wrapup_number,$subcat_number,$subject_type,$subbase){
	
	$myquery = new custom_query();
	
	if(intval($wrapup_number)==0){
		$wrapup_number = 3;
	}
	$report[totals][constants][wrapup_number] = $wrapup_number;
	$_POST[wrapup_number] = $report[totals][constants][wrapup_number];
	
	if(intval($subcat_number)==0){
		$subcat_number = 5;
	}
	$report[totals][constants][subcat_number] = $subcat_number;
	$_POST[subcat_number] = $report[totals][constants][subcat_number];
	
	$query_wrapsanalysis = "
		SELECT
			reportsphonecalls.wrapupsubcat as subcategory,
			reportsphonecalls.subject as subject,
			if(subsubcategory.subject_type is null,'Inquiry',subsubcategory.subject_type) as subject_type,
			COUNT(reportsphonecalls.subject) as number
		FROM
			subsubcategory
			LEFT OUTER JOIN reportsphonecalls ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
			INNER JOIN subcategory ON (subcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
	";
	
	$sub_base_query = "
		SELECT
			avg(subscount.active_subs) as average_subs,
			count(subscount.`day`) as no_of_days
		FROM
			subscount
		WHERE
	";
	
	if($from==''){
		$_POST[from] = date('Y-m-d');
		$from = $_POST[from];
		
	}
	$query_wrapsanalysis .= " reportsphonecalls.createdon >= '".$from." 00:00:00' ";
	$sub_base_query .= " subscount.`day` >= '".$from."' ";
	
	if($to==''){
		$_POST[to] = date('Y-m-d');
		$to = $_POST[to];
	}
	$query_wrapsanalysis .= " and reportsphonecalls.createdon <= '".$to." 23:59:59' ";
	$sub_base_query .= " and subscount.`day` <= '".$to."'";
	
	if($subject_type){
		$query_wrapsanalysis .= "
			and subsubcategory.subject_type = '$subject_type'
		";
	}
	
	$query_wrapsanalysis .= "
			and subsubcategory.subject_status = 'active'
			and subcategory.sub_cat_status = 'active'
		GROUP BY
			wrapupsubcat,subject,subsubcategory.subject_type 
		ORDER BY 
			number 
		DESC 
		
	";
	
	//echo $query_wrapsanalysis."<br><br>";
	
	custom_query::select_db('reportscrm');
	$wrapsanalysis = $myquery->multiple($query_wrapsanalysis);
	
	custom_query::select_db('ivrperformance');
	
	//echo $sub_base_query."<br><br>";
	$sub_base_result = $myquery->single($sub_base_query);

	if(intval($subbase)==0){
		$subbase = $sub_base_result[average_subs];
	}
	
	//echo "Result is "; print_r($sub_base_result);
	
	$report[totals][constants][subbase] = $subbase;
	$_POST[subbase] = $report[totals][constants][subbase]; 

	foreach($wrapsanalysis as $row){
		if(
		   (
				(count($report[data][$row[subject_type]][subcategories]) < $report[totals][constants][subcat_number]) ||
				(
				 	count($report[data][$row[subject_type]][subcategories]) == $report[totals][constants][subcat_number] &&
					in_array($row[subcategory],array_keys($report[data][$row[subject_type]][subcategories]))
				 )
			)&&
		   count($report[data][$row[subject_type]][subcategories][$row[subcategory]]) < $report[totals][constants][wrapup_number]
		  ){
			$report[data][$row[subject_type]][subcategories][$row[subcategory]][$row[subject]] = $row[number];
			$report[data][$row[subject_type]][numbers] += $row[number];
		}
		$report[totals][subject_types][$row[subject_type]] += $row[number];
		//SUBJECT TYPES BY SUB CATEGORY
		$report[totals][$row[subject_type]][$row[subcategory]] += $row[number];
		//BY SUB CATEGORY
		$report[totals][subcat_totals][$row[subcategory]] += $row[number];
	}	

	return display_wrapups_cpc($report);
}

function display_wrapups_cpc($report){

	$html = '
		<table border="0" cellpadding="2" cellspacing="0">
		<tr>
			<td>
				<table border="0" cellpadding="1" cellspacing="0">
					<tr>
						<th>Number of Subscribers</th>
						<td class="values">'.number_format($report[totals][constants][subbase],0).'</td>
					</tr>
					<tr>
						<th>Total Number of Wrap Ups</th>
						<td class="values">'.number_format(array_sum($report[totals][subject_types]),0).'</td>
					</tr>
					<tr>
						<th>Wrap Ups per subscriber</th>
						<td class="values">'.number_format(array_sum($report[totals][subject_types])/$report[totals][constants][subbase],6).'</td>
					</tr>
	';
				foreach($report[totals][subject_types] as $subject_type=>$subject_type_number){
					$html .= '
						<tr>
							<th>'.$subject_type.'</th>
							<td class="values">'.number_format($subject_type_number,0).' ['.number_format(($subject_type_number/array_sum($report[totals][subject_types]))*100,0).'%]</td>
						</tr>
					';
				}
				unset($subject_type);
	$html .= '
				</table>
			</td>
		</tr>
	';
	//number_format(($number/$report[totals][constants][subbase]),5)
	foreach($report[data] as $subject_type=>$subject_type_data){
		$html .= '
			<tr>
				<td>
					<table border="0" cellpadding="1" cellspacing="0" class="sortable">
						<tr>
							<th width="10"></th>
							<th width="150">Wrap Up Type</th>
							<th width="150">Subcategory</th>
							<th width="450">Wrap up</th>
							<th >Number</th>
							<th>%age of parent</th>
							<th>Wraps per Customer</th>
						</tr>
		';
		
		foreach($subject_type_data[subcategories] as $subcategory=>$subcategory_data){
			$html .= '
					<tr>
						<td class="values"></td>
						<td class="text_values">'.$subject_type.'</td>
						<td class="text_values" style="font-weight:bold;">'.$subcategory.'</td>
						<td class="text_values" style="font-weight:bold;">TOTAL '.$subcategory.' '.$subject_type.'</td>
						<td class="values" style="font-weight:bold;">'.number_format($report[totals][$subject_type][$subcategory],0).'</td>
						<td class="values" style="font-weight:bold;">'.number_format(($report[totals][$subject_type][$subcategory]/array_sum($report[totals][$subject_type]))*100,0).'</td>
						<td class="values" style="font-weight:bold;">'.number_format($report[totals][$subject_type][$subcategory]/$report[totals][constants][subbase],6).'</td>
					</tr>
				';
			foreach($subcategory_data as $subject=>$number){
				$html .= '
					<tr>
						<td class="values">'.++$i.'</td>
						<td class="text_values">'.$subject_type.'</td>
						<td class="text_values">'.$subcategory.'</td>
						<td class="text_values">'.$subject.'</td>
						<td class="values">'.number_format($number,0).'</td>
						<td class="values">'.number_format(($number/$report[totals][$subject_type][$subcategory])*100,0).'</td>
						<td class="values">'.number_format($number/$report[totals][constants][subbase],6).'</td>
					</tr>
				';
			}
		}
		unset($i);
		$html .= '
					</table>
				</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>