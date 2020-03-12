<?php

function generate_sales_lead_report($from,$to,$queues,$status,$platform){
	
	if(!$from) {$from = date("Y-m-")."01"; }
	if(!$to) {$to = date("Y-m-d", strtotime("-1 days")); }
	
	custom_query::select_db('wimax');
	$myquery = new custom_query();
	$lead_services = array();
	$bandwidth_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.download_bandwidth_c as bandwidth,
			ps_products.price as product_price,
			leads_cstm.cpe_type_c as cpe
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.download_bandwidth_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues.id=qs_queues_leads_c.qs_queues_lsqs_queues_ida)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND
			(leads_cstm.shared_packages_c = '' or leads_cstm.shared_packages_c != '')
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL) AND 
	";
	
	if($from){
		$period_condition .= " AND leads.date_entered >= date_add('".$from." 00:00:00', interval -3 hour) ";
	}else{
		$period_condition .= " AND leads.date_entered >= date_add('".$from." 00:00:00', interval -3 hour) ";
	}
	
	$_POST[from] = $from;
	
	if($to){
		$period_condition .= " AND leads.date_entered <= date_add('".$to." 23:59:59', interval -3 hour) ";
	}else{
		$period_condition .= " AND leads.date_entered <= date_add('".$to." 23:59:59', interval -3 hour) ";
	}
	$_POST[to] = $to;
	
	$bandwidth_query .= $period_condition;
	
	if($status){
		$bandwidth_query .= " AND leads.`status` = '".$status."' ";
	}
	
	if($platform){
		$bandwidth_query .= " AND leads_cstm.platform_c = '".$platform."' ";
	}
	
	if(count($queues) > 0){
		$bandwidth_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$bandwidth_query .= "qs_queues.id = '".$queue."'";
			}else{
				$bandwidth_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$bandwidth_query.= " OR ";
			}
		}
		$bandwidth_query .= ")";
	}

	$bandwidth = $myquery->multiple($bandwidth_query);

	foreach($bandwidth as $row){
	
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
	
		$lead_services[$row[id]][bandwidth_name] = $row[bandwidth];
		$lead_services[$row[id]][bandwidth_price] = $row[product_price];
		$lead_services[$row[id]][cpe_type] = $row[cpe];
	}
	
	$rental_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.shared_packages_c as rental_name,
			ps_products.price as product_price
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.shared_packages_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues.id=qs_queues_leads_c.qs_queues_lsqs_queues_ida)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND 
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL)
	";
	
	$rental_query .= $period_condition;
	
	if($status){
		$rental_query .= "
			AND
			leads.`status` = '".$status."'
		";
	}
	
	if($platform){
		$rental_query .= "
			AND
			leads_cstm.platform_c = '".$platform."'

		";
	}
	
	if(count($queues) > 0 ){
		$rental_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$rental_query .= "qs_queues.id = '".$queue."'";
			}else{
				$rental_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$rental_query .= " OR ";
			}
		}
		$rental_query .= ")";
	}
	
	$rental = $myquery->multiple($rental_query);

	//var_dump($rental); echo "<br><br>";

	foreach($rental as $row){
	
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
		
		$lead_services[$row[id]][rental_name] = $row[rental_name];
		$lead_services[$row[id]][rental_price] = $row[product_price];
	}
		
	$domain_hosting_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.package_type_domain_hosting_c as product_name,
			ps_products.price as product_price
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.package_type_domain_hosting_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER qs_queues ON (qs_queues_leads_c.qs_queues_lsqs_queues_ida=qs_queues.id)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND 
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL)
	";
	
	$domain_hosting_query .= $period_condition;
	
	if($status){
		$domain_hosting_query .= "
			AND
			leads.`status` = '".$status."'
		";
	}
	
	if($platform){
		$domain_hosting_query .= "
			AND
			leads_cstm.platform_c = '".$platform."'
		";
	}
	
	if(count($queues) > 0 ){
		$domain_hosting_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$domain_hosting_query .= "qs_queues.id = '".$queue."'";
			}else{
				$domain_hosting_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$domain_hosting_query .= " OR ";
			}
		}
		$domain_hosting_query .= ")";
	}
	
	$domain_hosting = $myquery->multiple($domain_hosting_query);
	//var_dump($domain_hosting); echo "<br><br>";
	
	foreach($domain_hosting as $row){
	
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
		
		$lead_services[$row[id]][domain_hosting_name] = $row[product_name];
		$lead_services[$row[id]][domain_hosting_price] = $row[product_price];
		
	}
	
	$domain_registration_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.package_domain_registration_c as product_name,
			ps_products.price as product_price
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.package_domain_registration_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues_leads_c.qs_queues_lsqs_queues_ida=qs_queues.id)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND 
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL)
	";
	
	$domain_registration_query .= $period_condition;
	
	if($status){
		$domain_registration_query .= "
			AND
			leads.`status` = '".$status."'
		";
	}
	
	if($platform){
		$domain_registration_query .= "
			AND
			leads_cstm.platform_c = '".$platform."'
		";
	}
	
	if(count($queues) > 0 ){
		$domain_registration_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$domain_registration_query .= "qs_queues.id = '".$queue."'";
			}else{
				$domain_registration_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$domain_registration_query .= " OR ";
			}
		}
		$domain_registration_query .= ")";
	}
	
	//echo $domain_registration_query.'<br>';
	
	$domain_registration = $myquery->multiple($domain_registration_query);
	//var_dump($domain_registration); echo "<br><br>";
	
	foreach($domain_registration as $row){
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
		
		$lead_services[$row[id]][domain_reg_name] = $row[product_name];
		$lead_services[$row[id]][domain_reg_price] = $row[product_price];
	}
	
	$mail_hosting_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.package_mail_hosting_c as product_name,
			ps_products.price as product_price
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.package_mail_hosting_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			LEFT OUTER JOIN qs_queues ON (qs_queues_leads_c.qs_queues_lsqs_queues_ida=qs_queues.id)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND 
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL)
	"; 
	
	$mail_hosting_query .= $period_condition;
	
	if($status){
		$mail_hosting_query .= "
			AND
			leads.`status` = '".$status."'
		";
	}
	
	if($platform){
		$mail_hosting_query .= "
			AND
			leads_cstm.platform_c = '".$platform."'
		";
	}
	
	if(count($queues) > 0 ){
		$mail_hosting_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$mail_hosting_query .= "qs_queues.id = '".$queue."'";
			}else{
				$mail_hosting_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$mail_hosting_query .= " OR ";
			}
		}
		$mail_hosting_query .= ")";
	}
	
	$mail_hosting = $myquery->multiple($mail_hosting_query);
	
	foreach($mail_hosting as $row){
	
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
		
		$lead_services[$row[id]][mail_hosting_name] = $row[product_name];
		$lead_services[$row[id]][mail_hosting_price] = $row[product_price];
	}
	
	$web_hosting_query = "
		SELECT 
			leads.id,
			leads.first_name,
			leads.last_name,
			leads_cstm.name_c as db_lead_name,
			qs_queues.name as queue,
			leads_cstm.industry_c,
			leads_cstm.customer_type_c,
			leads_cstm.platform_c,
			leads.`status`,
			leads.date_entered,
			leads.date_modified,
			leads.salutation,
			leads_cstm.sales_rep_c,
			leads_cstm.package_web_hosting_c as product_name,
			ps_products.price as product_price,
		FROM
			leads
			INNER JOIN leads_cstm ON (leads.id=leads_cstm.id_c)
			LEFT OUTER JOIN ps_products ON (ps_products.name=leads_cstm.package_web_hosting_c)
			LEFT OUTER JOIN qs_queues_leads_c ON (qs_queues_leads_c.qs_queues_leadsleads_idb=leads.id)
			INNER JOIN qs_queues ON (qs_queues_leads_c.qs_queues_lsqs_queues_ida=qs_queues.id)
		WHERE
			leads.deleted = '0' AND 
			(ps_products.deleted != '1' OR ps_products.deleted IS NULL) AND 
			(qs_queues_leads_c.deleted = '0' OR qs_queues_leads_c.deleted IS NULL)
	";
	
	$web_hosting_query .= $period_condition;
	
	if($status){
		$web_hosting_query .= "
			AND
			leads.`status` = '".$status."'
		";
	}
	
	if($platform){
		$web_hosting_query .= "
			AND
			leads_cstm.platform_c = '".$platform."'
		";
	}
	
	if(count($queues) > 0 ){
		$web_hosting_query .= "AND (";
		foreach($queues as $count=>$queue){
			if($queue != ''){
				$web_hosting_query .= "qs_queues.id = '".$queue."'";
			}else{
				$web_hosting_query .= "qs_queues.id LIKE '%%'";
			}
			if(count($queues) > $count+1){
				$web_hosting_query .= " OR ";
			}
		}
		$web_hosting_query .= ")";
	}

	$web_hosting = $myquery->multiple($web_hosting_query);
	
	foreach($web_hosting as $row){
		$lead_services[$row[id]][first_name] = $row[first_name];
		$lead_services[$row[id]][last_name] = $row[last_name];
		$lead_services[$row[id]][lead_name] = $row[db_lead_name];
		$lead_services[$row[id]][sales_rep] = $row[sales_rep_c];
		$lead_services[$row[id]][platform] = $row[platform_c];
		$lead_services[$row[id]][customertype] = $row[customer_type_c];
		$lead_services[$row[id]][industrytype] = $row[industry_c];
		$lead_services[$row[id]][queue] = $row[queue];
		$lead_services[$row[id]][lead_status] = $row[status];
		$lead_services[$row[id]][created_on] = $row[date_entered];
		$lead_services[$row[id]][modified_on] = $row[date_modified];
		$lead_services[$row[id]][salutation] = $row[salutation];
		//$accnt_services[$parent_id][$crn_c][customertype] = '';
		
		$lead_services[$row[id]][web_hosting_name] = $row[product_name];
		$lead_services[$row[id]][web_hosting_price] = $row[product_price];
	}

	return  display_sales_lead_report($lead_services);
}
	
