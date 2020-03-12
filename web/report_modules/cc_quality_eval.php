<?php
//require('includes/wdg/WDG.php');
//error_reporting(E_ALL);

function get_field_data(){
	$myquery = new custom_query();
	custom_query::select_db('ccba01.reportscrm');
	$query = "
			SELECT
				*
			FROM
				call_evaluation_fields
			WHERE
				call_evaluation_fields.deleted = '0'
			";
	
	$fields_result = $myquery->multiple($query,'reportscrm');
	
	foreach($fields_result as $fielddata){
		$fields[$fielddata[field_name]] = array(
								'id' => $fielddata[id],
								'category' => $fielddata[category],
								'subcategory' => $fielddata[subcategory],
								'field_name' => $fielddata[field_name],
								'label' => $fielddata[label],
								'row' => $fielddata[row],
								'column' => $fielddata[column],
								'weightage' => $fielddata[weightage],
								'fatal' => $fielddata[fatal],
								'options' => $fielddata[options],
								'type' => $fielddata[type],
								'validate' => $fielddata[validate],
								'attributes' => $fielddata[attributes]
								);
	}
	return $fields;
}

function get_category_data(){
	$myquery = new custom_query();
	custom_query::select_db('ccba01.reportscrm');
	$query = "
			SELECT
				*
			FROM
				call_evaluation_categories
			WHERE
				call_evaluation_categories.deleted = '0'
			";
	
	$cat_result = $myquery->multiple($query,'reportscrm');

	foreach($cat_result as $catdata){
		$categories[$catdata[id]] = $catdata[category];
	}
	return $categories;
}

/*function calculate_scores($data, $fatal_zero, $total_mark, $total_weightage, $weightage_deducted){
	if($fatal_zero == true){
		$final_score_with_fatal = 0;
	}else{
		$final_score_with_fatal = ($total_mark/($total_weightage-$weightage_deducted))*100;
	}
	
	$final_score_without_fatal = ($total_mark/($total_weightage-$weightage_deducted))*100;
	
	$total_weightage_minus_deducted = $total_weightage - $weightage_deducted;
	$row[params][$eval_scoresdata[id]] = array(
											'total_weightage' => $total_weightage,
											'total_weightage_minus_deducted' => $total_weightage_minus_deducted,
											'weightage_deducted' => $weightage_deducted,
											'total_mark' => $total_mark,
											'final_score_without_fatal' => $final_score_without_fatal,
											'final_score_with_fatal' => $final_score_with_fatal
											);
	$total_final_scores_without_fatal += $final_score_without_fatal;
	$total_final_scores_with_fatal += $final_score_with_fatal;
	++$recordcount;
	
	unset($total_mark);
	unset($total_weightage);
	unset($weightage_deducted);
	unset($final_score_without_fatal);
	unset($final_score_with_fatal);
	unset($fatal_zero);
}*/

