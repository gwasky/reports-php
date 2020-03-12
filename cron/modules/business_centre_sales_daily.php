<?php
function generate_business_centre_sales($date,$from){
	$myquery = new custom_query();
	$my_graph = new dbgraph();
	
	if($date == '' or $from == '') { exit("No dates specified \n"); }
	$report[usedate] = $date;
	$report[from] = $from;
	
	$query = "
		SELECT
			bc_sales.entry_date,
			cast(SUM(bc_sales.amount) AS UNSIGNED) AS sold_amount,
			bc_names.`name` as business_centre
		FROM
			bc_sales
			INNER JOIN bc_items ON bc_sales.item_id = bc_items.id
			INNER JOIN bc_names ON bc_sales.business_centre_id = bc_names.id
			INNER JOIN bc_item_groups ON bc_items.group_id = bc_item_groups.id
		WHERE
			bc_sales.entry_date BETWEEN '".$from."' AND '".$date."'
		GROUP BY
			bc_sales.entry_date, business_centre
	";
	
	$check = $myquery->multiple($query,'ccba02.businesssales');
	
	//echo $query." +++ >> ".PrintR($check)."<hr>";
	
	$day_diff = (intval(substr($date,-2)) - intval(substr($from,-2))) + 1;
	$no_of_txs = $day_diff * 2;	//IE 2 BUSINESS CENTRES X NUBER OF DAYS
	//$no_of_txs = $day_diff * 1;	//IE 1 BUSINESS CENTRES X NUBER OF DAYS PLAZA CLOSED
	
	//echo "Date diff ".$day_diff."; no of tx ".$no_of_txs."\n";
	//exit();
	
	if(count($check) < $no_of_txs){
		$report[error] = "MISSING DATES";
		$report[data] = $check;
		return display_business_centre_sales($report);
	}
	
	$query = "
		SELECT
			bc_sales.entry_date,
			bc_item_groups.group_name,
			bc_items.item_name as talkwyz_item,
			bc_items.description as talkwyz_description,
			bc_sales.qty,
			cast(bc_sales.amount AS UNSIGNED) AS sold_amount,
			bc_names.`name` as business_centre,
			bc_regions.region_name
		FROM
			bc_sales
			INNER JOIN bc_items ON bc_sales.item_id = bc_items.id
			INNER JOIN bc_names ON bc_sales.business_centre_id = bc_names.id
			INNER JOIN bc_item_groups ON bc_items.group_id = bc_item_groups.id
			INNER JOIN bc_regions ON bc_names.region_id = bc_regions.id
		WHERE
			bc_sales.entry_date BETWEEN DATE_SUB('".substr($date,0,7)."-01', INTERVAL 1 MONTH) AND '".$date."'
	";
	
	//echo PrintR($query)."\n";
		
	$sales = $myquery->multiple($query,'ccba02.businesssales');
	
	//BEGIN SUMMARY MTD
	$sales_joiners = array('MODEMS','PHONES','PCOS','ROUTER','TABS');
	$sales_revenue = array('ELECTRONIC RECHARGE','SCRATCH CARDS','WARID PESA/AIRTEL MONEY CHARGES','PHONES','PCOS','SIM CARDS','POLICE REPORTS','PHOTOS','ROUTER','AIRTEL MONEY');
	$report[data_groups][mtd_summary] = array('revenues','others','joiners');
	//END SUMMARY MTD
	
	//BEGIN ITEM GROUP REVENUES BY DATE BY CENTRE
	$sales_joiners_gp_dt_ctr = array('SIM CARDS','PHONES','MODEMS','PCOS','ROUTER','TABS');
	$sales_revenue_gp_dt_ctr = array('ELECTRONIC RECHARGE','SCRATCH CARDS','WARID PESA/AIRTEL MONEY CHARGES','AIRTEL MONEY','PHONES','PCOS','ROUTER','SIM CARDS','POLICE REPORTS');
	$report[data_groups][grp_info_by_date_by_ctr] = array('revenues','counts','others');
	//END ITEM GROUP REVENUES BY DATE BY CENTRE
	
	//BEGIN TOTAL PHONE,MODEMS,PCOS COUNTS BY PHONE,MODEMS,PCOS, ROUTER DATE
	$total_item_counts = array('PHONES','MODEMS','PCOS','ROUTER','TABS');
	$total_item_counts_items = array('SIM SWAP FEE');
	$report[data_groups][itm_cnts_by_date_by_rgn] = array('counts');
	//END TOTAL PHONE,MODEMS,PCOS COUNTS BY PHONE,MODEMS,PCOS, ROUTER DATE
	
	//BEGIN ITEMS REVENUES (PHONE,MODEMS,PCOS, ROUTER) BY DATE BY CENTRE
	$item_revenues = array('PHONES','MODEMS','PCOS','ROUTER','TABS');
	$report[data_groups][itm_revs_by_date_by_rgn] = array('revenues');
	//END ITEMS REVENUES (PHONE,MODEMS,PCOS, ROUTER) BY DATE BY CENTRE
		
	foreach($sales as $row){
		$report[months][substr($row[entry_date],0,7)] = substr($row[entry_date],0,7);
		if(substr($report[usedate],0,7) == substr($row[entry_date],0,7)) $report[dates][$row[entry_date]] = $row[entry_date];
		
		//BEGIN SUMMARY MTD COMPARISON WITH LAST MONTH
		if(in_array($row[group_name],$sales_revenue)){
			$report[data][mtd_summary][revenues][$row[group_name]][substr($row[entry_date],0,7)] += $row[sold_amount];
			
			$report[totals][mtd_summary][revenues][substr($row[entry_date],0,7)] += $row[sold_amount];
		}
		
		if(in_array($row[group_name],$sales_joiners)){
			$report[data][mtd_summary][joiners][$row[talkwyz_item]][substr($row[entry_date],0,7)] += $row[qty];
		}
		
		if(!in_array($row[group_name],$sales_joiners) and !in_array($row[group_name],$sales_revenue)){
			$report[data][mtd_summary][others][$row[group_name]][substr($row[entry_date],0,7)] += $row[sold_amount];
			
			$report[totals][mtd_summary][others][substr($row[entry_date],0,7)] += $row[sold_amount];
		}
		//END SUMMARY MTD
		
		if(substr($report[usedate],0,7) == substr($row[entry_date],0,7)){
			//BEGIN ITEM GROUP REVENUES BY DATE BY CENTRE
			if(in_array($row[group_name],$sales_revenue_gp_dt_ctr)){
				$report[data][grp_info_by_date_by_ctr][revenues][$row[group_name]][$row[region_name].' >> '.$row[business_centre]][$row[entry_date]] += $row[sold_amount];
				
				$report[totals][grp_info_by_date_by_ctr][revenues][$row[group_name]][dates][$row[entry_date]] += $row[sold_amount];
				$report[totals][grp_info_by_date_by_ctr][revenues][$row[group_name]][region_centres][$row[region_name].' >> '.$row[business_centre]] += $row[sold_amount];
				$report[totals][grp_info_by_date_by_ctr][revenues][$row[group_name]][total] += $row[sold_amount];
			}
			
			if(in_array($row[group_name],$sales_joiners_gp_dt_ctr)){
				$report[data][grp_info_by_date_by_ctr][counts][$row[group_name]][$row[region_name].' >> '.$row[business_centre]][$row[entry_date]] += $row[qty];
				
				$report[totals][grp_info_by_date_by_ctr][counts][$row[group_name]][dates][$row[entry_date]] += $row[qty];
				$report[totals][grp_info_by_date_by_ctr][counts][$row[group_name]][region_centres][$row[region_name].' >> '.$row[business_centre]] += $row[qty];
				$report[totals][grp_info_by_date_by_ctr][counts][$row[group_name]][total] += $row[qty];
			}
			
			if(	!in_array($row[group_name],$sales_revenue_gp_dt_ctr) and
				!in_array($row[group_name],$sales_joiners_gp_dt_ctr)
			){
				$report[data][grp_info_by_date_by_ctr][others][$row[group_name]][$row[region_name].' >> '.$row[business_centre]][$row[entry_date]] += $row[sold_amount];
				
				$report[totals][grp_info_by_date_by_ctr][others][$row[group_name]][dates][$row[entry_date]] += $row[sold_amount];
				$report[totals][grp_info_by_date_by_ctr][others][$row[group_name]][region_centres][$row[region_name].' >> '.$row[business_centre]] += $row[sold_amount];
				$report[totals][grp_info_by_date_by_ctr][others][$row[group_name]][total] += $row[sold_amount];
			}
			//END ITEM GROUP REVENUES BY DATE BY CENTRE
			
			//BEGIN TOTAL PHONE,MODEMS,PCOS COUNTS BY PHONE,MODEMS,PCOS DATE
			if(
				in_array($row[group_name],$total_item_counts) or 
				in_array($row[talkwyz_item],$total_item_counts_items)
				){
				$report[data][itm_cnts_by_date_by_rgn][counts][$row[group_name]][$row[region_name].' >> '.$row[talkwyz_item]][$row[entry_date]] += $row[qty];
				
				$report[totals][itm_cnts_by_date_by_rgn][counts][$row[group_name]][dates][$row[entry_date]] += $row[qty];
				$report[totals][itm_cnts_by_date_by_rgn][counts][$row[group_name]][region_items][$row[region_name].' >> '.$row[talkwyz_item]] += $row[qty];
				$report[totals][itm_cnts_by_date_by_rgn][counts][$row[group_name]][total] += $row[qty];
			}
			//END TOTAL PHONE,MODEMS,PCOS COUNTS BY PHONE,MODEMS,PCOS DATE
			
			//BEGIN ITEMS REVENUES (PHONE,MODEMS,PCOS) BY DATE BY CENTRE
			if(in_array($row[group_name],$item_revenues)){
				$report[data][itm_revs_by_date_by_rgn][revenues][$row[group_name]][$row[business_centre]][$row[region_name].' >> '.$row[talkwyz_item]][$row[entry_date]] += $row[sold_amount];
				
				$report[totals][itm_revs_by_date_by_rgn][revenues][$row[group_name]][$row[business_centre]][dates][$row[entry_date]] += $row[sold_amount];
				$report[totals][itm_revs_by_date_by_rgn][revenues][$row[group_name]][$row[business_centre]][region_items][$row[region_name].' >> '.$row[talkwyz_item]] += $row[sold_amount];
				$report[totals][itm_revs_by_date_by_rgn][revenues][$row[group_name]][$row[business_centre]][total] += $row[sold_amount];
			}
			//END ITEMS REVENUES (PHONE,MODEMS,PCOS) BY DATE BY CENTRE
		}
	}
	
	//echo PrintR($report[totals]);
	asort($report[months]);
	asort($report[dates]);

	$report[graph] = $graph_data; unset($graph_data);
	$report[table] = $table_data; unset($table_data);
	
	$graph_detail[data]=$report[graph];
	$graph_detail[title]='Business Centre Walkins '.date_format(date_create($date),'F Y');
	$graph_detail[display_title]=false;
	$graph_detail[legend]=true;
	$graph_detail[line_graph]=true;
	$graph_detail[bar_graph]=false;
	$graph_detail[set_data_points]=true;
	$graph_detail[set_data_values]=false;
	$graph_detail[width]=850;
	$graph_detail[height]=600;
	
	$my_graph->graph($graph_detail[title],"30 days before ".$date, $graph_detail);
	custom_query::select_db('graphing');
	//$report[graph_id] = $my_graph->Save();
	
	return display_business_centre_sales($report);
}

