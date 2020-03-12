<?

function processMessage($message){
	$messageArray = explode(' ',$message);
   	$messageFinal= '';
	foreach($messageArray as $key=>$message_part){
		$messageFinal .= $message_part;
   }
   return $messageFinal;
}

function generate_outlet_evaluation($report_type, $from, $to, $franchises, $answers){
	
	function summarise($rows){
		$franchises = array();
		foreach($rows as $row){ 	
			if($franchises[$row[franchise]] == ''){
        		$franchises[$row[franchise]] = array();
    		}
			
    		if($franchises[$row[franchise]][$row[answer]] == '') $franchises[$row[franchise]][$row[answer]] = 0;    
    
   		 	++$franchises[$row[franchise]][$row[answer]];
		}
		return $franchises;
		
	}
	
	function answersCount($rows){
		$answers = array();
		foreach($rows as $row){ 	
			if($answers[$row[answer]] == ''){
        		$answers[$row[answer]] = array();
    		}
    		if($answers[$row[answer]][$row[franchise]] == '') $answers[$row[answer]][$row[franchise]] = 0;    
    
   		 	++$answers[$row[answer]][$row[franchise]];
		}
		return $answers;	
	}
	
	require_once('config.franchise.php');
	$myquery = new custom_query();
	$query = "
		SELECT 
			date_format(date_time_recieved,'%Y-%m-%d') as eval_date,
			sender_msisdn as evaluator,
			message
		FROM 
			feedback
		WHERE	
		";
		
	if($from){
		$query .= " date_format(date_time_recieved,'%Y-%m-%d') >= '".$from." 00:00:00' AND ";
	}else{
		$query .= " date_format(date_time_recieved,'%Y-%m-%d') >= '".date('Y-m-d')." 00:00:00' AND ";
	}
	if($franchises){
		$query .= "
				(
		";
		foreach($franchises as $franchise){
			++$counter;	
			$query .= " 
				(message like 'care ".$franchise."%' or message like 'care".$franchise."%' )
			";
			if($counter < count($franchises)){
				$query .= ' or ';
			}
		}
		$query .= "
				) AND
		";
	}
	if($answers){
		$counter = 0;
		$query .= "
				(
		";
		foreach($answers as $answer){
			++$counter;	
			$query .= " 
				message like '%".$answer."'
			";
			if($counter < count($answers)){
				$query .= ' or ';
			}
		}
		$query .= "
				) AND
		";
	}
	
	if($to){
		$query .= " date_format(date_time_recieved,'%Y-%m-%d') <= '".$to." 23:59:59' ";
	}else{
		$query .= " date_format(date_time_recieved,'%Y-%m-%d') <= '".date('Y-m-d H:i:s')."' ";
	}
	
	//echo $query."<br>";
	$entries = $myquery->multiple($query);
	foreach($entries as $entry){
		$formatedMessage = processMessage($entry[message]);
		if(str_replace(' ','',$formatedMessage) != ''){
			$rows[] = array(
							'eval_date'=>$entry[eval_date],
							'evaluator'=>$entry[evaluator],
							'franchise'=>substr($formatedMessage,-3,-1),
							'answer'=>substr($formatedMessage,-1)
					);
			/*$entry[franchise] = substr($formatedMessage,-3,-1);
			$entry[answer] = substr($formatedMessage,-1);*/
		}
	}
	//var_dump($entries);
	switch($report_type){
		case 'detail':
			$report[rows] = $rows;
			break;
		case 'both':
			$report[rows] = $rows;
			$report[summary] = summarise($rows);
			$report[answersummary] = answersCount($rows);
			break;
		case 'summary':
		default:
			$_POST[report_type] = 'summary';
			$report[summary] = summarise($rows);
			$report[answersummary] = answersCount($rows);
	}
       
	return display_evaluation_report($report);
}

function display_evaluation_report($report){
	$html = '
		<table border="0" cellpadding="2" cellspacing="0" width="500px" class="sortable">
	';
	
	if(count($report[rows]) > 0){
		$html .= '
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<th>Date of Evaluation</th>
					<th>Franchise Name</th>
					<th>MISDN</th>
					<th>Answer</th>
				</tr>
		';
		foreach($report[rows] as $row){
			$html .= '
				<tr>
					<td class=\'text_values\'>'.$row[eval_date].'</td>
					<td class=\'text_values\'>'.getFranchise($row[franchise]).'</td>
					<td class=\'text_values\'>'.$row[evaluator].'</td>
					<td class=\'text_values\'>'.answer_name($row[answer]).'</td>
				</tr>
			';
		}
		$html .= '
			</table>
			</td>
		</tr>
		';
	}
	//if we have both reports let us space them by a row
	if((count($report[rows]) > 0) &&(count($report[summary]) > 0)){
		$html .= '
			<tr>
				<td style=\'height:20px;\'>
				</td>
			</tr>
		';
	}
	
	if(count($report[summary]) > 0){
		$html .= '
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" width="400px">
					<tr>
						<th> Franchises</th>
						<th> Poor</th>
						<th> Only Ok</th>
						<th> Very Nice</th>
						
					</tr>';
		//data
		foreach($report[summary] as $title=>$values){
			$html .= '<tr><td class=\'text_values\'>'.getFranchise($title).'</td>
			<td class=\'text_values\'>'.$values[1].'</td>
			<td class=\'text_values\'>'.$values[2].'</td>
			<td class=\'text_values\'>'.$values[3].'</td>';
		}
		$html .= '
			</tr>
		';
		//close tr td and table
		$html .= '
			</table>
			</td>
		</tr>
		';
	}
	
	if(count($report[answersummary]) > 0){
		//open tr td and table
		$html .= '
					<table border="0" cellpadding="0" cellspacing="0" width="400px">
					<tr>
						<th> Answers</th>
						<th> Total Franchises</th>
					</tr>';
		//data
		foreach($report[answersummary] as $answer=>$franchiseCounts){
			$html .= '<tr><td class=\'text_values\'>'.answer_name($answer).'</td>';
			$total = 0;
			foreach($franchiseCounts as $key=>$value)
			{
				$total += $value;
			}
			$html .= '
				<td class=\'text_values\'>'.$total.'</td>';
			}
		$html .= '
			</tr>
		';
		//close tr td and table
		$html .= '
			</table>
		';
	}
	
	return $html;
}

function getFranchise($code_num){
	 require_once('config.franchise.php');
	 $myquery = new custom_query();
	 $query="select name from locations where code_num = '$code_num'";
	 $result = $myquery->single($query);
	 if($result[name] == '') { $result[name] = "Franchise code :".$code_num; }
	 return $result[name];                      
}

function answer_name($answer_code){
	$answer = array('1'=>'Poor','2'=>'Only Ok','3'=>'Very Nice');
	if($answer[$answer_code] == ''){ $answer[$answer_code] = "Answer code : ".$answer_code; }
	return $answer[$answer_code];
}
?>