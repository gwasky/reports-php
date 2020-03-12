<?php

function generate_business_centre_camera_alerts(){

	$camera_links1 = array(
		'Cam1-Plaza-Middle' => 'http://CCAdmin:test1234@10.31.78.251/local/people-counter/.api?export-csv&date='.date("Ymd").'&res=1h',
		'Cam2-Plaza-3G-Area' => 'http://CCAdmin:test1234@10.31.78.250/local/people-counter/.api?export-csv&date='.date("Ymd").'&res=1h',
		'Forestmall-Cam01' => 'http://CCAdmin:ccadmin01@10.31.74.250/local/people-counter/.api?export-csv&date='.date("Ymd").'&res=1h');
				
	$camera_links_for_email = array(
		'Cam1-Plaza-Middle' => array(
			'link_settings' => '<a href="http://CCAdmin:test1234@10.31.78.251/people-counter/settings.html">Camera settings</a>',
			'link_statistics' => '<a href="http://CCAdmin:ccadmin01@10.31.78.251/people-counter/statistics.html">Camera</a>',
			'link_reboot' => '<a href="http://CCAdmin:ccadmin01@10.31.78.251/admin/maintenance.shtml?id=1">Reboot/Restart</a>'
		),
		'Cam2-Plaza-3G-Area' => array(
			'link_settings' => '<a href="http://CCAdmin:test1234@10.31.78.250/people-counter/settings.html">Camera settings</a>',
			'link_statistics' => '<a href="http://CCAdmin:ccadmin01@10.31.78.250/people-counter/statistics.html">Camera</a>',
			'link_reboot' => '<a href="http://CCAdmin:ccadmin01@10.31.78.250/admin/maintenance.shtml?id=1">Reboot/Restart</a>'
		),
		'Forestmall-Cam01' => array(
			'link_settings' => '<a href="http://CCAdmin:test1234@10.31.74.250/people-counter/settings.html">Camera settings</a>',
			'link_statistics' => '<a href="http://CCAdmin:ccadmin01@10.31.74.250/people-counter/statistics.html">Camera</a>',
			'link_reboot' => '<a href="http://CCAdmin:ccadmin01@10.31.74.250/admin/maintenance.shtml?id=1">Reboot/Restart</a>'
		)
	);		
	
	$cameras = array(
		'Cam1-Plaza-Middle' => array(
			'contact_info' => array(
				'Center Manager' => array(
					'Center' => 'Plaza',
					'Camera Name' => 'Cam1-Plaza-Middle',
					'Manager' => 'Grace Nantaba',
					'Phone' => '256704008231',
					'Email' => 'Grace.Nantaba@ug.airtel.com'
				),
				'Regional Manager' => array(
					'Center' => 'Plaza',
					'Camera Name' => 'Cam1-Plaza-Middle',
					'Manager' => 'Samuel Mwanje',
					'Phone' => '256704008410',
					'Email' => 'Samuel.Mwanje@waridtel.co.ug'
				)
			)	
		),
		'Cam2-Plaza-3G-Area' => array(
			'contact_info' => array(
				'Center Manager' => array(
					'Center' => 'Plaza',
					'Camera Name' => 'Cam1-Plaza-Middle',
					'Manager' => 'Grace Nantaba',
					'Phone' => '256704008231',
					'Email' => 'Grace.Nantaba@ug.airtel.com'
				),
				'Regional Manager' => array(
					'Center' => 'Plaza',
					'Camera Name' => 'Cam2-Plaza-3G-Area',
					'Manager' => 'Samuel Mwanje',
					'Phone' => '256704008410',
					'Email' => 'Samuel.Mwanje@waridtel.co.ug'
				)
			)	
		),
		'Forestmall-Cam01' => array(
			'contact_info' => array(
				'Center Manager' => array(
					'Center' => 'Forestmall',
					'Camera Name' => 'Forestmall-Cam01',
					'Manager' => 'Amanda Nanzira',
					'Phone' => '256704008220',
					'Email' => 'amanda.nanzira@waridtel.co.ug'
				),
				'Regional Manager' => array(
					'Center' => 'Plaza',
					'Camera Name' => 'Cam2-Plaza-3G-Area',
					'Manager' => 'Samuel Mwanje',
					'Phone' => '256704008410',
					'Email' => 'Samuel.Mwanje@ug.airtel.com'
				)
			)	
		));	
		
	$link_counter = 1;
	/* CHECK CAMERA STATUS AT 8:00 AM AND AT 9:00 AM */
	if(date("H:i:s") >= date("H:i:s", strtotime("08:00:00")) && date("H:i:s") < date("H:i:s", strtotime("09:00:00"))){
		$interval_stop_time = "08:00:00";
	}
	elseif(date("H:i:s") >= date("H:i:s", strtotime("09:00:00")) && date("H:i:s") < date("H:i:s", strtotime("10:00:00"))){
		$interval_stop_time = "09:00:00";
	}
	
	foreach($camera_links as $camera_links_key => $camera_links_value){
		if($handle = fopen($camera_links_value,"r")){
			//OPENS THE LINK
			$camera_data = split("\n", file_get_contents($camera_links_value));
			$headings = explode(",",array_shift($camera_data));
			
			foreach($camera_data as $camera_data_key){
				$camera_data_column = explode(",", $camera_data_key);
				foreach($headings as $key=>$headings_val){
					$camera_formatted_data[$headings_val] = $camera_data_column[$key];
				}
				$data['link'.$link_counter][] = $camera_formatted_data;
			}	
					
			foreach($data['link'.$link_counter] as $data_key){
				if(
				(date("H:i:s", strtotime($data_key['Interval stop'])) == $interval_stop_time) &&
				(intval($data_key['Pedestrians coming in']) == 0)){
					foreach($cameras[$camera_links_key] as $managers){
						$recipients['mail']['CM_mail'][] = $managers['Center Manager']['Email'];
						$recipients['mail']['RM_mail'][] = $managers['Regional Manager']['Email'];
						$recipients['sms']['CM_sms'][] = $managers['Center Manager']['Phone'];
						$recipients['sms']['RM_sms'][] = $managers['Regional Manager']['Phone'];
						$recipients['centers']['CM_name'][] = $managers['Center Manager']['Center'];
						$recipients['centers']['RM_name'][] = $managers['Regional Manager']['Center'];
						$recipients['managers']['CM'][] = $managers['Center Manager']['Manager'];
						$recipients['managers']['RM'][] = $managers['Regional Manager']['Manager'];
						$recipients['off_cameras'][] = $managers['Regional Manager']['Camera Name'];
						$recipients['links'][] = $camera_links_for_email[$camera_links_key];
					}
				}
				if(
				(date("H:i:s", strtotime($data_key['Interval stop'])) == $interval_stop_time) &&
				(intval($data_key['Pedestrians coming in']) > 0)){
					$recipients['camera']['status'][] = "Camera On";
					$recipients['camera']['center'][] = $cameras[$camera_links_key]['contact_info']['Center Manager']['Center'];
				}
			}
			
			$link_counter++;
			
			fclose($handle);
		}else{
			// DOES NOT OPEN THE LINK COZ THE CAMERA IS DOWN
			$recipients['camera']['open_failed'][] = "Failed To Open Link ".$camera_links_key." (".strchr($camera_links_value,"@").")";
		}
	}
	
	return $recipients;
}

