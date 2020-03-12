<?
ini_set('memory_limit','1600M');

function generate_form($report){
	
	$myreport = new report();
	
	//MX Widgets3 include
	require_once('includes/wdg/WDG.php');

	if($report){
		$html = '
			<table>
				<form id="form" name="form" method="post" action="index.php?report='.$_GET[report].'">
		';

		switch($report){
			case 'first_time_activation':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="activate_from" id="activate_from" value="'.$_POST[activate_from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="activate_to" id="activate_to" value="'.$_POST[activate_to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
				';
				break;
		case 'first_time_billing':		
				$html .= '
					<td width="160">
						<label>Bill from: <input size="10" name="bill_from" id="bill_from" value="'.$_POST[bill_from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>Bill To: <input size="10" name="bill_to" id="bill_to" value="'.$_POST[bill_to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
				';
				break;
			case 'churn_date':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
					';
				break;
			case 'broadband_account_status':
				$html .= '
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
					<td>'.display_broadband_status_dropdown($_POST[broadband_status]).'</td>
					';
				break;
			case 'broad_band_cases':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
					<td>'.display_casestatus_dropdown($_POST[case_status]).'</td>
					<td>'.display_customer_type_dropdown($_POST[customer_types],'select').'</td>
					<td>'.display_subject_setting_dropdown($_POST[subject_setting]).'</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
				';
				break;
			case 'gsm_cases':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_gsm_case_subject_setting_dropdown($_POST[subject_settings]).'</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
			case 'attendance_records':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td><label>Shift:'.shift_drop_down($_POST[shifts]).'</label>
					</td><td><label>Supervisor:'.supervisor_drop_down($_POST[supervisors]).'</label></td>
					</td><td><label>Teams:'.team_drop_down($_POST[teams]).'</label></td>
					';
				break;
			case 'absentism_summary_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td><label>Shift:'.shift_drop_down($_POST[shifts]).'</label>
					</td><td><label>Supervisor:'.supervisor_drop_down($_POST[supervisors]).'</label></td>
					</td><td><label>Teams:'.team_drop_down($_POST[teams]).'</label></td>
					';
				break;
			case 'daily_attendace_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td><label>Shift:'.shift_drop_down($_POST[shifts]).'</label>
					</td><td><label>Supervisor:'.supervisor_drop_down($_POST[supervisors]).'</label></td>
					</td><td><label>Teams:'.team_drop_down($_POST[teams]).'</label></td>
					';
				break;
			case 'detailed_attendace_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td><label>Shift:'.shift_drop_down($_POST[shifts]).'</label>
					</td><td><label>Supervisor:'.supervisor_drop_down($_POST[supervisors]).'</label></td>
					</td><td><label>Teams:'.team_drop_down($_POST[teams]).'</label></td>
					';
				break;
			case 'cca_attendance_by_data_team_supervisor':
				$html .= '
					<td width="160" valign="top">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160" valign="top">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td valign="top"><label>Shift:'.shift_drop_down($_POST[shifts]).'</label></td>
					<td valign="top">'.display_cca_agent_dropdown($_POST[agents], '').'</td>
					<td valign="top"><label>Supervisor:'.supervisor_drop_down($_POST[supervisors]).'</label></td>
					<td valign="top"><label>Teams:'.team_drop_down($_POST[teams]).'</label></td>
					';
				break;
			case 'sms_outlet_evaluation_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.franchises_dropdown($_POST[franchises]).'</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td>'.answers_dropdown($_POST[answers]).'
					</td>
					';
				break;
			case 'task_list':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					 <td>'.display_related_object_dropdown($_POST[related_object]).'</td>
					 <td>'.display_taskstatus_dropdown($_POST[status]).'</td>
					 <div><table><tr><td>'.display_parent_accounts_dropdown($_POST[account_id]).''.display_lead_dropdown('').'</td>
					  <td>'.task_type_drop_down($_POST[task_type]).'</td>
					 </tr>
					 </table></div>
					';
				break;
			case 'site_surveys':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label></td>
					<td>'.test_result_drop_down($_POST[test_results]).'</td>
					<td>'.cpe_type_drop_down($_POST[cpe_type]).'</td>
					<td>'.ss_status_drop_down($_POST[status]).'</td>
					<td>'.display_lead_dropdown($_POST[leads]).'</td>
					';
				break;
			case 'report_users':
				$html .= '
					<td width="160">
						<label>Username: <input size="10" name="username" id="username" value="'.$_POST[username].'" class="textbox" /></label>
					</td>
					<td width="160">
						<label>First record <input size="10" name="start" id="start" value="'.$_POST[start].'" class="textbox" /></label>
					</td>
					<td><label>Number of Records: <input size="10" name="number" id="number" value="'.$_POST[number].'" class="textbox" /></label></td>
					<td>'.display_user_role_dropdown($_POST[roleid]).'</td>
					';
				break;
			case 'standard_package_billing':
				$html .= '
					<td>'.measure_options_dropdown($_POST[measure_options]).'</td>
					<td>
						<label>No of times billed <input size="5" name="billed_times" id="billed_times" value="'.$_POST[billed_times].'" class="textbox" onblur="javascript:calculate_billed_total(\'billed_times\',\'billed_amount\')" /></label>
					</td>
					<td>
						<label>Monthly bill amount - VAT <input size="5" name="billed_amount" id="billed_amount" value="'.$_POST[billed_amount].'" class="textbox" onblur="javascript:calculate_billed_total(\'billed_times\',\'billed_amount\')" /></label>
					</td>
					<td>
						<label> OR Total - VAT<input size="11" name="billed_total" id="billed_total" value="'.$_POST[billed_total].'" class="textbox" /></label>
					</td>
					';
				break;
			case 'user_role':
				$html .= '';
				break;
//Accounts Views
			case 'invoices_revenue':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
				';
				break;
			case 'payments':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					<td>'.display_parent_accounts_dropdown().'</td>';
				break;
			case 'adjustments':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown().'</td>';
				break;
			case 'waiver_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown().'</td>';
				break;
			case 'charges':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_products_dropdown().'</td>
					<td>'.display_parent_accounts_dropdown().'</td>';
				break;
			case 'equipment_deposits':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>
						<label>Account Numbers <input size="50" name="account_ids" id="account_ids" value="'.$_POST[account_ids].'" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>';
				break;
			case 'accounts_financial_status':
				$html .= '
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_REQUEST[account_id]).'</td>
					<td>'.display_status_dropdown($_REQUEST[cn_status]).'</td>';
				break;
			case 'product_charges':
				$html .= '
					<td width="130">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="130">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.dropdown($label='Product', $name='product[]', $onchange_call, $selected=$_POST[product], $options=get_dataproduct_options(), $class='select', $size='5', $multiple=true).'</td>';
				break;			
			case 'All_revenue':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_customer_type_dropdown().'</td>
					<td>'.display_parent_accounts_dropdown().'</td>';
				break;
			case 'projected_revenue':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown().'</td>
					<td>'.display_status_dropdown().'</td>';
				break;
			case 'accounts_audit':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_REQUEST[account_id]).'</td>
					<td>'.display_field_dropdown($_REQUEST[field_name]).'</td>
					<td>'.display_crm_user_dropdown($_REQUEST[crm_user_id]).'</td>';
				break;
			case 'accounts_aging':
				$html .= '
					<td width="160">'.display_aging_period_dropdown($_POST[from]).'</td>
					<td width="160">
					<label>Up to: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
					<td>'.display_status_dropdown($_POST[cn_status]).'</td>
					<td>'.display_customer_type_dropdown($_POST[customer_types],'select').'</td>';
				break;
			case 'new_accounts_aging':
				$html .= '
					<td width="160">'.display_aging_period_dropdown($_POST[from]).'</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_parent_accounts_dropdown($_POST[account_id]).'</td>
					<td>'.display_status_dropdown($_POST[cn_status]).'</td>
					<td>'.display_customer_type_dropdown($_POST[customer_types],'select').'</td>
					';
				break;
			case 'r.r_actual_revenue':
				$html .= '
					<td width="160">
					<label>Month ending: <input size="10" name="month" id="month" value="'.$_POST[month].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_all_accounts_dropdown($_POST[account_id]).'</td>
					<td>'.display_customer_type_dropdown($_POST[customer_types],'select').'</td>
					<td>'.display_service_type_dropdown($_POST[service_type]).'</td>
					';
				break;
			case 'new_aaa_crm_reconciliation':
				$html .= '';
				break;
			case 'broadband_revenue_by_month_per_account':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>
					<label>Account Numbers <input size="50" name="account_nos" id="account_nos" value="'.$_POST[account_nos].'" class="textbox" /></label>
					</td>
					<td width="160">'.dropdown($label='Group', $name='datagroup[]', $onchange_call, $selected=$_POST[datagroup], $options=get_datagroup_options(), $class='select', $size='5', $multiple=TRUE).'</td>
					<td width="160">'.display_customer_type_dropdown($_POST[customer_types]).'</td>
				';
				break;
			case 'sales_report_-_accounts':
				$html .= '
					<td width="160">'.display_status_dropdown($_POST[cn_status]).'</td>
					<td width="160">'.display_queue_dropdown($_POST[queues]).'</td>
					<td width="160">'.display_platform_dropdown($_POST[platform]).'</td>
					<!--<td width="160">'.show_wimax_site_dropdown($_POST[wimax_site]).'</td>-->
					<td>
					<label>Wimax Site ID <input size="20" name="wimax_site" id="wimax_site" value="'.$_POST[wimax_site].'" class="textbox" /></label>
					</td>
				';
				break;
			case 'sales_report_-_leads':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">'.display_queue_dropdown($_POST[queues]).'</td>
					<td width="160">'.display_status_leads_dropdown($_POST[leads_status]).'</td>
					<td width="160">'.display_platform_dropdown($_POST[platform]).'</td>
					';					
					break;
			case 'data_tax':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_accounts_dropdown($_POST[account_id]).'</td>
				';
				break;
			case 'repeat_cca_wrapups':
				//$_POST[interval] = 1;
				$html .= '
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="12" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td>
						<label>MSISDNs: <input size="14" name="msisdn" id="msisdn" value="'.$_POST[msisdn].'" class="textbox" /></label>
					</td>
				';
				break;
			case 'crbt_wrapups':
				$html .= '
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="12" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_crbt_subjects_dropdown($_POST[crbt_subjects],'').'</td>
				';
				break;				
			case 'wrapups':
				$html_js = '
					<script language="javascript" type="text/javascript">
					'.generate_wrapup_dependent_drop_down_javascript($form_name='form', $subcategory_input_name='subcategory', $subject_input_name='subjects').'
					</script>
				';
				
				//echo my_print_r($_POST,'15','100');
				
				$html .= $html_js.'
					<td>'.display_wrap_up_data_source_drop_down($_POST[wrapup_datasource]).'</td>
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="12" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td >
						<label>MSISDNs: <input size="12" name="msisdns" id="msisdns" value="'.$_POST[msisdns].'" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td>'.display_wrapup_caller_groups($_POST[caller_groups]).'</td>
					<td>
						<div id="categories">'.
							dropdown($label='Categories', $name='category', $onchange_call='javascript: subcategory_dropdown(this.options[this.selectedIndex].value);', $selected=$_POST[category], $options=get_category_options(), $class='select',$size='1',FALSE).'
						</div>
					</td>
					<td>
						<!--
						<div id="subcategories">
						';
						//dropdown($label='Subcategories', $name='subcategories[]', $onchange_call='', $selected=$_POST[subcategories], $options=get_subcategory_options(''), $class='select',$size='8',TRUE)
						
						if($_POST[subcategory] != ''){
							$default_subcategory_option = '<option value="'.$_POST[subcategory].'">'.$_POST[subcategory].'</option>';
						}else{
							$default_subcategory_option = '<option value="">Select Sub-Category</option>';
						}
																		
						$html .= '
						</div>
						-->
						<label>Sub Category : <br>
						<script type="text/javascript" language="JavaScript">
						document.write(\'<select name="subcategory" id="subcategory" class="select" onchange="javascript: subject_dropdown(this.options[this.selectedIndex].value);">'.$default_subcategory_option.'</select>\')
						</script>
						</label>
					</td>
					<td>
						<!--
						<div id="subjects">
						';
						//dropdown($label='Subjects', $name='subjects[]', $onchange_call='', $selected=$_POST[subjects], $options=get_wrapup_options('',''), $class='select',$size='8',TRUE)
						
						if($_POST[subjects] != ''){
							$default_subject_option = '<option value="'.$_POST[subjects].'">'.$_POST[subjects].'</option>';
						}else{
							$default_subject_option = '<option value="">Select Subject</option>';
						}
						
						$html .= '
						</div>
						-->
						<label>Subject : <br>
						<script type="text/javascript" language="JavaScript">
							 document.write(\'<select name="subjects" id="subjects" size="1" class="select">'.$default_subject_option.'</select>\')
						</script>
						</label>
					</td>
					
					<td>'.
						dropdown($label='Agents', $name='agents[]', $onchange_call='', $selected=$_POST[agents], $options=get_agents_options(), $class='select',$size='5',TRUE)
					.'</td>
				';
				break;	
			case 'cases_handled_by_smt_team':
				$html .= '
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="12" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_smt_user_dropdown($_POST[crbt_subjects],'').'</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
				';
				break;
			case 'wrapups_cpc':
				$html .= '
					<td width="160">
					<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="130">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_subject_type_dropdown($_POST[subject_type]).'</td>
					<td>
					<label>Subscriber Base : <input size="10" name="subbase" id="subbase" value="'.$_POST[subbase].'" class="textbox" /></label>
					</td>
					<td>
					<label>Wrap Up Number : <input size="10" name="wrapup_number" id="wrapup_number" value="'.$_POST[wrapup_number].'" class="textbox" /></label>
					</td>
					<td>
					<label>Subcategory Number : <input size="10" name="subcat_number" id="subcat_number" value="'.$_POST[subcat_number].'" class="textbox" /></label>
					</td>
					';
				break;
			case 'ivr_report':
				$html .= '
					<td width="160">
					<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="130">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_ivr_queue_dropdown($_POST[queues]).'</td>
					<td>
					<label>Subscriber Base : <input size="10" name="subbase" id="subbase" value="'.$_POST[subbase].'" class="textbox" /></label>
					</td>
					<td>'.display_ivr_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
			case 'upsell_data':
				$html .= '
					<td >
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td >
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>
						<label>From (Activation): <input size="10" name="activation_from" id="activation_from" value="'.$_POST[activation_from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>
					<label>To (Activation): <input size="10" name="activation_to" id="activation_to" value="'.$_POST[activation_to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_upsell_report_type_dropdown($_POST[report_type]).'</td>
					<td>
					</td>
					<td>'.display_upsell_product_type_dropdown($_POST[product_type]).'</td>
					<td>
					<label>Agent Name: <input type="text" size="20" name="agent" id="agent" value="'.$_POST[agent].'" onclick="makeSelection(this.form, \'agent\')"></label>
					</td>
					<td><label>MSISDN<input size="20" name="msisdn" id="msisdn" value="'.$_POST[msisdn].'" class="textbox" /></label></td>
					<td><label>Service Charge<input size="20" name="service_charge" id="service_charge" value="'.$_POST[service_charge].'" class="textbox" /></label></td>';
				break;
			case 'offnet_sales_values':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_telesales_report_type_dropdown($_POST[report_type]).'</td>
					<td>
					<label>Agent Name: <input type="text" size="20" name="agent" id="agent" value="'.$_POST[agent].'" onclick="makeSelection(this.form, \'agent\')"></label>
					</td>
					<td><label>MSISDN<input size="20" name="msisdn" id="msisdn" value="'.$_POST[msisdn].'" class="textbox" /></label></td>
					<td>'.display_items_dropdown($_POST[item_sold]).'</td>';
				break;
			case 'upsale_crossale_commission':
				$html .= '
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>

					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					
					<!--<td>
						<label>GPRS 4,000 Rating <input size="15" name="commission[gprs4k]" id="commission[gprs4k]" value="'.$_POST[commission][gprs4k].'" class="textbox" /></label>
					</td>
					<td>
						<label>GPRS 15,000 Rating <input size="15" name="commission[gprs15k]" id="commission[gprs15k]" value="'.$_POST[commission][gprs15k].'" class="textbox" /></label>
					</td>
					<td>
						<label>GPRS 20,000 Rating <input size="15" name="commission[gprs20k]" id="commission[gprs20k]" value="'.$_POST[commission][gprs20k].'" class="textbox" /></label>
					</td>
					<td>
						<label>GPRS 15,000 Rating <input size="15" name="commission[gprs25k]" id="commission[gprs25k]" value="'.$_POST[commission][gprs25k].'" class="textbox" /></label>
					</td>
					<td>
						<label>GPRS 60,000 Rating <input size="15" name="commission[gprs60k]" id="commission[gprs60k]" value="'.$_POST[commission][gprs60k].'" class="textbox" /></label>
					</td>-->
					<td>
						<label>Agent <input size="20" name="agents" id="agents" value="'.$_POST[agents].'" class="textbox" /></label>
					</td>
					
					';
				break;
			case 'telesales_agent_perfomance':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_perfomance_dropdown($_POST[perfomance_report_type]).'</td>';
				break;
			case 'customer_knowledge':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.customer_knowledge_dropdown($_POST[reporttype]).'</td>';
				break;
			case 'csat_follow_up':
				$html .= '
					<td width="160">
						<label>Follow up date: From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>Follow up date: To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>';
				break;
			case 'retention_customer_bio_data':
				$html .= '';
				break;
			case 'ussd':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_ussd_service_code_dropdown($_POST[ussd_service_code]).'</td>
					<td>'.display_ussd_complete_state_dropdown($_POST[complete_state]).'</td>
					<td>'.display_period_dropdown($_POST[period_grouping]).'</td>
				';
					break;
			case 'bc_sales_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>'.display_report_type_dropdown($_POST[report_type]).'</label>
					</td>
					<td width="160">
					<label>'.dropdown($label='Business Centre', $name='business_centre', $onchange_call, $selected=$_POST[business_centre], $options=get_business_centre_options(), $class='select', $size=1, $multiple=false).'</label>
					</td>
					<td width="160">
					<label>'.dropdown($label='Item', $name='items[]', $onchange_call, $selected=$_POST[items], $options=get_business_centre_sales_item_options(), $class='select', $size=5, $multiple=true).'</label>
					</td>
					';
				break;
			case 'telesales_summary_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					';
				break;
			case 'sms_csat':
				$html .= '
					<td>'.display_csat_data_source_drop_down($_POST[csat_data_source]).'</td>
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_wrapups_sms_csat_evaluation_dropdown($_POST[csat_evaluation]).'</td>
					<td>'.display_period_dropdown($_POST[period_grouping]).'</td>
					<td width="120">
						<label>Show MSISDNs<input type="checkbox" name="show_msisdn_list" id="show_msisdn_list" '; if($_POST[show_msisdn_list]) { $html .= ' checked="checked" '; } $html .= ' /></label>
					</td>
					';
				break;
			case 'wrap_up_numbers':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.wrap_up_no_dropdown($_POST[reportype]).'</td>
					<td>'.display_subcategory_dropdown($_POST[subcategory]).'</td>';
						if(isset($_POST["subcategory"])){ 
							$subcategory = $_POST["subcategory"]; 
							//echo $subcategory;
						}
					$html .= '
					<td>'.display_subject_dropdown($_POST[subject]).'</td>
					';
				break;
			case 'service_outtage':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>';
				break;
			case 'expired_accounts':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					
					<td><label>Days 2 Expiry</label>'.displayDaysToExpire_box($_POST[expiry_days]).'</td>';
				break;
			case 'data_deleted_invoices':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>';
				break;
			case 'data_equipment_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_equipment_type_dropdown($_POST[equip_type]).'</td>';
				break;
			case 'prepaid_unpaid_accounts':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					';
				break;
			case 'sms_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>MSISDN: <input size="10" name="msisdn" id="msisdn" value="'.$_POST[msisdn].'" class="textbox" /></label>
					</td>
					<td>'.display_sms_feedback_status($_POST[status]).'</td>
					<td>'.display_sms_feedback_agents($_POST[last_modified_by]).'</td>
					<td>'.display_sms_feedback_report_type_dropdown($_POST[reportype]).'</td>
					';
				break;
			case 'correspondence':
				$html .= '
					<td width="160">
						<label>From: <input size="12" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="12" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<!--
					<td >
						<label>MSISDNs: <input size="12" name="msisdns" id="msisdns" value="'.$_POST[msisdns].'" class="textbox" /></label>
					</td>
					-->
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td>'.
						dropdown($label='Wrap up Source', $name='wrap_up_sources[]', $onchange_call='', $selected=$_POST[wrap_up_sources], $options=get_wrapup_source_options(), $class='select',$size='4',TRUE).'
					</td>
					<td>
						<div id="categories">'.
							dropdown($label='Categories', $name='categories[]', $onchange_call='', $selected=$_POST[categories], $options=get_category_options(), $class='select',$size='5',TRUE).'
						</div>
					</td>
					<td>
						<div id="subjects">'.
						dropdown($label='Subjects', $name='subjects[]', $onchange_call='', $selected=$_POST[subjects], $options=get_wrapup_options('',''), $class='select',$size='5',TRUE)
					.'	</div>
					</td>
				';
				break;
			case 'mmr':
				$html .= '
					<td width="160">
						<label>Period: <input size="10" name="period" id="period" value="'.$_POST[period].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					';
				break;
			case 'ucc':
				$html .= '
					<td width="160">
						<label>Period: <input size="10" name="period" id="period" value="'.$_POST[period].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					';
				break;
			case 'rentention_crbt_winback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
			case 'ivr_choices':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>					</td>
					<td><label>MSISDN<input size="20" name="msisdns" id="msisdns" value="'.$_POST[msisdns].'" class="textbox" /></label></td>
					<td>'.display_ivr_choices_last_option_group($_POST[last_option_groups]).'</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
			case 'courier_delivery_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>					</td>
					<td>'.display_courier_report_companies($_POST[company]).'</td>
					<td>'.display_courier_report_courier($_POST[courier]).'</td>
					<td>'.display_courier_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
			case 'telesales_commission_report':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_telesales_commission_report_type_dropdown($_POST[report_type]).'</td>
					<td>
					<label>Agent Name: <input type="text" size="20" name="agent" id="agent" value="'.$_POST[agent].'" onclick="makeSelection(this.form, \'agent\')"></label>
					</td>
					<td><label>MSISDN<input size="20" name="msisdn" id="msisdn" value="'.$_POST[msisdn].'" class="textbox" /></label></td>
					';
				break;
			case 'microwave_off_accounts':
				$html .= '
					';
				break;
			case 'mis_ccba':
				$html .= '
					<td width="160">
						<label>Period: <input size="10" name="month" id="month" value="'.$_POST[month].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					';
				break;
			case 'cc_quality_eval':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="yyyy-mm-dd" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="yyyy-mm-dd" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
			case 'retention_simreg_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
				
			case 'kyc_common_imei_numbers_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
				
			case 'warid_high_value_customers_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
			
			case 'warid_and_airtel_sim_holder_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td width="160">
					'.display_warid_and_airtel_sim_holder_segement_dropdown($_POST[wash_segment]).'
					</td>
					';
				break;
				
			case 'hvild_hv_ild_upsell_feedback':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td width="160">
					'.display_hvild_hv_ild_upsell_feedback_segement_dropdown($_POST[wash_segment]).'
					</td>
					';
				break;
				
			case 'telesales_sim_registration':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					<td width="160">
					'.display_telesales_sim_registration_segement_dropdown($_POST[segment]).'
					</td>
					';
				break;
				
			case 'bbz10_black_berry_z10':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
				
			case 'bk_beera_ko':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
				
			case 'phc_phc_premier_health_check':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_report_type_dropdown($_POST[report_type]).'</td>
					';
				break;
				
			case 'microwave_off_accounts':
				$html .= '
					';
				break;
			case 'bc_walkins':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					<td width="160">
					<label>'.dropdown($label='Business Centre Camera', $name='camera_id', $onchange_call, $selected=$_POST[camera_id], $options=get_business_centre_camera_options_with_ids(), $class='select', $size=1, $multiple=false).'</label>
					</td>
					<td width="160">
					<label>'.dropdown($label='Business Centre', $name='business_centre_id', $onchange_call, $selected=$_POST[business_centre_id], $options=get_business_centre_options_with_ids(), $class='select', $size=1, $multiple=false).'</label>
					</td>
					';
				break;
			case 'lead_account_onboarding':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
			case 'icr_icr_survey':
				$html .= '
					<td width="160">
						<label>From: <input size="10" name="from" id="from" value="'.$_POST[from].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
				
			case 'sr_report':
				$html .= '
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_standard_report_type_dropdown($_POST[reporttype]).'</td>
					';
				break;
				
			case 'wrapups_topx_report':
				$html .= '
					<td width="160">
					<label>To: <input size="10" name="to" id="to" value="'.$_POST[to].'" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="'.$KT_screen_date_format.'" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" class="textbox" /></label>
					</td>
					<td>'.display_wrapups_topx_dropdown($_POST[reporttype]).'</td>
					';
				break;
			default:
				break;
		}

		$report_list = $myreport->GetList(array(array('reportname','=',$report)));
		$myreport = $report_list[0];
		unset($report_list);
		
		if(!in_array($_GET[report],array('user_info','user_roles'))){
			$html .= '
				<td width="120">
					<label>Generate Excel<input type="checkbox" name="excel" id="excel" '; if($_POST[excel]) { $html .= ' checked="checked" '; } $html .= ' /></label>
				</td>
				<td width="150">
					<input name="Submit" type="submit" id="button" value="Generate Report" />
				</td>
				<td>
					<input type="reset" name="Reset" id="button" value="Reset" />
				</td>
			';
		}
		
		$html .= '
			</form></table>
		';
	}
	return $html;
}

