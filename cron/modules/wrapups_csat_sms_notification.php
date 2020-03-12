<?php
function generate_daily($yesterday){
	$myquery = new custom_query();
	custom_query::select_db('ccba02.smsfeedback');
	$query = "
		SELECT
			LEFT(smsfeedback.sms_evaluation.date_entered,10) AS period,
			IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
			COUNT(smsfeedback.sms_evaluation.text) as number
		FROM
			smsfeedback.sms_evaluation
		WHERE
			smsfeedback.sms_evaluation.date_entered BETWEEN '".$yesterday." 00:00:00' AND '".$yesterday." 23:59:59'
		GROUP BY
			period,
			evaluation_answer
	";
	
	$rows = $myquery->multiple($query);
	
	echo $query."\n\nNo of rows returned [".count($rows)."]\n";
	
	if(count($rows) == 0){
		$numbers = array(
			'Vincent L'=>'256704008777',
			'David D'=>'256704008010',
			'Christine Aanyu'=>'256704008065',
			'Mike M. Muhumuza'=>'256704008595',
			'Sandra Nabakooza'=>'256704008408',
			'Dipto G'=>'256700997777',
			'Pavan G'=>'256700995555',
			'Pavan G'=>'256706200200',
			'Kate Mitali' => '256704008044',
			'Sam Kulubya' => '256701077457'
		);
		
		$sms_text = "CSAT : Call Center answered calls.\n\n";
		$sms_text .= "There were NO CSAT responses from customers on ".date("l, jS F Y", strtotime("-1 days"));
		
		foreach($numbers as $number){
			$result[] = log_sms_send_request($message=$sms_text,$msisdn=$number,$source='notifications_CSAT',$sender_uid='0316');
			sleep(2);
		}
		
		exit("No Evaluation SMS ... EXITING ...\n");
	}
	
	foreach($rows as &$row){
		$report[data][$row[period]][$row[evaluation_answer]] += $row[number];
		if($evaluation_answer == ''){
			$report[data][$row[period]][ALL] += $row[number];
			$report[data][$row[period]][score] = ($report[data][$row[period]][Yes]/$report[data][$row[period]][ALL]) * 100;
		}
	}
	$DailyScore = $report[data][$row[period]][score];
	
	return number_format($DailyScore); 
}


function generate_monthly($to){
	if($to == '' ) { $to = date('Y-m-d',strtotime("-1 days")).' 23:59:59'; } else { $to .= " 23:59:59";}
	$from = date('Y-m-',strtotime("-1 days")).'01 00:00:00';
	
	$myquery = new custom_query();
	custom_query::select_db('ccba02.smsfeedback');

	$querymonth = "
		SELECT
			LEFT(smsfeedback.sms_evaluation.date_entered,7) AS period,
			IF(REPLACE(smsfeedback.sms_evaluation.text,' ','') like 'y%','Yes','No') as evaluation_answer,
			COUNT(smsfeedback.sms_evaluation.text) as number
		FROM
			smsfeedback.sms_evaluation
		WHERE
			smsfeedback.sms_evaluation.date_entered BETWEEN '".$from."' AND '".$to."'
		GROUP BY
			period,
			evaluation_answer
	";
	$rows = $myquery->multiple($querymonth);
	foreach($rows as &$row){
		$report[data][$row[period]][$row[evaluation_answer]] += $row[number];
		if($evaluation_answer == ''){
			$report[data][$row[period]][ALL] += $row[number];
			$report[data][$row[period]][score] = ($report[data][$row[period]][Yes]/$report[data][$row[period]][ALL]) * 100;
		}
	}
	$MonthlyScore = $report[data][$row[period]][score];
	
	return number_format($MonthlyScore); 
}
?>