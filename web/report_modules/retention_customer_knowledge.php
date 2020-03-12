<?php

function generate_cust_knowledge($from,$to,$reporttype){
//echo $reporttype;


	//OLD COLUMNS
	/*		sa_product_knowledge.artist,
			sa_product_knowledge.artist_song,
			sa_product_knowledge.gprs_awareness,
			sa_product_knowledge.gprs_lead,
			sa_product_knowledge.no_of_complaints,
			sa_product_knowledge.mobile_money_interest,
			sa_product_knowledge.crbt_awareness,
			sa_product_knowledge.cmb_awareness,
			sa_product_knowledge.kawa_awareness,
			sa_product_knowledge.pakalast_awareness,
			sa_product_knowledge.pakachini_awareness,
			sa_product_knowledge.crbt_lead,
			sa_product_knowledge.pakalast_lead,
			sa_product_knowledge.cmb_lead,
			sa_product_knowledge.kawa_lead,
			sa_product_knowledge.pakachini_lead,
	*/
 
	$myquery = new custom_query();
	$query = "
		SELECT
			users.first_name,
			users.last_name,
			users.department as team,
			concat(users.first_name,' ',users.last_name) as full_name,
			sa_product_knowledge.id,
			sa_product_knowledge.name as msisdn,
			sa_product_knowledge.call_status,
			sa_product_knowledge.warid_improve,
			sa_product_knowledge.district_pd_know as district,
			sa_product_knowledge.town,
			sa_product_knowledge.network_improve_landmark,
			sa_product_knowledge.network_subject_issue,
			sa_product_knowledge.help_medium,
			sa_product_knowledge.reason_for_using_medium,
			sa_product_knowledge.improve_distribution_products,
			left(sa_product_knowledge.date_modified, 10) AS call_date,
			sa_product_knowledge.customer_name,
			sa_product_knowledge.favourite_business_centre,
			sa_product_knowledge.fav_customer_care_access,
			sa_product_knowledge.wtu_promo_participation,
			sa_product_knowledge.defrauded_before,
			sa_product_knowledge.mostliked_wtu_aspect

--			sa_product_knowledge.reason_help_medium,
--			sa_product_knowledge.original_complaint,
--			sa_product_knowledge.dtf_awareness,
--			sa_product_knowledge.learn_source as dtf_learn_source,
--			sa_product_knowledge.kitabuse_awareness,
--			sa_product_knowledge.kitabuse_learn_source,
--			sa_product_knowledge.gen_customer_location,
--			sa_product_knowledge.freedom_draw_awareness,
--			sa_product_knowledge.freedom_draw_learn_source,
--			sa_product_knowledge.artist,
--			sa_product_knowledge.artist_song,
--			sa_product_knowledge.gprs_awareness,
--			sa_product_knowledge.gprs_lead,
--			sa_product_knowledge.no_of_complaints,
--			sa_product_knowledge.mobile_money_interest,
--			sa_product_knowledge.crbt_awareness,
--			sa_product_knowledge.cmb_awareness,
--			sa_product_knowledge.kawa_awareness,
--			sa_product_knowledge.pakalast_awareness,
--			sa_product_knowledge.pakachini_awareness,
--			sa_product_knowledge.crbt_lead,
--			sa_product_knowledge.pakalast_lead,
--			sa_product_knowledge.cmb_lead,
--			sa_product_knowledge.kawa_lead,
--			sa_product_knowledge.pakachini_lead,
--			sa_product_knowledge.reason_for_non_recharge
		FROM
			sa_product_knowledge
			Inner Join users ON sa_product_knowledge.assigned_user_id = users.id
		WHERE 
			sa_product_knowledge.call_status IN ('Answered','Busy') AND 
			users.status = 'active' AND 
			sa_product_knowledge.assigned_user_id != '' AND 
			sa_product_knowledge.assigned_user_id != '1' AND
	"; 
		
	if($from){
		$query .= " sa_product_knowledge.date_modified >= date_sub('".$from." 00:00:00', interval 3 hour) AND ";
	}else{
		$query .= " sa_product_knowledge.date_modified >= date_sub('".date('Y-m-d')." 00:00:00', interval 3 hour) AND ";
	}
	if($to){
		$query .= " sa_product_knowledge.date_modified <= date_sub('".$to." 23:59:59', interval 3 hour) ";
	}else{
		$query .= " sa_product_knowledge.date_modified <= date_sub('".date('Y-m-d')." 23:59:59', interval 3 hour) ";
	}
	
	$query .= "
		ORDER BY sa_product_knowledge.date_modified ASC
	";
	
	//echo '<pre>'.$query."<hr>"; exit();
	
	custom_query::select_db('survey');
	
	function summarise($entries){

		foreach($entries as $row){
			++$data['Number of calls by Month'][substr($row[call_date],0,7)];
			++$data['Number of calls by status'][$row[call_status]];
			
			++$data['Number of calls by Month by Status'][substr($row[call_date],0,7)." >>> ".$row[call_status]];
			++$data['Number of calls by Date by Status'][$row[call_date]." >>> ".$row[call_status]];
			//++$data['Number of calls by Team by Status'][$row[team]." >>> ".$row[call_status]];
			
			if($row[call_status] == 'Answered'){

				/*
				if($row[kawa_awareness] == ''){
					$row[kawa_awareness]  = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Kawa >>> ".$row[kawa_awareness]];
				
				if($row[pakalast_awareness] == ''){
					$row[pakalast_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Pakalast >>> ".$row[pakalast_awareness]];
				
				if($row[crbt_awareness] == ''){
					$row[crbt_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["SwingtoMabeat >>> ".$row[crbt_awareness]];
				
				if($row[cmb_awareness] == ''){
					$row[cmb_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["CMB >>> ".$row[cmb_awareness]];
				
				if($row[pakachini_awareness] == ''){
					$row[pakachini_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Pakachini >>> ".$row[pakachini_awareness]];
				
				if($row[gprs_awareness] == ''){
					$row[gprs_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["GPRS >>> ".$row[gprs_awareness]];
				
				if($row[crbt_lead] != '' && $row[crbt_lead] == 'Yes'){
					++$data['Number of Leads by Product'][SwingtoMabeat];
				}
				if($row[pakalast_lead] != '' && $row[pakalast_lead] == 'Yes'){
					++$data['Number of Leads by Product'][Pakalast];
				}
				if($row[cmb_lead] != '' && $row[cmb_lead] == 'Yes'){
					++$data['Number of Leads by Product'][CMB];
				}
				if($row[kawa_lead] != '' && $row[kawa_lead] == 'Yes'){
					++$data['Number of Leads by Product'][KAWA];
				}
				if($row[pakachini_lead] != '' && $row[pakachini_lead] == 'Yes'){
					++$data['Number of Leads by Product'][Pakachini];
				}
				if($row[gprs_lead] != '' && $row[gprs_lead] == 'Yes'){
					++$data['Number of Leads by Product'][GPRS];
				}
			
				if(trim($row[dtf_awareness]) == ''){
					$row[dtf_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Double the fun >>> ".$row[dtf_awareness]];
				
				if(trim($row[dtf_learn_source]) == ''){
					$row[dtf_learn_source] = 'NOT FILLED';
				}
				++$data['Number of Customers by Product by Information Source']["Double the fun >>> ".$row[dtf_learn_source]];
				
				if(trim($row[kitabuse_awareness]) == ''){
					$row[kitabuse_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Kitabuse >>> ".$row[kitabuse_awareness]];
				
				if(trim($row[kitabuse_learn_source]) == ''){
					$row[kitabuse_learn_source] = 'NOT FILLED';
				}
				++$data['Number of Customers by Product by Information Source']["Kitabuse >>> ".$row[kitabuse_learn_source]];
				
				if(trim($row[freedom_draw_awareness]) == ''){
					$row[freedom_draw_awareness] = 'NOT FILLED';
				}
				++$data['Number of Awareness responses by Product by response']["Freedom Draw >>> ".$row[freedom_draw_awareness]];
				
				if(trim($row[freedom_draw_learn_source]) == ''){
					$row[freedom_draw_learn_source] = 'NOT FILLED';
				}
				++$data['Number of Customers by Product by Information Source']["Freedom Draw >>> ".$row[freedom_draw_learn_source]];
				
				
				if($row[reason_for_non_recharge] == ''){
					$row[reason_for_non_recharge] = 'NOT FILLED';
				}
				++$data['Reasons for not recharging by Reason'][$row[reason_for_non_recharge]];
				*/
				
				if(trim($row[favourite_business_centre]) == ''){
					$row[favourite_business_centre] = 'NOT FILLED';
				}
				++$data['Number of responses by Favourite Business Centre'][$row[favourite_business_centre]];
				
				if(trim($row[fav_customer_care_access]) == ''){
					$row[fav_customer_care_access] = 'NOT FILLED';
				}
				++$data['Number of Subs by Favourite CC access channel'][$row[fav_customer_care_access]];
				
				if(trim($row[wtu_promo_participation]) == ''){
					$row[wtu_promo_participation] = 'NOT FILLED';
				}
				++$data['Number of Promo Participation responses by Answer'][$row[wtu_promo_participation]];
				
				if(trim($row[defrauded_before]) == ''){
					$row[defrauded_before] = 'NOT FILLED';
				}
				++$data['Number of Defrauded responses by Answer'][$row[defrauded_before]];
				
				if(trim($row[mostliked_wtu_aspect]) == ''){
					$row[mostliked_wtu_aspect] = 'NOT FILLED';
				}
				++$data['Number of Most liked Warid Aspects by Answer'][$row[mostliked_wtu_aspect]];
				
				if(trim($row[warid_improve]) == ''){
					$row[warid_improve] = 'NOT FILLED';
				}
				++$data['Number of Improvement areas by Responses'][$row[warid_improve]];
				/*
				$row[warid_improve] = str_replace('^','',$row[warid_improve]);
				$area_array = explode(',',$row[warid_improve]);
				foreach($area_array as $area){
					if($area != ''){
						++$data['Number of responses by Area to improve'][$area];
					}
				}
				*/
				switch($row[warid_improve]){
					case 'Network':
						if($row[district]==''){ $row[district] = 'NOT FILLED'; }
						if($row[town]==''){ $row[town] = 'NOT FILLED'; }
						if($row[network_improve_landmark]==''){ $row[network_improve_landmark] = 'NOT FILLED'; }
						if($row[network_subject_issue]==''){ $row[network_subject_issue] = 'NOT FILLED'; }
						
						++$data['Network Areas of improvement by Issue by District by Town by Landmark'][$row[network_subject_issue]." >>> ".$row[district]." >>> ".$row[town]." >>> ".$row[network_improve_landmark]];
						break;
					case 'Customer Care';
						if($row[help_medium] == ''){ $row[help_medium] = 'NOT FILLED'; }
						if($row[reason_for_using_medium] == ''){ $row[reason_for_using_medium] = 'NOT FILLED'; }
						++$data['Customer Care Areas of improvement by Used medium by Medium issue'][$row[help_medium]." >>> ".$row[reason_for_using_medium]];
						break;
					case 'Distribution';
						if($row[improve_distribution_products] == ''){ $row[improve_distribution_products] = 'NOT FILLED'; }
						
						$row[improve_distribution_products] = str_replace('^','',$row[improve_distribution_products]);
						$product_array = explode(',',$row[improve_distribution_products]);
						foreach($product_array as $improve_distribution_product){
							if($improve_distribution_product != ''){
								++$data['Product Distribution improvement responses by Prodcut'][$row[improve_distribution_product]];
							}
						}
						break;
				}
			}
			++$data['Number of calls by Student by Status'][$row[full_name]." >>> ".$row[call_status]];

		}
	
		return $data;
	}
	
	function getleadData($from,$to){
		
		/*$myquery = new custom_query();
		$query1 = '
			SELECT
				sa_product_knowledge.name as msisdn,
				sa_product_knowledge.crbt_lead,
				sa_product_knowledge.pakalast_lead,
				sa_product_knowledge.cmb_lead,
				sa_product_knowledge.kawa_lead,
				sa_product_knowledge.pakachini_lead,
				sa_product_knowledge.gprs_lead
			FROM
				sa_product_knowledge
				Inner Join users ON sa_product_knowledge.assigned_user_id = users.id
			WHERE 
				sa_product_knowledge.call_status IN ("Answered","Busy") AND 
				users.status = "active" AND 
				sa_product_knowledge.assigned_user_id != "" AND 
				sa_product_knowledge.assigned_user_id != 1 AND
		';
	
		if($from){
			$query1 .= " sa_product_knowledge.date_modified >= DATE_SUB('".$from." 00:00:00', INTERVAL 3 HOUR) AND ";
		}else{
			$query1 .= " sa_product_knowledge.date_modified >= DATE_SUB('".date('Y-m-d')." 00:00:00', INTERVAL 3 HOUR) ";
		}
		if($to){
			$query1 .= " sa_product_knowledge.date_modified <= DATE_SUB('".$to." 23:59:59', INTERVAL 3 HOUR) ";
		}else{
			$query1 .= " sa_product_knowledge.date_modified <= DATE_SUB('".date('Y-m-d')." 23:59:59', INTERVAL 3 HOUR) ";
  		}
		
		//echo $query1;
		custom_query::select_db('survey');
		$entries = $myquery->multiple($query1);
		$c=0;$pl=0;$pc=0;$k=0;$cmb=0;
		
		foreach($entries as $row)
		{
			if($row[crbt_lead] != '' && $row[crbt_lead] == 'Yes'){  $LeadData[crbt][$c++] = $row[msisdn]; }
			if($row[pakalast_lead] != '' && $row[pakalast_lead] == 'Yes'){ $LeadData[pakalast][$pl++] = $row[msisdn]; }
			if($row[pakachini_lead] != '' && $row[pakachini_lead] == 'Yes'){ $LeadData[pakachini][$pc++] = $row[msisdn]; }
			if($row[kawa_lead] != '' && $row[kawa_lead] == 'Yes'){ $LeadData[kawa][$k++] = $row[msisdn]; }
			if($row[cmb_lead] != '' && $row[cmb_lead] == 'Yes'){ $LeadData[cmb][$cmb++] = $row[msisdn]; }
			if($row[gprs_lead] != '' && $row[gprs_lead] == 'Yes'){ $LeadData[gprs][$gprs++] = $row[msisdn]; }
		}

		return $LeadData;
		*/
		
		return 'FORM DOES NOT CAPURTRE LEADS';
	}

	switch($reporttype){
		case 'leads':
			$report[leads] = getleadData($from,$to);
			break;
		case 'detail':
			$report[detail] = $myquery->multiple($query);
			break;
		case 'std_info':
			$report[std_info] = generate_student_bio($from,$to);
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($myquery->multiple($query));
			break;
	}

	return display_cust_knowledge($report);}
	
