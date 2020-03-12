<?php


	function generate_outage_service_list($from,$to)
	{
			$myquery = new custom_query();
			$query ="
			SELECT
				outag_service_outage.name as service,
				outag_service_outage.status_of_outtage as status,
				outag_service_outage.area as area,
				outag_service_outage.expected_resolution_time as resolution_time,
				outag_service_outage.date_entered as date_created
				FROM
				outag_service_outage where";
				
			if($from){
			$query .= " date(date_add(outag_service_outage.date_entered, interval 3 hour)) >= '".$from." 00:00:00' AND ";
		}else{
			$query .= " date(date_add(outag_service_outage.date_entered, interval 3 hour)) >= '".date('Y-m-d')." 00:00:00'";
		}
		if($to){
			$query .= " date(date_add(outag_service_outage.date_entered, interval 3 hour)) <= '".$to." 00:00:00'";
		}else{
			$query .= " date(date_add(outag_service_outage.date_entered, interval 3 hour)) <= '".date('Y-m-d')." 00:00:00'";
		}
		
			custom_query::select_db('wimax');
			$entries = $myquery->multiple($query);
			return display_outtage_report($entries);
			//foreach($entries as $row){}
			
	}

	function display_outtage_report($report)
	{
		
		$html .= '
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<table border="0" cellpadding="2" cellspacing="0" width="500px">
				<tr>
					<th>Service</th>
					<th>Status</th>
					<th>Area</th>
					<th>Date Created</th>
					<th>Expected Resolution Time</th>
				</tr>';
		foreach($report as $row)
		{
			$html .= 
				'<tr>
					<td class="text_values">'.$row[service].'</td>
					<td class="text_values">'.$row[status].'</td>
					<td class="text_values">'.$row[area].'</td>
					<td class="text_values">'.$row[date_created].'</td>
					<td class="text_values">'.$row[resolution_time].'</td>
				</tr>';
					
		}
		
		return $html;
	}

?>