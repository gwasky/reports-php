<?
//error_reporting(E_ALL);
function require_dir_nest($directory,$except){
	if(!$except){$except = array();}
	$dir = opendir($directory);
	
	//echo "Dir is ".$directory." and Dir result is "; print_r($dir); echo "<br>";
	while(($file = readdir($dir)) !== false){
		$path = $directory."/{$file}"; 
		//echo  "Path is ".$path." <br>";
		if(!in_array($file,$except)){
			if(!is_dir($path) && (strlen($file) > 2) && (substr(strtolower($file), strlen($file) - 4) === '.php')){
				if(!include_once $path){
					echo "Failed to include ".$path." <br>";
				}
			}elseif(is_dir($path)){
				if(($file != '.') && ($file != '..') && ($file != '_notes')){
					//echo "entering level 2 for [".$path."] <br>";
					require_dir_nest($path,'');
				}
			}
		}
	}
	closedir($dir);
}

function display_login_form($error){

	$html = '
		<html>
		<head>
		<title>Customer Care Reports Login</title>

	<style type="text/css">
	th {
		font-weight: normal;
		text-align:left;
		vertical-align:top;
		background:#009;
		color:#FFF;

		border-right:#CCC 1px solid;
		border-bottom:#CCC 1px solid;
		padding:2px;
	}
	
	td.notice{
		background-color:#009900;
		color:#FFFFFF;
		/*font-weight:bold;*/
		font-size:100%;
	}
	
	td.error{
		background-color:#FF0000;
		color:#FFFFFF;
		/*font-weight:bold;*/
		font-size:100%;
	}
	
	body,
	.select,
	.textbox{
		font-size:9px;
		font-family:Verdana, Geneva, sans-serif;
	}
	
	td.white{
		background:#fff;
	}
	
	td.blue{
		background:#F0F0FF;
	}

	
	form_td{
		white-space:nowrap;
	}
	
	</style>

		</head>
		<body>
			<form action="index.php?action=login" method="POST">
			<table align="center" border="0" cellspacing="0" cellpadding="2" style="border: #CCCCCC 1px solid; padding:10px;">
			<tr><td>
			<table align="center" border="0" cellspacing="0" cellpadding="2" style="border: 0px;">
			<tr>
				<th colspan = "2">CC Business Analysis Reports</td>
			</tr>		
	';
	if($error['notice']){ 
		$html .= '
			<tr>
				<td colspan = "2" class="notice">'.$error['notice'].'</td>
			</tr>';
	  }
	  
	if($error["error"]){ 
		$html .= '
			<tr>
				<td colspan = "2" class="error">'.$error["error"].'</td>
			</tr>';
	}
	  
	  $html .= '
				<tr>
					<td colspan = "2"></td>
				</tr>';
				
	
				$html .= '
				<tr>
					<th>User Name</th><td><input name="username" /></td>
				</tr>
				<tr>
					<th>Password</th><td><input name="password" type="password"  /></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="submit"  /><input type="reset" value="clear"  /></td>
				</tr>
			</table>
			</table>
			</td></tr>
			</form>
		</body>
		</html>
	';
	
	echo $html;
	die();
}