function generate_student_bio($from,$to){
	$myquery = new custom_query();
	$query = "
		SELECT
			reg_registration.id,
			reg_registration.name,
			reg_registration.date_entered,
			reg_registration.date_modified,
			reg_registration.modified_user_id,
			reg_registration.created_by,
			reg_registration.description,
			reg_registration.deleted,
			reg_registration.assigned_user_id,
			reg_registration.fname,
			reg_registration.lname,
			reg_registration.dob,
			reg_registration.other_instituion,
			reg_registration.current_educ_leve,
			reg_registration.university,
			reg_registration.course,
			reg_registration.residence,
			reg_registration.preferred_phone_number,
			reg_registration.alt_phone_no,
			reg_registration.email
		FROM
			reg_registration
		WHERE
			reg_registration.assigned_user_id !=  '1' AND
			reg_registration.deleted !=  '1' AND
	";	
	if($from){
		$query .= " reg_registration.date_modified >= date_sub('".$from." 00:00:00', interval 3 hour) AND ";
	}else{
		$query .= " reg_registration.date_modified >= date_sub('".date('Y-m-d')." 00:00:00', interval 3 hour) AND ";
	}
	if($to){
		$query .= " reg_registration.date_modified <= date_sub('".$to." 23:59:59', interval 3 hour) ";
	}else{
		$query .= " reg_registration.date_modified <= date_sub('".date('Y-m-d')." 23:59:59', interval 3 hour) ";
	}	
	$std_list = $myquery->multiple($query, 'survey');
	return $std_list;}
 
