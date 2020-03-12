<?php

function show_table($data,$padding=0,$spacing=0,$border=0,$align='centre',$width='90%',$table='normal',$style='border-color:#FFF;'){
	//print_r($data);
	
	if(count($data[rows]) < 1){ return ''; }
	
	
	$html = '
		<table width="'.$width.'" border="'.$border.'" align="'.$align.'" cellpadding="'.$padding.'" cellspacing="'.$spacing.'" style="'.$style.'" class="data_table">
	';
	
	$row_list = array_keys($data[rows]);
	$top_title_list = array_keys($data[rows][$row_list[0]][data]);
	
	if($data[title]!=''){
		$html .= '
			<tr class="th"><th colspan="'.count($top_title_list).'">'.$data[title].'</th></tr>
		';
	}
	
	$html .= '
	<tr class="th">
	';
	foreach($top_title_list as $title){
		$html .= '
		<th>'.titlelise($title).'</th>
		';
	}
	$html .= '</tr>';
	
	foreach($data[rows] as $row){
		++$V_counter;
		$html .= '
		<tr  class="'.row_style($V_counter).'"  id="'.$row[html_id].'">
		';
		
		foreach($row[data] as $cellvalue){
			++$H_counter;
			if($table=='top_left' and $H_counter == 1){
				$html .= '
				<th  '.'class="'.cell_class($cellvalue).'">'.$cellvalue.'</th>
				';
			}else{
				$html .= '
				<td  '.'class="'.cell_class($cellvalue).'">'.$cellvalue.'</td>
				';
			}
			
		}
		unset($H_counter);
		
		$html .= '
		</tr>
		';
	}
	unset($V_counter);
	
	if($data[notes]){
		$html .= '
			<!--<tr>
				<td class="spacer"></td>
			</tr>-->
			<tr>
				<td class="notes" colspan="'.count($top_title_list).'">'.$data[notes].'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}


function show_static_table($data,$padding=0,$spacing=0,$border=0,$align='centre',$width='90%',$table='normal',$style='border-color:#FFF;'){
	//print_r($data);
	if(count($data[rows]) < 1){ return ''; }
	
	
	$html = '
		<table width="'.$width.'" border="'.$border.'" align="'.$align.'" cellpadding="'.$padding.'" cellspacing="'.$spacing.'" style="'.$style.'" class="data_table">
	';
	
	$row_list = array_keys($data[rows]);
	$top_title_list = array_keys($data[rows][$row_list[0]][data]);
	
	if($data[title]!=''){
		$html .= '
			<tr class="th"><th colspan="'.count($top_title_list).'">'.$data[title].'</th></tr>
		';
	}
	
	$html .= '<tr class="th">';
	foreach($top_title_list as $title){
		$html .= '<th>'.titlelise($title).'</th>';
	}
	$html .= '</tr>';
	
	foreach($data[rows] as $cellName=>$cellvalue){
		++$V_counter;
		$html .= '<tr class="'.row_style($V_counter).'" id="'.$row[html_id].'">';
		//foreach($row[data] as $cellName=>$cellvalue){
			++$H_counter;
			if($table=='top_left' and $H_counter == 1){
			
				$html .= '<th class="'.cell_class($cellvalue).'">'.titlelise($cellName).'</th>';
			}else{
			
				$html .= '<td class="'.cell_class($cellName).'">'.titlelise($cellName). ': '.$cellvalue.'</td>';
			}
			
		//}
		unset($H_counter);
		
		$html .= '</tr>';
	}
	unset($V_counter);
	
	if($data[notes]){
		$html .= '
			<!--<tr>
				<td class="spacer"></td>
			</tr>-->
			<tr>
				<td class="notes" colspan="'.count($top_title_list).'">'.$data[notes].'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}

function cell_val($value){
	if(is_numeric($value) or (str_replace(array('-',' ','/',':','.',','), '', preg_replace('/[0-9]/', '',$value)) == '')){
		return number_format($value,1);
	}else{
		return ucfirst(strtolower($value));
	}
}

function titlelise($value){
	return str_replace(array('_'),' ',ucfirst($value));
}

function cell_class($value){
	if(is_numeric($value) or (str_replace(array('-',' ','/',':','.',','), '', preg_replace('/[0-9]/', '',$value)) == '')){ 
		return 'values'; 
	}else{ 
		return 'text_values'; 
	}
}

function attach_html_container2($title,$body){
	return '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=charset=iso-8859-1" />
	<style type="text/css">
	table,td,div{
		font-size:10px;
		font:calibri;
	}
	
	tr{
		border-bottom:#FFF 5px solid;
	}
	
	.even{
		background:#FBD4B4;
		height:30px;
	}
	
	.odd{
		background:#FDE9D9;
		height:30px;
	}
	
	td.data_td{
		border:none;
		border:0px;
	}
	
	#data_td{
		border-left:1px dashed #CCC;
		border-right:1px dashed #CCC;
	}
	
	.data_table{
		min-width:300px;
		width:320px;
	}
	
	.box,
	div.section_head,
	div.category_head{
		width:1500px;
	}
	
	div.section_head{
		font-size:100%;
		height:35px;
		line-height:140%;
		background:#009;
		vertical-align:middle;
		font-weight: normal;
		color:#FFF;
		text-align:center;
		padding:10px 0 10px 0;
	}
	
	div.category_head{
		font-size:85%;
		vertical-align:middle;
		font-weight: normal;
		color:#666;
		text-align:center;
		padding:3px 0 3px 0;
		border-bottom: 1px solid #999;
		border-top: 1px solid #999;
	}
	
	tr.th th{
		font-weight: normal;
		text-align:center;
		vertical-align:middle;
		color:#000;
		/*background:#FBD4B4;*/
		background:#E36C0A;
		white-space:nowrap;
		font-size:105%;
		height:20px;
		line-height:120%;
		border-bottom:#FFF 1px solid;
		border-right:#FFF 1px solid;
	}
	
	tr.odd th,
	tr.even th{
		font-weight: normal;
		text-align:left;
		vertical-align:middle;
		color:#000;
		background:#E36C0A;
		white-space:nowrap;
		font-size:102%;
		line-height:120%;
	}
	
	#totals{
		font-size:104%;
		font-weight:normal;
	}
	
	.spacer{
		padding:10px;
	}
	
	td.notes{
		padding:10px 1px 1px 1px;
		background:#FFFBF9;
	}
	
	body,
	.select,
	.textbox{
		font-size:100%;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.values{
		text-align:right;
		font-size: 100%;
		line-height:130%;
		white-space:nowrap;
		border-bottom:#FFF 1px solid;
		border-right:#FFF 1px solid;
		vertical-align:middle;
	}
	
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 100%;
		line-height:130%;
		white-space:nowrap;
		border-bottom:#FFF 1px solid;
		border-right:#FFF 1px solid;
		vertical-align:middle;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 100%;
		line-height:130%;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#FFF 1px solid;
		border-right:#FFF 1px solid;
		vertical-align:middle;
	}
	
	.red_text_values{
		background-color: #AE0000;
		color:#FFF;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 100%;
		font-weight: bold;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#FFF 1px solid;
		border-right:#FFF 1px solid;
		vertical-align:middle;
	}
	
	.rowhead{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 100%;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:20%;
		vertical-align:top;
		background:#009;
	}
	.wrap_text{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 100%;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:20%;
		vertical-align:top;
	}
	
	.wrap_text_task{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 100%;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:10%;
	}
	
	.Airtel{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
			
	.Warid{
		background:#0000FF;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	.Airtel_values{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.Airtel_text_values{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
			
	.Warid_values{
		background:#000066;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.Warid_text_values{
		background:#000066;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.totals_values{
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		font-weight:bold;
		background:#EBEBEB;
	}
	
	.totals_text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		font-weight:bold;
		background:#EBEBEB;
	}
	
	.totals_text_values_small{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		background:#EBEBEB;
	}

	</style>
	<title>'.$title.'</title>
	</head>
	<body>'.$body.'</body>
	</html>
	';
}

function attach_html_container($title,$body){
	return '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	
	.th{
		background:#CCCCCC;
	}
	
	.even{
		background:#DDDDDD;
	}
	
	.odd{
		background:#FFF;
	}
	
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	#totals{
		font-size:12px;
		font-weight:bold;
	}
	
	body,
	.select,
	.textbox{
		font-size:11px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.values{
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.red_text_values{
		background-color: #AE0000;
		color:#FFF;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		font-weight: bold;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.rowhead{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:20%;
		vertical-align:top;
		background:#009;
	}
	.wrap_text{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:20%;
		vertical-align:top;
	}
	
	.wrap_text_task{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
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
		line-height:20px;
		border-bottom: #CCCCCC 1px dashed;
	}
	
	.menu_link_active,
	.menu_link:hover{
		display:inherit;
		background-color:#006;
		color:#FFF;
	}
	
	.menu_link_active a{
		color:#FFF;
		text-decoration:none;
	}
	.menu_link a,.menu_link a:visited{
		color:#000;
		text-decoration:none;
	}
	
	.menu_link a:hover{
		color:#FFF;
	}
	
	.menu_link_active a:hover{
		font-weight:bold;
	}
	
	.menu_link_active:hover{
		display:inherit;
		background-color:#FF0000;
		/*color:#FFF;*/
	}
	
	.search{
		display:none;
		font-size: 11px;
	}
	.search_show{
		display:block;
	}
	
	.Airtel{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
			
	.Warid{
		background:#0000FF;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	.Airtel_values{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.Airtel_text_values{
		background:#FF0000;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
			
	.Warid_values{
		background:#000066;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.Warid_text_values{
		background:#000066;
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		color:#FFF;
		white-space:nowrap;
		font-size:14px;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
	}
	
	.totals_values{
		text-align:right;
		font-size: 11px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		font-weight:bold;
		background:#EBEBEB;
	}
	
	.totals_text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		font-weight:bold;
		background:#EBEBEB;
	}
	
	.totals_text_values_small{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 11px;
		line-height:13px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:middle;
		background:#EBEBEB;
	}
	</style>
	<title>'.$title.'</title>
	</head>
	<body>'.$body.'</body>
	</html>
	';
}

function display_value_trend($current_value,$value_before){
	if($current_value < $value_before){
		$html .= ' Down ';
	}else if ($current_value == 0 || $value_before == 0){
		$html .= ' - ';
	}else{
		$html .= ' Up ';
	}
	
	return $html;
}

function display_cases_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
	
	
					<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Total Cases (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Total Cases</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>';
	return $HTML;
}

function display_closed_cases_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Closed Cases (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Closed Cases</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function display_offered_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Offered Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Offered Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function display_answered_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Answered Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Answered Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function display_abandon_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;

			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Abandon Calls (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Abandoned Calls</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				 <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function display_prepaid_servicelevel_ivr_count($row){
	
	$row = $row[0];
	
	$HTML = '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Telesales Update</title>
		<style type="text/css">
		<!--
		body {
			font-family:Calibri, verdana, Arial;
		}
		
		th{
			text-align:left;
			font-size:12px;
			background-color:#006;
			color:#FFF;
			font-weight:bold;
			border-right:1px solid #333333;
			border-left:1px solid #333333;
			border-bottom:1px solid #333333;
		}
		th.titles{
			text-align:left;
			font-size:12px;
			background-color:#fff;
			color:#000;
			font-weight:bold;
			border-bottom:1px solid #333333;
		}
		
		td.values,
		td.comments{
			border-bottom:1px solid #333333;
			border-right:1px solid #333333;
			background-color:#fff;
			font-size:12px;
			text-align:center;
		}
		
		.values{
			text-align:right;
			font-size:90%;
		}
		
		.comments{
			font-size:70%;
		}
		-->
		</style></head>
				<table width="100%" border="0">
  <tr>
    <th width="219">&nbsp;</th>
    <th colspan="2">Prepaid Service Levels (Peak/Drop) Analysis</th>
  </tr>
  <tr>
    <th>Prepaid Service Levels</th>
    <th>Count</th>
    <th>Peak/Drop</th>
  </tr>
				  <tr>
					<th class = "titles">Yesterday</th>
					<td class = "values">'.$row[yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[yesterday]-$row[before_yesterday])/$row[before_yesterday])*100)).display_value_trend($row[yesterday],$row[before_yesterday]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Yesterday</th>
					<td class = "values">'.$row[before_yesterday].'</td>
					<td class = "values">'.abs(number_format((($row[before_yesterday]-$row[b_before_yesterday])/$row[b_before_yesterday])*100)).display_value_trend($row[before_yesterday],$row[b_before_yesterday]).'</td>
				  </tr>
				   <tr>
					<th class = "titles">Last Week ('.$row[last_week_num].')</th>
					<td class = "values">'.$row[last_week].'</td>
					<td class = "values">'.abs(number_format((($row[last_week]-$row[before_last_week])/$row[before_last_week])*100)).display_value_trend($row[last_week],$row[before_last_week]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Week ('.$row[before_last_week_num].')</th>
					<td class = "values">'.$row[before_last_week].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_week]-$row[b_before_last_week])/$row[b_before_last_week])*100)).display_value_trend($row[before_last_week],$row[b_before_last_week]).'</td>
				  </tr>
				  </tr>
				 <tr>
					<th class = "titles">Last Month</th>
					<td class = "values">'.$row[last_month].'</td>
					<td class = "values">'.abs(number_format((($row[last_month]-$row[before_last_month])/$row[before_last_month])*100)).display_value_trend($row[last_month],$row[before_last_month]).'</td>
				  </tr>
				  <tr>
					<th class = "titles">Before Last Month</th>
					<td class = "values">'.$row[before_last_month].'</td>
					<td class = "values">'.abs(number_format((($row[before_last_month]-$row[b_before_last_month])/$row[b_before_last_month])*100)).display_value_trend($row[before_last_month],$row[b_before_last_month]).'</td>
				  </tr>
				</table>
';
	return $HTML;
}

function display_cc_ivr_calls_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['IVR Daily Calls Trend'].'.jpg" />';
	
	return $html;
	}
	
function display_repeat_wrap_callstatus_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
	}
	
function display_repeat_wrap_customer_satisfaction_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
	}
function display_selfcare_wrapup_report($report){
	
	$html = '
	<table><tr><th align="left">This Trend is only an indication of the activity categorisation behaviour at call centre for scenarios available on USSD. It does not graph USSD usage as might be mis-interpreted.It starts from September 9th which is the date of Launch of this USSD Service.</th><tr>
	<tr><th><img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /><th></tr></table>';
	
	return $html;
	}

function display_repeat_wrap_customer_profile_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
	}


function display_cc_ivr_monthly_calls_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['IVR Monthly Calls Trend'].'.jpg" />';
	
	return $html;
	}