function show_the_way(){
	
	$entry_urls = array('http://reports.waridtel.co.ug/','','http://reports.waridtel.co.ug/index.php?action=login','http://reports.waridtel.co.ug/index.php?action=logout');
	
	if($_SESSION['logged_in']=="YES"){
		//echo "Logged in <br>";
		//check idle time
		if(check_idle_time($_SESSION['last_action_time'])){
			//echo "Passed idle time <br>";
			check_simple_password();
			
			if($_REQUEST['action'] != 'logout'){
				if(authorise($_GET[report])){
					log_actions();
					echo generate_html();
				}else{
					$_REQUEST['log_details'][access] = "CANNOT VIEW ".$_GET[report];
					//echo "Authoisation failed <br>";
					unset($_GET);
					logout();
					display_login_form(array('error'=>$_REQUEST['log_details'][access]));
					//show_the_way();
				}
			}else{
				//echo "Intentional Logout <br>";
				$_REQUEST['log_details'][access] = 'you have logged out';
				logout();
				display_login_form(array('warning'=>$_REQUEST['log_details'][access]));
			}
		}else{
			//echo "Failed Idle time .. Logging out <br>";
			$_REQUEST['log_details'][access] = 'You have taken more than 10 minutes without any activity';
			logout();
			display_login_form(array('error'=>$_REQUEST['log_details'][access]));
			
		}
	}else{
		
		//echo "Not logged in <br>";
		
		if($_REQUEST['action'] == 'login'){
			//echo "Logging in <br>";
			//authenticate
			if(login($_POST['username'], $_POST['password'])){
				//echo "Authenticating <br>";
				//echo "Referer 2 is ".$_SERVER['HTTP_REFERER']."<br>";
				if(in_array($_SERVER['HTTP_REFERER'],$entry_urls)){
					//echo "Authentication passed <br>";
					show_the_way();
				}else{
					//echo "Authentication Failed  <br>";
					header("Location:".$_SERVER['HTTP_REFERER']);
				}
			}else{
				//echo "Log in!! <br>";
				display_login_form(array('error'=>'Wrong password/Username combination'));
			}
			//and call authorise
		}else{
			//call log in page
			display_login_form(array('warning'=>'Please Log in'));
		}
	}
}

function authorise($report_name){
	
	$myreport = new report();
	
	custom_query::select_db('reporting');
	
	$list = $myreport->GetList(array(array('reportname','=',$report_name)));
	$myreport = $list[0];
	
	//if((count($list) != 0) and ($_SESSION[access][$myreport->reportId][access] == 'yes') and ($myreport->status == 'Active')){
	
	//count($list) == 0 considers the logging in scenario.
	
	if((count($list) == 0) || ($_SESSION[access][$myreport->reportId][access] == 'yes')){
		return TRUE;
	}else{
		return FALSE;
	}
}

function login($loginusername, $loginpassword){
	
	$myuser = new report_user();
	$log = new report_action();
	
	custom_query::select_db('reporting');
	
	$inputpassword = encrypt($loginpassword,'md5');
	
	$usercheckarray = array(
		array('username','=',$loginusername),
		array('password','=',$inputpassword)
	);
	
	$users = $myuser->GetList($usercheckarray,'','','');
	$user = $users[0];

	if($user->username){
		//clear username and pass
		unset($_POST);
		$_SESSION[username]=$user->username;
		$_SESSION[logged_in]="YES";
		$_SESSION[id]=$user->report_userId;
		$user_role = $user->GetUser_role();
		$_SESSION[access] = unserialize($user_role->access);
		//ALLOW ACCESS FOR HOUSE KEEPING MODULES/REPORTS
		//ALLOW ACCESS TO MANAGE PASSWORD WITH ID 73
		$_SESSION[access][73][access] = 'yes';
		
		$_SESSION[details] = unserialize($user->details);
		$_SESSION[last_action_time] = date('Y-m-d H:i:s');
		
		//clearning action
		unset($_GET['action']);
		$_REQUEST[log_details][access] = 'successfull login for username '.$_SESSION[username];
		
		$log->report_userId = $_SESSION[id];
		$log->reportId = 29;
		$log->report_action($access_time=$_SESSION[last_action_time],serialize($_REQUEST[log_details]));
		$log->SaveNew();
		
		return true;
	}else{
		$usercheckarray = array(
			array('username','=',$loginusername)
		);
		$users = $myuser->GetList($usercheckarray,'','','');
		$user = $users[0];
		
		$_REQUEST[log_details][access] = 'UNsuccessfull login for username '.$loginusername;
		
		$log->report_userId = $user->report_userId;
		$log->reportId = 31;
		$log->report_action($access_time=date('Y-m-d H:i:s'),serialize($_REQUEST[log_details]));
		$log->SaveNew();
		
		unset($_REQUEST);
		return false;
	}
}

function check_idle_time($last_action_time){
	
	$idle_time = strtotime(date('Y-m-d H:i:s')) - strtotime($last_action_time);
	
	if($idle_time < MAX_IDLE_TIME){
		$_SESSION[last_action_time] = date('Y-m-d H:i:s');
		return true;
	}else{
		return false;
	}
}

