<?php

function generate_retention_customber_bio_data(){

	custom_query::select_db('survey');
	
	$report[start_time] = strtotime(date('Y-m-d H:i:s'));

	$myquery = new custom_query();
	
	$query = "
		(
			SELECT
			sv_welcome_call.name as MSISDN,
			sv_welcome_call.customer_name,
			sv_welcome_call.dob,
			sv_welcome_call.email
			FROM
			sv_welcome_call
			WHERE
			(sv_welcome_call.customer_name != '' OR (sv_welcome_call.dob != '0000-00-00' OR sv_welcome_call.dob != '') OR sv_welcome_call.email != '')
		)
		UNION
		(
			SELECT
			sa_product_knowledge.name AS MSISDN,
			sa_product_knowledge.customer_name,
			sa_product_knowledge.birth_date AS dob,
			sa_product_knowledge.email
			FROM
			sa_product_knowledge
			WHERE
			(sa_product_knowledge.customer_name != '' OR  (sa_product_knowledge.birth_date != '0000-00-00' OR sa_product_knowledge.birth_date != '') OR sa_product_knowledge.email != '')
		)
		UNION
		(
			SELECT
			if(
				left(sv_paka_care.name,1)=0,
				concat('256',right(sv_paka_care.name,LENGTH(sv_paka_care.name)-1)),
				sv_paka_care.name
			) AS MSISDN,
			sv_paka_care.customer_name,
			'' AS dob,
			sv_paka_care.email
			FROM
			sv_paka_care
			WHERE
			(sv_paka_care.customer_name != '' OR sv_paka_care.email != '')
		)
		UNION
		(
			SELECT
			CAST(sv_repeat_wraups.name AS UNSIGNED) AS MSISDN,
			sv_repeat_wraups.customer_name,
			sv_repeat_wraups.dob,
			sv_repeat_wraups.email
			FROM
			sv_repeat_wraups
			WHERE
			(sv_repeat_wraups.customer_name != '' OR (sv_repeat_wraups.dob != '000-00-00' OR sv_repeat_wraups.dob != '') OR sv_repeat_wraups.email != '')
		)
		UNION
		(
			SELECT
			if(
				left(sv_questions.name,1)=0,
				concat('256',right(sv_questions.name,LENGTH(sv_questions.name)-1)),
				sv_questions.name
			) AS MSISDN,
			sv_questions.full_names AS customer_name,
			sv_questions.dob,
			sv_questions.email
			FROM
			sv_questions
			WHERE
			(sv_questions.full_names != '' OR (sv_questions.dob != '000-00-00' OR sv_questions.dob != '') OR sv_questions.email != '')
		)
		UNION
		(
			SELECT
			ac_abondoned_callers.name AS MSISDN,
			ac_abondoned_callers.customer_name,
			ac_abondoned_callers.dob,
			ac_abondoned_callers.email
			FROM
			ac_abondoned_callers
			WHERE
			(ac_abondoned_callers.customer_name != '' OR (ac_abondoned_callers.dob != '0000-00-00' OR ac_abondoned_callers.dob != '') OR ac_abondoned_callers.email != '')
		)
		UNION
		(
			SELECT
			sa_gprs.name AS MSISDN,
			sa_gprs.customer_name,
			sa_gprs.date_of_birth AS dob,
			sa_gprs.email_address AS email
			FROM
			sa_gprs
			WHERE
			(sa_gprs.customer_name != '' OR (sa_gprs.date_of_birth != '0000-00-00' OR sa_gprs.date_of_birth != '') OR sa_gprs.email_address != '')
		)
		UNION
		(
			SELECT
			csat_csat_evaluation_follow_up.name AS MSISDN,
			csat_csat_evaluation_follow_up.customer_name,
			csat_csat_evaluation_follow_up.date_of_birth AS dob,
			csat_csat_evaluation_follow_up.customer_email AS email
			FROM
			csat_csat_evaluation_follow_up
			WHERE
			(csat_csat_evaluation_follow_up.customer_name != '' OR (csat_csat_evaluation_follow_up.date_of_birth != '0000-00-00' OR csat_csat_evaluation_follow_up.date_of_birth != '') OR csat_csat_evaluation_follow_up.customer_email != '')
		)
		UNION
		(
		 	SELECT
			REPLACE(REPLACE(REPLACE(reg_registration.preferred_phone_number,' ',''),'+',''),'-','') AS MSISDN,
			reg_registration.name AS customer_name,
			reg_registration.dob,
			reg_registration.email
			FROM
			reg_registration
			WHERE
			REPLACE(REPLACE(REPLACE(reg_registration.preferred_phone_number,' ',''),'+',''),'-','') != '' AND
			(reg_registration.name !='' OR (reg_registration.dob != '0000-00-00' OR reg_registration.dob != '' OR reg_registration.dob IS NOT NULL) OR reg_registration.email != '')
		)
	";
	
	$rows = $myquery->multiple($query);
	
	foreach($rows as $index=>$r){
		if(strlen(trim($r[MSISDN])) == 12 and substr(trim($r[MSISDN]),0,5) == '25670' and intval(trim($r[MSISDN])) > 0){
			$r[MSISDN] = trim($r[MSISDN]);
			
			//Names
			if(strlen(str_replace(' ','',$r[customer_name])) > 1){
				$data['Customer Names'][$r[MSISDN]] = ucwords(strtolower(trim($r[customer_name])));
			}else{
				++$report[summary][faults]['Customer Names'];
			}
			
			//Date of birth
			if(str_replace(array(' '),'',$r[dob]) != '' and str_replace(array(' '),'',$r[dob]) != '0000-00-00'){
				$data['Dates of birth'][$r[MSISDN]] = str_replace(array(' '),'',$r[dob]);
			}else{
				++$report[summary][faults]['Dates of birth'];
			}

			//Email address
			if(str_replace(' ','',$r[email]) != ''){
				$split_1 = explode('@',str_replace(' ','',$r[email]));
				if(count($split_1) == 2){
					$split_2 = explode('.',str_replace(' ','',$r[email]));
					if(count($split_2) > 1){
						$data['Email addresses'][$r[MSISDN]] = strtolower(str_replace(' ','',$r[email]));
					}else{
						++$report[summary][faults]['Email addresses'];
					}
				}else{
					++$report[summary][faults]['Email addresses'];
				}
			}
		}else{
			++$report[summary][faults][MSISDN];
		}
		
		//save memory
		unset($rows[$index],$index);
	}
	
	$report[data] = $data;
	
	return display_retention_customber_bio_data($report);
}