function display_cc_ivr_svl_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Daily IVR Service Level Trend'].'.jpg" />';
	
	return $html;
	}


function display_cc_monthly_ivr_svl_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Monthly IVR Service Level Trend'].'.jpg" />';
	
	return $html;
	}


function display_cc_cases_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Cases Trend By Day'].'.jpg" />';
	
	return $html;
}


function display_cc_monthly_cases_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Cases Trend By Month'].'.jpg" />';
	
	return $html;
}

function display_pakacenter_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}

function display_warid_cs_rate_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}

function display_paka_rate_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}


function display_recommend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}

function display_period_network_pie($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}

function display_conversion_rate_trend_report($report){
$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}

function display_account_cases_trend_report($report){
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
                
	return $html;
}

function display_analysis_report_T(){
			
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	/*font:calibri;*/
	font-family:Verdana, Geneva, sans-serif;
	font-size:11px;
	/*font-family:Tahoma, Geneva, sans-serif;*/
}

th,td{
	border-bottom:#333333 1px solid; border-right:#333333 1px solid; padding:2px;
}

td{
	font-size:100%;
}

th{
	white-space:nowrap;
	font-size:100%;
	vertical-align:middle;
	text-align:left;
	font-weight:bold;
}
	
.top_th{
	background-color:#FF0000;color:#FFFFFF;
}