function encrypt($text,$method){
	custom_query::select_db('reporting');
	
	if($method == ''){
		$method = 'md5';
	}
	
	$myquery = new custom_query();
	$result = $myquery->single("select ".$method."('$text') as encryptedtext;");
		
	return $myquery->Unescape($result[encryptedtext]); 
}

function logout(){
	log_actions();
	unset($_SESSION);
	session_destroy();
}

function log_actions(){

	$log = new report_action();
	$report = new report();
	
	custom_query::select_db('reporting');
	
	//echo "Logging ... <br>";
	
	$reports = $report->GetList(array(array('reportname','=',$_GET["report"])),'','','');
	$report = $reports[0];
	
	$log->SetReport($report);
	$log->report_userId = $_SESSION['id'];
	
	$_REQUEST['log_details']['source_ip'] = $_SERVER[REMOTE_ADDR];
	$_REQUEST['log_details']['SERVER_HTTP_REFERER']=$_SERVER[HTTP_REFERER];
	$_REQUEST['log_details']['SERVER_URL']='http://'.$_SERVER[SERVER_NAME].$_SERVER[REQUEST_URI];
	$_REQUEST['log_details']['SERVER_REQ_METHOD']=$_SERVER[REQUEST_METHOD];
	$_REQUEST['log_details']['CLIENT_IP'] = $_SERVER[REMOTE_ADDR];
	
	foreach($_GET as $key=>$value){
		$_REQUEST['log_details']['GET_'.$key]=$value;
	}
	foreach($_POST as $key=>$value){
		if(!in_array($key,array('old_pass','new_pass','retryped_new_pass','password'))){
			$_REQUEST['log_details']['POST_'.$key]=$value;
		}
	}
	
	$log->report_action($access_time=date('Y-m-d H:i:s'),serialize($_REQUEST['log_details']));

	$log->SaveNew();

}

function generate_user_report($username,$limit_start,$limit_num,$roleid,$action){
	
	$myuser = new report_user();
	$myrole = new user_role();
	$myquery = new custom_query();
	
	switch($action){
		case 'edit':
			return display_users_report($users='',$action);
			break;
		case 'save':
			$myuser->user_roleId = $_POST[roleid];
			
			$crm_query="
				SELECT
					users.user_name as username,
					users.user_hash as pass,
					users.first_name,
					users.last_name
				FROM
					users
				where
					users.user_name = '".$_POST[user][username]."'
			";
			
			custom_query::select_db('wimax');
			$crm_user = $myquery->single($crm_query);
			
			//print_r($crm_user);
			
			if($crm_user[username] == ''){
				$details = '';
				$email= '';
				$password = '1234';
				$myuser->report_user($username=$_POST[user][username], $password=$password, $email, $details);
			}else{	
				if(($_POST[user][password] == $user_details[password]) && (strlen($_POST[user][password]) >= 3)){
					$password = encrypt($_POST[user][password],'md5');
				}else{
					$password = $_POST[user][oldpass];
				}
				$details = serialize(array('first_name'=>ucwords(strtolower($crm_user[first_name])),'last_name'=>ucwords(strtolower($crm_user[last_name]))));
				$email = strtolower($crm_user[first_name]).'.'.strtolower($crm_user[last_name]).'@waridtel.co.ug';
				$myuser->report_user($username=$crm_user[username], $password=$crm_user[pass], $email, $details);
			}
			custom_query::select_db('reporting');
			//print_r($myuser);
			$myuser->Save();
			unset($myuser);
			
			return generate_user_report($username='',$limit_start='',$limit_num='',$roleid='',$action='');
			break;
		case 'update':
			//echo "Updating ... <br>";
			foreach($_POST[user] as $user_id=>$user_details){
				$myuser->report_userId = $user_id;
				$myuser->user_roleId = $_POST[roleid];
				if(($user_details[password] == $user_details[password2]) and (strlen($user_details[password]) >= 3)){
					$password = encrypt($user_details[password],'md5');
				}else{
					$password = $user_details[oldpass];
				}
				$details = serialize(array('first_name'=>ucwords(strtolower($user_details[first_name])),'last_name'=>ucwords(strtolower($user_details[last_name]))));
				$myuser->report_user($username=$user_details[username], $password, $email=$user_details[email], $details);
				if($user_details[username] != ''){
					$myuser->Save();
				}
			}
			unset($_POST[user],$_GET[action],$_POST[roleid]);
		default:
			$usercheckarray = array();
			if($username){
				array_push($usercheckarray,array('username','LIKE','%'.$username.'%'));
			}
			
			if($roleid){
				array_push($usercheckarray,array('user_roleid','=',$roleid));
			}
			
			if((trim($roleid) == '') && (trim($username) == '')){
				if(($limit_start != '') && ($limit_num != '')){
					$limit = $limit_start.','.$limit_num;
				}else{
					$limit = '20';
				}
			}else{
			}
			
			$users = $myuser->GetList($usercheckarray,'username',TRUE,$limit);
			
			foreach($users as &$user){
				$user->details = unserialize($user->details);
			}
			return display_users_report($users,$action);
		break;
	}
}

