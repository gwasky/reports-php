<?
require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');
require_once('/srv/www/htdocs/reports/web/includes/includes.php');
require_once('lib.html.php');

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'Task Manager <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	$headers .= "BCC: ".$bcc." \r\n";
    
	mail($to,$subject,$message,$headers);
	
	}

function my_strtotime($duration){
	return (strtotime($duration) - strtotime('00:00:00'));
}

function timetostr($seconds){
    /*** return value ***/
    $ret = "";

    /*** get the hours ***/
    $hours = intval(intval($seconds) / 3600);
    if($hours > 0)
    {
        $ret .= "$hours:";
    }else{
		$ret .= "00:";
	}
    /*** get the minutes ***/
    $minutes = bcmod((intval($seconds) / 60),60);
    if($hours > 0 || $minutes > 0)
    {
		if($minutes < 10){
			$ret .= "0";
		}
        $ret .= "$minutes:";
    }else{
		$ret .= "00:";
	}
  
    /*** get the seconds ***/
    $seconds = bcmod(intval($seconds),60);
    $ret .= "$seconds";

    return $ret;
}





	
/*function display_trend_graph($width,$height,$data,$title,$graph_type){

	///set default graph type
	if($graph_type == ''){
		$graph_type = 'line';
	}
	
	$graph=new PHPGraphLib($width,$height);

	$graph->addData($data);
	$graph->setTitle($title);
	$graph->setGradient("lime", "green");
	$graph->setBarOutlineColor("black");
	if($graph_type == 'bar'){
		$graph->setBars(true);
	}else{
		$graph->setBars(false);
	}
	if($graph_type == 'line'){
		$graph->setLine(true);
	}else{
		$graph->setLine(false);
	}

	$graph->createGraph();
	
}*/








?>