<?
function generate_microwave_off_accounts(){

	custom_query::select_db('wimax');
	
	$myquerys = new custom_query();
	
	$myssh = new SSH();
	
	
	$myftp = new FTP($ftproot='', $host='41.221.86.21', $username='wimaxcrm', $password='wak0k0', $debug=TRUE, $docroot='', $passive_mode = FALSE);
	
	$result = $myftp->f_get_and_open('mw_clients_down.csv');
	
	if($result == false){ 
		return "CAN NOT RETRIEVE FILE FROM NAGIOS";
	}
	
	/*
	phpinfo();
	
	$myssh->login($user='wimaxcrm', $pass='wak0k0',  $host='41.221.86.21', $port=22);
	
	echo $myssh->error."<hr>";

	exit();
	*/

	$now_timestamp = date('Y-m-d H:i:s');
	
	foreach($myftp->open_file_data as $key=>$row){
		//GET TIME THE FILE WAS GENERATED
		
		if(strtolower(substr($row,0,14)) == 'date modified:') {
			$report[FILE_GENERATION] = trim(substr($row,15,strlen($row)));
			
			list($date,$time) = explode(' ',$report[FILE_GENERATION]);
			list($day,$month,$year) = explode('-',$date);
			$report[FILE_GENERATION] = $year.'-'.$month.'-'.$day.' '.$time;
			
			$query = "
				SELECT DATE_ADD('".$report[FILE_GENERATION]."', INTERVAL 30 MINUTE) AS new_time
			";
			
			//echo $query."<hr>";
			
			$result = $myquerys->single($query);
			$report[FILE_GENERATION] = $result[new_time];
		}
		$row = str_replace(array('"'),'',$row);
		$split_line = explode(',',$row);
		
		if(count($split_line) == 2 and $split_line[0] != "Host Name"){
			
			$client_key =  str_replace(array('-client-site','-warid-site','-Client-Site','-Warid-Site'),'',strtolower($split_line[0]));
			//$client_key =  str_replace(array('-client-site','-warid-site','-Client-Site','-Warid-Site'),'',$split_line[0]);
			$lean_nagios_key = strtolower(preg_replace("/[^a-zA-Z0-9]+/","", $client_key));
			
			$report[rows][$lean_nagios_key][down_time] = strtotime($now_timestamp) - strtotime($split_line[1]);
			$report[rows][$lean_nagios_key][nagios_key] = str_replace(array('-client-site','-warid-site'),'',strtolower($split_line[0]));
			
			if(count($report[rows]) > 1 ){
				$microwave_username_in_list .= ",";
			}
			
			//echo $lean_nagios_key." ==> ";
			
			$microwave_username_in_list .= "'".$client_key."'";
		}
	}
	
	$myftp->cleanup();
	
	unset($myftp->open_file_data);
	
	arsort($report[rows]);
	
	//echo "No of Nagios returned is ".count($report[rows])."<hr>";

	$query = "
		SELECT
			accounts.id,
			accounts_cstm.crn_c as account_no,
			accounts.name,
			accounts_cstm.preferred_username_c as usercrm,
			cn_contracts.`status`, 
			accounts_cstm.download_bandwidth_c as bandwidth,
			accounts_cstm.technical_contact_person_c as tech_person,
			accounts_cstm.technical_contact_phone_c as tech_phone,
			accounts_cstm.contact_person_c as contact_person,
			accounts_cstm.contact_person_phone_c as contact_phone
		FROM
			accounts
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c) 
		WHERE 
			accounts.deleted=0 AND 
			cn_contracts.deleted=0 AND
			accounts_cstm.download_bandwidth_c != 'No Bandwidth' AND
			accounts_cstm.platform_c = 'Microwave' AND
			LOWER(accounts_cstm.preferred_username_c) NOT IN ('','na')
	";
	
	//echo nl2br($query)."<hr>";
	
	$crm_list = $myquerys->multiple($query,'wimax');
	
	//echo "No of CRM returns is ".count($crm_list)."<hr>";
	
	foreach($crm_list as $key=>$row){
		$lean_crm_key = preg_replace("/[^a-zA-Z0-9]+/","",strtolower($row[usercrm]));
		$crm_list[$lean_crm_key] = $row;
		
		//echo $lean_crm_key." ==> ";
		
		unset($crm_list[$key],$key,$row);
	}
	
	foreach($report[rows] as $key=>$row){
		
		$crm_list[$key][down_time] = $row[down_time];
		$crm_list[$key][nagios_key] = $row[nagios_key];
		
		$report[rows][$key] = $crm_list[$key];
		
		unset($crm_list[$key]);
	}
	
	//print_r($report[rows]);
	
	return display_microwave_off_accounts($report);
}

function display_microwave_off_accounts($report){

	if($report == "CAN NOT RETRIEVE FILE FROM NAGIOS"){
		return $report;
	}
	
	$html = '
	<table width="100%" border=0 cellpadding="1" cellspacing="0">
		<tr>
			<th>NAGIOS LAST UPDATE TIME</th>
			<th>'.$report[FILE_GENERATION].'</th>
		</tr>
	</table>
	';
	
	$html .= '
	<table width="100%" border=0 cellpadding="1" cellspacing="0" class="sortable" >
		<tr>
			<th>No</th>
			<th>NAGIOS SITE NAME</th>
			<th>ACCOUNT NUMBER</th>
			<th>ACCOUNT NAME</th>
			<th>CRM STATUS</th>
			<th>SITE DOWNTIME</th>
			<th>CRM SITE NAME</th>
			<th>CRM BANDWIDTH</th>
			<th>ADMIN CONTACT</th>
			<th>TECH CONTACT</th>
		</tr>
	';
	
	foreach($report[rows] as $row){
		$html .= '
		<tr>
			<td class="values">'.++$number.'</td>
			<td class="text_values">'.$row[nagios_key].'</td>
			<td class="values">
				<a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record='.$row[id].'" target="_blank">'.
				$row[account_no].'
				</a>
			</td>
			<td class="text_values">'.$row[name].'</td>
			<td class="text_values">'.$row[status].'</td>
			<td class="values">'.sec_to_time($row[down_time]).'</td>
			<td class="text_values">'.$row[usercrm].'</td>
			<td class="text_values">'.$row[bandwidth].'</td>
			<td class="text_values">'.$row[contact_person].' - '.$row[contact_phone].'</td>
			<td class="text_values">'.$row[tech_person].' - '.$row[tech_phone].'</td>
		</tr>
		';
	}
	
	$html .= "</table>";
	
	return $html;
}

?>