function display_users_report($users,$action){
	
	switch($action){
		case 'edit':
			$html = '
				<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%"> 
				<tr> 
					<td valign="top" colspan="3">
						<a href="index.php?report=report_users&action=edit">Get a New CRM User</a>
					</td>
				</tr>
				<tr> 
					<form id=user name=user action="index.php?report=report_users&action=save" method="post">
						<td valign="top">
							Username : <input name="user[username]" type="text" id="user[username]" value="" size="20" class="textbox" />
						</td>
						<td valign="top">
							'.display_user_role_dropdown('').'
						</td>
						<td valign="top">
							<input type="submit" name="get_user" id="get_user" value="Get User from CRM" class="textbox" />
							<input type="reset" name="clear_user" id="clear_user" value="Clear" class="textbox" />
						</td>
					</form>
				</tr>
				</table>
			';
			break;
		case 'update':
		default:	
			$html = '
				<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
				<tr> 
					<td valign="top" colspan="8">
						<a href="index.php?report=report_users&action=edit">Get a New CRM User</a>
					</td>
				</tr>
				<tr> 
					  <th>Username</th>
					  <th>First Name</th>
					  <th>Last Name</th>
					  <th>Email</th>
					  <th>Password</th>
					  <th>Role</th>
					  <th>Access Details</th>
					  <th>Update</th>
				</tr>
			';
			foreach($users as $user){
				$html .= '
					<tr> 
						<form id=user name=user action="index.php?report=report_users&action=update" method="post">
						<td valign="top">
							<input type="hidden" id="user['.$user->report_userId.']" name="user['.$user->report_userId.']" value="'.$user->report_userId.'" class="textbox" />
							<input type="hidden" id="user['.$user->report_userId.'][oldpass]" name="user['.$user->report_userId.'][oldpass]" value="'.$user->password.'" class="textbox" />
							<input name="user['.$user->report_userId.'][username]" type="text" id="user['.$user->report_userId.'][username]" value="'.$user->username.'" size="20" class="textbox" />
						</td>
						<td valign="top"><input name="user['.$user->report_userId.'][first_name]" type="text" id="user['.$user->report_userId.'][first_name]" value="'.$user->details[first_name].'" size="20" class="textbox" /></td>
						<td valign="top"><input name="user['.$user->report_userId.'][last_name]" type="text" id="user['.$user->report_userId.'][last_name]" value="'.$user->details[last_name].'" size="20" class="textbox" /></td>
						<td valign="top"><input name="user['.$user->report_userId.'][email]" type="text" id="user['.$user->report_userId.'][email]" value="'.$user->email.'" size="40" class="textbox" /></td>
						<td valign="top">
							<table border="0" cellpadding="2" cellspacing="0" class="sortable" width="100%">
								<tr>
									<td valign="top">New password</td>
									<td valign="top"><input name="user['.$user->report_userId.'][password]" type="password" id="user['.$user->report_userId.'][password]" value="" size="20" class="textbox" /></td>
								</tr>
								<tr>
									<td valign="top">Type it again </td>
									<td valign="top"><input name="user['.$user->report_userId.'][password2]" type="password" id="user['.$user->report_userId.'][password2]" value="" size="20" class="textbox" /></td>
								</tr>
							</table>
						</td>
						<td valign="top">'.display_user_role_dropdown($user->user_roleId).'</td>
						<td valign="top"><a href="index.php?report=user_roles&action=view_role&report_userId='.$user->report_userId.'">'.$user->username.'\'s access Details</a></td>
						<td valign="top">
							<input type="submit" name="update_user" id="update_user" value="Update User" class="textbox" />
						</td>
						</form>
					</tr>
				';
			}
			$html .= '
				</table>
			';
			break;
			}
	
	return $html;
}

