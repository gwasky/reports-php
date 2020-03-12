<?
function generate_new_aaa_crm_reconciliation(){

	custom_query::select_db('wimax');

	//inner function definitions for this main fx
	function active($status){
		if ($status=='Y'){
				$status="Active";
			}else{
				$status='Not Active';
			}
		return $status;
	}
	
	function load_profiles(){
		/*$profiles = array();
		$profiles[prof_ids] = array();
		$profiles[bandwidth_ids] = array();*/
		
		$aaafile="sources/profiles.cvs";
	
		$handle = fopen($aaafile, "r");
		while (($p_row = fgetcsv($handle, 200, ",")) !== FALSE){
			//creating array with prof ids as main key
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][contention] = $p_row[1];
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][aaa_bandwidth] = $p_row[2];
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][aaa_up] = $p_row[3];
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][aaa_down] = $p_row[4];
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][aaa_prof_id] = $p_row[5];
			$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][aaa_bandwidth_id] = $p_row[6];
			
			//creating an array with band_ids as main keys
			$profiles[aaa_bandwidth_ids][$p_row[6]] = $p_row[6];
			
			if(count($profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][crm_names]) == 0){
				$profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][crm_names][0] = $p_row[0];
			}else{
				//In case the proile also has a second deployment avenue eg staff and commercial
				array_push($profiles[aaa_prof_ids][$p_row[5]][$p_row[6]][crm_names],$p_row[0]);
			}
		}
		fclose($handle);
		
		return $profiles;
	}
	
	function profiler(&$user_row,$profiles){
		
		if(count($profiles[aaa_prof_ids][$user_row[aaa_prof_id]][$user_row[aaa_bandwidth_id]]) > 0){
			/*if($user_row[user] == 'Ayton.leased'){
				print_r($profiles[aaa_prof_ids][$user_row[aaa_prof_id]][$user_row[aaa_bandwidth_id]]); echo "<br>";
			}*/
			foreach($profiles[aaa_prof_ids][$user_row[aaa_prof_id]][$user_row[aaa_bandwidth_id]] as $key=>$value){
				if(!isset($user_row[$key])){
					$user_row[$key] = $value;
				}
			}
		}else{
			$user_row[contention] = 'New AAA definition';
			$user_row[aaa_up] = $user_row[aaa_prof_id];
			$user_row[aaa_down] = $user_row[aaa_bandwidth_id];
		}
		
		/*if($user_row[user] == 'Ayton.leased'){
			print_r($user_row); echo "<br>";
		}*/
	}
	
	function set_ids(&$user_row,$check_id,$profiles){
		/*if($user_row[user] == 'Ayton.leased'){
			echo "1 Checking id ".$check_id." in profiles[".$user_row[aaa_prof_id]."][".$user_row[aaa_bandwidth_id]."]<br>";
		}*/
		if($profiles[aaa_prof_ids][$user_row[aaa_prof_id]][$user_row[aaa_bandwidth_id]] == ''){
			if(in_array($check_id,array_keys($profiles[aaa_prof_ids]))){
				$user_row[aaa_prof_id] = $check_id;
			}elseif(in_array($check_id,array_keys($profiles[aaa_bandwidth_ids]))){
				$user_row[aaa_bandwidth_id] = $check_id;
			}else{
				if(!isset($user_row[aaa_bandwidth_id]) && !isset($user_row[aaa_prof_id])){
					$user_row[aaa_prof_id] = 'Un defined';
					$user_row[aaa_bandwidth_id] = 'Un defined';
				}
			}
		}
		/*if($user_row[user] == 'Ayton.leased'){
			echo "2 Checking id ".$check_id." in profiles[".$user_row[aaa_prof_id]."][".$user_row[aaa_bandwidth_id]."]<br>";
		}*/
	}
	
	$myquerys = new custom_query();
	
	/*$query_wimax = "
		SELECT 
			accounts_cstm.preferred_username_c as usercrm, 
			cn_contracts.`status`, 
			accounts_cstm.download_bandwidth_c as bandwidth, 
			accounts_cstm.whole_sale_type_c as wholesale 
		FROM 
			accounts
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c) 
		WHERE 
			accounts.deleted=0 and 
			cn_contracts.deleted=0 and
			accounts_cstm.download_bandwidth_c != 'No Bandwidth' and
			accounts_cstm.platform_c != '' and
			accounts_cstm.download_bandwidth_c not like 'Whole Sale Bandwidth%'
	";*/

	$query_wimax = "
		SELECT
			accounts.id,
			accounts_cstm.preferred_username_c as usercrm, 
			cn_contracts.`status`, 
			accounts_cstm.download_bandwidth_c as bandwidth, 
			accounts_cstm.whole_sale_type_c as wholesale 
		FROM
			accounts
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c) 
		WHERE 
			accounts.deleted=0 and 
			cn_contracts.deleted=0 and
			accounts_cstm.download_bandwidth_c != 'No Bandwidth' and
			accounts_cstm.platform_c = 'Wimax'
	";
	
	$crm_user_array = $myquerys->multiple($query_wimax);
	
	foreach($crm_user_array as $key=>$row){
		//$row[usercrm] = strtolower($row[usercrm]);
		if($row[status] != 'Active'){
			$row[overall_status] = 'Not Active';
		}else{
			$row[overall_status] = 'Active';
		}
		$crm_user_array[$row[usercrm]] = $row;
		unset($crm_user_array[$key]);
	}

	//Load all profiles in the cvs file
	$profiles = load_profiles();

	//New file
	$userfile="http://wimax:warid@41.221.86.21/wimaxsubs/user2.cvs";
	//Temp
	//$userfile="users.cvs";
	
	
	if($handle = fopen($userfile,"r")){
		while (($data = fgetcsv($handle, 200, ",")) !== FALSE){
			$num = count($data);
			
			//when a row from the file has more than four columns i.e useful data start action
			if($num >= 4 and $data[0] != 'npm-ping'){
				if($aaa_users[$data[0]][user] == ''){
					$aaa_users[$data[0]][user] = $data[0];
					$aaa_users[$data[0]][datemodified] = substr_replace($data[2],'',10);
					$aaa_users[$data[0]][status] = active($data[3]);
					$aaa_users[$data[0]][datecreated] = substr_replace($data[4],'',10);
					
					//Setting bandwidth and prof ids
					set_ids($aaa_users[$data[0]],$data[1],$profiles);
				}else{
					/*test if username is repeated (ie a new row) and the consider for printing */
					//test the other data[1] which can be used to give the profile details
	
					//Setting bandwidth and prof ids
					set_ids($aaa_users[$data[0]],$data[1],$profiles);
				}
				$row++;
			}//close if action
		}
		
		//close the handle
		fclose($handle);
	}else{
		$report[errors] = "<br>The userfile from which user bandwidth allocations are obtained can not be opened ... <br>";
		return display_aaa_crm_reconciliation($report);
	}
	
	foreach($aaa_users as &$aaa_user){
		profiler($aaa_user, $profiles);
		//checking username
		if($aaa_user[user] != $crm_user_array[$aaa_user[user]][usercrm]){
			$aaa_user[usercrm] = 'AAA user not in CRM';
			$aaa_user[statuscrm] = $aaa_user[usercrm];
			$aaa_user[bandwidthcrm] = $aaa_user[usercrm];
			$aaa_user[error_column] = 'CRM User';
			$descrepancies += 1;
		}else{
			$aaa_user[usercrm] = $aaa_user[user];
			$aaa_user[crmid] = $crm_user_array[$aaa_user[user]][id];
			
			//checking status
			if($aaa_user[status] != $crm_user_array[$aaa_user[user]][overall_status]){
				$aaa_user[error_column] .= 'CRM Status,';
				$aaa_user[statuscrm] = $crm_user_array[$aaa_user[user]][status].' - Different Status';
				$descrepancies += 1;
			}else{
				$aaa_user[statuscrm] = $aaa_user[status];
			}

			//Checking bandwidths
			/*if($aaa_user[user] == 'Ayton.leased'){
				echo "Testing ".$crm_user_array[$aaa_user[user]][bandwidth]." ==>> "; print_r($aaa_user[crm_names]); echo " =>> ".$aaa_user[aaa_prof_id]."<br><br>";
			}*/
			if(!in_array($crm_user_array[$aaa_user[user]][bandwidth], $aaa_user[crm_names])){
				$aaa_user[error_column] .= 'CRM Bandwidth,';
				$aaa_user[bandwidthcrm] = $crm_user_array[$aaa_user[user]][bandwidth].' - Different Bandwidth';
				++$descrepancies;
			}else{
				$aaa_user[bandwidthcrm] = $crm_user_array[$aaa_user[user]][bandwidth];
			}
		}
	}
	
	foreach($crm_user_array as $key=>$row){
		if((count($aaa_users[$key])==0) && ($row[overall_status] == 'Active')){
			$aaa_users[$key][user] = 'CRM User not in AAA';
			$aaa_users[$key][usercrm] = $key;
			$aaa_users[$key][status] = $aaa_users[$key][user];
			$aaa_users[$key][modified_on] = $aaa_users[$key][user];
			$aaa_users[$key][date_created] = $aaa_users[$key][user];
			$aaa_users[$key][statuscrm] = $row[status];
			$aaa_users[$key][bandwidthcrm] = $row[bandwidth];
			$aaa_users[$key][error_column] = 'AAA User';
			$descrepancies += 1;
		}elseif((count($aaa_users[$key])==0) && ($row[overall_status] != 'Active')){
			$aaa_users[$key][user] = 'CRM User need not be in AAA';
			$aaa_users[$key][usercrm] = $key;
			$aaa_users[$key][status] = $aaa_users[$key][user];
			$aaa_users[$key][modified_on] = $aaa_users[$key][user];
			$aaa_users[$key][date_created] = $aaa_users[$key][user];
			$aaa_users[$key][statuscrm] = $row[status];
			$aaa_users[$key][bandwidthcrm] = $row[bandwidth];
		}
	}
	
	//Excluding accounts that are inactive on the AAA and none existent in the CRM. ie even though they are on the AAA they are inactive and Un billable in CRM
	foreach($aaa_users as &$aaa_user){
		/*if($aaa_user[error_column]){
			echo $aaa_user[user]." ==============>> Usercrm [".$aaa_user[usercrm]."] \t\t\t AAA status [".$aaa_user[status]."] \t\t\t CRM Status [".$crm_user_array[$aaa_user[user]][overall_status]."] \n";
		}*/
		if(($aaa_user[usercrm] == 'AAA user not in CRM') && ($aaa_user[status] == 'Not Active') && ($crm_user_array[$aaa_user[user]][overall_status] != 'Active')){
			--$descrepancies;
			unset($aaa_user[error_column]);
		}
	}
	
	$report[rows] = $aaa_users;
	$report[descrepancies] = $descrepancies;
	
	return display_aaa_crm_reconciliation($report);
}

