<?
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_PARSE | E_ERROR);

if(!isset($_SESSION)){
	session_start();
}

define('MAX_IDLE_TIME','600');

$_REQUEST['log_details']['source_browser'] = $_SERVER[HTTP_USER_AGENT];
if($_SESSION[username] != ''){
	$_REQUEST['log_details'][USER_NAME] = $_SESSION[username];
}else{
	$_REQUEST['log_details'][USER_NAME] = $_POST[username];
}
$_REQUEST['log_details']['source_ip'] = $_SERVER[REMOTE_ADDR];

require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
LOAD_RESOURCE('FTP');

//echo 'Part 1<br>';
require_once('authorise.php');
//echo 'Part 2<br>';
require_once('crm.php');
//echo 'Part 3<br>';
require_once('includes/dropdowns.php');
//echo 'Part 4<br>';
require_once('includes/includes.php');
//echo 'Part 5<br>';

show_the_way();

//echo generate_html();

?>