function generate_cc_quality_eval_report($from, $to, $report_type){
	
	$row[fields] = get_field_data();
	$row[categories] = get_category_data();
	
	if(!$to){
		$to = date('Y-m-d');
	}
	
	if(!$from){
		$from = date('Y-m-').'01';
	}
	
	$myquery = new custom_query();
	custom_query::select_db('ccba01.reportscrm');
	$query = "
			SELECT
				call_evaluation_scores.id,
				LEFT(call_evaluation_scores.datecreated, 10) as datecreated,
				call_evaluation_scores.eval_information,
				call_evaluation_scores.eval_scores,
				call_evaluation_scores.deleted
			FROM
				call_evaluation_scores
			WHERE
				call_evaluation_scores.deleted = '0'
			AND
				call_evaluation_scores.datecreated BETWEEN '".$from." 00:00:00' AND '".$to." 23:59:59'
			ORDER BY
				call_evaluation_scores.datecreated
			DESC
			";
	
	$eval_score_result = $myquery->multiple($query,'reportscrm');
	
	if(count($eval_score_result) <= 0){ return 'There is no data for the selected period ['.$from.'] to ['.$to.'] ....'; }
	//print nl2br($query);
	//echo '<pre>'.print_r($eval_score_result,true).'</pre>';
	//exit();
	
	foreach($eval_score_result as $eval_scoresdata){
	
		$eval_info = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $myquery->my_unescape($eval_scoresdata[eval_information], 'reportscrm'));
		$eval_scrs = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $myquery->my_unescape($eval_scoresdata[eval_scores], 'reportscrm'));
		
		$eval_information = unserialize($eval_info);
		$eval_scores = unserialize($eval_scrs);
		//echo $eval_scoresdata['id'].'<br>';
		
		$row[information][$eval_scoresdata[id]] = $eval_information;

		//echo '<pre>'.print_r($row[information],true).'</pre>';
		$weightage_deducted = 0;
		$total_weightage = 0;
		$total_weightage_minus_deducted = 0;
		$weightage_deducted = 0;
		$total_mark = 0;
		$final_score = 0;
		
		foreach($eval_scores as $question => $scoremark){
			$score = trim($scoremark);
			$question_data = $row[fields][$question];

			$question_options = split(',',$question_data[options]);
			
			foreach($question_options as $option){
				if(is_numeric($option)){ $sumoptions += $option; }
				if($sumoptions == 3){ $full_score = 2; }
				if($sumoptions == 1){ $full_score = 1; }
			}
			
			if($score == 'NA'){
				$percent_score = 0;
				$weightage_deducted += $question_data[weightage];
			}elseif($score == 0){
				$percent_score = 0;
			}
			
			if(($full_score == 1 && $score == 1) || ($full_score == 2 && $score == 2)){
				$percent_score = $question_data[weightage];
			}elseif($full_score == 2 && $score == 1){
				$percent_score = $question_data[weightage]/2;
			}
			
			$total_weightage += $question_data[weightage];
			$total_mark += $percent_score;
			
			if($question_data[fatal] == 1 && ($score == 0 && $score != 'NA')){ $fatal_zero = true; }
			
			$row[scores][$eval_scoresdata[datecreated]][$eval_scoresdata[id]][categories][$row[categories][$question_data[category]]][subcategories][$question_data[subcategory]][$question_data[field_name]] = array(
							'question' => $question_data[label],
							'weightage' => $question_data[weightage],
							'score' => $score,
							'percent_score' => $percent_score,
							'options' => $question_data[options],
							'sumoptions' => $sumoptions,
							'full_score' => $full_score,
							'fatal' => $fatal_zero
							);
			unset($sumoptions);
		}
		
		if($fatal_zero == true){
			$final_score_with_fatal = 0;
		}else{
			$final_score_with_fatal = ($total_mark/($total_weightage-$weightage_deducted))*100;
		}
		
		$final_score_without_fatal = ($total_mark/($total_weightage-$weightage_deducted))*100;
		
		$total_weightage_minus_deducted = $total_weightage - $weightage_deducted;
		$row[params][period][$eval_scoresdata[datecreated]][$eval_scoresdata[id]] = array(
												'total_weightage' => $total_weightage,
												'total_weightage_minus_deducted' => $total_weightage_minus_deducted,
												'weightage_deducted' => $weightage_deducted,
												'total_mark' => $total_mark,
												'final_score_without_fatal' => $final_score_without_fatal,
												'final_score_with_fatal' => $final_score_with_fatal
												);
		$row[params][month][substr($eval_scoresdata[datecreated],0,7)][totalfinal_score_without_fatal] += $final_score_without_fatal;
		$row[params][month][substr($eval_scoresdata[datecreated],0,7)][totalfinal_score_with_fatal] += $final_score_with_fatal;
		++$row[params][month][substr($eval_scoresdata[datecreated],0,7)][monthcount];
		//++$monthcount[substr($eval_scoresdata[datecreated],0,7)];
		//print $final_score_with_fatal.'<br>';
		
		$total_final_scores_without_fatal += $final_score_without_fatal;
		$total_final_scores_with_fatal += $final_score_with_fatal;
		++$recordcount[$eval_scoresdata[datecreated]];
		
		unset($total_mark);
		unset($total_weightage);
		unset($weightage_deducted);
		unset($final_score_without_fatal);
		unset($final_score_with_fatal);
		unset($fatal_zero);
	}
	//echo '<pre>'.print_r($row[params][month],true).'</pre>';
	foreach($row[params][month] as $yearmonth => $yearmonthdata){
		$row[params][month][$yearmonth][final_score_without_fatal] = $yearmonthdata[totalfinal_score_without_fatal]/$yearmonthdata[monthcount];
		$row[params][month][$yearmonth][final_score_with_fatal] = $yearmonthdata[totalfinal_score_with_fatal]/$yearmonthdata[monthcount];
	}
	
	//echo '<pre>'.print_r($row[params][period],true).'</pre>';
	
	function calculate_avgs($params){
	
		foreach($params[period] as $param_datecreated => $param_datecreateddata){
			foreach($param_datecreateddata as $param_eval_id => $param_eval_iddata){
				$total_final_scores_without_fatal += $param_eval_iddata[final_score_without_fatal];
				$total_final_scores_with_fatal += $param_eval_iddata[final_score_with_fatal];
				++$recordcount[$param_datecreated];
				
			}
			
			$avg_total_without_fatal = $total_final_scores_without_fatal/$recordcount[$param_datecreated];
			$avg_total_with_fatal = $total_final_scores_with_fatal/$recordcount[$param_datecreated];
			
			$avgdata[$param_datecreated] = array(
							'avg_total_with_fatal' => $avg_total_with_fatal,
							'avg_total_without_fatal' => $avg_total_without_fatal,
							'total_final_scores_without_fatal' => $total_final_scores_without_fatal,
							'total_final_scores_with_fatal' => $total_final_scores_with_fatal,
							'recordcount' => $recordcount[$param_datecreated]
							);
			unset($total_final_scores_without_fatal);
			unset($total_final_scores_with_fatal);
			unset($avg_total_without_fatal);
			unset($avg_total_with_fatal);
		}
		return $avgdata;
	}
	
	$row[params][avg] = calculate_avgs($row[params]);
						
	function summarize($rows_summaries){
		$row[fields] = get_field_data();
		//echo '<pre>'.print_r($row[fields],true).'</pre>';
		foreach($rows_summaries as $datecreated => $datecreateddata){
			foreach($datecreateddata as $scores_id => $scoresdata){
				foreach($scoresdata as $categories => $categoriesdata){
					foreach($categoriesdata as $category => $categorydata){
						foreach($categorydata as $subcategories => $subcategoriesdata){
							foreach($subcategoriesdata as $subcategory => $subcategorydata){
								foreach($subcategorydata as $fieldid => $field_data){
									$percent_score_category += $field_data[percent_score];
									$weightage_category += $field_data[weightage];
									$percent_score_subcategory += $field_data[percent_score];
									$weightage_subcategory += $field_data[weightage];
									$percent_score_field += $field_data[percent_score];
									$weightage_field += $field_data[weightage];
									
									/*if($row[fields][$fieldid][fatal] == 1){
										$summary[summary][fatals][$scores_id][$row[fields][$fieldid][label]] = $percent_score_field;
										if($percent_score_field == 0){
											++$summary[summary][fatalscount][$scores_id];
										}
									}*/
									
									$summary[summary][$datecreated][field][$scores_id][$row[fields][$fieldid][label]] = ($percent_score_field/$weightage_field)*100;
									$summary[weights][$datecreated][field][$scores_id][$row[fields][$fieldid][label]] = array($percent_score_field, $weightage_field);
									$summary[titles][questions][$row[fields][$fieldid][label]] = $row[fields][$fieldid][label];
									$summary[percentage_score_on_question][$scores_id][$row[fields][$fieldid][label]] = ($percent_score_field/$weightage_field)*100;
									$summary[actual_score_on_question][$scores_id][$row[fields][$fieldid][label]] = $percent_score_field;
								unset($percent_score_field);
								unset($weightage_field);
								}
								$summary[summary][$datecreated][subcategory][$scores_id][$subcategory] = ($percent_score_subcategory/$weightage_subcategory)*100;
								$summary[weights][$datecreated][subcategory][$scores_id][$category][$subcategory] = array($percent_score_subcategory, $weightage_subcategory);
								if($subcategory != ''){ $summary[titles][subcategories][$subcategory] = $subcategory; }
								unset($percent_score_subcategory);
								unset($weightage_subcategory);
							}
							$summary[summary][$datecreated][categories][$scores_id][$category] = ($percent_score_category/$weightage_category)*100;
							$summary[weights][$datecreated][categories][$scores_id][$category] = array($percent_score_category, $weightage_category);
							if($category != 'Comments'){ $summary[titles][categories][$category] = $category; }
							$summary[evalids][$datecreated][$scores_id] = $scores_id;
							unset($percent_score_category);
							unset($weightage_category);
						}
					}
				}
				unset($rows_summaries[$scores_id]);
			}
		}

		return $summary;
	}
	
	$summary = summarize($row[scores]);
	$row[summary] = $summary[summary];
	$row[titles] =  $summary[titles];
	$row[evalids] =  $summary[evalids];
	$row[percentage_score_on_question] = $summary[percentage_score_on_question];
	$row[actual_score_on_question] = $summary[actual_score_on_question];
	//echo '<pre>'.print_r($row[evalids],true).'</pre>';

	switch($report_type){
		case 'detail':
			$row[reporttype][detail] = 'detail';
			break;
		case 'both':
			$row[reporttype][summary] = 'summary';
			$row[reporttype][detail] = 'detail';
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$row[reporttype][summary] = 'summary';
	}
	
	return display_cc_quality_eval_report($row);
}


