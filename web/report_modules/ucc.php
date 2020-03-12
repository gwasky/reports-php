<?php

function generate_ucc_report($period){

	if($period == ''){ return ""; }
	
	$period_input_month = date_reformat($period,'%m');
	
	if(1 <= intval($period_input_month) and intval($period_input_month) <= 3){
		$dates[0] = date_reformat($period,'%Y').'-01-01';
		//$dates[2] = date_reformat($period,'%Y').'-03-31';
		$dates[1] = date_reformat($period,'%Y').'-02-01';
		$dates[2] = date_reformat($period,'%Y').'-03-31';
	}elseif(4 <= intval($period_input_month) and intval($period_input_month) <= 6){
		$dates[0] = date_reformat($period,'%Y').'-04-01';
		//$dates[2] = date_reformat($period,'%Y').'-06-30';
		$dates[1] = date_reformat($period,'%Y').'-05-01';
		$dates[2] = date_reformat($period,'%Y').'-06-30';
	}elseif(7 <= intval($period_input_month) and intval($period_input_month) <= 9){
		$dates[0] = date_reformat($period,'%Y').'-07-01';
		//$dates[2] = date_reformat($period,'%Y').'-09-30';
		$dates[1] = date_reformat($period,'%Y').'-08-01';
		$dates[2] = date_reformat($period,'%Y').'-09-30';
	}else{
		$dates[0] = date_reformat($period,'%Y').'-10-01';
		//$dates[2] = date_reformat($period,'%Y').'-12-31';
		$dates[1] = date_reformat($period,'%Y').'-11-01';
		$dates[2] = date_reformat($period,'%Y').'-12-31';
	}
	$ucc[dates] = $dates;
	$from = $dates[0]; $to = $dates[2];
	$myquery = new custom_query();
	
	
	//BILLED DATA ACCOUNTS
	$service_type = '%service_type%Postpaid%';
	$invoicing_type = '%invoicing_type%normal%';
	$invoice = '%Title%TAX INVOICE%';
	
	$query = "
		select
			count(if(left(wimax_invoicing.billing_date,7) = '".substr($dates[0],0,7)."', 1, NULL)) as '".date_reformat($dates[0],'%b - %y')."',
			count(if(left(wimax_invoicing.billing_date,7) = '".substr($dates[1],0,7)."', 1, NULL)) as '".date_reformat($dates[1],'%b - %y')."',
			count(if(left(wimax_invoicing.billing_date,7) = '".substr($dates[2],0,7)."', 1, NULL)) as '".date_reformat($dates[2],'%b - %y')."'
		from
			wimax_invoicing
		where
			wimax_invoicing.billing_date between '".$from."' and '".$to."' AND
			deleted = 0 AND
		(
			wimax_invoicing.details LIKE '".$service_type."'
			AND wimax_invoicing.details LIKE '".$invoicing_type."'
			AND wimax_invoicing.details LIKE '".$invoice."'
		);
	";
	
	//echo nl2br($query);
	
	$ucc[records][0]['Number of billed accounts']['Number of billed accounts - PHONE'][data] = '0';
	$ucc[records][0]['Number of billed accounts']['Number of billed accounts - DATA'][data] = $myquery->single($query,'wimax');
	
	//NO OF SERVICE RESTORATION
	$ucc[records][1]['Number of service restoration wrap ups']['Number of service restoration wrap ups'][total] = get_service_restoration_wrapups($dates);
	$ucc[records][1]['Number of service restoration wrap ups']['Total No of service restoration requests (Wrap ups and complaints)'][total] = '0';
	$ucc[records][1]['Number of service restoration wrap ups']['Number of service restoration requests resolved with 1 day of receipt'][total] = '0';
	$ucc[records][1]['Number of service restoration wrap ups']['Number of service restoration requests resolved with 2 days of receipt'][total] = '0';

	
	//COMPLAINTS
	$ucc[records][2]['Number of complaints']['Number of complaints - PHONE'][gsm] = get_gsm_complaints($dates, $billing=false);
	$ucc[records][2]['Number of complaints']['Number of complaints - DATA'][data] = get_data_complaints($dates, $billing=false);
	
	//COMPLAINTS - BILLING
	$ucc[records][3]['Number of billing complaints']['Number of billing complaints - PHONE'][gsm] = get_gsm_complaints($dates, $billing=true);
	$ucc[records][3]['Number of billing complaints']['Number of billing complaints - DATA'][data] = get_data_complaints($dates, $billing=true);
	
	
	//COMPLAINTS - NON BILLING SOLVED IN 1 DAY
	$ucc[records][4]['Number of non billing complaints resolved in 1 day of receipt']['Number of non billing complaints resolved in 1 day of receipt - PHONE'][gsm] = get_gsm_complaints_solved_in_x_days($dates, $billing=false, $solved_in=1);
	$ucc[records][4]['Number of non billing complaints resolved in 1 day of receipt']['Number of non billing complaints resolved in 1 day of receipt - DATA'][data] = get_data_complaints_solved_in_x_days($dates, $billing=false, $solved_in=1);
	
	
	//COMPLAINTS - NON BILLING SOLVED IN 2 DAYS
	$ucc[records][5]['Number of non billing complaints resolved with 2 days']['Number of non billing complaints resolved with 2 days - PHONE'][gsm] = get_gsm_complaints_solved_in_x_days($dates, $billing=false, $solved_in=2);
	$ucc[records][5]['Number of non billing complaints resolved with 2 days']['Number of non billing complaints resolved with 2 days - DATA'][data] = get_data_complaints_solved_in_x_days($dates, $billing=false, $solved_in=2);
	
	
	//COMPLAINTS - BILLING SOLVED IN 5 DAYS
	$ucc[records][6]['Number of billing complaints resolved with 5 days']['Number of billing complaints resolved with 5 days - PHONE'][gsm] = get_gsm_complaints_solved_in_x_days($dates, $billing=true, $solved_in=5);
	$ucc[records][6]['Number of billing complaints resolved with 5 days']['Number of billing complaints resolved with 5 days - DATA'][data] = get_data_complaints_solved_in_x_days($dates, $billing=true, $solved_in=5);
	
	
	//COMPLAINTS - BILLING SOLVED IN 20 DAYS
	$ucc[records][7]['Number of billing complaints resolved with 20 days']['Number of billing complaints resolved with 20 days - PHONE'][gsm] = get_gsm_complaints_solved_in_x_days($dates, $billing=true, $solved_in=20);
	$ucc[records][7]['Number of billing complaints resolved with 20 days']['Number of billing complaints resolved with 20 days - DATA'][data] = get_data_complaints_solved_in_x_days($dates, $billing=true, $solved_in=20);
	
	
	//COMPLAINTS - BILLING SOLVED IN 2 DAYS
	$ucc[records][8]['Number of billing complaints resolved with 30 days']['Number of billing complaints resolved with 30 days - PHONE'][gsm] = get_gsm_complaints_solved_in_x_days($dates, $billing=true, $solved_in=30);
	$ucc[records][8]['Number of billing complaints resolved with 30 days']['Number of billing complaints resolved with 30 days - DATA'][data] = get_data_complaints_solved_in_x_days($dates, $billing=true, $solved_in=30);
	
	
	return display_ucc_report($ucc); 
}


function display_ucc_report($report){
	
	
	//$html = nl2br(print_r($report,true));
	
if(count($report)>0){
	
	$html = '<table width="32%" border="0" cellpadding="0" cellspacing="1">';
	$html .= '<tr>';
	$html .= '<td></td>';
	foreach($report[dates] as $datekey => $month){
		$html .= '<th>'.date("M-Y", strtotime($month)).'</th>';
	}
	$html .= '</tr>';
	
	foreach($report[records] as $position => $param_arr){
		foreach($param_arr as $paramname => $data_arr){
			$html .= '<tr>';
			$html .= '<th colspan=4>'.$paramname.'</th>';
			$html .= '<tr>';
			foreach($data_arr as $title => $subtitles){	 	
				foreach($subtitles as $subtitle => $info){
					$html .= '<tr>';
					$html .= '<td class="text_values">'.$title.'</td>';
					foreach($info as $key => $value){
						$html .= '<td width="68%" class="values">'.number_format($value,0).'</td>';	
					}
					$html .= '</tr>';
				}	
			}
			$html .= '<tr>';
			$html .= '<td colspan=4>&nbsp;</td>';
			$html .= '<tr>';
		}
	}
	$html .= '</table>';
}
	
	return $html;
}
?>