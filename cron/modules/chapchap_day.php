<?php
function generate_chapchap($yesterday){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	//custom_query::select_db('reportscrm');
	$query = "
		SELECT
			reportsphonecalls.subject as subject,
			count(reportsphonecalls.subject) as number
		FROM
			reportsphonecalls
		WHERE left(reportsphonecalls.createdon,10) = '$yesterday' and
			reportsphonecalls.wrapupsubcat = 'Chap Chap'
		GROUP BY subject
		ORDER BY number desc
	";
	
	$query_all = "
		SELECT
			left(reportsphonecalls.createdon,10) as the_date,
			chap_call_dest,
            chap_caller_type,
			count(reportsphonecalls.subject) as number
		FROM
			reportsphonecalls
		WHERE reportsphonecalls.createdon between DATE_SUB(NOW(), INTERVAL 14 DAY) and NOW() AND 
			reportsphonecalls.wrapupsubcat = 'Chap Chap' AND
			reportsphonecalls.chap_call_dest != 0 AND 
			reportsphonecalls.chap_call_dest != '' AND
			reportsphonecalls.chap_caller_type != '' AND
			reportsphonecalls.chap_call_dest != 1
		GROUP BY
			the_date,chap_call_dest,chap_caller_type
	";
	
	//echo $query_all."\n";
	
	$data[subject_count] = $myquery->multiple($query,'ccba02.reportscrm');
	$data[row_stuff] = $myquery->multiple($query_all,'ccba02.reportscrm');
	foreach($data[row_stuff] as $row){
		//echo $row[the_date]." =? ".$yesterday.
		if($row[the_date] == $yesterday){
			$data[call_dest][$row[chap_call_dest]] += $row[number];
			$data[call_type][$row[chap_caller_type]] += $row[number];
		}
		
		//Getting the total number of calls per day
		$data[graphs][dates][$row[the_date]] += $row[number];
		
		//Getting retailer calls per day
		if($row[chap_caller_type] == 'Retailer'){
			$trend_data[retailer_calls][$row[the_date]] += $row[number];
		}
	}
	
	//GETTING THE %AGE OF RETAILER CALLS PER DAY
	foreach($data[graphs][dates] as $date=>$call_number){
		$data[graphs][retailer_calls][$date] = number_format(($trend_data[retailer_calls][$date]/$call_number * 100),1);
	}
	
	//print_r($data);
	
	//Graph data for the Wrapped up calls per day
	$graph_detail[data]=array('Calls'=>$data[graphs][dates]);
	$graph_detail[type] = 'line';
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[title] = 'Wrapped up chap chap calls per day as at '.$yesterday;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=600;
	$graph_detail[legend]=true;
	$graph_detail[line_colors]=array('red');
	$period = 'First of month to '.$yesterday;

	$my_graph->graph($title=$graph_detail[title], $period, $graph_detail,$type=$graph_detail[type]);
	custom_query::select_db('graphing');
	$data[graph_ids][dates] = $my_graph->Save();
	
	//Graph data for the Percentage of Retailer calls per day to the overall chap chap calls
	$my_graph->id = '';
	$graph_detail[data] = array('Percentages'=>$data[graphs][retailer_calls]);
	$graph_detail[title] = 'Percentages of retailer calls to all Chap chap calls as at '.$yesterday;
	$my_graph->graph($title = $graph_detail[title], $period, $graph_detail,$type=$graph_detail[type]);
	custom_query::select_db('graphing');
	$data[graph_ids][retailer_calls] = $my_graph->Save();
	
	return $data;
}