function display_sales_lead_report($report){

	$html = '<table border="0" cellpadding="2" cellspacing="0" class="sortable">
			<tr>
			<th></th>
			<th>Lead Name</th>
			<th>Lead Contact</th>
			<th>Bandwidth Allocated</th>
			<th>Platform</th>
			<th>Monthly Subscription</th>
			<th>Lead Status</th>
			<th>Date Created</th>
			<th>Date Modified</th>
			<th>Sales Rep</th>
			<th>Mail hosting</th>
			<th>Domain Hosting</th>
			<th>Web hosting</th>
			<th>Domain Registration</th>
			<th>Industry Type</th>
			<th>Equipment Type</th>
			<th>Equipment Rental</th>
			<th>Current Queue</th>
			</tr>
	';
	foreach($report as $id=>$row){
			//print_r($row); echo '<br><br>';
			$html .= '
				<tr>
					<td class="values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Leads&action=DetailView&record='.$id.'" target="_blank">'.++$i.'</a></td>
					<td class="text_values">'.$row[lead_name].'</td>
					<td class="text_values">'.$row[first_name].' '.$row[last_name].'</td>
					<td class="text_values">'.$row[bandwidth_name].'</td>
					<td class="text_values">'.$row[platform].'</td>
					<td class="values">'.accounts_format($row[bandwidth_price]).'</td>
					<td class="text_values">'.$row[lead_status].'</td>
					<td class="text_values">'.date_reformat($row[created_on],'').'</td>
					<td class="text_values">'.date_reformat($row[modified_on],'').'</td>
					<td class="text_values">'.$row[sales_rep].'</td>
					<td class="text_values">'.$row[mail_hosting_name].'</td>
					<td class="text_values">'.$row[domain_hosting_name].'</td>
					<td class="text_values">'.$row[web_hosting_name].'</td>
					<td class="text_values">'.$row[domain_reg_name].'</td>
					<td class="text_values">'.$row[industrytype].'</td>
					<td class="text_values">'.$row[cpe_type].'</td>
					<td class="text_values">'.$row[rental_name].'</td>
					<td class="text_values">'.$row[queue].'</td>
				</tr>
			'; 
		
	}
	return $html;
}
?>