function generate_user_roles($action){
	
	$myuser = new report_user();
	$myrole = new user_role();
	$myreport = new report();
	
	custom_query::select_db('reporting');
	
	switch($action){
		case 'edit':
			$myrole->Get($_GET[id]);
			$myrole->access = unserialize($myrole->access);
			return display_user_roles($myrole,$action);
			break;
		case 'save':
			foreach($_POST[role][report] as $report_id=>$report_access){
				$access[$report_id][id] = $report_id;
				$access[$report_id][access] = $report_access[access];
				//$access[$report_id][name] = $report_access[name];
			}
			$myrole->user_roleId = $_POST[role][id];
			$myrole->user_role($_POST[role][name],$_POST[role][description],serialize($access));
			if($_POST[role][name] != ''){
				$myrole->Save();
			}
			unset($_GET[id]);
			return generate_user_roles('edit');
			break;
		case 'view_role':
			$myuser->Get($_GET[report_userId]);
			$myuser->details = unserialize($myuser->details);
			$myuser->user_role = $myuser->GetUser_role();
			$myuser->user_role->access = unserialize($myuser->user_role->access);
			return display_user_roles($myuser,$action);
			break;
		default:
			return display_user_roles($myrole->GetList(),$action);
			break;
	}
}

function yes_no_dd($report_id,$definition,$readonly){
	
	if($readonly == FALSE){
		/*$html = '
			<input name="role[report]['.$definition[id].'][name]" id="role[report]['.$definition[id].'][name]" type="hidden" value="'.$definition[name].'" />
		';*/
		$html = '<select name="role[report]['.$report_id.'][access]" size="1" id="role[report]['.$report_id.'][access]" class="select">
		';
		$html .= '<option value="no" ';if(($definition[access] == '')||($definition[access] == 'no')){ $html .= 'selected="selected"'; } $html .= '>NO</option>';
		$html .= '<option value="yes" ';if($definition[access] == 'yes'){ $html .= 'selected="selected"'; } $html .= '>YES</option>';
		$html .= '</select>';
	}else{
		if($definition[access] != 'yes'){
			$html = 'NO';
		}else{
			$html = strtoupper($definition[access]);
		}
	}
	
	return $html;
}

function display_user_roles($data,$action){
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%"> 
			<tr> 
				<td colspan="4"><a href="index.php?report=user_roles&action=edit">Add a New role</a></td>
			</tr>
		</table>
	';
	
	switch($action){
		case 'edit':
			$html .= '
				<table border="0" cellpadding="2" cellspacing="0" width="100%">
					<form id="role[]" name="role[]" method="post" action="index.php?report=user_roles&action=save">
					<tr>
						<td></td>
						<td>
							<input name="role[Submit]" type="Submit" id="button" value="Save Role" class="textbox" />
							<input name="role[Reset]" type="Reset" id="button" value="Clear" class="textbox" />
						</td>
					</tr>
					<tr>
						<th>Role Name</th>
						<td>
							<input name="role[id]" id="role[id]" type="hidden" value="'.$data->user_roleId.'" />
							<input name="role[name]" id="role[name]" type="text" value="'.$data->name.'" size="50" class="textbox" />
						</td>
					</tr>
					<tr>
						<th>Role Description</th>
						<td><textarea name="role[description]" id="role[description]" cols="50" rows="5"  class="textbox" >'.$data->description.'</textarea></td>
					</tr>
					<tr>
						<th>Access Definitions</th>
						<td>'.display_role_definition_html($data->access,FALSE).'</td>
					</tr>
					</form>
				</table>
			';
			break;
		case 'view_role':
			$html = '
				<table border="0" cellpadding="2" cellspacing="0" width="100%">
					<tr>
						<th>User</th>
						<td class="text_values">'.$data->username.'</td>
					</tr>
					<tr>
						<th>Role Name</th>
						<td class="text_values">'.$data->user_role->name.'</td>
					</tr>
					<tr>
						<th>Role Description</th>
						<td class="text_values">'.$data->user_role->description.'</td>
					</tr>
					<tr>
						<th>Access Definitions</th>
						<td>'.display_role_definition_html($data->user_role->access,TRUE).'</td>
					</tr>
					</form>
				</table>
			';
			break;
		case 'save':
		default:
			$html .= '
				<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable"> 
					<tr> 
						  <th></th>
						  <th>Role Name</th>
						  <th>Description</th>
						  <th>Edit Access Definition</th>
					</tr>
			';
			
			foreach($data as $row){
				$html .= '
					<tr>
						<td class="text_values">'.++$i.'</td>
						<td class="text_values">'.$row->name.'</td>
						<td class="text_values">'.$row->description.'</td>
						<td class="text_values"><a href="index.php?report=user_roles&action=edit&id='.$row->user_roleId.'">Edit '.$row->name.'\'s Access</a></td>	
					<tr>
				';
			}
			
			$html .= '
				</table>
			';
			break;
	}
	
	return $html;
}

