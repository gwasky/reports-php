<?php
function generate_mis_ccba_report($month){
	
	$this_month = date('m',strtotime($month));
	$mis_data[no_days_month][0][number] = date('t',strtotime($month));
	$mis_data[no_days_month][1][month] = date('M-y',strtotime($month));
	$from = substr($month,0,8).'01 00:00:00';
	$to = substr($month,0,8).date('t',strtotime($month)).' 23:59:59';
	
	if(!$month){
		$month = date('Y-m-d');
		$this_month = date('m',strtotime($month));
		$mis_data[no_days_month][0][number] = date('t',strtotime($month));
		$mis_data[no_days_month][1][month] = date('M-y',strtotime($month));
		$from = substr($month,0,8).'01 00:00:00';
		$to = substr($month,0,8).date('t',strtotime($month)).' 23:59:59';
	}
	
	//Queries
	custom_query::select_db('ccba02.reportscrm');
	$myquery = new custom_query();
	$query_total_smss = "SELECT
							sum(reportsphonecalls.sms_count) as Total_SMS
							FROM
							reportsphonecalls
							WHERE
							reportsphonecalls.createdon BETWEEN '".$from."' and '".$to."'
						";
	
	$mis_data[total_smss] = $myquery->multiple($query_total_smss);
	
	custom_query::select_db('ccba02.ivrperformance');
	$myquery = new custom_query();
	$query_subscount = "SELECT
							subscount.total_subs
							FROM
							subscount
							WHERE
							left(subscount.`day`,7) = '".date('Y-m',strtotime($month))."';'
						";
	
	$mis_data[subscount] = $myquery->multiple($query_subscount);
	
	custom_query::select_db('ccba01.smsdata');
	$myquery = new custom_query();
	$query_outbound_smss = "SELECT
							incoming.source,
							COUNT(incoming.source) AS num
							FROM
							incoming
							WHERE
							datesent BETWEEN '".$from."' and '".$to."'
							GROUP BY
							source
							";
	//print nl2br($query_outbound_smss);
	//exit();		
	$mis_data[outbound_smss] = $myquery->multiple($query_outbound_smss);
	
	custom_query::select_db('ccba02.reportscrm');
	$myquery = new custom_query();
	$query_months_wrapups = "SELECT
							count(*) as Number
							FROM
							reportsphonecalls
							WHERE
							reportsphonecalls.createdon between '".$from."' and '".$to."'
							";
							
	//print nl2br($query_months_wrapups);
	//exit();						
	$mis_data[months_wrapups] = $myquery->multiple($query_months_wrapups);
	//echo '<textarea name="" cols="40" rows="50">'.print_r($mis_data[outbound_smss],true).'</textarea>';
	//exit();
	
	custom_query::select_db('ccba01.smsbroadcasts');
	$myquery = new custom_query();
	$query_bithdays = "SELECT
							count(autobirth.msisdn) as sms_count
							FROM
							autobirth
							WHERE
							month(autobirth.birthdate) = '".$this_month."'
							";
							
	$mis_data[bithdays] = $myquery->multiple($query_bithdays);
	//print nl2br($query_bithdays);
	//exit();	
	
	
	custom_query::select_db('ccba01.reportscrm');
	$myquery = new custom_query();
	$query_gsm_cases = "select count(*) as num_cases from reportscrm where createdon between '".$from."' and '".$to."'";
	$mis_data[gsm_cases] = $myquery->multiple($query_gsm_cases);
	//print nl2br($query_gsm_cases);
	//exit();
	
	
	custom_query::select_db('ccba01.reportscrm');
	$myquery = new custom_query();
	$query_gsm_cases_closed = "SELECT
									count(status) as `count`
								FROM
									(
										SELECT
											if(caseresolution.casenum is null,'Open','Closed') as status
										FROM
											reportscrm
											left outer Join caseresolution ON reportscrm.casenum = caseresolution.casenum
										WHERE
											(caseresolution.actualend between '".$from."' and '".$to."')
													or
											(reportscrm.createdon between '".$from."' and '".$to."')
									)
								 	as tablee
								WHERE
								status = 'Closed'
								GROUP BY
								status";
	$mis_data[gsm_cases_closed] = $myquery->multiple($query_gsm_cases_closed);
	
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	$query_data_cases = "select count(*) as num_cases from cases where date_entered between '".$from."' and '".$to."' and deleted = 0";
	$mis_data[data_cases] = $myquery->multiple($query_data_cases);
	//print nl2br($query_data_cases);
	//exit();
	
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	$query_data_cases_closed = "SELECT count(*) as num_cases
						FROM
						cases
						INNER JOIN cases_cstm ON (cases.id=cases_cstm.id_c)
						INNER JOIN accounts ON (cases.account_id=accounts.id)
						LEFT OUTER JOIN cases_audit ON (cases_audit.parent_id = cases.id AND cases_audit.after_value_string = 'Closed'
						AND cases_audit.before_value_string != 'Closed' AND cases_audit.field_name = 'status')
						WHERE cases.deleted = '0' AND accounts.deleted = '0'
						and ((cases.date_entered between '".$from."' and '".$to."') OR
						(cases_audit.date_created between '".$from."' and '".$to."'))";
	$mis_data[data_cases_closed] = $myquery->multiple($query_data_cases_closed);
	//print nl2br($query_data_cases);
	
	
	custom_query::select_db('ccba02.smsfeedback');
	$myquery = new custom_query();
	$query_csat = "SELECT
							IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
							COUNT(smsfeedback.sms_evaluation.text) as number
						FROM
							smsfeedback.sms_evaluation
							LEFT OUTER JOIN reportscrm.reportsphonecalls ON smsfeedback.sms_evaluation.wrapup_id = reportscrm.reportsphonecalls.id
						WHERE
							IF(
								reportscrm.reportsphonecalls.createdon IS NULL,
								smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' and '".$to."',
								reportscrm.reportsphonecalls.createdon BETWEEN '".$from."' and '".$to."'
							)
						GROUP BY
							evaluation_answer";
	$mis_data[csat] = $myquery->multiple($query_csat);
	//print nl2br($query_data_cases);
			
	
	custom_query::select_db('ccba02.ivrperformance');
	$myquery = new custom_query();
	$query_ivr = "SELECT 
						(SELECT count(asterisk_cdrs.id) as num
						FROM asterisk_cdrs
						WHERE
						asterisk_cdrs.date_entered
						BETWEEN '".$from."' and '".$to."') as total_received,
						
						(SELECT
						count(*) as num
						FROM
						asterisk_cdrs
						INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value
						WHERE
						asterisk_cdrs.date_entered BETWEEN '".$from."' and '".$to."'
						AND asterisk_cdrs.last_option_group = 'IVR') as in_ivr,
						
						(SELECT
						count(*) as num
						FROM
						asterisk_cdrs
						INNER JOIN asterisk_translations ON asterisk_cdrs.last_option_value = asterisk_translations.option_value
						WHERE
						asterisk_cdrs.date_entered BETWEEN '".$from."' and '".$to."'
						AND asterisk_cdrs.last_option_group = 'Agent') as to_agent";
	$mis_data[ivr] = $myquery->multiple($query_ivr);
	//print nl2br($query_ivr);
	
	custom_query::select_db('ccba02.ivrperformance');
	$myquery = new custom_query();
	$query_ivr_data = "select
						queue.que as queue,
						queue.entrydate as thedate,
						queue.servicelevel as sl,
						queue.avgcallduration as acd,
						queue.avgabancallwait as aacw,
						queue.avgspeedofans as asos,
						calldetail.status,
						calldetail.calls
					from
						queue
						inner join calldetail on calldetail.id_c = queue.id
					where
					queue.entrydate BETWEEN  '".$from."' and '".$to."'";
	$ivr_data = $myquery->multiple($query_ivr_data);
	
	foreach($ivr_data as $row){
		$sum_sl += $row[sl]*$row[calls];
		$sum_calls += $row[calls];
		
		if($row[status] == 'Received'){
			$received_calls += $row[calls];
		}
		
		if($row[status] == 'Handled'){
			$handled_calls += $row[calls];
			$acd_sum += (time_to_sec($row[acd])*$row[calls]);
			$asos_sum += (time_to_sec($row[asos])*$row[calls]);
		}
		
		if($row[status] == 'Abandon'){
			$abandon_calls += $row[calls];
			$aacw_sum += (time_to_sec($row[aacw])*$row[calls]);
		}
	}
	$mis_data['ivr_stats'][0]['service_level'] = number_format($sum_sl/$sum_calls,2);
	$mis_data['ivr_stats'][1]['received_calls'] = $received_calls;
	$mis_data['ivr_stats'][2]['handled_calls'] = $handled_calls;
	$mis_data['ivr_stats'][3]['abandon_calls'] = $abandon_calls;
	$mis_data['ivr_stats'][4]['avg_call_duration'] = sec_to_time(intval($acd_sum/$handled_calls));
	$mis_data['ivr_stats'][5]['avg_abandon_waiting'] = sec_to_time(intval($aacw_sum/$abandon_calls));
	$mis_data['ivr_stats'][6]['avg_queue_waiting'] = sec_to_time(intval($asos_sum/$handled_calls));
	
	
	custom_query::select_db('ccba02.reportscrm');
	$myquery = new custom_query();
	$query_inquiries_complaints_srr = "SELECT 
									count(reportsphonecalls.id) as num,
									subsubcategory.subject_type as types
								FROM 
									reportsphonecalls
									LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject)
								AND
									(subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
								WHERE
									reportsphonecalls.createdon BETWEEN '".$from."' and '".$to."'
								GROUP BY
								types";
	$mis_data[inquiries_complaints_srr] = $myquery->multiple($query_inquiries_complaints_srr);
	//print nl2br($query_inquiries_complaints_srr);
	//echo '<textarea name="" cols="40" rows="50">'.print_r($mis_data[inquiries_complaints_srr],true).'</textarea>';
	//exit();
	
	
	function summarise($mis_data){
		foreach($mis_data as $report_item => $report_data){
			foreach($report_data as $key => $data_item){
				if($report_item == 'outbound_smss'){
					$report[$report_item][$data_item[source]] = $data_item[num];
				}elseif($report_item == 'csat'){
					$report[$report_item][$data_item[evaluation_answer]] = $data_item[number];
					$report[$report_item][total] += $data_item[number];
				}elseif($report_item == 'inquiries_complaints_srr'){
					$report[$report_item][$data_item[types]] = $data_item[num];
					$report[$report_item][total_warupups] += $data_item[num];
				}else{
					foreach($data_item as $key_name => $value){
						$report[$report_item][$key_name] = $value;
					}
				}			
			}
		}
		$report['inquiries_complaints_srr']['Non Service Restoration Request'] = $report['inquiries_complaints_srr']['total_warupups'] - $report['inquiries_complaints_srr']['Service Restoration Request'];
		
		return $report;
	}
	
	$report = summarise($mis_data);
	//echo '<textarea name="" cols="40" rows="50">'.print_r($report,true).'</textarea>';
	return display_mis_ccba_report($report);
}

