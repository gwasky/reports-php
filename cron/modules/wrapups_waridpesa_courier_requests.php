<?php
function get_pick_up_request_wrapups(){
	$myquery = new custom_query();
	custom_query::select_db('reportscrm');
	
	if(date('H') < 12){
		//morning midnight to Noon
		$from = date('Y-m-d')." 00:00:00";
	}else{
		//froom afternoon to midnight
		$from = date('Y-m-d')." 11:59:00";
	}
	
	$to = date('Y-m-d H:i:s');
	
	$wrapup_query = "
		SELECT
			reportsphonecalls.createdon,
			reportsphonecalls.createdby as agent,
			reportsphonecalls.phonenumber as number,
			reportsphonecalls.description AS db_description,
			TRIM(SUBSTRING(
				LEFT(reportsphonecalls.description,LOCATE('~',reportsphonecalls.description)-1),
				LENGTH('Retailer Physical location >> ')
			)) AS physical_location,
			TRIM(SUBSTRING_INDEX(reportsphonecalls.description, 'Description >> ', -1)) as cleaned_description,
			reportsphonecalls.customername
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.subject = 'Distribution : Operation : Send me a courier to pick up forms' AND
			reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
		GROUP BY
			number
	";
	
	//echo $wrapup_query."\n";
	
	$wrapups = $myquery->multiple($wrapup_query);
	
	if(count($wrapups) == 0){ return "NONE"; }

	return $wrapups;
}

function display_pick_up_request_wrapups($data){
	if($data == "NONE"){ return "There are no requests for couriers to pick up forms";  }
	
	$html = '
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="sortable">
			<tr>
				<th width="5">#</th>
				<th>DATE TIME</th>
				<th>RETAILER MSISDN</th>
				<th>RETAILER NAME</th>
				<th>LOGGED BY (CCA)</th>
				<th>PHYSICAL LOCATION</th>
				<th width="60%">DESCRIPTION</th>
			</tr>
	';
	
	foreach($data as $row){
		$html .= '
			<tr>
				<td width="5" class="values">'.++$ii.'</td>
				<td class="text_values">'.$row[createdon].'</td>
				<td class="values">'.$row[number].'</td>
				<td class="text_values">'.ucwords(strtolower($row[customername])).'</td>
				<td class="text_values">'.$row[agent].'</td>
				<td class="text_values">'.$row[physical_location].'</td>
				<td class="wrap_text">'.$row[cleaned_description].'</td>
			</tr>
		';
	}
	
	$html .= '
		</table>
	';
	
	return $html;
}
?>