.row {
	color:#000000; border-top:#333333 1px solid; border-left:#333333 1px solid; border-right:#333333 1px solid;
}

.row_title {
	color:#FFFFFF; background-color:#00F;font-weight:bold;
}

.value{
	text-align:right;	
}

</style>
</head>
		<body>
			<table width="100%" border="0" cellpadding="1" cellspacing="0">

		<tr>
			<th scope="col" class="row_title">Day By Day Trend</th>

			<th scope="col" class="top_th">Monthly Trend</th>

		</tr>
		<tr>
			<th scope="col" colspan="2">Paka Care centre Activities in numbers</th>
		</tr>
		  <tr class="row">
			<td scope="col">';
				$html .= display_generic_graph(generate_sms_counts_graph_data(generate_sms_counts($from=date('Y-m-d'),$back_period ='30',$interval = 'day'))); $html .= '
			</td>
		
			<td class="value">';
				$html .= display_generic_graph(generate_sms_counts_graph_data(generate_sms_counts($from=date('Y-m-d'),$back_period ='6',$interval = 'month'))); $html .= '
				</td>
		  </tr>

		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact cc business analysis.</p>
</body>
</html>';
	
	//exit("Exiting ...\n");
	
	return $html;
}

function display_cpc_month_trend_report($report){
	
	$html = '<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" />';
	
	return $html;
}
function display_conversion_rate_report($report){
	
	//print_r($report['Prepaid service level']); echo "<br>";
	
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	/*font:calibri;*/
	font-family:Verdana, Geneva, sans-serif;
	font-size:11px;
	/*font-family:Tahoma, Geneva, sans-serif;*/
}

