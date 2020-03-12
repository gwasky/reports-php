<?
function generate_overall_commission_report($from,$to,$telesales_report_type){
	
	echo "I THINK THIS REPORT DOES NOT HAVE UPDATED COMMISSION STRUCTURE. CHECK TO CONFIRM ....";
	
	$flag = 1;
	$report_type = 'summary';
	$gprs = generate_upsale_crossale_commission_report($from,$report_type,$commission,$agents,$flag);
	$report_type ='sales_per_agent_per_item';
	$telesales = generate_offnet_sales_report($from,$to,$report_type,$user,$msisdn,$item_sold,$flag);
	$agent_array = $telesales[sales_summary][agents];
		
	addToAgents($agent_array,$gprs[summary][Totals]["commission"], "gprs", "commission" );
	addToAgents($agent_array,$gprs[summary][Totals]["commission"], "CRBT Activation", "commission" );
	addToAgents($agent_array,$gprs[summary][Totals]["sales_values"], "CRBT Activation", "sales" );
	addToAgents($agent_array,$gprs[summary][Totals]["sales_values"], "gprs", "sales");
	foreach($agent_array as $agent=>$product_array){
		 $agentData[$agent][ATSALES] = $product_array[products][Airtime][sales];
		 $agentData[$agent][ATCOMMISS] = $product_array[products][Airtime][commission];
		 $agentData[$agent][ModemSALES] = $product_array[products][Modem][sales];
		 $agentData[$agent][ModemCOMMISS] = $product_array[products][Modem][commission];
		 $agentData[$agent][PhoneSALES] = $product_array[products][Phone][sales];
		 $agentData[$agent][PhoneCOMMISS] = $product_array[products][Phone][commission];
		 $agentData[$agent][gprsSALES] = $product_array[products][gprs][sales];
		 $agentData[$agent][gprsCOMMISS] = $product_array[products][gprs][commission];
		}
		
		foreach($agentData as $agent=>$row){
			$sales[Airtime][$agent] = $row[ATSALES]/10000;
			$sales[Modems][$agent] = $row[ModemSALES]/10000;
			$sales[Phone][$agent] = $row[PhoneSALES]/10000;
			$sales[GPRS][$agent] = $row[gprsSALES]/10000;
			$commission[Airtime][$agent] = $row[ATCOMMISS]/10000;
			$commission[Modems][$agent] = $row[ModemCOMMISS]/10000;
			$commission[Phone][$agent] = $row[PhoneCOMMISS]/10000;
			$commission[GPRS][$agent] = $row[gprsCOMMISS]/10000;
		}
		
		//print_r($sales).'.................<br>';
		switch($telesales_report_type){
		case 'Gross Sales':
			$graph_detail[data] = $sales;
			break;
		case 'Commission':
			$graph_detail[data] = $commission;
			break;
		default:
			break;
	}
	
	//$graph_detail[data]= $sales;
	custom_query::select_db('reporting');
	$myreport = new report();
	if($to=='') { $to = date('Y-m-d');}
	//$graph_detail[title]= 'Telesales '.$telesales_report_type.' Perfomance Report '. $_POST[from].' to '.$to;
	$graph_detail[title]= 'Note: Multiply Values on Y-axis by 1000 ['. $_POST[from].' to '.$to.' ]';
	$graph_detail[title1] = 'Telesales '.$telesales_report_type.' Perfomance Report '. $_POST[from].' to '.$to;
	$graph_detail[line_graph]=false;
	$graph_detail[set_data_points]=false;
	$graph_detail[bar_graph]=true;
	$graph_detail[width]=800;
	$graph_detail[height]=1000;
	$graph_detail[legend]=true;
	$graph_detail[setBarColor]= true;
	$period = $_POST[from].' to '.$_POST[to];
	$my_graph = new dbgraph();
	$my_graph->graph($title=$graph_detail[title], $period, $data=$graph_detail,$type='bar');
	custom_query::select_db('graphing');
	$report[graph] = $my_graph->Save();
	$html = '
		<table border="0" cellpadding="1" cellspacing="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th align="center">'.$graph_detail[title1].'</th>
		</tr>
		<tr>
			<td style="height:10px;"><img class="graph" src="http://ccba01.waridtel.co.ug/resources/GRAPHING/graph.php?id='.$report[graph].'.jpg" /></td>
		</tr>
		</table>
	';

	return $html;

}

function addToAgents(&$agents, $agentArrayToBeAppended, $productName, $productValueKey){
   foreach($agentArrayToBeAppended as $agentName => $agentValue){
		if(!$agents[$agentName]) $agents[$agentName]=array();
		if(!$agents[$agentName]["products"]) $agents[$agentName]["products"]=array();
		if(!$agents[$agentName]["products"][$productName]) $agents[$agentName]["products"][$productName] = array();
		
		if(!$agents[$agentName]["products"][$productName][$productValueKey]) $agents[$agentName]["products"][$productName][$productValueKey]=0;
		
		$agents[$agentName]["products"][$productName][$productValueKey] +=  $agentValue;        
	}
}
?>