function display_aaa_crm_reconciliation($report){
	
	function show_val($up,$down){
		if((intval($up) == 0) && (strlen($up) > 0)){
			switch($up){
				case 'LL':
					$up = '';
					break;
				default:
			}
			$output = $up." ";
		}else{
			//$output = intval($up)."/";
			$output = $up."/";
		}
		if((intval($down) == 0) && (strlen($down) > 0)){
			$output .= $down;
		}else{
			$output .= intval($down);
		}
		return $output;
	}
	
	$html = "
	<table width='100%' border=0 cellpadding='3' cellspacing='1' id='table1' >
	";
	
	if($report[errors]){
		$html .= '
		<tr>
			<th colspan="11">'.$report[errors].'</td>
		</tr>
	';
	}

	if($report[descrepancies]){
		$html .= "
		<tr>
			<th colspan='8'>Number of Descrepancies </td>
			<th colspan='3'>".$report[descrepancies]."</td>
		</tr>
		";
	}

	$html .= '
	</table>
	
	<table width="100%" border=0 cellpadding="3" cellspacing="1" class="sortable" >
	';
	
	$html .= "
		<tr>
			<th>No</th>
			<th>AAA USER NAME</th>
			<th>CRM USER NAME</th>
			<th>AAA USER PROFILE</th>
			<th>AAA BANDWIDTH</th>
			<th>CRM BANDWIDTH</th>
			<th>AAA ACCOUNT STATUS</th>
			<th>CRM ACCOUNT STATUS</th>
			<th>DATE Changed</th>
			<th>DATE Created</th>
			<th>ERRONEOUS COLUMNS</th>
		</tr>
	";
	
	foreach($report[rows] as $aaa_user){
		$html .= "
			<tr style='background-color:"; if($aaa_user[error_column]){ $html .="#AE0000";}else{ $html .="#FFFFFF";} $html .= ";'>
		";
		
		$html .= "
			<td class='values'>".++$number."</td>
			<td class='text_values'>".$aaa_user[user]."</td>
			<td class='text_values'><a href='http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&action=DetailView&record=".$aaa_user[crmid]."' target='_blank'>".$aaa_user[usercrm]."</a></td>
			<td class='text_values'>".$aaa_user[contention]."</td>
			<td class='values'>".show_val($aaa_user[aaa_up],$aaa_user[aaa_down])." </td>
			<td class='text_values'>".$aaa_user[bandwidthcrm]."</td>
			<td class='text_values'>".$aaa_user[status]."</td>
			<td class='text_values'>".$aaa_user[statuscrm]."</td>
			<td class='text_values'>".$aaa_user[datemodified]."</td>
			<td class='text_values'>".$aaa_user[datecreated]."</td>
			<td class='text_values'>".$aaa_user[error_column]."</td>
		</tr>
		";
	}
	
	$html .= "</table>";
	
	return $html;
}

?>