th,td{
	border-bottom:#333333 1px solid; border-right:#333333 1px solid; padding:2px;
}

td{
	font-size:100%;
}

th{
	white-space:nowrap;
	font-size:100%;
	vertical-align:middle;
	text-align:left;
	font-weight:bold;
}
	
.top_th{
	background-color:#FF0000;color:#FFFFFF;
}

.row {
	color:#000000; border-top:#333333 1px solid; border-left:#333333 1px solid; border-right:#333333 1px solid;
}

.row_title {
	color:#FFFFFF; background-color:#00F;font-weight:bold;
}

.value{
	text-align:right;	
}

</style>
</head>
		<body>
			<table width="100%" border="0" cellpadding="1" cellspacing="0">

			<tr>
			<td>
			<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /></td>
			</tr>
		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact cc business analysis.</p>
</body>
</html>
	';
	return $html;
}

function display_account_cases_report($report){
	
	//print_r($report['Prepaid service level']); echo "<br>";
	
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

body{
	/*font:calibri;*/
	font-family:Verdana, Geneva, sans-serif;
	font-size:11px;
	/*font-family:Tahoma, Geneva, sans-serif;*/
}

th,td{
	border-bottom:#333333 1px solid; border-right:#333333 1px solid; padding:2px;
}

td{
	font-size:100%;
}

th{
	white-space:nowrap;
	font-size:100%;
	vertical-align:middle;
	text-align:left;
	font-weight:bold;
}
	
.top_th{
	background-color:#FF0000;color:#FFFFFF;
}

.row {
	color:#000000; border-top:#333333 1px solid; border-left:#333333 1px solid; border-right:#333333 1px solid;
}

.row_title {
	color:#FFFFFF; background-color:#00F;font-weight:bold;
}

.value{
	text-align:right;	
}

</style>
</head>
		<body>
			<table width="100%" border="0" cellpadding="1" cellspacing="0">

			<tr>
			<td>
			<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /></td>
			</tr>
		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact cc business analysis.</p>
</body>
</html>
	';
	return $html;
}

