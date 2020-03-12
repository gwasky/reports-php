<?php

	function generate_account_equipment($from,$to,$equip){
	
		$myquery = new custom_query();
		custom_query::select_db('wimax');
		
		if($from){
			$from .= " 00:00:00";
		}else{
			$from = date('Y-m-')."01 00:00:00";
		}
		if($to){
			$to .= " 23:59:59";
		}else{
			$to = date('Y-m-d H:i:s');
		}
		
		$query = "SELECT
					accounts.id,
					accounts.name as account_name,
					accounts_cstm.download_bandwidth_c as download_bandwidth,
					accounts_cstm.crn_c as crn,
					cn_contracts.expiry_date,
					accounts_cstm.service_type_internet_c as service_type,
					accounts_cstm.service_type_voice_c,
					accounts_cstm.shared_packages_c,
					accounts_cstm.cpe_type_c as cpe_type,
					cn_contracts.status,
					accounts_cstm.mac_address_c
					FROM
					accounts
					Inner Join accounts_cstm ON accounts.id = accounts_cstm.id_c
					Inner Join accounts_cn_contracts_c ON accounts_cstm.id_c = accounts_cn_contracts_c.accounts_cntsaccounts_ida
					Inner Join cn_contracts ON accounts_cn_contracts_c.accounts_cn_contracts_idb = cn_contracts.id
					Inner Join cn_contracts_cstm ON cn_contracts.id = cn_contracts_cstm.id_c AND cn_contracts.id = cn_contracts_cstm.id_c
					where accounts.deleted = 0 and accounts_cn_contracts_c.deleted=0 and accounts.date_entered >= '".$from."' and accounts.date_entered <= '".$to."'";
					
					if(!in_array('',$equip) and count($equip)>0)
					{
						$i=0;
						$query .=" AND accounts_cstm.cpe_type_c IN (";
						foreach($equip as $k=>$v){
							$query .=" '$v'";
							++$i;
							if($i < count($equip)) {
								$query .=",";
							}
						}
						$query .=")";
					}
					//echo $query;
		//$data = $myquery->multiple($query);
		return display_account_equipment($myquery->multiple($query));
		
		}
		
		function display_account_equipment($report)
		{
			//var_dump($report);
			if(count($report)>0)
			{
				$html ='
					<table border="0" cellpadding="2" cellspacing="0" width="50%">
					<tr>
						<th>No</th>
						<th>Account Name</th>
						<th>CRN</th>
						<th>Service Type</th>
						<th>Download BW</th>
						<th>Contract Status</th>
						<th>Shared/Unshared</th>
						<th>Equipment Type</th>
						<th>MAC Address</th>
					</tr>';
					foreach($report as $row)
					{
						++$i;
						$html .='
							<tr>
							<td class="values">'.$i.'</td>
							<td class="text_values"><a href="http://wimaxcrm.waridtel.co.ug/index.php?module=Accounts&offset=18&stamp=1313152069098708800&return_module=Accounts&action=DetailView&record='.$row[id].'">'.$row[account_name].'</a>
							<td class="values">'.$row[crn].'</td>
							<td class="text_values">'.$row[service_type].'</td>
							<td class="text_values">'.$row[download_bandwidth].'</td>
							<td class="text_values">'.$row[status].'</td>
							<td class="text_values">'.$row[shared_packages_c].'</td>
							<td class="text_values">'.$row[cpe_type].'</td>
							<td class="text_values">'.$row[mac_address_c].'</td>
						</tr>';
					}
					$html .='</table>';
					
			}
			return $html;
		}
		
					

?>