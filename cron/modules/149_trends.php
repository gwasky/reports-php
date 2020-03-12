<?php
function generate_149_trend_report($date){
	
	if($date == ''){
		$date = date('Y-m-d');
	}
	
	$myquerys = new custom_query();
	$my_graph = new dbgraph();
	
	//'pakalast','kankolera','kawa'
	$query = "
		SELECT
			left(reportsphonecalls.createdon,10) as created_on,
			reportsphonecalls.wrapupsubcat as category,
			if(subsubcategory.subject_type = 'Negative Feedback','Complaints','Inquiries') as subject_type,
			count(if(subsubcategory.subject_type = 'Negative Feedback','Complaints','Inquiries')) as number
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			subsubcategory.subject_status = 'active' and reportsphonecalls.wrapupsubcat IN ('pakalast','Beera ko') AND 
			reportsphonecalls.createdon between date_sub('".$date." 00:00:00', interval 7 day) AND date_sub('".$date." 23:59:59', interval 1 day) 
		GROUP BY 
			created_on,
			category,
			subject_type
		ORDER BY
			created_on ASC
	";
	
	echo date('Y-m-d H:i:s')." : Running Pakalast and Beera ko \n";
	
	$input_list = $myquerys->multiple($query,'ccba02.reportscrm');
	foreach($input_list as $row)
	{
		$blocks[dates][$row[created_on]] = $row[created_on];
		$blocks[categories][$row[category]] = $row[category];
		$blocks[subject_types][$row[subject_type]] = $row[subject_type];
		$data[$row[created_on]][$row[category]][$row[subject_type]] = $row[number];
		$data[$row[created_on]]['All'][$row[subject_type]] += $row[number];
	}
	
	asort($blocks[dates]);
	
	$blocks[categories]['All'] = 'All';
	
	foreach($blocks[dates] as $date){	
		foreach($blocks[categories] as $category){
			foreach($blocks[subject_types] as $subject_type){
				$report[data][pakalast_pepe_kawa][$category." ".$subject_type][$date] = number_format($data[$date][$category][$subject_type],'0','.','');
			}
		}	
	}
	
	$graph_detail[data]=$report[data][pakalast_pepe_kawa];
	$graph_detail[title]='Pakalast/Beera ko Feedback Trends: '.date("Y-m-d", strtotime("-7 days")).' to '.date('Y-m-d',strtotime("-1 days"));
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[display_title]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[width]=1000;
	$graph_detail[height]=800;
	$graph_detail[legend]=true;
	//$graph_detail[line_colors]=array('red','black','green','blue', 'purple','yellow','navy','lime');
	$period= date("Y-m-d", strtotime("-6 days")).' to '.date('Y-m-d');
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph][pakalast_pepe_kawa] = $my_graph->Save();
	
	//'Ovanite','Pakachini','Beera ko'
	$query = "
		SELECT
			left(reportsphonecalls.createdon,10) as created_on,
			reportsphonecalls.wrapupsubcat as category,
			if(subsubcategory.subject_type = 'Negative Feedback','Complaints','Inquiries') as subject_type,
			count(if(subsubcategory.subject_type = 'Negative Feedback','Complaints','Inquiries')) as number
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			subsubcategory.subject_status = 'active' and reportsphonecalls.wrapupsubcat IN ('Ovanite','Pakachini','kankolera','kawa') AND 
			reportsphonecalls.createdon between date_sub('".$date." 00:00:00', interval 7 day) AND date_sub('".$date." 23:59:59', interval 1 day) 
		GROUP BY 
			created_on,
			category,
			subject_type
		ORDER BY
			created_on ASC
	";
	
	echo date('Y-m-d H:i:s')." : Running Ovanite, Pakachini, kankolera, kawa \n";
	
	$input_list = $myquerys->multiple($query,'ccba02.reportscrm');
	foreach($input_list as $row)
	{
		$blocks[dates][$row[created_on]] = $row[created_on];
		$blocks[categories][$row[category]] = $row[category];
		$blocks[subject_types][$row[subject_type]] = $row[subject_type];
		$data[$row[created_on]][$row[category]][$row[subject_type]] = $row[number];
		$data[$row[created_on]]['All'][$row[subject_type]] += $row[number];
	}
	
	asort($blocks[dates]);
	
	$blocks[categories]['All'] = 'All';
	
	foreach($blocks[dates] as $date){	
		foreach($blocks[categories] as $category){
			foreach($blocks[subject_types] as $subject_type){
				$report[data][ovanite_beerako_pakacbini][$category." ".$subject_type][$date] = number_format($data[$date][$category][$subject_type],'0','.','');
			}
		}	
	}
	
	$graph_detail[data]=$report[data][ovanite_beerako_pakacbini];
	$graph_detail[width]=1100;
	//$graph_detail[title]='Pakachini/Ovanite/Beera ko Feedback Trends: '.date("Y-m-d", strtotime("-7 days")).' to '.date('Y-m-d',strtotime("-1 days"));
	$graph_detail[title]='Ovanite/Pakachini/Kankolera/Kawa Feedback Trends: '.date("Y-m-d", strtotime("-7 days")).' to '.date('Y-m-d',strtotime("-1 days"));
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail);
	custom_query::select_db('graphing');
	$report[graph][ovanite_beerako_pakacbini] = $my_graph->Save();
		
	return $report;
}

function display_149_trend_report($report){
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font:calibri;">
			<tr>
				<th style="background-color:#009; font-size:16px; color:#FFF;" valign="middle">Pakalast/Kankolera/Kawa Feedback Trends</th>
			</tr>
			<tr>
				<td style="color:#F00; font-style:bold; font-size:12px;">NOTE: \'All\' is a total of the Pakalast, Kankolera and Kawa related wrap ups and not the entire warid products/services</td>
			</tr>
			<tr>
			<td>
	';
	$html .= display_generic_graph($graph_id = $report[graph][pakalast_pepe_kawa]);
	$html .= '
			</td>
			</tr>
			<tr>
				<td style="height:20px;"></td>
			</tr>
			<tr>
				<th style="font-size:16px; font:calibiri; color:#FFF; background-color:#009;" valign="middle">Pakachini/Ovanite/Beera ko Feedback Trends</th>
			</tr>
			<tr>
				<td style="color:#F00; font-style:bold; font-size:12px;">NOTE: \'All\' is a total of the Pakachini, Overnite and Beera ko related wrap ups and not the entire warid products/services</td>
			</tr>
			<tr>
			<td>
	';
	
	$html .= display_generic_graph($graph_id = $report[graph][ovanite_beerako_pakacbini]);
	$html .= '
			</td>
			</tr>
	';
	
	$html .= '
		</table>
	';
	
	return $html;
}

?>