function display_repeat_calls_call_status1($report){
	$html = '<table>
				<tr>
					<td>Repeat Call Report</td>
				</tr>
				<tr>
					 <td>Answered</td>
					 <td>Not Answered</td>
					 <td>Call Dropped</td>
					 <td>Busy</td> 
				</tr>'; 
				//print_r($data[callstatus]);
				foreach($report[callstatus] as $row){
				$html .= '
				<tr>
					<td>'.$row[Answered].'</td>
					<td>'.$row[NotAnswered].'</td>
					<td>'.$row[CallDropped].'</td>
					<td>'.$row[Busy].'</td>
				</tr>';
				} 
	$html .= '</table>';
	//echo $html;
	return $html;				
}

function display_repeat_calls_call_status($report){
//print_r($report);
$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CC IVR Flash Report</title>
<style>

.rowhead{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 12px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		border-left:#333333 1px dashed;
		width:50%;
		vertical-align:top;
		background:white;
	}
th{
	white-space:nowrap;
	font-size:100%;
	vertical-align:middle;
	text-align:left;
	font-weight:bold;
	background:#009;
}
.values{
		text-align:right;
		font-size: 10px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
</style>
</head>
<body><table>
<tr><th colspan="2">KEY:</td></tr>
<tr><td class="rowhead">NA:</td><td class="rowhead">Not Answered</td>
</tr>
</table>
<table width="100%">
	<tr><td width="100px">
			<table border="0" cellpadding="2" cellspacing="0" width="100px" style="float:left">
				<tr>
					<th colspan="2">Caller Status</th>
				</tr>';
				$total_status=array_sum($report[callstatus]);
				foreach($report[callstatus] as $key=>$value){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
					$html .='<tr>
				 			<td class="rowhead">Total</td>
							<td class="values">'.$total_status.'</td>
							</tr>
			</table>
		 </td>
		 <td width="100px">
		 	 <table border="0" cellpadding="2" cellspacing="0" width="100px" style="float:left">
				<tr>
					<th colspan="2">Reason for Repeat Call According to the Customer</th>
				</tr>';
				$total_reasonForRepeatCall=array_sum($report[ReasonForRepeatCall]);
				foreach($report[ReasonForRepeatCall] as $key=>$value){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
					$html .='<tr>
				 			<td class="rowhead">Total</td>
							<td class="values">'.$total_reasonForRepeatCall.'</td>
							</tr>
			</table>
		 </td>
	</tr>
	<tr>
		<td width="100px">
		<table border="0" cellpadding="2" cellspacing="0" width="100px" style="float:left">
				<tr>
					<th colspan="2">How Customers Rate Warid Services at the Helpline:</th>
				</tr>';
				$total=array_sum($report[satisfaction]);
				foreach($report[satisfaction] as $key=>$value){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
					$html .='<tr>
				 			<td class="rowhead">Total</td>
							<td class="values">'.$total.'</td>
							</tr>
			</table>
		 </td>
		 <td width="100px">
		 	 <table border="0" cellpadding="2" cellspacing="0" width="10px" style="float:left">
				<tr>
					<th colspan="2">Compliments about Warid</th>
				</tr>';
				$total_compliments=array_sum($report[Compliments]);
				foreach($report[Compliments] as $key=>$value){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
					$html .='<tr>
				 			<td class="rowhead">Total</td>
							<td class="values">'.$total_compliments.'</td>
							</tr>
			</table>
		 </td>
		 <td width="100px">
		 	 <table border="0" cellpadding="2" cellspacing="0" width="100px" style="float:left">
				<tr>
					<th colspan="2">Services Activated as a Result of the Rentention Call</th>
				</tr>';
				$total_profile=array_sum($report[wrapupprofiles]);
				foreach($report[wrapupprofiles] as $key=>$value){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
					$html .='<tr>
				 			<td class="rowhead">Total</td>
							<td class="values">'.$total_profile.'</td>
							</tr>
			</table>
		 </td>
	</tr>
	<tr><td width="100px">
			<table border="0" cellpadding="2" cellspacing="0" width="100px" style="float:left">
				<tr>
					<th colspan="2">Highest Repeat Call Agents</th>
				</tr>';
				//$total_reasonForRepeatCall=array_sum($report[agent_repeat_calls]);
				foreach($report[agent_repeat_calls] as $key=>$value){
				$i++;
				if($i<6){
				$html .='<tr>
							<td class="rowhead">'.$key.'</td>
							<td class="values">'.$value.'</td>
					</tr>'; 
						}
						}
					$html .='<tr>
				 			<td class="rowhead"></td>
							<td class="values"></td>
							</tr>
			</table>
		 </td>
		 <td>
		 	 
		 </td>
	</tr>
				';
		$html .='</body>';
	return $html;				
}

function display_generic_graph($graph_id,$with_td=false){
	
	if($with_td){ $html .= '
		<td>
	';}
	$html .= '
		<span style="background-color:#000000; padding:5px;">
			<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$graph_id.'.jpg" />
		</span>
	';
	if($with_td){ $html .= '
		</td>
	';}
	
	return $html;
}

function display_paka_care_report1($report){

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
	
	.red_text_values{
		background-color: #AE0000;
		color:#FFF;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		font-weight: bold;
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
	
	.menu_link_active,
	.menu_link:hover{
		display:inherit;
		background-color:#006;
		color:#FFF;
	}
	
	.menu_link_active a{
		color:#FFF;
		text-decoration:none;
	}
	.menu_link a,.menu_link a:visited{
		color:#000;
		text-decoration:none;
	}
	
	.menu_link a:hover{
		color:#FFF;
	}
	
	.menu_link_active a:hover{
		font-weight:bold;
	}
	
	.menu_link_active:hover{
		display:inherit;
		background-color:#FF0000;
		/*color:#FFF;*/
	}
	
	.search{
		display:none;
		font-size: 9px;
	}
	.search_show{
		display:block;
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
	<body><table>';
	
	
	if(count($report[warid_cs]>0)){
	arsort($report[warid_cs]);
	$total = '';
		$report[warid_cs]['Good(7-9)'][total]= array_sum($report[warid_cs]['Good(7-9)']);
		$report[warid_cs]['Average(4-6)'][total] = array_sum($report[warid_cs]['Average(4-6)']);
		$report[warid_cs]['Poor(1-3)'][total] = array_sum($report[warid_cs]['Poor(1-3)']);
		$report[warid_cs]['Excellent(10)'][total] = array_sum($report[warid_cs]['Excellent(10)']);
		$html .=  '
			<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="2">Overall Rating of Customer Service</th>
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[warid_cs] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
							  $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	
	
	
	if(count($report[paka_rate]>0)){
			arsort($report[paka_rate]);
		$total = '';
		$report[paka_rate]['Good(7-9)'][total]= array_sum($report[paka_rate]['Good(7-9)']);
		$report[paka_rate]['Average(4-6)'][total] = array_sum($report[paka_rate]['Average(4-6)']);
		$report[paka_rate]['Poor(1-3)'][total] = array_sum($report[paka_rate]['Poor(1-3)']);
		$report[paka_rate]['Excellent(10)'][total] = array_sum($report[paka_rate]['Excellent(10)']);
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="2">Rating of Service at Warid Centers</th>
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[paka_rate] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
							 $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	
	
	
	if(count($report[services_recommend]>0)){
	$total = '';
		$report[services_recommend]['Detractors(0-6)'][total]= array_sum($report[services_recommend]['Detractors(0-6)']);
		$report[services_recommend]['Passives(7-8)'][total] = array_sum($report[services_recommend]['Passives(7-8)']);
		$report[services_recommend]['Promoters(9-10)'][total] = array_sum($report[services_recommend]['Promoters(9-10)']);
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="2">Likelihood on Future Recommendation</th></tr>
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[services_recommend] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
				 $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	
	
	
	if(count($report[period_on_network])>0){
	arsort($report[period_on_network]);
		$total = ''; 
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="2">Rating as Per Lifetime on Network</th>
			<tr><th>Period</th><th>Count</th>';
			foreach($report[period_on_network] as $reason=>$count){
				$html .= '<tr><td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= ' <tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td></tr>';
	}
	
	if(count($report[paka_center_count])>0){
	arsort($report[paka_center_count]);
	
		$html .=  '
			<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Survey Participation by Centers</th>
			<tr><th></th><th>PAKA Center</th><th>Count</th></tr>';
			foreach($report[paka_center_count] as $center_name=>$count){
			++$i;
				$html .= '	
				<tr>
							<td class="values">'.$i.'</td>
							<td class="values">'.$center_name.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
			}
			$html .= '
			</table></td>';
	}
	
	if(count($report[warid_cs_reason])>0){
	arsort($report[warid_cs_reason]);
		$total = '';
		$i = '';
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reason for The Rate Given by the Customer</th>
			<tr><th colspan="3">Reasons [Good]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][good] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '
			<tr><th colspan="3">Reasons [Average]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][average] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '
			<tr><th colspan="3">Reasons [Poor]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][poor] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= ' 
			</table></td>';
	}
	
	if(count($report[paka_rate_reason])>0){
	arsort($report[paka_rate_reason]);
	$total = '';
	$i = '';
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reason for The Paka Care Rating</th>
			<tr><th colspan="3">Reasons [Good]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[paka_rate_reason][good_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '<tr><th colspan="3">Reasons [Average]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[paka_rate_reason][average_pakarate_reason]);
			foreach($report[paka_rate_reason][average_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '<tr><th colspan="3">Reasons [Poor]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[paka_rate_reason][poor_pakarate_reason]);
			foreach($report[paka_rate_reason][poor_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
		$html .= '</table></td>';
	} 
	
	if(count($report[reason_recommend])>0){
		$i = '';
		arsort($report[reason_recommend]);
		$total = ''; 
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reason for Recommendation</th>
			<tr><th colspan="3">Reason [Promoters]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[reason_recommend][promoters] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '<tr><th colspan="3">Reasons [Passives]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[reason_recommend][passives]);
			foreach($report[reason_recommend][passives] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '<tr><th colspan="3">Reasons [Detractors]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[reason_recommend][detractors]);
			foreach($report[reason_recommend][detractors] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .='</table></td>';
			}
	
	if(count($report[network_age])>0){	
				$html .='<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left">
								<tr><th colspan="2">Network Age Rating</th></tr>';
				foreach($report[network_age] as $rating=>$values){
					$html .='<tr><th colspan="2">'.$rating.'</th></tr>';
						foreach($values as $age=>$count){
							$html .='<tr><td class="text_values">'.$age.'</td><td class="text_values">'.$count.'</td></tr>';
						}
				}
				$html .=' 
						</table></td></tr>';
		}
	$html .='</body></table>';
	return $html;
}

function display_paka_care_report($report){

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
	
	.red_text_values{
		background-color: #AE0000;
		color:#FFF;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		font-weight: bold;
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
	
	.menu_link_active,
	.menu_link:hover{
		display:inherit;
		background-color:#006;
		color:#FFF;
	}
	
	.menu_link_active a{
		color:#FFF;
		text-decoration:none;
	}
	.menu_link a,.menu_link a:visited{
		color:#000;
		text-decoration:none;
	}
	
	.menu_link a:hover{
		color:#FFF;
	}
	
	.menu_link_active a:hover{
		font-weight:bold;
	}
	
	.menu_link_active:hover{
		display:inherit;
		background-color:#FF0000;
		/*color:#FFF;*/
	}
	
	.search{
		display:none;
		font-size: 9px;
	}
	.search_show{
		display:block;
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
	<body><table>';
	
	$html .= '<tr><th colspan="4" align="center">Overall Rating of Customer Service</th></tr>';
	if(count($report[warid_cs]>0)){
	arsort($report[warid_cs]);
	$total = '';
		$report[warid_cs]['Good(7-9)'][total]= array_sum($report[warid_cs]['Good(7-9)']);
		$report[warid_cs]['Average(4-6)'][total] = array_sum($report[warid_cs]['Average(4-6)']);
		$report[warid_cs]['Poor(1-3)'][total] = array_sum($report[warid_cs]['Poor(1-3)']);
		$report[warid_cs]['Excellent(10)'][total] = array_sum($report[warid_cs]['Excellent(10)']);
		$html .=  '
			<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[warid_cs] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
							  $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	if(count($report[warid_cs_reason])>0){
	
		arsort($report[warid_cs_reason]);
		$total = '';
		$i = '';
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reasons [Good]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][good] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> <th colspan="3">Reasons [Average]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][average] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> <th colspan="3">Reasons [Average]</th>
			<tr><th colspan="3">Reasons [Poor]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[warid_cs_reason][poor] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= ' 
			</table></td></tr>';
	}
	
	//Paka Care Centers
	$html .= '<tr><th colspan="4" align="center">Rating of Service at Business Centers</th></tr>';
	if(count($report[paka_rate]>0)){
			arsort($report[paka_rate]);
		$total = '';
		$report[paka_rate]['Good(7-9)'][total]= array_sum($report[paka_rate]['Good(7-9)']);
		$report[paka_rate]['Average(4-6)'][total] = array_sum($report[paka_rate]['Average(4-6)']);
		$report[paka_rate]['Poor(1-3)'][total] = array_sum($report[paka_rate]['Poor(1-3)']);
		$report[paka_rate]['Excellent(10)'][total] = array_sum($report[paka_rate]['Excellent(10)']);
		$html .=  '<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[paka_rate] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
							 $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	if(count($report[paka_rate_reason])>0){
	arsort($report[paka_rate_reason]);
	$total = '';
	$i = '';
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reason for The Paka Care Rating</th>
			<tr><th colspan="3">Reasons [Good]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[paka_rate_reason][good_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
							<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
							<th colspan="3">Reasons [Average]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[paka_rate_reason][average_pakarate_reason]);
			foreach($report[paka_rate_reason][average_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
								<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left">
								<tr><th colspan="3">Reasons [Poor]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[paka_rate_reason][poor_pakarate_reason]);
			foreach($report[paka_rate_reason][poor_pakarate_reason] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
		$html .= '</table></td></tr>';
	} 
	
	//Recommending warid product
	$html .= '<tr><th colspan="4" align="center">Likelihood to Recommendation Brand to Customers</th></tr>';
	if(count($report[services_recommend]>0)){
	$total = '';
		$report[services_recommend]['Detractors(0-6)'][total]= array_sum($report[services_recommend]['Detractors(0-6)']);
		$report[services_recommend]['Passives(7-8)'][total] = array_sum($report[services_recommend]['Passives(7-8)']);
		$report[services_recommend]['Promoters(9-10)'][total] = array_sum($report[services_recommend]['Promoters(9-10)']);
		$html .=  '
		<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th>Rating ranges</th><th>Count</th></tr>';
			foreach($report[services_recommend] as $key=>$row){
				$html .= '<tr><td class="values">'.$key.'</td>
							 <td class="text_values">'.$row[total].'</td></tr>';
				 $total += $row[total];
			}
			$html .= '
			<tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	if(count($report[reason_recommend])>0){
		$i = '';
		arsort($report[reason_recommend]);
		$total = ''; 
		$html .=  '
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Reason [Promoters]</th>
			<tr><th></th><th>Reason</th><th>Count</th>';
			foreach($report[reason_recommend][promoters] as $reason=>$count){
			++$i;
				$html .= '	<tr><td class="values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left">
			<th colspan="3">Reasons [Passives]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[reason_recommend][passives]);
			foreach($report[reason_recommend][passives] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= '</table></td>
			<td valign="top">
				<table border="0" cellpadding="1" cellspacing="0" style="float:left"><th colspan="3">Reasons [Detractors]</th></tr>
			<tr><th></th><th>Reason</th><th>Count</th>';
			arsort($report[reason_recommend][detractors]);
			foreach($report[reason_recommend][detractors] as $reason=>$count){
				++$i;
				$html .= '<tr>
							<td class="text_values">'.$i.'</td>
							<td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .='</table></td></tr>';
			}
	
	// Period on Network
	$html .= '<tr><th colspan="5" align="center">Period of Stay on Network</th></tr>';

	if(count($report[period_on_network])>0){
	arsort($report[period_on_network]);
		$total = ''; 
		$html .=  '
		<tr>
			<td valign="top">
					<table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
						<tr><th>Period</th><th>Count</th>';
			foreach($report[period_on_network] as $reason=>$count){
				$html .= '<tr><td class="values">'.$reason.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
							 $total += $count;
			}
			$html .= ' <tr><th>Total</th><th>'.$total.'</th></tr>
			</table></td>';
	}
	if(count($report[network_age])>0){	
						foreach($report[network_age] as $rating=>$values){
							$html .='
							<td valign="top">
								<table border="0" cellpadding="1" cellspacing="0" style="float:left">
										<th colspan="2">'.$rating.'</th>';
											foreach($values as $age=>$count){
											$html .='<tr>
														<td class="text_values">'.$age.'</td>
														<td class="text_values">'.$count.'</td>
													</tr>';
											}
							$html .='</table></td>';	
						}
				
				$html .=' </td>
							</tr>
									    ';
									
		}
	
	//Paka center counts
	/*if(count($report[paka_center_count])>0){
	arsort($report[paka_center_count]);
	
		$html .=  '
			<tr>
			<td valign="top"><table border="0" cellpadding="1" cellspacing="0" style="float:left"> 
			<tr><th colspan="3">Survey Participation by Centers</th>
			<tr><th></th><th>PAKA Center</th><th>Count</th></tr>';
			foreach($report[paka_center_count] as $center_name=>$count){
			++$i;
				$html .= '	
				<tr>
							<td class="values">'.$i.'</td>
							<td class="values">'.$center_name.'</td>
							 <td class="text_values">'.$count.'</td></tr>';
			}
			$html .= '
			</table></td>';
	}*/
	
	
		
	$html .='</body></table>';
	return $html;
}

?>