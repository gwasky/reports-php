<?php

function generate_leads_summary($date){
	if($date==''){ echo "date is ".$date."<br>"; return show_wimax_leads_summary(FALSE);}
	$db_ref[db] = 'wimax';
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	
	$query = "
		SELECT
			(SELECT count(*) FROM leads where date_entered between date_sub('".$date." 21:00:00', interval 1 month) and date_sub('".$date." 20:59:59', interval 1 month)) as leads_created_last_month,
			(SELECT count(*) FROM leads where date_converted between date_sub('".$date." 21:00:00', interval 1 month) and date_sub('".$date." 20:59:59', interval 1 month)) as leads_converted_last_month,
			(SELECT count(*) FROM leads where converted = 0) as unconverted_leads
	";
	 
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Leads summary - Base information ... \n";
	
	//echo nl2br($query)."<hr>";
	 
	$leads[base] = $myquery->single($query);
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Leads summary - Yesterday information ... \n";
	$query = "
		SELECT

			(SELECT count(*) FROM leads where leads.date_entered between '".$date." 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Created,
			(SELECT count(*) FROM leads where leads.date_converted between '".$date." 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Converted";
			
	$leads[yesterday] = $myquery->single($query);
	
	//echo nl2br($query)."<hr>";
	
	$query = "
		SELECT
			(SELECT count(*) FROM leads where leads.date_entered between '".substr($date,0,7)."-01 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Created,
			(SELECT count(*) FROM leads where leads.date_converted between '".substr($date,0,7)."-01 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Converted";
		
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Leads summary - Month information ... \n";
	 
	$leads[month] = $myquery->single($query);
	
	//echo nl2br($query)."<hr>";
	 
	$query = "
		SELECT
			(SELECT count(*) FROM leads where leads.date_entered between '".substr($date,0,4)."-01-01 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Created,
			(SELECT count(*) FROM leads where leads.date_converted between '".substr($date,0,4)."-01-01 21:00:00' and '".$date." 20:59:59' and deleted = 0) as Converted";
	
	echo date('Y-m-d H:i:s')." : [".$date."] Generating Wimax Leads summary - Year information ... \n";
	
	$leads[year] = $myquery->single($query);
	
	//echo nl2br($query)."<hr>";
	
	//print_r($leads[bases]); 
	$leads[bases][last_date] = $date;
	$query = "select date_format('".$date."','%M') as this_month;";
	$result = $myquery->single($query);
	$leads[bases][this_month] = $result[this_month];
	$leads[bases][this_year] = substr($date,0,4);
	$leads[bases][unconverted] = $leads[base][unconverted_leads];
	$leads[queueleads] = prep_leads_queues_table_data(generate_unconverted_leads_by_queue(),$total = $leads[bases][unconverted]); 
	//print_r($leads);
	return show_wimax_leads_summary($leads);
}

function generate_unconverted_leads_by_queue(){
	$myquery = new custom_query();
	custom_query::select_db('wimax');
	
	$query = "
		SELECT
			qs_queues.name as queue,
			count(qs_queues.name) as counts
		FROM
			qs_queues
			Inner Join qs_queues_leads_c ON qs_queues.id = qs_queues_leads_c.qs_queues_lsqs_queues_ida
			Inner Join leads ON qs_queues_leads_c.qs_queues_leadsleads_idb = leads.id
		WHERE leads.deleted = 0 and 
			leads.converted = 1 and 
			qs_queues.deleted = 0 and 
			qs_queues_leads_c.deleted = 0
		group by 
			qs_queues.name
		order by 
			counts DESC
	";
	
	return $myquery->multiple($query);
}

function prep_leads_queues_table_data($raw_stats,$total){
	
	foreach($raw_stats as $key=>$row){
		unset($skip);
		$row_key_list = array_keys($row);
			if(in_array('queue',$row_key_list)){
				if(trim($row[queue]) == '') { $skip = TRUE; }
				$row['Queue'] = $row[queue]; 
				$row['number_of_leads'] = $row['counts']; 
				unset($row['counts'],$row[queue]);
				$row['%age'] = number_format($row['number_of_leads']*100/$total,1);
				$row['number_of_leads'] = number_format($row['number_of_leads'],0);
			}else{ echo "uncatered for scenario in unconverted leads opps ... <br>"; }
		
		if(!$skip) { $stats[] = array('data'=>$row); }
		unset($raw_stats[$key],$key,$row);	
	}
	return $stats;
}
	
		
function show_wimax_leads_summary($data){
	if($data == FALSE){return "NO LEAD DATA <br>";}
	
	$html = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">SUMMARY</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';
	
	//CASE ORIGINS
	//Day
	$table = array(
				   'title'=>'Yesterday '.$_REQUEST[use_date],
				   'rows'=>$data[yesterday],
				   'notes'=>'
				   			No Leads Created Last Month on Date: '.number_format($data[yesterday_cases_created],0).' <br>
							No Leads Converted Last Month on Date : '.number_format($data[last_months_cases_created],0).' '
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>';
		
	//MONTH
	$table = array(
				   'title'=>'Month of '.$data[bases][this_month],
				   'rows'=>$data[month]);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>';
	
	//YEAR
	$table = array(
				   'title'=>'Year of '.$data[bases][this_year],
				   'rows'=>$data[year]);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_static_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='',$table='',$style='border-color:#FFF;').'
	</td>
		</tr>
			</table>
		</div>
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div class="category_head">UN CONVERTED LEADS BY QUEUE</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr></table>
		<div align="centre" class="box">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
	';

	$table = array(
				   'rows'=>$data[queueleads],
				   'notes'=>'Total number of Leads : '.number_format($data[bases][unconverted],0).'  <br>'
					);
	
	$html .= '
	<td class="data_td" width="30%" align="center" valign="top">
	'.show_table($table,$padding=1,$spacing=0,$border=0,$align='centre',$width='50%',$table='',$style='border-color:#FFF;').'
	</td>
	</tr>
			</table>
		</div>
	';
	
	return $html;
}

?>