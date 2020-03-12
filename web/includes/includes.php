<?
require_once('/srv/www/htdocs/reports/web/includes/dropdowns.php');

function script_action($get){
	switch($get[retrieve]){
		/*case 'category':
			$html = dropdown($label='Category', $name='wapup[category]', $onchange_call='contentpulse(\'ajax.php?retrieve=subcategory&cat_id=\'+this.value,\'subcategories\')', $selected='', $options=get_category_options(), $class='');
			break;*/
		case 'subcategory':
			$html = dropdown($label='Subcategory', $name='subcategory', $onchange_call='contentpulse(\'ajax.php?retrieve=subsubcategory&subcategory=\'+this.value+\'&category=\'+document.getElementById(\'category\').value,\'subjects\')', $selected=$_POST[subcategory], $options=get_subcategory_options($get[category]), $class='select');
			//+\'&cat_id=\'+document.getElementById(\'wapup[category]\').value
			break;
		case 'subsubcategory':
			$html = dropdown($label='Subject', $name='subject', $onchange_call='', $selected=$_POST[subject], $options=get_wrapup_options($get[category],$get[subcategory]), $class='select');
			break;
		default:
			$html = 'No Retrieve selected ';
			break;
	}
	return $html;
}

function time_to_sec($time){
    /*
	THIS HAS LIMITATIONS
	$hours = substr($time, 0, -6);
    $minutes = substr($time, -5, 2);
    $seconds = substr($time, -2);
	*/
	
	$time_array = explode(":",$time);
	
	if(count($time_array) == 3){
		$hours = trim($time_array[0]);
		$minutes = trim($time_array[1]);
		$seconds = trim($time_array[2]);
	}elseif(count($time_array) == 2){
		$hours = 0;
		$minutes = trim($time_array[0]);
		$seconds = trim($time_array[1]);
	}else{
		return "Illegal time format [".$time."]";
	}
	
	//echo "Time : [".$time."] => ".($hours * 3600 + $minutes * 60 + $seconds)." <br>";

    return $hours * 3600 + $minutes * 60 + $seconds;
} 

function sec_to_time($seconds) { 
    $hours = floor($seconds / 3600); 
    $minutes = floor(($seconds % 3600) / 60); 
    $seconds = $seconds % 60; 

    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds); 
} 

function generate_footer(){

return '
	<tbody>
		<tr>
			<td height="15" align="center" bgcolor="#ffffff" class="text2">
			   Copyright  2008, Warid Telecom (U) Limited All rights reserved.
			</td>
		</tr>
  	<tr>
    	<td class="textfooter" align="center" bgcolor="#034DA2" height="40">
   <img src="images/plot.gif">  Warid Telecom Building, Plot 16A, Clement Hill Road <br> 
   <img src="images/tel.gif" alt="Telephone" height="14" width="20"><strong> Tel :</strong>&nbsp;100 Toll Free for WARID Customers      or 0700100100 for non WARID Customers<br>
    <img src="images/address.gif" alt="Address" height="15" width="20">  P.O Box 70665, Kampala   <img src="images/email.gif"> <b>Email : </b>customercare@waridtel.co.ug<br>
    <br>
 		</td>
	</tr>
	</tbody>
	';
}

function accounts_format($number){
	
	/*$myquery = new custom_query();
	
	$result = $myquery->single("select format(abs('$number'),2) as identifier;");
	$formated_number = $myquery->Unescape($result[identifier]);
	
	if($number < 0){
		$formated_number = "(".$formated_number.")";
	}*/
	
	return number_format($number,2,'.',',');
}

function display_user($user_id){

	require_once('config.wimax.php');

	$myquery = new custom_query();
	
	if($user_id){
		$result = $myquery->single("select user_name, concat(concat(first_name,' '), last_name) as crm_user from users where deleted=0 and (first_name != '' or last_name != '') and id='$user_id'");
		$user = $result[crm_user].' ('.$result[user_name].')';
	}else{
		$user = 'No User (No username)';
	}
	
	return $user;
}

function display_label($name){
	$name = str_replace('_',' ',$name);
	return str_replace(' c','',$name);
}

function date_reformat($date,$format){

	if(!$format){
		$format = "%d %b %Y";
	}

	$myquery = new custom_query();
	$query = "select date_format('$date', '$format') as identifier";
	$result = $myquery->single($query);
	$date = $myquery->Unescape($result[identifier]);
	
	return $date;
}