function display_chap_chap_report($report){

$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;
		font-size:9px;
		white-space:nowrap;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	body{
		font-family:Verdana, Geneva, sans-serif;
	}
	
	label,
	.select,
	.textbox{
		font-size:9px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.values{
		text-align:left;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		border-left:#333333 1px dashed;
		vertical-align:top;
	}
	
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		line-height:12px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.wrap_text{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		border-left:#333333 1px dashed;
		width:20%;
		vertical-align:top;
	}
	
	.wrap_text_task{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:10%;
	}
	
	.form_bar{
		background-color:#CCC;
		background-color:#00C;
	}
	
	form_td{
		white-space:nowrap;
	}
	
	.menu_link_active,
	.menu_link{
		font-size:9px;
		line-height:20px;
		border-bottom: #CCCCCC 1px dashed;
	}
	
	tr#totals td{
		font-size:10px;
		font-weight:bold;
		background-color:#CCC;
	}
	
	img.graph{
		background-color:#000000;
		padding:5px;
	}
	
	</style>
	</head>
	<body><table><tr>';
	if(count($report[subject_count])>0){
		$total = '';
		$html .='<td valign="top">
			<table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<tr><th colspan="2">Chap Chap Subject Summary</th></tr>
			<tr><th>Chap Chap Subject</th><th>Inquiry Count</th></tr>';
			foreach($report[subject_count] as $row){
				$html .= '<tr><td class="text_values">'.$row[subject].'</td><td class="text_values">'.$row[number].'</td></tr>';
				$total += $row[number];
			}
		$html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}else{
		echo "Subject count ".count($report[subject_count])."\n";
	}
	if(count($report[call_dest])>0){
		$total = '';
		$html .='<td valign="top">
				<table border="0" cellpadding="1" cellspacing="0" style="float:left">
				<tr><th colspan="2">Help Line Number Hits</th></tr>
				<tr><th>Call Destination</th><th>Count</th></tr>';
				foreach($report[call_dest] as $dest=>$values){
				$html .= '<tr><td class="text_values">'.$dest.'</td><td class="text_values">'.$values.'</td></tr>';
				$total += $values;
		}
			$html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}else{
		echo "Call dest count ".count($report[call_dest])."\n";
	}
	if(count($report[call_type])>0){
		$total = '';
		$html .='<td valign="top">
				<table border="0" cellpadding="1" cellspacing="0" style="float:left">
				<tr><th colspan="2">Caller Type</th></tr>
				<tr><th>Call Type</th><th>Count</th></tr>';
				foreach($report[call_type] as $type=>$values){
				$html .= '<tr><td class="text_values">'.$type.'</td><td class="text_values">'.$values.'</td></tr>';
				$total += $values;
				}
			 $html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}else{
		echo "Call type count ".count($report[call_type])."\n";
	}
	$html .= '
		</tr>
		<tr><td colspan="3"><table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>Trend : Chap chap calls per day</th>
			</tr>
			<tr>
				<td>'.display_generic_graph($report[graph_ids][dates]).'</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<th>Trend : Percentages of retailer to all chap chap calls per day</th>
			</tr>
			<tr>
				<td>'.display_generic_graph($report[graph_ids][retailer_calls]).'</td>
			</tr>
		</table></td></tr>
		</table>
		</body>
	';
	
	return $html;
}	

/*
function display_chap_chap_report($report){

$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;
		font-size:9px;
		white-space:nowrap;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	body{
		font-family:Verdana, Geneva, sans-serif;
	}
	
	label,
	.select,
	.textbox{
		font-size:9px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.values{
		text-align:left;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		border-left:#333333 1px dashed;
		vertical-align:top;
	}
	
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		line-height:12px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.wrap_text{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		border-left:#333333 1px dashed;
		width:20%;
		vertical-align:top;
	}
	
	.wrap_text_task{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:10%;
	}
	
	.form_bar{
		background-color:#CCC;
		background-color:#00C;
	}
	
	form_td{
		white-space:nowrap;
	}
	
	.menu_link_active,
	.menu_link{
		font-size:9px;
		line-height:20px;
		border-bottom: #CCCCCC 1px dashed;
	}
	
	tr#totals td{
		font-size:10px;
		font-weight:bold;
		background-color:#CCC;
	}
	
	img.graph{
		background-color:#000000;
		padding:5px;
	}
	
	</style>
	</head>
	<body><table><tr>';
	if(count($report[subject_count])>0){
		$total = '';
		$html .='<td valign="top">
			<table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<tr><th colspan="2">Chap Chap Subject Summary</th></tr>
			<tr><th>Chap Chap Subject</th><th>Inquiry Count</th></tr>';
			foreach($report[subject_count] as $row){
				$html .= '<tr><td class="text_values">'.$row[subject].'</td><td class="text_values">'.$row[number].'</td></tr>';
				$total += $row[number];
			}
		$html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}
	if(count($report[call_dest])>0){
		$total = '';
		$html .='<td valign="top">
				<table border="0" cellpadding="1" cellspacing="0" style="float:left">
				<tr><th colspan="2">Help Line Number Hits</th></tr>
				<tr><th>Call Destination</th><th>Count</th></tr>';
				foreach($report[call_dest] as $dest=>$values){
				$html .= '<tr><td class="text_values">'.$dest.'</td><td class="text_values">'.$values.'</td></tr>';
				$total += $values;
		}
			$html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}
	if(count($report[call_dest])>0){
		$total = '';
		$html .='<td valign="top">
				<table border="0" cellpadding="1" cellspacing="0" style="float:left">
				<tr><th colspan="2">Caller Type</th></tr>
				<tr><th>Call Type</th><th>Count</th></tr>';
				foreach($report[call_type] as $type=>$values){
				$html .= '<tr><td class="text_values">'.$type.'</td><td class="text_values">'.$values.'</td></tr>';
				$total += $values;
				}
			 $html .= '<tr><th>Total</th><th>'.$total.'</th></tr></table></td>';
	}
	$html .= '</tr></body></table>';
	
	return $html;
}
*/

?>