function display_business_centre_sales($report){
	
	if($report[error] == "MISSING DATES"){
		$html = '
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>There are missing sales between '.date('l jS',strtotime($report[from])).' to '.date('l jS F Y',strtotime($report[usedate])).'. Below is what we have. Please upload the missing information and <a href="http://ccba02.waridtel.co.ug/reports/cron/business_centre_sales_daily.php?to='.$report[usedate].'&from='.$report[from].'">click here to rerun this email report</a>.<hr></td>
			</tr>
			<tr>
				<td hieght="20"></td>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th>Date</th>
						<th>Centre</th>
						<th>Total Amount</th>
					</tr>
		';
		foreach($report[data] as $row){
			$html .= '
					<tr>
						<td class="values">'.$row[entry_date].'</td>
						<td class="text_values">'.$row[business_centre].'</td>
						<td class="values">'.number_format($row[sold_amount],0).'</td>
					</tr>
			';
		}
		
		$html .= '
				</table>
				</td>
			</tr>
			</table>
		';
		
		$to_list = 'Samuel Mwanje/Customer Service/Uganda <Samuel.Mwanje@ug.airtel.com>, Macdavid Mugga/Customer Service/Uganda <macdavid.mugga@ug.airtel.com>';
		$bcc_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>';
		//$to_list = 'Steven Ntambi/CC/Kampala <steven.ntambi@waridtel.co.ug>';
		$html = attach_html_container($title='',$body=$html);
		sendHTMLemail($to=$to_list,$bcc=$bcc_list,$message=$html,$subject='AIRTEL OWNED SHOPS PERFORMANCE (WTU) MTD '.date('F Y',strtotime("-1 days")).' - ERRORS',$from="Business centre<ccnotify@waridtel.co.ug>");
		
		exit("Missing info. Leaving .... \n");		
		return FALSE;
	}
	
	$html .= '
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<TH>BUSINESS CENTRE REVENUES</TH>
			</tr>
			<tr>
				<TD height="20"></TD>
			</tr>
			<tr>
				<TD>
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<th>ITEM</th>
	';
	
	//OTHER VALUES OF HEADING ROW
	foreach($report[months] as $month){
		$html .= '
						<th>'.date('F Y',strtotime($month."-01")).'</th>
		';
	}
	
	$html .= '
					</tr>
	';
	
	//DATA ROWS
	foreach($report[data_groups][mtd_summary] as $data_group){
		if($data_group == 'others'){ $data_group_name = 'other revenues'; }else{ $data_group_name = $data_group; }
		$html .= '
					<tr>
						<th colspan="'.(1+count($report[months])).'">'.strtoupper($data_group).'</th>
					</tr>
		';
		foreach($report[data][mtd_summary][$data_group] as $parameter=>$parameter_data){
			$html .= '
					<tr>
						<td class="text_values">'.$parameter.'</td>
			';
		
			foreach($report[months] as $month){
				$html .= '
						<td class="values">'.number_format($parameter_data[$month],0).'</td>
				';
			}
		
			$html .= '
					</tr>
			';
		}
		
		//TOTAL TRS
		if(count($report[totals][mtd_summary][$data_group]) > 0){
		$html .= '
					<tr id="totals">
						<td class="text_values">TOTAL</td>
		';
		foreach($report[months] as $month){
			$html .= '
						<td class="values">'.number_format($report[totals][mtd_summary][$data_group][$month],0).'</td>
			';
		}
		$html .= '
					</tr>
		';
		}
		
		$html .= '
					<tr style="height:20px;">
						<td></td>
						<td colspan="'.count($report[months]).'"></td>
					</tr>
		';
	}
	
	$html .= '
				</table>
				</TD>
			</tr>
		</table>
	';
	
	$mail .= '
		<table border="0" cellpadding="0" cellspacing="0" width="'.((count($report[dates])+4)*50).'">
	';
	
	foreach($report[data_groups][grp_info_by_date_by_ctr] as $data_group){
		foreach($report[data][grp_info_by_date_by_ctr][$data_group] as $item_group=>$item_group_data){
			$mail .= '
			<tr><td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th colspan="5">'.strtoupper($data_group).' : '.strtoupper($item_group).'</th>
				<td colspan="'.(count($report[dates]) + 1 - 2).'"> </td>
			</tr>
			<tr>
				<th>REGRION</th>
				<th>#</th>
				<th>CENTRE</th>
			';
			foreach($report[dates] as $date){
				$mail .= '
				<th align="centre">'.date('d-M',strtotime($date)).'<br>'.date('D',strtotime($date)).'</th>
				';
			}
			
			$mail .= '
				<th>Total</th>
			</tr>
			';
			
			foreach($item_group_data as $region_centre=>$row){
				$mail .= '
			<tr>
				';
				list($region,$centre) = explode(" >> ",$region_centre);
				$mail .= '
					<td class="text_values">'.$region.'</td>
					<td class="values">'.++$ii.'</td>
					<td class="text_values">'.$centre.'</td>
				';
				
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($row[$date],0).'</td>
					';
				}
				
				$mail .= '
					<td class="values">'.number_format($report[totals][grp_info_by_date_by_ctr][$data_group][$item_group][region_centres][$region_centre],0).'</td>
			</tr>
				';
			}
			
			$mail .= '
			<tr id="totals">
					<td class="text_values" colspan="3">Totals</td>
			';
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($report[totals][grp_info_by_date_by_ctr][$data_group][$item_group][dates][$date],0).'</td>
					';
				}
			$mail .= '
					<td class="values">'.number_format($report[totals][grp_info_by_date_by_ctr][$data_group][$item_group][total],0).'</td>
			</tr>
			';
			unset($ii);
			
			$mail .= '
			</table>
			</td></tr>
			<tr><td height="15"></td></tr>
		';
		}
	}
	
	//$mail .= '<hr>';
	
	foreach($report[data_groups][itm_cnts_by_date_by_rgn] as $data_group){
		foreach($report[data][itm_cnts_by_date_by_rgn][$data_group] as $item_group=>$item_group_data){
			$mail .= '
			<tr><td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th colspan="5">'.strtoupper($data_group).' : '.strtoupper($item_group).'</th>
				<td colspan="'.(count($report[dates]) + 1 - 2).'"> </td>
			</tr>
			<tr>
				<th>REGRION</th>
				<th>#</th>
				<th>ITEM</th>
			';
			foreach($report[dates] as $date){
				$mail .= '
				<th align="centre">'.date('d-M',strtotime($date)).'<br>'.date('D',strtotime($date)).'</th>
				';
			}
			
			$mail .= '
				<th>Total</th>
			</tr>
			';
			
			foreach($item_group_data as $region_item=>$row){
				$mail .= '
			<tr>
				';
				list($region,$item) = explode(" >> ",$region_item);
				$mail .= '
					<td class="text_values">'.$region.'</td>
					<td class="values">'.++$ii.'</td>
					<td class="text_values">'.$item.'</td>
				';
				
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($row[$date],0).'</td>
					';
				}
				
				$mail .= '
					<td class="values">'.number_format($report[totals][itm_cnts_by_date_by_rgn][$data_group][$item_group][region_items][$region_item],0).'</td>
			</tr>
				';
			}
			
			$mail .= '
			<tr id="totals">
					<td class="text_values" colspan="3">Totals</td>
			';
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($report[totals][itm_cnts_by_date_by_rgn][$data_group][$item_group][dates][$date],0).'</td>
					';
				}
			$mail .= '
					<td class="values">'.number_format($report[totals][itm_cnts_by_date_by_rgn][$data_group][$item_group][total],0).'</td>
			</tr>
			';
			unset($ii);
			
			$mail .= '
			</table>
			</td></tr>
			<tr><td height="15"></td></tr>
		';
		}
	}
	
	//$mail .= '<hr>';
	
	foreach($report[data_groups][itm_revs_by_date_by_rgn] as $data_group){
		foreach($report[data][itm_revs_by_date_by_rgn][$data_group] as $item_group=>$item_group_data){
		foreach($item_group_data as $centre=>$centre_data){
			$mail .= '
			<tr><td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th colspan="5">'.strtoupper($data_group).' : '.strtoupper($centre).' - '.strtoupper($item_group).'</th>
				<td colspan="'.(count($report[dates]) + 1 - 2).'"> </td>
			</tr>
			<tr>
				<th>REGRION</th>
				<th>#</th>
				<th>ITEM</th>
			';
			foreach($report[dates] as $date){
				$mail .= '
				<th align="centre">'.date('d-M',strtotime($date)).'<br>'.date('D',strtotime($date)).'</th>
				';
			}
			
			$mail .= '
				<th>Total</th>
			</tr>
			';
			
			foreach($centre_data as $region_item=>$row){
				$mail .= '
			<tr>
				';
				list($region,$item) = explode(" >> ",$region_item);
				$mail .= '
					<td class="text_values">'.$region.'</td>
					<td class="values">'.++$ii.'</td>
					<td class="text_values">'.$item.'</td>
				';
				
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($row[$date],0).'</td>
					';
				}
				
				$mail .= '
					<td class="values">'.number_format($report[totals][itm_revs_by_date_by_rgn][$data_group][$item_group][$centre][region_items][$region_item],0).'</td>
			</tr>
				';
			}
			
			$mail .= '
			<tr id="totals">
					<td class="text_values" colspan="3">Totals</td>
			';
				foreach($report[dates] as $date){
					$mail .= '
					<td class="values">'.number_format($report[totals][itm_revs_by_date_by_rgn][$data_group][$item_group][$centre][dates][$date],0).'</td>
					';
				}
			$mail .= '
					<td class="values">'.number_format($report[totals][itm_revs_by_date_by_rgn][$data_group][$item_group][$centre][total],0).'</td>
			</tr>
			';
			unset($ii);
			
			$mail .= '
			</table>
			</td></tr>
			<tr><td height="15"></td></tr>
		';
		}
		}
	}
	
	$mail .= '
	</table>
	';
	
	//$html = $html.$mail;
	
	return array('html'=>$html,'attach'=>$mail);
}
?>