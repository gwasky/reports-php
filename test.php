<?php
$homepage = file_get_contents('http://127.0.0.1/reports/web/');
exit($homepage);

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'Automated Action <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
 	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	if($bcc){
		$headers .= "BCC: ".$bcc." \r\n";
	}
	
	//echo "Sending mail subject [".$subject."] to [".$to."] bcc [".$bcc."] from [".$from."] headers [<br>".nl2br($headers)."<br>]with the following message <hr>".$message."<hr>";	
    return mail($to,$subject,$message,$headers);
}

$message = '';

$result = sendHTMLemail($to='sntaven@gmail.com,steven.ntambi@waridtel.co.ug',$bcc,$message=nl2br($message),$subject='Test from CCBA01',$from);

echo $result."\n";

?>
