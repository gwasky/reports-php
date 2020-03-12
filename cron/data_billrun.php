<?
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR);

set_time_limit(0);
ini_set('memory_limit','1024M');

$ccba_list = 'Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>; Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>';
$server_ips[live] = array('ugkpdev03.waridtel.co.ug eth1'=>'10.31.7.7','wimaxcrm.waridtel.co.ug'=>'10.31.7.24','ugkpdev03.waridtel.co.ug eth0'=>'10.31.140.31');
$server_ips[dev] = array('ccba01.waridtel.co.ug'=>'10.31.8.17');

//$selected = 'dev';
$selected = 'live';
$ip = getServerAddress();
$selected_ips = $server_ips[$selected];

echo date('Y-m-d H:i:s')." : Initiating Bill Run request from [".$selected." - ".$ip."] \n";

if($selected == 'live'){
	$root = '/opt/lampp/htdocs/wimaxcrm/';
	if(!include($root.'billing/control.php')){
		$error = date('Y-m-d H:i:s')." : You are running the [".$selected."] billrun script on a wrong IP [".$ip."]\n";
		exit($error);
	}
	
	echo date('Y-m-d H:i:s')." : Including [".$selected."] FILE - [".$root."billing/control.php] \n";
}elseif($selected == 'dev'){
	$root = '/srv/www/htdocs/wimaxcrm/';
	if(!include($root.'billing/control.php')){
		$error = date('Y-m-d H:i:s')." : You are running the [".$selected."] billrun script on a wrong IP [".$ip."]\n";
		exit($error);
	}
	$EXECUTE_HOST = 'wimaxcrm.waridtel.co.ug';
	echo date('Y-m-d H:i:s')." : Including [".$selected."] FILE - [".$root."billing/control.php] \n";
}else{
	$error = date('Y-m-d H:i:s')." : No selected platform to run the script. It should be live or dev\n";
	exit($error);
}

if(date('Y-m-d') == last_day(date('Y-m-d'))){
	if(in_array($ip,$selected_ips)){
		$_POST[HOST_CONFIG] = $selected."/".$ip;
		billrun_invoiceGeneration(date('Y-m-d'));
	}else{
		$body = "
		Dear CCBA Team
		<br><br>
		Date : ".date('Y-m-d H:i:s')."<br>
		Server IP : ".$ip."<br>
		Platform : ".$selected."
		<br><br>
		The returned IP [".$ip."] does not match any of the selected server IPs [".print_r($selected_ips,true)."].
		<br><br>
		Bill Run script.
		";
		
		sendHTMLemail($to=$ccba_list,$bcc='',$message=$body,$subject='Bill Run Error',$from='Data Billrun <ccnotify@waridtel.co.ug>');
		
		echo $body;
	}
}else{
	/*
	$body = "
		Dear CCBA Team
		<br><br>
		Date : ".date('Y-m-d H:i:s')."<br>
		Server IP : ".getServerAddress()."
		<br><br>
		Today [".date('Y-m-d')."] is not the last day [".last_day(date('Y-m-d'))."] of the month.
		<br><br>
		Bill Run script.
	";
	
	sendHTMLemail($to=$ccba_list,$bcc='',$message=$body,$subject='Bill Run Log',$from='Data Billrun <ccnotify@waridtel.co.ug>');
	
	echo $body;
	*/
}

function getServerAddress() {
    if(isset($_SERVER["SERVER_ADDR"])) {
    	return $_SERVER["SERVER_ADDR"];
	}else {
		// Running CLI
		if(stristr(PHP_OS, 'WIN')) {
			//  Rather hacky way to handle windows servers
			exec('ipconfig /all', $catch);
			foreach($catch as $line) {
			if(eregi('IP Address', $line)) {
				// Have seen exec return "multi-line" content, so another hack.
				if(count($lineCount = split(':', $line)) == 1) {
				list($t, $ip) = split(':', $line);
				$ip = trim($ip);
				} else {
				$parts = explode('IP Address', $line);
				$parts = explode('Subnet Mask', $parts[1]);
				$parts = explode(': ', $parts[0]);
				$ip = trim($parts[1]);
				}
				if(ip2long($ip > 0)) {
				echo 'IP is '.$ip."\n";
				return $ip;
				} else
				; // TODO: Handle this failure condition.
			}
			}
		} else {
			$ifconfig = shell_exec('/sbin/ifconfig eth0');
			preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
			return $match[1];
		}
    }
}

/*
//We are running a script not accessint this via the web
$_POST[HOST] = 'wimaxcrm.waridtel.co.ug';
echo "Running Bill run on ".$_POST[HOST]." \n";
//TO BE RUN FROM CCBA01 ON WIMAXCRM
$root = '/srv/www/htdocs/wimaxcrm/';

require_once($root.'billing/control.php');

if(date('Y-m-d') == last_day(date('Y-m-d'))){
	billrun_invoiceGeneration(date('Y-m-d'));
}
*/
?>