function month_reformat($date){

	$myquery = new custom_query();
	
	$result = $myquery->single("select date_format('$date', '%b %Y') as identifier;");
	$date = $myquery->Unescape($result[identifier]);
	
	return $date;
}

function date_time_add($date,$value=0,$mysql_interval=''){

	$myquery = new custom_query();
	
	$result = $myquery->single("select date_add('$date', interval '".$value."' ".$mysql_interval.") as identifier;");
	
	return $myquery->Unescape($result[identifier]);;
}

function get_rate($date){
	/*
	$rating = new wimax_rates();
	
	$rate_rows = $rating->GetList(array(array('rate_date','=',$date)));
	$row = $rate_rows[0];
	
	return $row->rate;
	*/
	
	$myquery = new custom_query();

	//CCBA
	$query = "SELECT rate_date, rate FROM wimax_rates where id = (select max(id) from wimax_rates where rate_date <= '".$date."')";

	//Live crm
	//$query = "SELECT rate_date, rate FROM wimax_rates where rate_date <= '$date' ORDER BY rate_date DESC LIMIT 1";
	
	//echo $query."<br>";

	$result = $myquery->single($query);
	
	/*foreach($result as $key=>$value){
		echo "Key [".$key."] Value [".$value."]<br>";
	}*/
	
	return $result;
}

function convert_value($value, $from, $date, $to){
	
	//echo "Converting [".$value."] From [".$from."] to [".$to."] with date `".$date."` <br>";
	
	$rating = new wimax_rates();
	
	if($from == ''){
		$from = 'USD';
	}

	if(($from =='USD')&&($to =='')){
		$to = 'UGX';
	}elseif(($from == 'UGX')&&($to =='')){
		$to = 'USD';
	}

	//echo "Converting [".$value."] From [".$from."] to [".$to."] with date `".$date."`<br>";
	
	//$rate_rows = $rating->GetList(array(array('rate_date','=',$date)));
	//$rate_row = $rate_rows[0];
	$result = get_rate($date);
	$rate_row->rate = $result[rate];
	
	if($from != $to){
		if($rate_row){
			if(($from == 'UGX')&&($to == 'USD')){
				$new_value = $value / $rate_row->rate;
			}elseif(($from == 'USD')&&($to == 'UGX')){
				$new_value = $value * $rate_row->rate;
			}else{
				$new_value = $value;
			}
		}else{
			echo "No rate set for ".$date."<br>";
		}
	}else{
		$new_value = $value;
	}
	
	//echo " = ".$new_value." \n <br>";
	
	return $new_value;
}

function display_footer(){
	
/*	return '
		<div class="text2" align="center" style="height:50px;">
			<div class="text2" style="height:15px" align="center">
				Copyright  2008, Warid Telecom (U) Limited All rights reserved.
			</div>
			<div class="textfooter" style="height:40px; background-color:#034DA2;" align="center">

   					<img src="images/plot.gif"> Warid Telecom Building, Plot 16A, Clement Hill Road <br> 
   					<img src="images/tel.gif" alt="Telephone" height="14" width="20"><strong> Tel :</strong>&nbsp;100 Toll Free for WARID Customers      or 0700100100 for non WARID Customers<br>
    				<img src="images/address.gif" alt="Address" height="15" width="20">  P.O Box 70665, Kampala<img src="images/email.gif"> <b>Email : </b>customercare@waridtel.co.ug<br>
    <br>
			</div>
		</div>
	';*/
}

function last_day($date){
	
	$myquery = new custom_query();
	custom_query::select_db('cs');
	$result = $myquery->single("select last_day('$date') as last_date");
	
	return $result[last_date];
}

function month_days($date){
	
	$myquery = new custom_query();
	
	$query = "SELECT date_format(LAST_DAY('$date'),'%d') as days";
	$result = $myquery->single($query);
	
	return $result[days];
}

function month_diff($end, $start){
	
	$myquery = new custom_query();
	
	$query = "select period_diff(date_format('".$start."','%Y%m'),date_format('".$end."','%Y%m')) as diff";
	$result = $myquery->single($query);
	
	return $result[diff];
}



function get_rate_date($date1,$date2){
	
	if($date1 != $date2){
		$date = $date2;
	}else{
		$date = $date1;
	}
	
	return $date;
}