function display_cust_knowledge($report){
	//print_r($report[heading]);
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="70%">
	';
	if(count($report[summary]) > 0 ){
		
		foreach($report[summary] as $title=>$title_data){
			$columns = explode(' by ',$title);
			array_shift($columns);
			$html .= '
			<tr>
				<th>'.$title.'</th>
			</tr>
			<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
				<!--HEADINGS-->
				<tr>
			';
			
			foreach($columns as $column){
				$html .= '
					<th>'.$column.'</th>
				';
			}
			
			$html .= '
					<th>Number</th>
				</tr>
			';
			
			//BEGINNING OF ROWS
			foreach($title_data as $parameter_str_row=>$row_value){
				$row_parameters = explode(" >>> ",$parameter_str_row);
				$html .= '
					<tr>
				';
				
				foreach($row_parameters as $parameter){
					$html .= '
						<td class="text_values">'.$parameter.'</td>
					';
				}
				
				$html .= '
						<td class="values">'.number_format($row_value).'</td>
					</tr>
				';
			}
			
			$html .= '
			</table>
			</td>
			</tr>
			';
		}
	}
	
	if(count($report[summary]) > 0 and count($report[detail]) > 0){
		$html .= '
			<tr><td height="20px">_</td></tr>
		';
	}
		
	if(count($report[detail]) > 0){
		$html.= '
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="sortable">
				<tr>
					<th>#</th>
					<th>MSISDN</th>
					<th>CUSTOMER NAME</th>
					<th>CALL STATUS</th>
					<th>CALL DATE</th>
					<th>USER</th>
					<th>AREA TO IMPROVE</th>
					<th>DISTRICT</th>
					<th>TOWN</th>
					<th>LANDMARK</th>
					<th>NETWORK ISSUE</th>
					
					<th>HELP MEDIUM</th>
					<th>WHY MEDIUM NEEDS IMPROVEMENT</th>
					
					<th>FAVOURITE BUSINESS CENTRE</th>
					<th>PREFFERED CUSTOMER CARE ACCESS</th>
					<th>WTU PROMO PARTICIPATION</th>
					<th>PREVIOUSLY DEFRAUDED?</th>
					<th>MOST LIKED WTU ASPECT</th>
					<!--
					<th>CRBT AWARENESS</th>
					<th>CRBT LEAD</th>
					<th>CMB AWARENESS</th>
					<th>CMB LEAD</th>
					<th>GPRS AWARENESS</th>
					<th>GPRS LEAD</th><strong></strong>
					<th>PAKALAST AWARENESS</th>
					<th>PAKALAST LEAD</th>
					<th>PAKACHINI AWARENESS</th>
					<th>PAKACHINI LEAD</th>
					<th>KAWA AWARENESS</th>
					<th>KAWA LEAD</th>
					<th>ORIGINAL COMPLAINT</th>
					-->
					<!--
					<th>INTEREST IN MOBILE MONEY</th>
					<th>ARTIST</th>
					<th>SONG</th>
					<th>DTF AWARENESS</th>
					-->
					<!--
					<th>KITABUSE AWARENESS</th>
					<th>KITABUSE SOURCE</th>
					<th>FREEDOM DRAW AWARENESS</th>
					<th>FREEDOM DRAW AWARENESS SOURCE</th>
					<th>GEN CUSTOMER LOCATION</th>
					-->
				</tr>';
		$YY = 0;
		foreach($report[detail] as $row){
			if(
			   	$row[call_status] == 'Answered' and 
				(
				 /*trim($row[dtf_awareness]) == '' and
				 trim($row[favourite_business_centre]) == '' and
				 trim($row[fav_customer_care_access]) == '' and
				 trim($row[wtu_promo_participation]) == '' and
				 trim($row[kitabuse_awareness]) == '' and
				 trim($row[kitabuse_learn_source]) == '' and
				 trim($row[gen_customer_location]) == '' and
				 trim($row[freedom_draw_awareness]) == '' and
				 trim($row[freedom_draw_learn_source]) == ''
				*/
				 
				 trim($row[favourite_business_centre]) == '' and
				 trim($row[fav_customer_care_access]) == '' and
				 trim($row[wtu_promo_participation]) == '' and
				 trim($row[mostliked_wtu_aspect]) == '' and
				 trim($row[warid_improve]) == ''
				)
			){
				//echo print_r($row,true)."<hr>";
				$tr_class = 'class="flagged"';
			}else{
				$tr_class = '';
			}
			
			$html.= '
				<tr '.$tr_class.'>
					<td class="values">'.++$YY.'</td>
					<td class="values"><a href="http://ccba02.waridtel.co.ug/survey/index.php?module=sa_product_knowledge&return_module=sa_product_knowledge&action=DetailView&record='.$row[id].'" target="_blank">'.$row[msisdn].'</a></td>
					<td class="text_values">'.$row[customer_name].'</td>
					<td class="text_values">'.$row[call_status].'</td>
					<td class="values">'.$row[call_date].'</td>
					<td class="text_values">'.$row[full_name].'</td>
					<td class="text_values">'.$row[warid_improve].'</td>
					<td class="text_values">'.$row[district].'</td>
					<td class="text_values">'.$row[town].'</td>
					<td class="text_values">'.$row[network_improve_landmark].'</td>
					<td class="text_values">'.$row[network_subject_issue].'</td>
					
					<td class="text_values">'.$row[help_medium].'</td>
					<td class="text_values">'.$row[reason_for_using_medium].'</td>
					
					<td class="text_values">'.$row[favourite_business_centre].'</td>
					<td class="text_values">'.$row[fav_customer_care_access].'</td>
					<td class="text_values">'.$row[wtu_promo_participation].'</td>
					<td class="text_values">'.$row[defrauded_before].'</td>
					<td class="text_values">'.$row[mostliked_wtu_aspect].'</td>
					<!--
					<td class="text_values">'.$row[mobile_money_interest].'</td>
					<td class="text_values">'.$row[artist].'</td>
					<td class="text_values">'.$row[artist_song].'</td>
					-->
					<!--
					<td class="text_values">'.$row[crbt_awareness].'</td>
					<td class="text_values">'.$row[crbt_lead].'</td>
					<td class="text_values">'.$row[cmb_awareness].'</td>
					<td class="text_values">'.$row[cmb_lead].'</td>
					<td class="text_values">'.$row[gprs_awareness].'</td>
					<td class="text_values">'.$row[gprs_lead].'</td>
					<td class="text_values">'.$row[pakalast_awareness].'</td>
					<td class="text_values">'.$row[pakalast_lead].'</td>
					<td class="text_values">'.$row[pakachini_awareness].'</td>
					<td class="text_values">'.$row[pakachini_lead].'</td>
					<td class="text_values">'.$row[kawa_awareness].'</td>
					<td class="text_values">'.$row[kawa_lead].'</td>
					<td class="text_values">'.$row[original_complaint].'</td>
					<td class="text_values">'.$row[dtf_awareness].'</td>
					-->
					<!--
					<td class="text_values">'.$row[kitabuse_awareness].'</td>
					<td class="text_values">'.$row[kitabuse_learn_source].'</td>
					<td class="text_values">'.$row[gen_customer_location].'</td>
					<td class="text_values">'.$row[freedom_draw_awareness].'</td>
					<td class="text_values">'.$row[freedom_draw_learn_source].'</td>
					-->
				</tr>';
		}
		$YY = 0;
		
		$html.= '
			</table>
		';
	}
	
	if(count($report[std_info]) > 0){
		$html.= '
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<th>NAME</th>
					<th>MODIFICATION DATE</th>
					<th>DATE OF BIRTH</th>
					<th>OTHER INSTITUTION</th>
					<th>CURRENT EDUCATION LEVEL</th>
					<th>UNIVERSITY</th>
					<th>COURSE</th>
					<th>RESIDENCE</th>
					<th>PREFERRED PHONE NUMBER</th>
					<th>ALTERNATIVE PHONE NUMBER</th>
					<th>EMAIL</th>
				</tr>';
		foreach($report[std_info] as $row_key){
			$html.='
				<tr>
					<td class="text_values">'.ucwords(strtolower($row_key['name'])).'</td>
					<td class="text_values">'.$row_key['date_modified'].'</td>
					<td class="text_values">'.$row_key['dob'].'</td>
					<td class="text_values">'.ucwords(strtolower($row_key['other_instituion'])).'</td>
					<td class="text_values">'.ucwords(strtolower($row_key['current_educ_leve'])).'</td>
					<td class="text_values">'.ucwords(strtolower($row_key['university'])).'</td>
					<td class="text_values">'.$row_key['course'].'</td>
					<td class="text_values">'.ucwords(strtolower($row_key['residence'])).'</td>
					<td class="values">'.$row_key['preferred_phone_number'].'</td>
					<td class="values">'.$row_key['alt_phone_no'].'</td>
					<td class="text_values">'.$row_key['email'].'</td>
				</tr>
			';		
		}
		$html.='</table>';				
	}
	
	return $html;}

?>