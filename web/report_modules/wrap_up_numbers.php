<?php

function generate_msisdns($from,$to,$reporttype,$cat,$subject){

	$myquery = new custom_query();
	$query = "
				SELECT
					left(reportsphonecalls.createdon,10) as createdon,
					reportsphonecalls.phonenumber,
					reportsphonecalls.wrapupsubcat,
					reportsphonecalls.subject
					FROM
					reportsphonecalls
					WHERE
					reportsphonecalls.wrapupsubcat = '$cat'";
				
				if($subject){
					$query .= " AND reportsphonecalls.subject = '".$subject."' ";
				}
				
				if($from){
					$query .= " AND createdon >= '".$from." 00:00:00' ";
				}else{
					$_POST[from] = date('Y-m-d',strtotime("-1 days"));
					$from = $_POST[from];
					$query .= " AND createdon >= '".$from." 00:00:00' ";
				}
				if($to){
					$query .= " AND createdon <= '".$to." 23:59:59' ";
				}else{
					$_POST[to] = date('Y-m-d',strtotime("-1 days"));
					$to = $_POST[to];
					$query .= " AND createdon <= '".$to." 23:59:59' ";
				}
				
				//echo $query;
				
			//$data = $myquery->multiple($query);
			
			function summary($rows)
			{
				foreach($rows as $row)
				{
						++$data[summary][$row[subject]];	
				}
				return $data[summary];
			}
			
		switch($reporttype){
			case 'raw':
				$data[raw] = $myquery->multiple($query,'ccba02.reportscrm');
				break;
			case'Summary':
				$data[summaries] = summary($myquery->multiple($query,'ccba02.reportscrm'));
				break;
		} 
		
		return display_msisdns($data,$reporttype);

}

	function display_msisdns($report,$reporttype){
	if(!$reporttype){
	
	$html .= '
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr>
		</br>
		</br>
		</br>
		</br>
		</br>
		</br>
		<tr>
		<td colspan="5">Please Select The Report Type</td>
		</tr>
		</table>';
		
	}
	if(count($report[raw]) > 0)
	{
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="50%">
		<tr>
		<th>No</th><th>DATE CREATED</th><th>MSISDN</th><th>SubCategory</th><th>Subject</th>
		</tr>';
		$i=0;
		foreach($report[raw] as $row){
				$html .= '<tr>
								<td class="values">'.++$i.'</td>
								<td class="values">'.$row[createdon].'</td>
								<td class="values">'.$row[phonenumber].'</td>
								<td class="values">'.$row[wrapupsubcat].'</td>
								<td class="text_values">'.$row[subject].'</td>
							</tr>';
		}
		}
		if(count($report[summaries]) > 0){
		$i=0;
			arsort($report[summaries]);
				$html .= '
					<table border="0" cellpadding="2" cellspacing="0" width="50%">
					<tr>
						<th>No</th>
						<th>Subject</th>
						<th>Count</th>
					</tr>';
				foreach($report[summaries] as $key=>$value)
				{
					++$i;
					$html .= '<tr>
								<td class="text_values">'.$i.'</td>
								<td class="text_values">'.$key.'</td>
								<td class="values">'.number_format($value).'</td>
							</tr>';
					$total += $value;
				}
				$html .= '<tr>
								<td class="values"></td>
								<td class="values">Total</td>
								<td class="values">'.number_format($total).'</td>
							</tr>';
		}
		
		return $html;
	}
?>