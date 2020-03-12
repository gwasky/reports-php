<?
//RUN ON CCBA02
//error_reporting(E_ALL);
error_reporting(E_PARSE | E_ERROR);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
//error_reporting(E_ERROR);

//exit("ALERT!!! ->> This report is now called to run within the /srv/www/htdocs/ccportal/cron/gsm.cron.php file .... \n");

echo date('Y-m-d H:i:s')." : Business sales report started<br>\n";
require_once('/srv/www/htdocs/reports/cron/lib.php');

//$to_list = 'Jael Wawulira/Customer Service/Airtel Ug <jael.wawulira@ug.airtel.com>,Abhishek Mudgal/Customer Service/Airtel Africa <abhishek.mudgal@africa.airtel.com>,Arindam.Chakrabarty@ug.airtel.com, Bavo.Mzee@ug.airtel.com, Dapo.Olasope@ug.Airtel.com, Dennis.Kakonge@ug.Airtel.com, FlaviaNtambi.Lwanga@ug.airtel.com, Micheal.Walekwa@ug.Airtel.com, Nuhu.Kanyike@ug.airtel.com, Patrice.Namisano@ug.airtel.com, Prasoon.Lal@ug.airtel.com, Rajesh.Agrawal@ug.airtel.com, Somasekhar.VG@ug.airtel.com, Martin.Nahamya@ug.airtel.com, Joweria.Nabakka@ug.airtel.com, Ritah.Nakafero@ug.airtel.com, Daniel.Katatumba@ug.airtel.com, Ian.Mugambe@ug.airtel.com, Moses.Musiime@ug.airtel.com';

$to_list = 'Directors@ug.airtel.com, Martin.Nahamya@ug.airtel.com, Joweria.Nabaka@ug.airtel.com, Rita.Nakafero@ug.airtel.com, Daniel.katatumba@ug.airtel.com, Ian.Mugambe@ug.airtel.com, Moses.Musiime@ug.airtel.com, Paul.Emwodu@ug.Airtel.com, Josephine.Keihura@ug.Airtel.com, David.Tugume@ug.airtel.com, Christine.Baguma@ug.airtel.com, Maurine.Bwengye@ug.airtel.com, Nathan.Tumwebaze@ug.airtel.com';

//Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>
//$to_list = 'steven.ntambi@waridtel.co.ug,Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>';

//$cc_list = 'Flavia Kiggundu/Customer Service/Uganda <flavia.kiggundu@ug.airtel.com>, Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Nelson Migusha/Customer Service/Airtel Ug <Nelson.Mugisha@ug.airtel.com>, Samuel Mwanje/Customer Service/Uganda <Samuel.Mwanje@ug.airtel.com>, Macdavid Mugga/Customer Service/Uganda <macdavid.mugga@ug.airtel.com>, Manish Dashputre/Finance/Kampala <manish.dashputre@waridtel.co.ug>,Vincent Lukyamuzi/Customer Service/Uganda <Vincent.Lukyamuzi@ug.airtel.com>, Martin.Nahamya@ug.airtel.com, Joweria.Nabakka@ug.airtel.com, Ritah.Nakafero@ug.airtel.com, Daniel.Katatumba@ug.airtel.com, Ian.Mugambe@ug.airtel.com, Moses.Musiime@ug.airtel.com';
//$cc_list = 'Vincent Lukyamuzi <vincent.lukyamuzi@waridtel.co.ug>';
$cc_list = 'RetailSupervisors@ug.airtel.com, AreaServiceManagers@ug.airtel.com, Bright.Twebaze@ug.airtel.com, Nelson.Mugisha@ug.airtel.com, Vincent.Lukyamuzi@ug.airtel.com';



//$SPECIAL_RUN = 1;
$execution_date = date('Y-m-d');

if($_GET['to'] == '' or $_GET['from'] == ''){
	if(date('D',strtotime($execution_date)) == 'Mon' and $SPECIAL_RUN != 1){
		echo date('Y-m-d H:i:s')." : Running business centre sales for Monday <br>\n";
		$to = date('Y-m-d',strtotime("-1 days"));
		$from = date('Y-m-d',strtotime($to) - (1*86400));
	}elseif($SPECIAL_RUN != 1){
		echo date('Y-m-d H:i:s')." : Running business centre sales for a noromal Day Tue - Friday <br>\n";
		$to = date('Y-m-d',strtotime("-1 days"));
		$from = $to;
	}else{
		$to = '';
		$from = '';
		
		echo date('Y-m-d H:i:s')." : Running SPECIAL business centre sales with check from [".$from."] to [".$to."] \n";
	}
}else{
	echo date('Y-m-d H:i:s')." : Running business centre sales Via web for [".$to."] <br>\n";
	$to = $_GET['to'];
	$from = $_GET['from'];
}

$body = generate_business_centre_sales($to,$from);
$body[html] = attach_html_container($title='',$body[html]);
$body[attach] = attach_html_container($title='',$body[attach]);

my_mail(
	$mail_to = $to_list,
	$mail_cc = $cc_list,
	$bcc = '',
	$message = $body[html],
	$subject = 'Airtel Shops Performance Report '.date('jS M Y',strtotime($to)),
	$from = "SHOPS PERFORMANCE<ccnotify@waridtel.co.ug>",
	$fileparams=array('data'=>$body[attach],'filename'=>str_replace(array(" ","/"),"_",$subject).".xls")
);

echo date('Y-m-d H:i:s')." : Business sales report execution done.<br>\n";

?>
