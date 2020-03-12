<?php
function generate_ivr_choices($from,$to,$reporttype,$last_option_groups,$msisdns){
	
	ini_set('memory_limit','2048M');
	
	custom_query::select_db('ivrperformance');
	$myquery = new custom_query();
	
	function uni_msisdns(){
	
	}
	
	function summarise($rows, $last_option_groups){
		$msisdnarray = array();
		foreach($rows as $row){
			$lang = split(':',$row[name]);
			if(count($lang)>1){
				++$summary['Number of calls by Language Option'][$lang[0]];
			}
			++$summary['Number of calls by Option Group'][$row[last_option_group]];
			++$summary['Number of calls by Month'][substr($row[date_entered],0,7)];
			++$summary['Number of calls by Option Group by Last Option'][$row[last_option_group]." >> ".$row[name]];
			
			
			//++$summary['Number of calls by Date by Option Group'][substr($row[date_entered],0,10)." >> ".$row[last_option_group]];
			++$summary['Number of calls by Date'][substr($row[date_entered],0,10)];
			++$summary['Number of calls by Month by Option Group'][substr($row[date_entered],0,7)." >> ".$row[last_option_group]];
			
			//++$msisdns[[substr($row[date_entered],0,10)]][$row[msisdn]];
			//$msisdns[] = $row[msisdn];
			//Get unique numbers
			//$summary;
			$msisdnarray[] = $row[msisdn];
		}
		
		if(!$last_option_groups){
			$mykey = 'Date: '.$_POST['from'].' to '.$_POST['to'];
		}else{
			$mykey = 'Date: '.$_POST['from'].' to '.$_POST['to'].' Last option group: '.$_POST['last_option_groups'];
		}
		
		$summary['No of Unique Callers by Period'] = array($mykey => count(array_unique($msisdnarray)));
		
		return $summary;
	}
	
	//$to = '2012-03-14'; $from = $to;
	if($from == ''){
		$date = date("Y-m-d");
		$default = strtotime ('-1 day', strtotime($date));
		$default = date ( 'Y-m-d' , $default );
		$from = $default;
		$_POST['from'] = $default;
		//$from = date('Y-m-d');
	}
	$from .= ' 00:00:00';
	
	if($to == ''){
		$date = date("Y-m-d");
		$default = strtotime ('-1 day', strtotime($date));
		$default = date ( 'Y-m-d' , $default );
		$to = $default;
		$_POST['to'] = $default;
	}
	$to .= ' 23:59:59';
	
	if($to == $from){
		$period = " asterisk_cdrs.date_entered LIKE '".$from."%'";
	}else{
		$period = " (asterisk_cdrs.date_entered >= '".$from."' AND asterisk_cdrs.date_entered <= '".$to."')";
	}
	
	if($msisdns){
		$msisdn_condition = ' AND asterisk_cdrs.msisdn IN (';
		$msisdns = explode(',',$msisdns);
		foreach($msisdns as $msisdn_key=>$msisdn){
			$msisdn_condition .= "'".trim($msisdn)."'";
			if($msisdn_key + 1 < count($msisdns)) { $msisdn_condition .= ","; }
		}
		$msisdn_condition .= ')';
	}
	
	if($last_option_groups != ''){
		$lastoptiongrouping = " asterisk_cdrs.last_option_group = '".$last_option_groups."' AND";
	}else{
		$lastoptiongrouping =""; 
	}
	
	
	$query = "
		SELECT
			asterisk_cdrs.msisdn,
			asterisk_cdrs.date_entered,
			asterisk_cdrs.last_option_group,
			asterisk_cdrs.last_option_value,
--			IF(asterisk_translations.`name` IS NULL,CONCAT('NO TRANSLATION FOR ',asterisk_cdrs.last_option_value),asterisk_translations.`name`) AS `name`,
			IF(
				asterisk_cdrs.last_option_group = 'IVR',
				IF(asterisk_translations.`name` IS NULL,CONCAT('NO TRANSLATION FOR ',asterisk_cdrs.last_option_value),SUBSTRING_INDEX(asterisk_translations.`name`, ': ', -1)),
				IF(asterisk_translations.`name` IS NULL,CONCAT('NO TRANSLATION FOR ',asterisk_cdrs.last_option_value),asterisk_translations.`name`)
			) AS `name`,
			asterisk_cdrs.ivr_duration,
			if(asterisk_cdrs.ivr_duration >= 4, 'Normal','Short') as call_type
		FROM
			asterisk_cdrs
			LEFT OUTER JOIN asterisk_translations ON asterisk_translations.option_value = asterisk_cdrs.last_option_value
		WHERE
			".$lastoptiongrouping.
			$period.
			$msisdn_condition;
	//echo PrintR($query);
	
	$entries = $myquery->multiple($query);
	
	if(count($entries) == 0) { return display_generate_ivr_choices("NO DATA"); }
	
	switch($reporttype){
		case 'both':
			$report[summary] = summarise($entries,$last_option_groups);
			$report[rows] = $myquery->multiple($query);
			break;
		case 'detail':
			$report[rows] = $myquery->multiple($query);
			break;
		case 'summary':
		default:
			$_POST[reporttype] = 'summary';
			$report[summary] = summarise($entries,$last_option_groups);
			break;
	}

	return display_generate_ivr_choices($report);
}

