<?php
function generate_waridpesa_sl_trends($date_input){
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	custom_query::select_db('ivrperformance'); 
	
	if(($date_input=='') or ($date_input == date('Y-m-d'))){
		$date_input = date('Y-m-d',strtotime("-1 days"));
	}

	$query = "
			SELECT
				date_format(queue.`entrydate`,'%D-%b') as full_date,
				queue.que,
				queue.servicelevel,
				queue.avgcallduration,
				queue.avgspeedofans,
				calldetail.status,
				calldetail.calls
			FROM 
				calldetail 
				Inner Join queue ON queue.id = calldetail.id_c 
			WHERE 
				queue.entrydate between date_format('".$date_input."' ,'%Y-%m-01') AND '".$date_input."' and
				calldetail.status != 'Abandon'
	";
	
	//queue.entrydate between date_format(date_sub(date(now()), interval 1 day) ,'%Y-%m-01') AND date_sub(date(now()), interval 1 day) and
	
	$input_list = $myquerys->multiple($query);
	
	//echo $check_query." \n\n";
	if(count($input_list) > 0){
		
		foreach($input_list as $row){
			$wb[$row[full_date]][$row[que]]['Service Level'] = $row[servicelevel];
			$wb[$row[full_date]][$row[que]]['Average Call Duration'] = $row[avgcallduration];
			$wb[$row[full_date]][$row[que]]['Average Answer Speed'] = $row[avgspeedofans];
			$wb[$row[full_date]][$row[que]][$row[status].' Calls'] = $row[calls];
			
			$wb[$row[full_date]]['All Queues']['service_level_call_index'] += ($row[calls] *  $row[servicelevel]);
			$wb[$row[full_date]]['All Queues']['avg_call_duration_call_index'] += ($row[calls] * my_strtotime($row[avgcallduration]));
			$wb[$row[full_date]]['All Queues']['avg_ans_speed_call_index'] += ($row[calls] * my_strtotime($row[avgspeedofans]));
			$wb[$row[full_date]]['All Queues']['Total Calls '.$row[status]] +=  $row[calls];
			$wb[$row[full_date]]['All Queues']['Total Calls'] +=  $row[calls];	
		}
		
		foreach($wb as $date=>$date_data){
		
			$report[data]['Warid pesa service level'][$date] = number_format($date_data[MobileMoney]['Service Level'],2);
			$report[unit]['Warid pesa service level'] = '%';
			
			$report[data]['Service level - Overall'][$date] = number_format(($date_data['All Queues']['service_level_call_index']/$date_data['All Queues']['Total Calls']),2);
			$report[unit]['Service level - Overall'] = '%';
			
			$report[data]['Warid pesa - Calls Recieved'][$date] = number_format($date_data[MobileMoney]['Received Calls'],0);
			$report[unit]['Warid pesa - Calls Recieved'] = '';
			
			$report[data]['Total Calls Recieved'][$date] = number_format($date_data['All Queues']['Total Calls Received'],0);
			$report[unit]['Total Calls Recieved'] = '';
			
			$report[data]['Warid pesa - Calls Handled'][$date] = number_format($date_data[MobileMoney]['Handled Calls'],0);
			$report[unit]['Warid pesa - Calls Handled'] = '';
			
			$report[data]['Total Calls Handled'][$date] = number_format($date_data['All Queues']['Total Calls Handled'],0);
			$report[unit]['Total Calls Handled'] = '';
			
			$report[data]['Warid pesa - Average call duration'][$date] = $date_data[MobileMoney]['Average Call Duration'];
			$report[unit]['Warid pesa - Average call duration'] = '';

			$report[data]['Average call duration - Overall'][$date] = timetostr($date_data['All Queues']['avg_call_duration_call_index']/$date_data['All Queues']['Total Calls']);
			$report[unit]['Average call duration - Overall'] = '';
			
			$report[data]['Warid pesa - Average Answer Speed'][$date] = $date_data[MobileMoney]['Average Answer Speed'];
			$report[unit]['Warid pesa - Average Answer Speed'] = '';
			
			$report[data]['Average Answer Speed - Overall'][$date] = timetostr($date_data['All Queues']['avg_ans_speed_call_index']/$date_data['All Queues']['Total Calls']);
			$report[unit]['Average Answer Speed - Overall'] = '';
		}
		
		$graph_detail[data]['Warid pesa service level']=$report[data]['Warid pesa service level'];
		$graph_detail[data]['Overall Service level']=$report[data]['Service level - Overall'];
		$graph_detail[title]='Warid pesa Service level trend '.date_format(date_create($date_input),'F Y');
		$graph_detail[display_title]=false;
		$graph_detail[legend]=true;
		$graph_detail[line_graph]=true;
		$graph_detail[bar_graph]=false;
		$graph_detail[set_data_points]=true;
		$graph_detail[set_data_values]=false;
		$graph_detail[width]=850;
		$graph_detail[height]=470;
		
		$my_graph->graph($title=$graph_detail[title], $period=date_format(date_create($date_input),'Y-m-')."01 to ".$date_input, $data=$graph_detail);
		custom_query::select_db('graphing');
		$report[graph]['Warid pesa service level'] = $my_graph->Save();
		
		return $report;
	}else{
		sendHTMLemail($to='ccbusinessanalysis@waridtel.co.ug',$bcc,$message='There is no data on this date ['.$date_input.'].<br> Report run has been cancelled ...',$subject='Contact Centre Flash report - ERROR',$from='CCREPORTS <ccnotify@waridtel.co.ug>');
		exit('Exiting due to no data on date ['.$date_input.']');
	}
}

function display_waridpesa_sl_trends($report){
	
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
	';
	
	//Getting the date as tiltes
	$dates = array_keys($report[data]['Warid pesa service level']);
	$html .= '
		<tr>
			<th scope="col" class="row_title">Contact Center Daily Flash Report : Warid Pesa</th>
	';
	
	foreach($dates as $date){
		$html .= '
			<th scope="col" class="top_th">'.$date.'</th>
		';
	}
	
	$html .= '
		</tr>
	';

	foreach($report[data] as $row_title=>$row_data){
		$color[0] = '';
		$color[1] = '#CCCCCC';
		
		$html .= '
		  <tr class="row" bgcolor="'.$color[(++$countnum-1)%2].'">
			<th scope="col">'.$row_title.'</th>
		';
		
		foreach($dates as $date){
			$html .= '
			<td class="value">'.$row_data[$date].''.$report[unit][$row_title].'</td>
			';
		}
		
		$html .= '
		  </tr>
		';
	}
	
	$html .= '
		</table>
		<table>
			<tr>
		';

	$html .= '
			<td>
			<img src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph]['Warid pesa service level'].'.jpg" /></td>
			</tr>
		</table>
		<p>For further manipulations press Ctrl+A to select table, copy and paste into excel. This is a system generated email, do not reply to it. For any assistance please contact ccbusinessanalysis@waridtel.co.ug.</p>
</body>
</html>
	';
	
	return $html;
}

?>