function display_service_type_dropdown($service_type){
	
	$html = '<label>Select Service type <select name="service_type" size="1" id="service_type" class="select">';
	$html .= '<option value="" ';if($service_type == ''){ $html .= 'selected="selected"'; } $html .= '>ALL SERVICE TYPES</option>';
	$html .= '<option value="Postpaid" ';if(strtolower(trim($service_type)) == "postpaid"){ $html .= 'selected="selected"'; } $html .= '>Postpaid</option>';
	$html .= '<option value="Prepaid" ';if(strtolower(trim($service_type)) == "prepaid"){ $html .= 'selected="selected"'; } $html .= '>Prepaid</option>';
	$html .= '</select></label>';
	
	return $html;
}

function date_diff_days($from,$to){
	$diff = strtotime($to) - strtotime($from) + 1; //Find the number of seconds
	$days = ceil($diff / (60*60*24)) ;  //Find how many days that is
	return $days;
}



//Attendance Tracker
function generateDateRangeArray($strDateFrom,$strDateTo) {
  $aryRange=array();
  $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),substr($strDateFrom,8,2),substr($strDateFrom,0,4));
  $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),substr($strDateTo,8,2),substr($strDateTo,0,4));
  if ($iDateTo>=$iDateFrom) {
    array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
    while ($iDateFrom<$iDateTo) {
      $iDateFrom+=86400; // add 24 hours
      array_push($aryRange,date('Y-m-d',$iDateFrom));
    }
  }
  return $aryRange;
}