function display_role_definition_html($access,$readonly){

	$myreport = new report();
	
	custom_query::select_db('reporting');

	foreach($access as $report_id=>$report_access){
		$report = $myreport->Get($report_id);
		$access[$report_id][access] = $report_access[access];
		$access[$report_id][id] = $report_id;
		$access[$report_id][name] = $report->name;
		unset($report);
	}
	
	$reports = $myreport->GetList(array(array('type','NOT LIKE','authent%')));
	//$reports = $myreport->GetList();

	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" class="sortable">
			<tr> 
				  <th></th>
				  <th>Report Name</th>
				  <th>Report Description</th>
				  <th>Role\'s Access</th>
			</tr>
	';

	foreach($reports as $report){
		$html .= '
			<tr>
				<td class="text_values">'.++$i.'</td>
				<td class="text_values">'.$report->name.'</td>
				<td class="text_values">'.$report->details.'</td>
				<td class="text_values">'.yes_no_dd($report->reportId,$access[$report->reportId],$readonly).'</td>
			</tr>
		';
	}

	$html .= '
		</table>
	';
	
	return $html;
}

function display_user_role_dropdown($roleid){
	
	$myrole = new user_role();
	
	custom_query::select_db('reporting');
	
	$roles = $myrole->GetList();
	
	$html = '<select name="roleid" size="1" id="roleid" class="select">';
	$html .= '<option value="" ';if($roleid ==''){$html .= 'selected="selected"';} $html .='>SELECT A ROLE</option>';
	foreach($roles as $role){
		$html .= '<option value="'.$role->user_roleId.'" ';if($roleid == $role->user_roleId){$html .= 'selected="selected"';} $html .='>'.$role->name.'</option>';
	}
	$html .= '</select>';
	
	return $html;
}

