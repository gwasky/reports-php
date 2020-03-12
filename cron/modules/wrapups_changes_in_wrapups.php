<?php
function generate_changes_in_wrapups($usedate,$number_of_subjects=10,$period_grouping='month'){
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	custom_query::select_db('reportscrm');
	
	$period_interval_to = '0 DAY';
	
	if($period_grouping == 'day'){
		$period_interval_from = '7 DAY';
	}else{
		$period_interval_from = '5 MONTH';
		$usedate = substr($usedate,0,7).'-01';
	}

	$query = "
		select
			left(createdon,10) as date_created,
			wrapupsubcat as sub_category,
			subject, 
			count(subject) as number
		from 
			reportsphonecalls 
		where
			createdon between DATE_SUB('".$usedate."', INTERVAL ".$period_interval_from.") and DATE_ADD('".$usedate."', INTERVAL ".$period_interval_to.")
		group by
			date_created,sub_category,subject
	";
	
	//echo $query."\n";
	
	$wrapups = $myquerys->multiple($query);
	
	if(count(sub_category) == 0){ exit("No DATA!!! /n");}
	
	foreach($wrapups as $row){
		if(!in_array($row[date_created],$report[dimensions][dates])){
			$report[dimensions][dates][] = $row[date_created];
		}
		$report[dimensions][hierachy][$row[sub_category]][] = $row[subject];
		$report[raw_data][$row[date_created]][$row[sub_category]][$row[subject]] = $row[number];
	}
	
	foreach($report[dimensions][dates] as $date_key=>$date){
		foreach($report[dimensions][hierachy] as $sub_category=>$subject_list){
			foreach($subject_list as $subject){
				$report[raw_data][$date][$sub_category][$subject] = intval($report[raw_data][$date][$sub_category][$subject]);
				$report[raw_graph_data][$sub_category][$subject][$date] = $report[raw_data][$date][$sub_category][$subject];
				
				if($date_key > 0){
					$change = ($report[raw_data][$date][$sub_category][$subject] - $report[raw_data][$report[dimensions][dates][$date_key - 1]][$sub_category][$subject]);
					$report[org_data][change][$sub_category." => ".$subject] += $change;
					$report[org_data][abs_change][$sub_category." => ".$subject] += abs($change);
				}
			}
		}
	}
	
	unset($report[raw_data]);
	
	arsort($report[org_data][abs_change]);
	foreach($report[org_data][abs_change] as $key=>$value){
		$report[overall][change][$key] = $report[org_data][change][$key];
		if(
		   (count($report[change][least]) < $number_of_subjects) and
		   ($report[org_data][change][$key] < 0)
		   ){
			$report[change][least][$key] = $report[org_data][change][$key];
		}
		if(
		   (count($report[change][most]) < $number_of_subjects) and
		   ($report[org_data][change][$key] > 0)
		   ){
			$report[change][most][$key] = $report[org_data][change][$key];
		}
		if(
		   (count($report[change][most]) == $number_of_subjects) and (count($report[change][least]) == $number_of_subjects)
		   ){
			break;
		}
	}
	
	foreach($report[change] as $change_bracket=>$bracket_list){
		foreach($bracket_list as $sub_cat_subject_key=>$value){
			$sub_cat_subject_array = explode(" => ",$sub_cat_subject_key);
			$report[graph_key][$sub_cat_subject_key] = 'line '.++$graph_key;
			$report[graph_data][$report[graph_key][$sub_cat_subject_key]] = $report[raw_graph_data][$sub_cat_subject_array[0]][$sub_cat_subject_array[1]];
		}
	}

	$graph_detail[data]=$report[graph_data];
	$graph_detail[line_graph]=true;
	$graph_detail[display_title]=false;
	$graph_detail[title] = 'Largest Changes in Wrap up by '.$period_grouping.' up to '.$usedate;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=1100;
	$graph_detail[height]=800;
	$graph_detail[legend]=true;
	$period = 'Use date:'.$usedate.' Interval from '.$period_interval_from.' Interval to '.$period_interval_to;
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph_ids]['Large changes'] = $my_graph->Save();
	
	return display_changes_in_wrap_ups($report);
}

function display_changes_in_wrap_ups($report){
	$html = '
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<td>'.display_generic_graph($report[graph_ids]['Large changes']).'</td>
			</tr>
			<tr>
				<td style="height:10px;"></td>
			</tr>
			<tr>
				<td">
					<table border="0" cellpadding="1" cellspacing="0" width="50%">
					<tr>
						<th colspan="2">KEY :</th>
					</tr>
	';
		foreach($report[graph_key] as $key_value=>$key){
			$html .= '
				<tr>
					<th>'.$key.'</th>
					<td class="text_values">'.$key_value.'</td>
				</tr>
			';
		}
	$html .= '
					</table>
				</td>
			</tr>
		</table>
	';
	
	return attach_html_container($title='',$body=$html);
}
?>