function generate_html(){
	
	switch($_GET[report]){
		case 'first_time_activation':
			if($_POST[Submit]){
				$result_html = generate_first_time_activation_report($_POST[activate_from], $_POST[activate_to], $_POST[account_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'first_time_billing':
			if($_POST[Submit]){
				$result_html = generate_first_billing_date_report($_POST[bill_from], $_POST[bill_to], $_POST[activate_from], $_POST[activate_to],$_POST[account_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'churn_date':
			if($_POST[Submit]){
				$result_html = generate_churn_date_report($_POST[from], $_POST[to], $_POST[account_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'broadband_account_status':
			if($_POST[Submit]){
				$result_html = generate_broadband_account_status_report($_POST[account_id],$_POST[broadband_status]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'broad_band_cases':
			if($_POST[Submit]){
				$result_html = generate_cases_report($_POST[from], $_POST[to],  $_POST[account_id], $_POST[case_status],$_POST[customer_types],$_POST[subject_setting],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'gsm_cases':
			if($_POST[Submit]){
				$result_html = generate_gsm_cases($_POST[report_type], $_POST[from], $_POST[to], $_POST[subject_settings], $_POST[affectec_num],$_POST[status]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'attendance_records':
			if($_POST[Submit]){
				$result_html = generate_attendance_report($_POST[from], $_POST[to], $_POST[shifts],$_POST[supervisors],$_POST[teams]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'cca_attendance_by_data_team_supervisor':
			if($_POST[Submit]){
				$result_html = generate_cca_attendance_by_data_team_supervisor($_POST[from], $_POST[to], $_POST[agents], $_POST[shifts],$_POST[supervisors],$_POST[teams]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'absentism_summary_report':
			if($_POST[Submit]){
				$result_html = generate_absentism_summary($_POST[from], $_POST[to], $_POST[shifts],$_POST[supervisors],$_POST[teams]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'daily_attendace_report':
			if($_POST[Submit]){
				$result_html = generate_daily_attendance_summary($_POST[from], $_POST[to], $_POST[shifts],$_POST[supervisors],$_POST[teams]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'detailed_attendace_report':
			if($_POST[Submit]){
				$result_html = generate_detailed_report($_POST[from], $_POST[to], $_POST[shifts],$_POST[supervisors],$_POST[teams]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'sms_outlet_evaluation_report':
			if($_POST[Submit]){
				$result_html = generate_outlet_evaluation($_POST[report_type],$_POST[from], $_POST[to], $_POST[franchises],$_POST[answers]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'task_list':
			if($_POST[Submit]){
				$result_html = generate_task_list($_POST[relatedto],$_POST[from],$_POST[to],$_POST[account_id],$_POST[leads],$_POST[task_status],$_POST[task_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'site_surveys':
			if($_POST[Submit]){
				$result_html = generate_site_surveys($_POST[cpe_type],$_POST[from],$_POST[to],$_POST[test_results],$_POST[assigned_to],$_POST[status],$_POST[leads]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'standard_package_billing':
			if($_POST[Submit]){
				$result_html = generate_standard_package_billing($_POST[measure_options],$_POST[billed_amount],$_POST[billed_times],$_POST[billed_total]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'report_users':
			$result_html = generate_user_report($_POST[username],$_POST[start],$_POST[number],$_POST[roleid],$_GET[action]);
			break;
		case 'user_roles':
			$result_html = generate_user_roles($_GET[action]);
			break;
		case 'user_info':
			$result_html = generate_change_user_info($_GET[action]);
			break;
		//Accounts views
		case 'invoices_revenue':
			if($_POST[Submit]){
				$result_html = generate_invoice_report($_POST[from], $_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'payments':
			if($_POST[Submit]){
				$result_html = generate_payments_report($_POST[from], $_POST[to], $_POST[account_id],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'adjustments':
			if($_POST[Submit]){
				$result_html = generate_adjustments_report($_POST[from], $_POST[to], $_POST[account_id],$_POST[adjustment_type],$_POST[proration]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'waiver_report':
			if($_POST[Submit]){
				$result_html = generate_waiver_report($_POST[from], $_POST[to], $_POST[account_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'accounts_financial_status':
			if($_POST[Submit]){
				$result_html = generate_accounts_financial_summary($_POST[to],$_POST[account_id],$_POST[cn_status]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'product_charges':
			if($_POST[Submit]){
				$result_html = generate_prod_charges_report($_POST[from],$_POST[to],$_POST[product]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'equipment_deposits':
			if($_POST[Submit]){
				$result_html = generate_equipment_deposits_report($_POST[from],$_POST[to],$_POST[account_ids],$_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'All_revenue':
			if($_POST[Submit]){
				$result_html = generate_all_revenue_report($_POST[from],$_POST[to],$_POST[account_id],$_POST[customer_types]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'projected_revenue':
			if($_POST[Submit]){
				$result_html = generate_projected_revenue_report($_POST[from],$_POST[to],$_POST[account_id],$_POST[cn_status]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'accounts_audit':
			if($_POST[Submit]){
				$result_html = generate_accounts_audit_report($_POST[from],$_POST[to],$_POST[account_id],$_POST[crm_user_id],$_POST[parent_id],$_POST[field_name]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'accounts_aging':
			if($_POST[Submit]){
				$result_html = generate_aging_report($_POST[from], $_POST[to], $_POST[account_id], $_POST[cn_status], $_POST[customer_types]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'new_accounts_aging':
			if($_POST[Submit]){
				$result_html = generate_new_aging_report($_POST[from], $_POST[to], $_POST[account_id], $_POST[cn_status], $_POST[customer_types]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'r.r_actual_revenue':
			if($_POST[Submit]){
				$result_html = generate_rr_revenue_report($_POST[month], $_POST[account_id], $_POST[customer_types],$_POST[service_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'broadband_revenue_by_month_per_account':
			if($_POST[Submit]){
			$result_html = generate_revenue_by_period_report($_POST[from], $_POST[to], $_POST[customer_types],$_POST[account_nos],$_POST[datagroup]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'new_aaa_crm_reconciliation':
			if($_POST[Submit]){
				$result_html = generate_new_aaa_crm_reconciliation();
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'sales_report_-_accounts':
			if($_POST[Submit]){
				$result_html = generate_sales_account_report($_POST[cn_status],$_POST[queues],$_POST[platform],$_POST[wimax_site]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'sales_report_-_leads':
			if($_POST[Submit]){
				$result_html = generate_sales_lead_report($_POST[from], $_POST[to], $_POST[queues],$_POST[leads_status],$_POST[platform]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'data_tax':
			if($_POST[Submit]){
				$result_html = generate_data_tax_report($_POST[from],$_POST[to],$_POST[account_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
	//End of Accounts views
		case 'repeat_cca_wrapups':
			if($_POST[Submit]){
				$result_html = generate_repeat_cca_wrapups($_POST[from],$_POST[to],$_POST[interval],$_POST[agent],$_POST[cat],$_POST[sub_cat],$_POST[subject],$_POST[msisdn],$_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'cca_wrapups':
			if($_POST[Submit]){
				$result_html = generate_cca_wrapups($_POST[from],$_POST[to],$_POST[agent],$_POST[cat],$_POST[sub_cat],$_POST[subject]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'crbt_wrapups':
			if($_POST[Submit]){
				$result_html = generate_crbt_wrapups($_POST[from],$_POST[to],$_POST[crbt_subjects]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'wrapups':
			if($_POST[Submit]){
				//print_r($_POST); echo "<br>"; //print_r($_GET);
				$result_html = generate_wrapups($_POST[from],$_POST[to],$_POST[report_type],$_POST[category],$_POST[subcategory],$_POST[subjects],$_POST[agents],$_POST[msisdns],$_POST[caller_groups]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'cases_handled_by_smt_team':
			if($_POST[Submit]){
				$result_html = generate_cases_handled_by_smt_team($_POST[from],$_POST[to],$_POST[smt_user],$_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'wrapups_cpc':
			if($_POST[Submit]){
				$result_html = generate_wrapups_cpc($_POST[from],$_POST[to],$_POST[wrapup_number],$_POST[subcat_number],$_POST[subject_type],$_POST[subbase]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'ivr_report':
			if($_POST[Submit]){
				$result_html = generate_ivr_report($_POST[from],$_POST[to],$_POST[queues],$_POST[report_type],$_POST[subbase]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'upsell_data':
			if($_POST[Submit]){
				//$from,$to,$report_type,$user,$msisdn,$service_charge,$product_type,$activation_from,$activation_to
				$result_html = generate_upsell_report($_POST[from],$_POST[to],$_POST[report_type],$_POST[agent],$_POST[msisdn],$_POST[service_charge],$_POST[product_type],$_POST[activation_from],$_POST[activation_to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'offnet_sales_values':
			if($_POST[Submit]){
				$result_html = generate_offnet_sales_report($_POST[from],$_POST[to],$_POST[report_type],$_POST[agent],$_POST[msisdn],$_POST[item_sold]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'upsale_crossale_commission':
			if($_POST[Submit]){
				$result_html = generate_upsale_crossale_commission_report($from = $_POST[from],$report_type = $_POST[report_type],$commission = $_POST[commission],$agents = $_POST[agents]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'telesales_agent_perfomance':
			if($_POST[Submit]){
				$result_html = generate_overall_commission_report($from = $_POST[from],$_POST[to],$_POST[perfomance_report_type],$commission = $_POST[commission]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'customer_knowledge':
			if($_POST[Submit]){
				$result_html = generate_cust_knowledge($_POST[from],$_POST[to],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'csat_follow_up':
			if($_POST[Submit]){
				$result_html = generate_csat_followup($_POST[from],$_POST[to],$_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'retention_customer_bio_data':
			if($_POST[Submit]){
				$result_html = generate_retention_customber_bio_data();
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'ussd':
			if($_POST[Submit]){
				$result_html = generate_ussd_report($from=$_POST[from],$to=$_POST[to],$ussd_service_code=$_POST[ussd_service_code],$complete_state = $_POST[complete_state],$end_state=$_POST[end_state],$period_grouping=$_POST[period_grouping]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'bc_sales_report':
			if($_POST[Submit]){
				$result_html = generate_sales_report($_POST[from], $_POST[to],$_POST[report_type],$_POST[business_centre], $_POST[items]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'telesales_summary_report':
			if($_POST[Submit]){
				$result_html = generate_telesales_summary($_POST[from], $_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'sms_csat':
			if($_POST[Submit]){
				$result_html = generate_wrapups_sms_csat_report($_POST[from], $_POST[to], $_POST[csat_evaluation_answer],$_POST[period_grouping], $_POST[show_msisdn_list]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'wrap_up_numbers':
			if($_POST[Submit]){
				$result_html = generate_msisdns($_POST[from], $_POST[to], $_POST[reporttype],$_POST[subcategory],$_POST[subject]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'service_outtage':
			if($_POST[Submit]){
				$result_html = generate_outage_service_list($_POST[from], $_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'expired_accounts':
			if($_POST[Submit]){
				$result_html = generate_expired_accounts($_POST[from], $_POST[to],$_POST[expiry_days]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'data_deleted_invoices':
			if($_POST[Submit]){
				$result_html = generate_deleted_invoices($_POST[from], $_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'data_equipment_report':
			if($_POST[Submit]){
				$result_html = generate_account_equipment($_POST[from], $_POST[to],$_POST[equip_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'prepaid_unpaid_accounts':
			if($_POST[Submit]){
				$result_html = generate_prepaid_unpaid_accounts($_POST[from], $_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'sms_feedback':
			if($_POST[Submit]){
				$result_html = generate_sms_feedback_report($_POST[from],$_POST[to],$_POST[msisdn],$_POST[status],$_POST[last_modified_by],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'correspondence':
			if($_POST[Submit]){
				$result_html = generate_correnspondence($_POST[from],$_POST[to],$_POST[report_type],$_POST[categories], $_POST[subjects], $_POST[agents], $_POST[msisdns], $_POST[wrap_up_sources]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'mmr':
			if($_POST[Submit]){
				$result_html = generate_mmr_report($_POST[period]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'ucc':
			if($_POST[Submit]){
				$result_html = generate_ucc_report($_POST[period]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'rentention_crbt_winback':
			if($_POST[Submit]){
				$result_html = generate_crbt_winback($_POST[from],$_POST[to],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'ivr_choices':
			if($_POST[Submit]){
				$result_html = generate_ivr_choices($_POST[from],$_POST[to],$_POST[reporttype],$_POST[last_option_groups],$_POST[msisdns]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'courier_delivery_report':
			if($_POST[Submit]){
				$result_html = generate_courier_delivery_report($_POST[from],$_POST[to],$_POST[reporttype],$_POST[company],$_POST[courier]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'telesales_commission_report':
			if($_POST[Submit]){
				$result_html = generate_telesales_commission_report($_POST[from],$_POST[to],$_POST[report_type],$_POST[agent],$_POST[msisdn],$_POST[item_sold]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'mis_ccba':
			if($_POST[Submit]){
				$result_html = generate_mis_ccba_report($_POST[month]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'cc_quality_eval':
			if($_POST[Submit]){
				$result_html = generate_cc_quality_eval_report($_POST[from],$_POST[to], $_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'retention_simreg_feedback':
			if($_POST[Submit]){
				$result_html = retention_simreg_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'warid_high_value_customers_feedback':
			if($_POST[Submit]){
				$result_html = warid_high_value_customers_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		
		case 'warid_and_airtel_sim_holder_feedback':
			if($_POST[Submit]){
				$result_html = warid_and_airtel_sim_holder_feedback_report($_POST[from],$_POST[to], $_POST[report_type], $_POST[wash_segment]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'hvild_hv_ild_upsell_feedback':
			if($_POST[Submit]){
				$result_html = hvild_hv_ild_upsell_feedback_report($_POST[from],$_POST[to], $_POST[report_type], $_POST[hvild_segment]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'kyc_common_imei_numbers_feedback':
			if($_POST[Submit]){
				$result_html = kyc_common_imei_numbers_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'telesales_sim_registration':
			if($_POST[Submit]){
				$result_html = telesales_sim_registration_feedback_report($_POST[from],$_POST[to], $_POST[report_type], $_POST[segment]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'bbz10_black_berry_z10':
			if($_POST[Submit]){
				$result_html = bbz10_black_berry_Z10_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'bk_beera_ko':
			if($_POST[Submit]){
				$result_html = bk_beera_ko_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'phc_phc_premier_health_check':
			if($_POST[Submit]){
				$result_html = phc_phc_premier_health_Check_feedback_report($_POST[from],$_POST[to], $_POST[report_type]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'microwave_off_accounts':
			if($_POST[Submit]){
				$result_html = generate_microwave_off_accounts($_POST[from],$_POST[to]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'bc_walkins':
			if($_POST[Submit]){
				$result_html = generate_walkins_report($_POST[from],$_POST[to],$_POST[reporttype],$_POST[camera_id],$_POST[business_centre_id]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'lead_account_onboarding':
			if($_POST[Submit]){
				$result_html = generate_enterprise_sales_report($_POST[from],$_POST[to],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'icr_icr_survey':
			if($_POST[Submit]){
				$result_html = generate_icr_icr_survey_report($_POST[from],$_POST[to],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
			
		case 'sr_report':
			if($_POST[Submit]){
				$result_html = generate_sr_report($_POST[to],$_POST[reporttype]);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		case 'wrapups_topx_report':
			if($_POST[Submit]){
				$result_html = generate_wrapups_topx_report($_POST[to],$_POST[reporttype],$top=5,$subject_type_filter);
				if($_POST[excel] != ''){
					generate_excel_file($result_html);
				}
			}
			break;
		default:
			break;
	}
	
	custom_query::select_db('reporting');
	$myreport = new report();
	$list = $myreport->GetList(array(array('reportname','=',$_GET[report])));
	$myreport = $list[0];
	
	if($myreport->name == ''){
		$myreport->name = 'Welcome to the CC Business Analysis reports';
	}
	
	$html = '
		<div style="font-size:14px; font-weight:bold; background-color:#009; color:#FFF; line-height:20px; padding-left:4px;" align="centre">'.
		ucwords($myreport->name)
		.'</div>
		';
		
	$html .= generate_form($_GET[report]);
	$html .= $result_html;

	return show_html($html,$menu = generate_accounts_links($_SESSION[access]), $footer = display_footer());
}

function show_html($body,$menu,$footer){

	custom_query::select_db('reporting');
	$myreport = new report();
	$list = $myreport->GetList(array(array('reportname','=',$_GET[report])));
	$myreport = $list[0];

	$Title = 'CC Business Analysis reports : '.$myreport->name;

return '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;
		font-size:9px;
		white-space:nowrap;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	
	
	body{
		font-family:Verdana, Geneva, sans-serif;
	}
	
	label,
	.select,
	.textbox{
		font-size:9px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	.th1 {
		font-weight: normal;
		text-align:center;
		vertical-align:top;
		background:#009;
		color:#FFF;
		font-size:9px;
		white-space:nowrap;
		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	.values{
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.white{
		background:#FFF;
	}
	
	.blue{
		background:#F0F0FF;
	}
	
	.faintred{
		background:#FFDDDD;
	}
	
	.sortable th{
		cursor:pointer;
	}
	
	.grand_titles{
		background:#ccc;
		color:#000;
		font-weight:bold;
	}
	
	.red_values{
		background-color: #AE0000;
		color:#FFF;
		text-align:right;
		font-size: 9px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.text_values{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		line-height:12px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.red_text_values{
		background-color: #AE0000;
		color:#FFF;
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		font-weight: bold;
		line-height:12px;
		white-space:nowrap;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		vertical-align:top;
	}
	
	.wrap_text{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:20%;
		vertical-align:top;
	}
	
	.wrap_text_task{
		vertical-align:top;
		text-align:left;
		padding-left:1px;
		font-size: 9px;
		border-bottom:#333333 1px dashed;
		border-right:#333333 1px dashed;
		width:10%;
	}
	
	.form_bar{
		background-color:#CCC;
		background-color:#00C;
	}
	
	form_td{
		white-space:nowrap;
	}
	
	.menu_link_active,
	.menu_link{
		font-size:9px;
		line-height:20px;
		border-bottom: #CCCCCC 1px dashed;
	}
	
	.menu_link_active,
	.menu_link:hover{
		display:inherit;
		background-color:#006;
		color:#FFF;
	}
	
	.menu_link_category{
		background-color:#666;
		font-size:12px;
		color:#FFF;
		padding:3px 0 3px 0;
	}
	
	.menu_link_active a{
		color:#FFF;
		text-decoration:none;
	}
	.menu_link a,.menu_link a:visited{
		color:#000;
		text-decoration:none;
	}
	
	.menu_link a:hover{
		color:#FFF;
	}
	
	.menu_link_active a:hover{
		font-weight:bold;
	}
	
	.menu_link_active:hover{
		display:inherit;
		background-color:#FF0000;
		/*color:#FFF;*/
	}
	
	.search{
		display:none;
		font-size: 9px;
	}
	.search_show{
		display:block;
	}
	
	tr#totals td{
		font-size:10px;
		font-weight:bold;
		background-color:#CCC;
	}
	
	img.graph{
		background-color:#000000;
		padding:5px;
	}
	
	tr.flagged{
		background-color:#FF0000;
	}
	
	tr.flagged td{
		color:#FFF;
		font-weight:bold;
	}
	
	tr.odd th,
	tr.even th{
		font-weight: normal;
		text-align:left;
		vertical-align:middle;
		color:#000;
		background:#E36C0A;
		white-space:nowrap;
		font-size:102%;
		line-height:120%;
	}
	
	.even{
		background:#C3CBD9;
	}
	
	.odd{
		background:#FFFFFF;
	}
	
	</style>
	<!-- Java -->
	<script type="text/javascript" src="includes/common/js/sigslot_core.js"></script>
	<script src="includes/common/js/base.js" type="text/javascript"></script>
	<script src="includes/common/js/utility.js" type="text/javascript"></script>
	<script type="text/javascript" src="includes/wdg/classes/MXWidgets.js"></script>
	<script type="text/javascript" src="includes/wdg/classes/MXWidgets.js.php"></script>
	<script type="text/javascript" src="includes/wdg/classes/Calendar.js"></script>
	<script type="text/javascript" src="includes/wdg/classes/SmartDate.js"></script>
	<script type="text/javascript" src="includes/wdg/calendar/calendar_stripped.js"></script>
	<script type="text/javascript" src="includes/wdg/calendar/calendar-setup_stripped.js"></script>
	
	<script src="includes/resources/calendar.js"></script>
	<script src="js/sort.js"></script>
	<script src="js/lib.js"></script>
	<script src="js/jquery-1.4.2.js"></script>
	
	<script language="javascript" type="text/javascript">
	function makeSelection(frm, id) {
      if(!frm || !id)
        return;
      targetElement = frm.elements[id];
      var handle = window.open(\'get_agent.php\');
    }
	
	function autoSubmit()
	{	
    var formObject = document.forms[\'form\'];
    formObject.submit();
	}

	function objectdiv(data){
	
	if(data==\'Accounts\'){
	
	document.getElementById(\'accounts\').className = \'search_show\';
	document.getElementById(\'leads\').className = \'search\';
	
	}else if(data==\'Leads\'){
	
	document.getElementById(\'accounts\').className = \'search\';
	document.getElementById(\'leads\').className = \'search_show\';
	
	}else {
	document.getElementById(\'accounts\').className = \'search\';
	document.getElementById(\'leads\').className = \'search\';
	}
	}
	</script>
	
	<link href="includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
	<title>'.$Title.'</title>
	</head>
	<body>
	<div>
		<!--BEGIN LEFT COL-->
		<div style="border: #CCCCCC 1px solid; float:left; width:10%; padding:0px 4px 0px 4px;">
		<div>
			'.$menu.'
		</div>
		</div>
		<!--END LEFT COL-->
		<!--BEGIN RIGHT COL-->
		<div style="float:right; width:88%; border: #CCCCCC 1px solid;">
		<div style="padding:4px;">
			'.$body.'
		</div>
		</div>
		<!--END RIGHT COL-->
	</div>
	<!--IMPORTANT--><div style="clear:both;"></div><!--IMPORTANT-->
	
	<div>'.$footer.'</div>
	
	</body>
	</html>
	';
}

function list_array($array,$line_spacer='br'){
	
	$spacers = array('br' => '<br>','cli' => '\n','hr' => '<hr>');
	
	$first_element = array_shift($array);
	
	if(is_array($first_element)){
		foreach($array as $key=>$value){
			$text .= $key." => ".list_array($value).$spacers[$line_spacer];
		}
	}else{
		$text = "[~".str_replace(array("\n\n","\n"),array($spacers[$line_spacer],$spacers[$line_spacer]),print_r($array,true))."~]".$spacers[$line_spacer];
	}
	
	return $text;
}

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'CCBA <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "To: ".$to." \r\n";
	$headers .= "From: ".$from."\r\n";
	$headers .= "Reply-To: ccbusinessanalysis@waridtel.co.ug \r\n";
	if($bcc !=''){
		$headers .= "BCC: ".$bcc." \r\n";
	}
	
    return mail($to,$subject,$message,$headers);
}

function my_print_r($array,$rows='20',$cols='80',$return=true){
	
	$output = '<pre>'.print_r($array,true).'</pre>';
	
	if($return == true){
		return $output;
	}else{
		echo $output;
	}
}

function row_style($counter){
	if($counter%2){ return 'even'; }else{ return 'odd'; }
}

function generate_uuid($prefix = ''){
	
    $chars = md5(uniqid(mt_rand(), true));
    $uuid  = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $prefix . $uuid;
  
}
?>