function generate_change_user_info($action){
	
	//echo list_array($_SERVER,'br')."<br>";
	
	$myuser = new report_user();
	
	//GET CURRENT USER DETAILS
	$myuser->Get($_SESSION[id]);
	$myuser->details = unserialize($myuser->details);
	
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="100%" style="font-size:12px;"> 
			<tr> 
				<td colspan="4"><lable>NAME : '.$myuser->details[first_name].' '.$myuser->details[last_name].', USER NAME '.$myuser->username.', EMAIL ADDRESS '.$myuser->email.'</lable></td>
			</tr>
			<tr> 
				<td colspan="4" style="height:12px;"></td>
			</tr>
			<tr> 
				<td colspan="4" >IF YOU HAVE BEEN REDIRECTED TO THIS PAGE BY FORCE, THEN IT MEANS YOU NEED TO CHANGE YOUR PASSWORD AND OR INCLUDE A VALID EMAIL ADDRESS.</td>
			</tr>
			<tr> 
				<td colspan="4" style="height:12px;"></td>
			</tr>
			<tr> 
				<td colspan="4"><a href="index.php?report=user_info&action=change">Change Password</a> [Type in a new password for your CCBA reports Access]</td>
			</tr>
			<tr> 
				<td colspan="4"><a href="index.php?report=user_info&action=get_crm_pass">Get DATA CRM Password</a> [Make CCBA Reports allow you to use your DATA CRM Password]</td>
			</tr>
			<tr> 
				<td colspan="4" style="height:12px;"></td>
			</tr>
			<tr>
				<td colspan="4">
	';
	
	switch($action){
		case 'change':
			$html .= '
				<table border="0" cellpadding="1" cellspacing="0" width="100%">
					<form id="user_info[]" name="user_info[]" method="post" action="index.php?report=user_info&action=save">
					<tr>
						<td>
							<label>New Email Address * : <input name="user_info[email]" type="text" id="user_info[email]" size="30" value="'.$myuser->email.'" class="textbox" /></label>
						</td>
						<td>
							<label>Current Password: <input name="user_info[old_pass]" type="password" id="user_info[old_pass]" value="" class="textbox" /></label>
						</td>
						<td>
							<label>New Password: <input name="user_info[new_pass]" type="password" id="user_info[new_pass]" value="" class="textbox" /></label>
						</td>
						<td>
							<label>Retype New Password: <input name="user_info[retryped_new_pass]" type="password" id="user_info[retryped_new_pass]" value="" class="textbox" /></label>
						</td>
						<td>
							<input name="user_info[Submit]" type="Submit" id="user_info[Submit]" value="Update User Info" class="textbox" />
							<input name="user_info[Reset]" type="Reset" id="user_info[Reset]" value="Clear Form" class="textbox" />
						</td>
					</tr>
					</form>
				</table>
			';
			break;
		case 'save':
			$html .= change_user_info($_POST[user_info]);
			break;
		case 'get_crm_pass':
			$html .= update_password_with_data_crm_pass();
			break;
	}
	
	$html .= '
			</td>
		</table>
	';
	
	return $html;
}

function change_user_info($change_pass_form){
	
	$myuser = new report_user();
	custom_query::select_db('reporting');
	
	//GET CURRENT USER DETAILS
	$myuser->Get($_SESSION[id]);
	$myuser->details = unserialize($myuser->details);

	if($myuser->email != trim($change_pass_form[email]) and trim($change_pass_form[email]) != ''){
		$old_email = $myuser->email;
		$myuser->email = trim($change_pass_form[email]);
		
		$html .= '<hr>You email has been changed from '.$old_email.' to '.$myuser->email.' successfully.<hr>';
		
		$message .= '<hr>MAIL ADDRESS CHANGE<hr>Some one logged in as '.$_SESSION[username].' changed the email addresss associated with ('.$_SESSION[username].') from '.$old_email.' to '.$myuser->email.'<hr>';
		
		$update = true;
	}

	if(trim($change_pass_form[old_pass]) != '' and trim($change_pass_form[new_pass]) != '' and trim($change_pass_form[retryped_new_pass]) != ''){
		if($myuser->password != encrypt($change_pass_form[old_pass])){
			
			//echo print_r($change_pass_form,true)." and ".print_r($myuser,true)."<br>";
			
			$html .= '<hr>('.ucfirst($_SESSION[username]).'), you filled in an incorrect current password.<hr>';
			
			$cleaned_logs = sensitive_clean($_REQUEST[log_details],array('old_pass','new_pass','retryped_new_pass'));
			$message .= '
			<p>
			Some one using your username ('.$_SESSION[username].') logged in session in the <a href="http://reports.waridtel.co.ug/">CCBA reports</a> is trying to change your password but they filled in a wrong old password
			</p>
			<p>
			If it was not you who innitiated this action, Let CCBA ccbusinessanalysis@waridtel.co.ug know with the copy of this mail.
			</p>
			<hr>FOR ADMINISTRATOR USE ONLY <BR>'.list_array($cleaned_logs,'br').'';
		}elseif(strlen(trim($change_pass_form[new_pass])) <= 5 or trim($change_pass_form[new_pass]) == '12345'){
			$html .= '<hr>Your new password should be atleast 6 characters and should not be too simple<hr>';
		}elseif($change_pass_form[new_pass] != $change_pass_form[retryped_new_pass]){
			$html .= '<hr>Your retyped new typed password is not the same, Please try again<hr>';
		}elseif($myuser->password == encrypt($change_pass_form[new_pass])){
			$html .= "<hr>Your 'new' password is the same as the old one<hr>";
		}elseif($change_pass_form[new_pass] == $change_pass_form[retryped_new_pass]){
			$myuser->password = encrypt($change_pass_form[new_pass]);
			
			$html .= '<hr>You password has been changed successfully. You can now click on any view any of the reports on the left.<hr>';
			
			$cleaned_logs = sensitive_clean($_REQUEST[log_details],array('old_pass','new_pass','retryped_new_pass'));
			$message .= '<hr>PASSWORD CHANGE<hr>
			<p>
			Some one using your username ('.$_SESSION[username].') logged in the <a href="http://reports.waridtel.co.ug/">CCBA reports</a> and successfully changed your password.
			</p>
			<p>
			If it was not you who innitiated this action, Let CCBA ccbusinessanalysis@waridtel.co.ug know with the copy of this mail.
			</p>
			<hr>FOR CCBA ADMINISTRATOR USE ONLY <BR>'.list_array($cleaned_logs,'br').'';
			
			$update = true;
		}
	}
	
	if($update == true){
		$myuser->details = serialize($myuser->details);
		$id = $myuser->Save();
		
		if($id > 0){
			if($message != ''){
				sendHTMLemail($to=$myuser->email.','.$old_email,$bcc='ccbusinessanalysis@waridtel.co.ug',$message,$subject="CCBA REPORTS - ACCOUNT INFO UPDATE",$from='CCBA Reports [DO NOT REPLY] <ccnotify@waridtel.co.ug>');
			}
		}else{
			$html = 'UPDATE FAILED FOR '.$myuser->details[first_name].' '.$myuser->details[last_name].' ('.$_SESSION[username].')<BR><BR><BR><HR>'.$html.'<HR>'.$message.'<HR>';
			sendHTMLemail($to='ccbusinessanalysis@waridtel.co.ug',$bcc='',$message,$subject="CCBA REPORTS - UPDATE FAILED",$from='CCBA Reports [DO NOT REPLY] <ccnotify@waridtel.co.ug>');
		}
	}else{
		//$html = 'No changes to make';
	}
	
	return $html;
}