function display_retention_customber_bio_data($report){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr>
			<th>Summaries</th>
		</tr>
		<tr>
			<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	
	foreach($report[summary][faults] as $fault_name=>$value){
		$html .= '
				<tr>
					<th>FAULTS '.$fault_name.'</th>
					<td class="values">'.$value.'</td>
				</tr>	
		';
	}
	
	$html .= '
			</table>
		</tr>
		<tr>
			<th>Customer Bio data by MSISDN</th>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<tr>
	';
	$table_names = array_keys($report[data]);
	foreach($table_names as $table_name){
		$html .= '
				<th>'.$table_name.'</th>
		';
	}
	unset($table_name);
	$html .= '
			</tr>
			<tr>
	';
	
	foreach($report[data] as $table_name=>$table_data){
		$html .= '
				<td valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
					<tr>
						<th>NO</th>
						<th>MSISDN</th>
						<th>'.strtoupper($table_name).'</th>
					</tr>
		';
		
		$II = 0;
		foreach($table_data as $msisdn=>$value){
			$html .= '
					<tr>
						<td class="values">'.++$II.'</td>
						<td class="text_values">'.$msisdn.'</td>
						<td class="text_values">'.$value.'</td>
					</tr>
			';
		}
		
		$html .= '
					</table>
				</td>
		';
	}
	
	$html . '
			</tr>
			</table>
			</td>
		</tr>
		</table>
	';
	
	$report[end_time] = strtotime(date('Y-m-d H:i:s'));
	
	return '<div>Report took '.number_format($report[end_time] - $report[start_time]).' seoconds</div>'.$html;
}

?>