function generate_accounts_links($access){
	
	$myrole = new user_role();
	$myreport = new report();
	
	custom_query::select_db('reporting');
	
	$html = '
		<div class="menu_link">
			<a href="index.php?action=logout">Log OFF, '.$_SESSION[details][first_name].' '.$_SESSION[details][last_name].' <br>['.$_SESSION[username].']</a>
		</div>
		<div class="menu_link">
			<a href="http://reports.waridtel.co.ug/">Home Of Reports</a>
		</div>
	';
	
	foreach($access as $report_id=>$details){
		if($details[access] == 'yes'){
			$myreport->Get($report_id);
			if($myreport->status == 'Active') { $link_list[$myreport->category][$myreport->reportname] = $myreport->name; }
		}
	}
	
	foreach($link_list as $category=>$category_reports){
		$html .= '<div class="menu_link_category">'.ucwords($category).'</div>';

		foreach($category_reports as $reportname=>$name){
			
			$html .= '
				<div class="
			';
		
			if($reportname == $_GET[report]){$html .= 'menu_link_active';}else{$html .= 'menu_link';}
			
			$html .= '
						">
					<a href="index.php?report='.$reportname.'">'.$name.'</a>
				</div>
			';
		}
	}
	
	return $html;
}

function generate_excel_file($body){
	
	//$filename = urldecode($_GET['filename']).".xls";
	$filename = $_GET[report]."_".date('YmdHis').".xls";
	// required for IE, otherwise Content-disposition is ignored
	if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
	
	# This line will stream the file to the user rather than spray it across the screen
	header("Content-type: application/vnd.ms-excel");
	
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment;filename=".$filename);
	header("Expires: 0");
	header("Cache-Control: private");
	session_cache_limiter("public");
	
	$xls = '
		<head>
			<meta http-equiv="Content-Type">
			<style type="text/css">
			
			th {
				font-weight: bold;
				font-size: 10px;
			}
			
			body{
				font-size: 10px;
			}
			
			</style>
		</head>
		<body>
		'.$body.'
		</body>
		';
		
		echo $xls; 
		exit;
}

//LOAD THE INDIVIDUAL REPORTS
require_dir_nest("report_modules");
?>