function display_generate_ivr_choices($report){
	if($report == "NO DATA") { return "There is no data that matches your filter selection <br>"; }

	$html = '';
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<th style="height:20px;">SUMMARIES</th>
			</tr>
		';
		
		foreach($report[summary] as $summary_heading=>$summary_data){
			$html .= '
				<tr>
					<td style="font-size:15px;">'.$summary_heading.'</td>
				</tr>
				<tr>
					<td>
					<table border="0" cellpadding="0" cellspacing="0" class="sortable" width="100%">
						<tr>
							<th>#</th>
			';
			
			//Titles
			$columns = explode(" by ",$summary_heading);
			$first_col = array_shift($columns);
			
			foreach($columns as $column){
				$html .= '
							<th>'.$column.'</th>
				';
			}
			
			$html .= '
					<th>'.$first_col.'</th>
			';
			
			$html .= '
						</tr>
			';
			
			unset($row_counter);
			//row
			foreach($summary_data as $parameter_string=>$parameter_string_value){
				
				$html .= '
						<tr>
							<td class="values">'.++$row_counter.'</td>
				';
				
				$parameters = array();
				$parameters = explode(" >> ",$parameter_string);
				
				foreach($parameters as $parameter){
					$html .= '
							<td class="'; if(!is_numeric($parameter)){ $html .= 'text_'; } $html .= 'values">'; 
								if(!is_numeric($parameter)){ $html .= $parameter; }else{ $html .= number_format($parameter,0); } $html .= '
							</td>
					';
				}
				$html .= '
							<td class="values">'.number_format($parameter_string_value,0).'</td>
						</tr>
				';
			}
			
			$html .= '
					</table>
					</td>
				</tr>
				<tr>
					<td style="height:10px;"></td>
				</tr>
			';

	}
	
	$html .= '</table>';
	}
	
	
	$count = 0;
	if(count($report[rows]) > 0){
	
		$html .='
			<table width=100%>
				<tr>
					<th colspan="3">Detail</th>
				</tr>
				<tr>
					<td></td>
				</tr>
			</table>';
			
			$html .= '<table width=100% border="0" cellpadding="2" cellspacing="0" class="sortable" >';
			$html .= '<tr>';
			$html .= '<th>#</th>';
			$html .= '<th>MISIDN</th>';
			$html .= '<th>Last Option Group</th>';
			$html .= '<th>Last Option</th>';
			$html .= '<th>Date Entered</th>';
			$html .= '<th>Time Entered</th>';
			$html .= '<th>IVR Duration</th>';
			$html .= '<th>Call Type</th>';
			$html .= '</tr>';
		
		foreach($report[rows] as $row){
			$html .= '<tr>';
			$html .= '<td class="values">'.++$count.'</td>';
			$html .= '<td class="values">'.$row[msisdn].'</td>';
			$html .= '<td class="text_values">'.$row[last_option_group].'</td>';
			$html .= '<td class="text_values">'.$row[name].'</td>';
			$html .= '<td class="values">'.substr($row[date_entered],0,10).'</td>';
			$html .= '<td class="values">'.substr($row[date_entered],11,8).'</td>';
			$html .= '<td class="values">'.sec_to_time($row[ivr_duration]).'</td>';
			$html .= '<td class="text_values">'.$row[call_type].'</td>';		
			$html .= '</tr>';
		}
		$html .= '</table>';
	}
	
	return $html;
}

//var_dump(array_unique(array('a','a','b','c','c','d')));
?>