function display_mis_ccba_report($report){

$html .= '<table cellspacing="0" cellpadding="0">
			  <col width="297">
			  <col width="74">
			  <tr height="20">
				<th height="20" width="297">Description</th>
				<th width="74">'.$report['no_days_month']['month'].'</th>
			  </tr>
			  <tr height="20">
				<td class="text_values">Average 90day active Subcriber    base</td>
				<td class="values">'.number_format($report['subscount']['total_subs'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Total No. of Agents</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Average agents available per    hr</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Days in Month</td>
				<td class="values">'.number_format($report['no_days_month']['number'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Cost per Agent</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Total Calls Received</td>
				<td class="values">'.number_format($report['ivr']['total_received'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Resolved/stopped at IVR</td>
				<td class="values">'.number_format($report['ivr']['in_ivr'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Transferred to Call    Centre/Agent</td>
				<td class="values">'.number_format($report['ivr']['to_agent'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">% Resolution/Stop at IVR</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Number of Calls (Call Centre)</td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Calls Offered</td>
				<td class="values">'.number_format($report['ivr_stats']['received_calls'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Calls Answered</td>
				<td class="values">'.number_format($report['ivr_stats']['handled_calls'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Calls Answered in 20 s</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Calls Answered more than 20 s</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Calls Abandoned</td>
				<td class="values">'.number_format($report['ivr_stats']['abandon_calls'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Abandoned Rate</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">1st call Resolution( %)</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Call Center KPI</td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Service Level</td>
				<td class="values">'.number_format($report['ivr_stats']['service_level'],2).'%</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Average Talk Time (ATT)</td>
				<td class="values">'.$report['ivr_stats']['avg_call_duration'].'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Average Queue Time (AQT)</td>
				<td class="values">'.$report['ivr_stats']['avg_queue_waiting'].'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Avg. No. of Calls per    subscriber</td>
				<td width="74">&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Avg. No. of Calls per Agent</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Utilisation per Agent</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Cost per Call Received</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Cost per Call Handled</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Weighted average Cost per Call</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Type of Interaction</td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Enquiries</td>
				<td class="values">'.number_format($report['inquiries_complaints_srr']['Inquiry'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Complaints</td>
				<td class="values">'.number_format($report['inquiries_complaints_srr']['Negative Feedback'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Service (restoration) requests</td>
				<td class="values">'.number_format($report['inquiries_complaints_srr']['Service Restoration Request'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Non Service (restoration) Requests</td>
				<td class="values">'.number_format($report['inquiries_complaints_srr']['Non Service Restoration Request'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">TOTAL Interactions</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Customer Satisfaction</td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Evaluation Requests Sent</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Evaluation Responses Received</td>
				<td class="values">'.number_format($report['csat']['total'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Response rate</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Satisfied Responses</td>
				<td class="values">'.number_format($report['csat']['Yes'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Un satisfied Responses</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">CSAT Score</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Resolutions</td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Opened</td>
				<td width="74">&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">GSM</td>
				<td class="values">'.number_format($report['gsm_cases']['num_cases'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">DATA</td>
				<td class="values">'.number_format($report['data_cases']['num_cases'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">No. of Tickets opened</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Avg No. of Tickets opened per    day</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Closed</td>
				<td width="74">&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">GSM</td>
				<td class="values">'.number_format($report['gsm_cases_closed']['num_cases'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">DATA</td>
				<td class="values">'.number_format($report['data_cases_closed']['num_cases'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">No. of Tickets closed</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Avg No. of Tickets closed per    day</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Efficiency %</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound calls</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound Calls - Telesales</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound Calls - Retention</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values"></td>
				<td></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound Calls per Sub (VLR 90    days base)</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - Birthday</td>
				<td class="values">'.number_format($report['bithdays']['sms_count'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - Wrap ups (from Interactions)</td>
				<td class="values">'.number_format($report['total_smss']['Total_SMS'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - SMS Helpline auto response</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - SMS Helpline non auto response</td>
				<td class="values">'.number_format($report['outbound_smss']['ccportal smsmanager'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - Case/Ticket followup</td>
				<td class="values">'.number_format($report['outbound_smss']['ccportal send_free_sms'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - Warid pesa Account info follow up</td>
				<td class="values">'.number_format($report['outbound_smss']['SIM Reg broadcast'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS - DATA expiry Notifications</td>
				<td class="values">'.number_format($report['outbound_smss']['DATA Expiry Notifications'],0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Outbound SMS per Sub (VLR 90 days base)</td>
				<td class="values"></td>
			  </tr>
			  <tr height="20">
				<td class="text_values">&nbsp;</td>
				<td width="74">&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Business center Walkins</td>
				<td>&nbsp;</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Plaza</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			  <tr height="20">
				<td class="text_values">Forest Mall</td>
				<td class="values">'.number_format(0,0).'</td>
			  </tr>
			</table>';
			
	return $html;
}
?>