function display_cc_quality_eval_report($row){

	if(isset($row[reporttype][summary])){
		$html .= '<hr>Monthly Evaluation Scores<hr>
				<table width="800" cellpadding="0" cellspacing="0">
					<tr>
						<th>Month</th>
						<th>Non Fatal Score</th>
						<th>Overall Interaction Score (With Fatal)</th>
					</tr>';
		foreach($row[params][month] as $yearmonth => $yearmonthdata){
			$html .= '<tr>
							<td class="text_values">'.date('M-Y',strtotime($yearmonth)).'</td>
							<td class="values">'.number_format($yearmonthdata[final_score_without_fatal],2).'%</td>
							<td class="values">'.number_format($yearmonthdata[final_score_with_fatal],2).'%</td>
					</tr>';
		}
		$html .= '</table>';
		
		
		$html .= '<hr>Average Evaluation Scores By Period<hr>
				<table width="800" cellpadding="0" cellspacing="0">
					<tr>
						<th>Eval Period</th>
						<th>Average Non Fatal Score</th>
						<th>Average Overall Interaction Score (With Fatal)</th>
					</tr>';
		foreach($row[params][avg] as $period => $period_data){
		
			$html .= '<tr>
							<td class="text_values">'.$period.'</td>
							<td class="values">'.number_format($period_data[avg_total_without_fatal],2).'%</td>
							<td class="values">'.number_format($period_data[avg_total_with_fatal],2).'%</td>
					</tr>';
		
		}
		$html .= '</table>';
		
		$html .= '<hr>Evaluation Scores<hr>
				<table width="800" cellpadding="0" cellspacing="0" class="sortable">
					<tr>
						<th>Eval Date</th>
						<th>Eval ID</th>
						<th>Auditor Name</th>
						<th>Login ID</th>
						<th>Agent Name</th>
						<th>MSISDN</th>
						<th>Non Fatal Score</th>
						<th>Overall Interaction Score (With Fatal)</th>
					</tr>';
		foreach($row[params][period] as $period => $period_data){
			foreach($period_data as $eval_id => $eval_id_data){
		
			$html .= '<tr>
							<td class="text_values">'.$period.'</td>
							<td class="text_values">'.$eval_id.'</td>
							<td class="text_values">'.$row[information][$eval_id][auditors_name].'</td>
							<td class="text_values">'.$row[information][$eval_id][login_id].'</td>
							<td class="text_values">'.$row[information][$eval_id][agent_name].'</td>
							<td class="text_values">'.$row[information][$eval_id][msisdn].'</td>
							<td class="values">'.number_format($eval_id_data[final_score_without_fatal],2).'%</td>
							<td class="values">'.number_format($eval_id_data[final_score_with_fatal],2).'%</td>
					</tr>';
			}
		
		}
		$html .= '</table><hr>';
		
		//echo '<pre>'.print_r($row[information],true).'</pre>';
	}
	
	if(isset($row[reporttype][detail])){
	//echo '<pre>'.print_r($row[titles][questions],true).'</pre>';
		$html .= 'Detail<hr>
				<table width="800" cellpadding="0" cellspacing="0" class="sortable">';
				$html .= '<tr>
						<th>Audit Date</th>
						<th>Auditors Name</th>
						<th>Interaction Date</th>
						<th>AHT of Call (In Seconds)</th>
						<th>AHT Bucket</th>
						<th>Time of Interaction</th>
						<th>Agent Name</th>
						<th>Login ID</th>
						<th>Team Leader</th>
						<th>Location</th>
						<th>Caller Mobile Number</th>
						<th>Call Que/Customer Que</th>
						<th>Language of the Customer</th>
						<th>No. of Queries on Call</th>
						<th>Case Type (As selected by agent)</th>
						<th>Call Type (As selected by agent)</th>
						<th>Call Subtype (As selected by agent)</th>
						<th>Case Type (Actual)</th>
						<th>Call Type  (Actual)</th>
						<th>Call Subtype  (Actual)</th>
						<th>Sample Criteria</th>
						<th>Week</th>
						<th>DOJ</th>
						<th>Tenure</th>
						<th>Tenure Bucket</th>
						<th>Average Non Fatal Score</th>
						<th>Average Overall Interaction Score (With Fatal)</th>';
		foreach($row[titles][categories] as $category){
			$html .= '<th>'.$category.'</th>';
		}
		foreach($row[titles][subcategories] as $subcategory){
			$html .= '<th>'.$subcategory.'</th>';
		}
		foreach($row[titles][questions] as $question){
			$html .= '<th colspan="2">'.$question.'</th>';
		}
		$html .= '<th>QA Comments on Overall Call</th>
					<th>QA Comments On Fatal</th>
					<th>Did the Agent Create Task(if Needed)</th>
					<th>Did the Agent provide FTR</th>
					<th>Hold Procedure Followed</th>
					<th>Was the Task Created Properly</th>
					<th>Did the Agent Provide On Call Resolution</th>
					<th>Repeat Predictability (Will the customer call back for the same issue)</th>';
	
		$html .= '</tr>';
		
			foreach($row[evalids] as $datecreated => $dateids){
				foreach($dateids as $ekey => $eval_id){
					$html .= '<tr>
								<td class="text_values">'.$datecreated.'</td>
								<td  class="text_values">'.$row[information][$eval_id][auditors_name].'</td>
								<td  class="text_values">'.$datecreated.'</td>
								<td  class="text_values">'.$row[information][$eval_id][aht].'</td>
								<td  class="text_values"><!--AHT Bucket--></td>
								<td  class="text_values">'.$row[information][$eval_id][interaction_time].'</td>
								<td  class="text_values">'.$row[information][$eval_id][agent_name].'</td>
								<td  class="text_values">'.$row[information][$eval_id][login_id].'</td>
								<td  class="text_values">'.$row[information][$eval_id][team_leader].'</td>
								<td  class="text_values">'.$row[information][$eval_id][location].'</td>
								<td  class="text_values">'.$row[information][$eval_id][msisdn].'</td>
								<td  class="text_values">'.$row[information][$eval_id][queue].'</td>
								<td  class="text_values">'.$row[information][$eval_id][language].'</td>
								<td  class="text_values">'.$row[information][$eval_id][no_of_queries].'</td>
								<td  class="text_values">'.$row[information][$eval_id][case_type_agent].'</td>
								<td  class="text_values">'.$row[information][$eval_id][call_type_agent].'</td>
								<td  class="text_values">'.$row[information][$eval_id][call_sub_type_agent].'</td>
								<td  class="text_values">'.$row[information][$eval_id][case_type].'</td>
								<td  class="text_values">'.$row[information][$eval_id][call_type].'</td>
								<td  class="text_values">'.$row[information][$eval_id][call_sub_type].'</td>
								<td  class="text_values">'.$row[information][$eval_id][sample_criteria].'</td>
								<td  class="text_values">'.$row[information][$eval_id][week].'</td>
								<td  class="text_values">'.$row[information][$eval_id][joining_date].'</td>
								<td  class="text_values"><!--Tenure--></td>
								<td  class="text_values"><!--Tenure Bucket--></td>
								<td class="text_values">'.number_format($row[params][period][$datecreated][$eval_id][final_score_without_fatal],2).'%</td>
								<td class="text_values">'.number_format($row[params][period][$datecreated][$eval_id][final_score_with_fatal],2).'%</td>
								';
					
					foreach($row[titles][categories] as $category){
						$html .= '<td class="values">'.number_format($row[summary][$datecreated][categories][$eval_id][$category],2).'%</td>';
					}
					
					foreach($row[titles][subcategories] as $subcategory){
						$html .= '<td class="values">'.number_format($row[summary][$datecreated][subcategory][$eval_id][$subcategory],2).'%</td>';
					}
					
					foreach($row[titles][questions] as $question){
						$html .= '<td class="values">'.number_format($row[percentage_score_on_question][$eval_id][$question],0).'%</td>';
						$html .= '<td class="values">'.$row[actual_score_on_question][$eval_id][$question].'</td>';
					}
		
					$html .= '<td  class="text_values">'.$row[information][$eval_id][qa_comments_on_overall_call].'</td>
							<td  class="text_values">'.$row[information][$eval_id][qa_comment_on_fatal].'</td>
							<td  class="text_values">'.$row[information][$eval_id][agent_create_task].'</td>
							<td  class="text_values">'.$row[information][$eval_id][agent_provide_fcr].'</td>
							<td  class="text_values">'.$row[information][$eval_id][hold_procedure_followed].'</td>
							<td  class="text_values">'.$row[information][$eval_id][task_created].'</td>
							<td  class="text_values">'.$row[information][$eval_id][agent_provide_resolution].'</td>
							<td  class="text_values">'.$row[information][$eval_id][repeat_predictability].'</td>';
					
					$html .= '</tr>';
					
				}
			}
			
		$html .= '</table><hr>';
	}
	
	/*$html .= '<hr>Evaluation Scores<hr>
			<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
				<tr>
					<th>Eval Date</th>
					<th>Auditor Name</th>
					<th>Agent Name</th>
					<th>MSISDN</th>
					<th>Quality Attributes</th>
					<th>Quality Parameter</th>
					<th>Question</th>
					<th>Weightage</th>
					<th>Score</th>
					<th>Percent Score</th>
				</tr>';
	foreach($row[scores] as $datecreated => $datecreateddata){
		foreach($datecreateddata as $scores_id => $scoresdata){
			foreach($scoresdata as $categories => $categoriesdata){
				foreach($categoriesdata as $category => $categorydata){
					foreach($categorydata as $subcategories => $subcategoriesdata){
						foreach($subcategoriesdata as $subcategory => $subcategorydata){
							foreach($subcategorydata as $fieldid => $field_data){
								$html .= '<tr>
												<td class="text_values">'.$datecreated.'</td>
												<td class="text_values">'.$row[information][$scores_id][auditors_name].'</td>
												<td class="text_values">'.$row[information][$scores_id][agent_name].'</td>
												<td class="text_values">'.$row[information][$scores_id][msisdn].'</td>
												<td class="text_values">'.$category.'</td>
												<td class="text_values">'.$subcategory.'</td>
												<td class="text_values">'.$field_data[question].'</td>
												<td class="text_values">'.$field_data[weightage].'</td>
												<td class="text_values">'.$field_data[score].'</td>
												<td class="text_values">'.$field_data[percent_score].'</td>
										</tr>';
							}
						}
					}
				}
			}
		}
	}
	$html .= '</table>';*/
	return $html;
	
}
?>