function format_mail_and_sms($recipients){	
	
	$formatted['Array']['email']['CM'] = $recipients['mail']['CM_mail'];
	$formatted['Array']['email']['RM'] = $recipients['mail']['RM_mail'];
	$formatted['Array']['sms']['CM'] = $recipients['sms']['CM_sms'];
	$formatted['Array']['sms']['RM'] = $recipients['sms']['RM_sms'];
	$formatted['Array']['centers']['CM'] = $recipients['centers']['CM_name'];
	$formatted['Array']['centers']['RM'] = $recipients['centers']['RM_name'];
	$formatted['Array']['managers']['CM'] = $recipients['managers']['CM'];
	$formatted['Array']['managers']['RM'] = $recipients['managers']['RM'];
	$formatted['Array']['cameras']['CM'] = $recipients['off_cameras'];
	$formatted['Array']['cameras']['RM'] = $recipients['off_cameras'];
	$formatted['Array']['links']['CM'] = $recipients['links'];
	$formatted['Array']['links']['RM'] = $recipients['links'];
	
	$formatted['List']['email']['CM'] = implode(",", $recipients['mail']['CM_mail']);
	$formatted['List']['email']['RM'] = implode(",", $recipients['mail']['RM_mail']);
	$formatted['List']['sms']['CM'] = implode(",", $recipients['sms']['CM_sms']);
	$formatted['List']['sms']['RM'] = implode(",", $recipients['sms']['RM_sms']);
	$formatted['List']['centers']['CM'] = implode(",", $recipients['centers']['CM_name']);
	$formatted['List']['centers']['RM'] = implode(",", $recipients['centers']['RM_name']);
	$formatted['List']['managers']['CM'] = implode(",", $recipients['managers']['CM']);
	$formatted['List']['managers']['RM'] = implode(",", $recipients['managers']['RM']);
	$formatted['List']['cameras']['CM'] = implode(",", $recipients['off_cameras']);		
	
	return $formatted;
}

?>