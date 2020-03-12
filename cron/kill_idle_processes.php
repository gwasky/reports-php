<?
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
require_once('/srv/www/htdocs/reports/cron/lib.php');
if(GetServerAddress() == '10.31.163.34'){
	ccba02_kill_idle_processes();
	ccba01_kill_idle_processes();
}else{
	ccba01_kill_idle_processes();
	ccba02_kill_idle_processes();
}

?>
