<?php
function get_business_centre_walkins($date){
	
	if($date == '') $date = date('Y-m-d', strtotime("-1 days"));
	
	$myquery = new custom_query();
	
	$camera_links = array(
						  'http://CCAdmin:test1234@10.31.78.251/local/people-counter/.api?export-csv&date='.str_replace(array('-'),'',$date).'&res=24h',	//Cam1-Plaza-Middle
						  'http://CCAdmin:test1234@10.31.78.250/local/people-counter/.api?export-csv&date='.str_replace(array('-'),'',$date).'&res=24h',	//Cam2-Plaza-3G-Area
						  'http://CCAdmin:ccadmin01@10.31.74.250/local/people-counter/.api?export-csv&date='.str_replace(array('-'),'',$date).'&res=24h'	//Forest-Mall
					);
	
	/*
	$camera_links = array(
						  'http://CCAdmin:test1234@10.31.78.251/local/people-counter/.api?export-csv&date=all&res=24h',
						  'http://CCAdmin:test1234@10.31.78.250/local/people-counter/.api?export-csv&date=all&res=24h',
						  'http://CCAdmin:ccadmin01@10.31.74.250/local/people-counter/.api?export-csv&date=all&res=24h'
					);
	*/
	
	/*
	0	Interval start,
	1	Interval stop,
	2	Camera serial number,
	3	Counter name,
	4	Pedestrians coming in,
	5	Pedestrians going out
	*/
	
	//<a href="http://CCAdmin:test1234@10.31.78.251/people-counter/settings.html" target="_blank">10.31.78.251/people-counter/settings.html</a>
	//<a href="http://CCAdmin:test1234@10.31.78.250/people-counter/settings.html" target="_blank">10.31.78.250/people-counter/settings.html</a>
	//<a href="http://CCAdmin:test1234@10.31.74.250/people-counter/settings.html" target="_blank">10.31.74.250/people-counter/settings.html</a>
	
	foreach($camera_links as $link){
		echo date('Y-m-d H:i:s')." : Opening link ".$link." \n";
		if($handle = fopen($link,"r")){
			$log[notice] .= date('Y-m-d H:i:s')."\n".$link."\n\n".file_get_contents($link)."\n";
			$file_data = split("\n",file_get_contents($link));
			$headings = explode(",",array_shift($file_data));
			
			foreach($file_data as $file_row){
				$data = explode(",",$file_row);
				if(count($data) > 3){
					foreach($headings as $key=>$heading){
						$row[$heading] = $data[$key];
					}
					
					$rows[] = $row;
				}else{
					continue;
				}
			}
		}else{
			$log[error] .= "Failed to open ".$link." \n";
		}
	}
	
	foreach($rows as $row){

		$query = "
			INSERT INTO
				bc_traffic (camera_id,traffic_date_start,traffic_date_end,traffic_in,traffic_out,max_traffic)
			VALUES (
				'".get_camera($input=$row['Camera serial number'],$input_col='serial',$output='id')."','".$row['Interval start']."',DATE_SUB('".$row['Interval stop']."', INTERVAL 1 SECOND),'".$row['Pedestrians coming in']."','".$row['Pedestrians going out']."','".get_max_value($row['Pedestrians coming in'],$row['Pedestrians going out'])."'
			);
		";
		
		//echo $query."\n\n";
		
		$result = $myquery->addit($query,'ccba02.businesssales');
		
		$mysql_error = mysql_error();
		
		if(!$result){

			$query = " select id from bc_traffic where camera_id = '".get_camera($input=$row['Camera serial number'],$input_col='serial',$output='id')."' and traffic_date_start = '".$row['Interval start']."' ";
			$result = $myquery->single($query,'ccba02.businesssales');
			
			$query = "
				UPDATE
					bc_traffic
				SET
					camera_id = '".get_camera($input=$row['Camera serial number'],$input_col='serial',$output='id')."',
					traffic_date_start = '".$row['Interval start']."',
					traffic_date_end = DATE_SUB('".$row['Interval stop']."', INTERVAL 1 SECOND),
					traffic_in = '".$row['Pedestrians coming in']."',
					traffic_out = '".$row['Pedestrians going out']."',
					max_traffic = '".get_max_value($row['Pedestrians coming in'],$row['Pedestrians going out'])."'
				WHERE
					id = '".$result[id]."'
			";
			$result = $myquery->no_row($query,'ccba02.businesssales');
			
			$line = "UPDATED [".get_camera($input=$row['Camera serial number'],$input_col='serial',$output='name')."] for : ".substr($row['Interval start'],0,10)." \n MYSQL QUERY : [".str_replace(array("\n","\t"),array(" ",""),$query)."] ... \n";
			echo date('Y-m-d H:i:s')." : ".$line;
			$log[notice] .= $line;
		}else{
			$log[notice] .= "Added ".get_camera($input=$row['Camera serial number'],$input_col='serial',$output='name')." : IN [".$row['Pedestrians coming in']."], OUT [".$row['Pedestrians going out']."], DATE [".substr($row['Interval start'],0,10)."] \n";
		}
	}
		
	return $log;
}

function get_camera($input='',$input_col='id',$output=''){
	$myquery = new custom_query();
	
	$query = "
		SELECT
			bc_cameras.`".$output."` AS parameter
		FROM
			bc_cameras
		WHERE
			bc_cameras.`".$input_col."` = '".$input."' AND
			bc_cameras.status = 'active'
	";
	
	$result = $myquery->single($query,'ccba02.businesssales');
	
	return $result[parameter];
}

function get_max_value($a,$b){
	
	if($a >= $b){
		return $a;
	}else{
		return $b;
	}
}

?>