function update_password_with_data_crm_pass(){
	$myuser = new report_user();
	$myquery = new custom_query();
	
	$crm_query="
		SELECT
			users.user_hash as pass
		FROM
			users
		WHERE
			users.user_name = '".$_SESSION[username]."'
		LIMIT 1
	"; 
	
	$crm_user = $myquery->single($crm_query,'wimax');
	
	custom_query::select_db('reporting');
	$myuser->Get($_SESSION[id]);

	$old = $myuser->password; 
	if($crm_user[pass] != ''){
		$myuser->password = $crm_user[pass];
		
		$id = $myuser->Save();
		if($id != ''){
			$html = 'Your CCBA Reports Password is now the same as your DATA CRM Password';
		}else{
			$html = 'While trying to get your ['.$_SESSION[id].' - '.$_SESSION[username].'] CRM Password, Something went wrong .... Contact CC Business Analysis Team with a screen shot of this page for assistence ...';
		}
	}else{
		$html = 'You do not have DATA CRM Password, please <a href="http://reports.waridtel.co.ug/index.php?report=user_info&action=change">Just change your password</a>';
	}
	
	return $html;
}

function sensitive_clean($input,$clear=array('old_pass','new_pass','retryped_new_pass','password')){
	
	foreach($input as $key=>$value){
		if(in_array($key,$clear)){
			$output[$key] = 'ENCRYPTED';
		}elseif(is_array($value)){
			$output[$key] = sensitive_clean($value,$clear);
		}else{
			$output[$key] = $value;
		}
	}
	
	return $output;
}

function check_simple_password(){
	
	$myuser = new report_user();
	custom_query::select_db('reporting');
	
	$myuser->Get($_SESSION[id]);
	if(($myuser->password == encrypt('1234') or $myuser->password == encrypt('12345') or $myuser->password == encrypt('123')) and $_GET[report] != 'user_info'){
		unset($_GET,$_POST);
		
		$_GET[report] = 'user_info';
		$_GET[action] = 'change';
	}
}
?>