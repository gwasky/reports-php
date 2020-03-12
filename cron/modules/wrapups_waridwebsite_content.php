<?php
/*
function get_popular_content(){
	$myquerys = new custom_query();
	custom_query::select_db('reportscrm');
	
	$NO_OF_ISSUES_TO_USE = 5;
	
	//resetting the current weights ... NOT REALLY NECESSARY
	//$query = "update subsubcategory set weight = 9999";
	//$result = $myquerys->no_row($query);
	
	// GET THE ORDER WEIGHTED ORDER OF THE MOST RECENT WRAP UPS. WE ARE USING LAST 8 HOUR INTERVAL BUT YOU CAN CHANGE THIS IN THE QUERY
	$query = "
		SELECT
			reportsphonecalls.wrapupsubcat as product_name,
			reportsphonecalls.subject as issue,
			subsubcategory.web_help as howto_info
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			subsubcategory.subject_status = 'active' AND
			subsubcategory.subject_type != 'Not Applicable' AND
			reportsphonecalls.createdon between date_sub(now(), interval 8 hour) and now()
		GROUP BY
			product_name,issue
		ORDER BY
			count(reportsphonecalls.subject) DESC
		LIMIT 8
	";
	
	$data = $myquerys->multiple($query);
	
	if(count($data) > 0){
		$file[contents] = "<?php \n//GENERATED ON ".date('D jS M Y - H:i:s')."\n\n$"."popular_wrapups_array = array( \n";
		foreach($data as $key=>$row){
			if($row[howto_info] != ''){
				$file[contents] .= "\t'".$key."' => array(\n";
				foreach($row as $header=>$value){
					$file[contents] .= "\t\t'".$header."' => '".str_replace(array("'","\r"),"",$value)."'";
					if(++$cols < count($row)) $file[contents] .= ",";
					$file[contents] .= " \n";
				}
				$cols = '';
				$file[contents] .= "\t)";
				++$rows;
				if($rows < count($data) and $rows < $NO_OF_ISSUES_TO_USE) $file[contents] .= ",";
				$file[contents] .= " \n";
				++$file[uploaded_issues]; if($file[uploaded_issues] == $NO_OF_ISSUES_TO_USE) break;
			}else{
				$log[ccba] .= "BLANK HELP: Product Name = '".$row[product_name]."', issue = '".$row[issue]."', howto_info = '".$row[howto_info]."' <br>";
			}
		}
		$file[contents] .= " ); \n?>";
	}else{
		$log[ccba] .= "Query [".nl2br($query)."] returns no data ...";
	}
	
	if($file[uploaded_issues] < 5) { $log[ccba] .= " Only ".$file[uploaded_issues]." have been provided .. <br>";}
	
	$myftp = new FTP($ftproot = 'ftp_dumps/', $host = 'ccba02.waridtel.co.ug', $username = 'survey', $password = 'survey01', $debug=FALSE);
	
	if($myftp == FALSE){
		$log[fatal] .= "Failed to connect with credentials ".$myftp->username."@".$myftp->host." ... <br>";
	}else{
		$myftp->docroot = "sources/";
		$result = $myftp->f_fputs($filename='popular_wrapups_array.php', $mode = 'w+', $data=$file[contents]);
		if($result == FALSE){
			$log[ccba] .= "Failed to save ".$myftp->docroot.$filename." ...<br>";
		}else{
			$result = $myftp->f_upload($filename,$filename);
			if($result == FALSE){
				$log[fatal] .= "Failed to upload to ".$myftp->username."@".$myftp->host.":~/".$myftp->ftproot.$filename." ...<br>";
			}
		}
	}
	
	$myftp->cleanup();

	return $log;
}*/

function generate_warid_website_howto_txt_file(){

	$myquerys = new custom_query();
	$myftp = new FTP();
	custom_query::select_db('reportscrm');
	//$final_list[] = array();
	
	$sql = "
		select
			reportsphonecalls.subject,
			reportsphonecalls.wrapupsubcat,
			subsubcategory.weight,
			subsubcategory.web_help
		FROM
			reportsphonecalls
			LEFT OUTER JOIN subsubcategory ON (subsubcategory.subsubcategory=reportsphonecalls.subject) AND (subsubcategory.subcategory=reportsphonecalls.wrapupsubcat)
		WHERE
			subsubcategory.subject_status = 'active' AND
			reportsphonecalls.createdon between date_sub(now(), interval 8 hour) and now() AND
--			subsubcategory.web_help != '' AND
			reportsphonecalls.wrapupsubcat NOT IN ('Prank Calls')
		GROUP BY
			wrapupsubcat,subject
		ORDER BY
			subsubcategory.weight ASC
		LIMIT 10
	";
	
	echo date('Y-m-d H:i:s')." : Getting top issues from CCBA01 DB ... \n";
	
	$issue_list = $myquerys->multiple($sql);
	
	if(count($issue_list) == 0 ) { return 'No top issues obtained ... '; }
	
	//$faqs_text_headings = "subject<col>category<col>webhelp<linebr>\n";
	
	foreach($issue_list as &$row){
		if(strlen($row['web_help']) > 5){
			if(count($final_list["TO BE SHOWN ON WEBSITE"]) < 5){
				$faqs[]=array(
					'subject' => $row['subject'],
					'wrapupsubcat' => $row['wrapupsubcat'],
					'web_help' => $row['web_help']
				);
				
				//$faqs_text_content .= $row['subject']."<col>".$row['wrapupsubcat']."<col>".$row['web_help'];
				
				$final_list["TO BE SHOWN ON WEBSITE"][] = $row;
				
				/*if(count($final_list["TO BE SHOWN ON WEBSITE"]) < 5 ){
					$faqs_text_content .= "<linebr>\n";
				}*/
			}else{
				$final_list["OTHER INFO IN TOP 10"][] = $row;
			}
		}else{
			$final_list["TOP ISSUES WITH NO HELP"][] = $row;
		}
		
		//echo ++$UU."<br>";
	}
	
	//echo serialize($faqs)."\n\n";
	//echo base64_encode(serialize($faqs));

	# save it to file

	/*
	$filename = "/www/ccportal/cron/Files/howto.txt";
	$fp = fopen($filename, 'w+') or die("I could not open $filename.");
	fwrite($fp, serialize($faqs));
	fclose($fp);
	*/
	
	if(count($final_list["TO BE SHOWN ON WEBSITE"]) >= 5){
		//$total_file_content = $faqs_text_headings.$faqs_text_content;
		$total_file_content = base64_encode(serialize($faqs));
		
		echo $total_file_content;
		
		$myftp->FTP($ftproot='', $host='file.waridtel.co.ug', $username='squad', $password='5qu@d*4u0', $debug = FALSE, $docroot='', $passive_mode = TRUE, $ftp_mode = 'FTP_ASCII');
		
		if($myftp->f_del('howto.txt')){
			echo date('Y-m-d H:i:s')." : Existing howto.txt deleted ... \n";
		}else{
			echo date('Y-m-d H:i:s')." : WARNING : Existing howto.txt NOT deleted ... \n";
		}
		//echo "---------------------------------------------------------------------- \n";
		
		if($myftp->f_fputs('howto.txt', $mode = 'w+', $data = $total_file_content)){
			echo date('Y-m-d H:i:s')." : Uploaded new howto.txt file ... \n";
		}else{
			echo date('Y-m-d H:i:s')." : WARNING : Uploaded new howto.txt file FAILED ... \n";
		}
		//echo "---------------------------------------------------------------------- \n";
		
		$myftp->cleanup();
	}else{
		echo date('Y-m-d H:i:s')." : WARNING : TOP ISSUES IS LESS THAN 5!! PLEASE FIX! ... \n";
		//show_warid_website_howto_txt_file_exception($final_list);
	}
	
	//DEBUG TO SEE WHAT IS BEING SENT....
	show_warid_website_howto_txt_file_exception($final_list);
	
}

function show_warid_website_howto_txt_file_exception($lists){
	
	foreach($lists as $list_title=>$list){
		$html .= '
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th>'.$list_title.'</th>
				</tr>
				<tr>
					<td><table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<th>#</th>
		';
		
		$headings = array_keys($list[0]);
		foreach($headings as $heading){
			if($heading == 'web_help') { $width = 'width="50%"'; }else{ $width = '';}
			$html .= '
							<th '.$width.'>'.$heading.'</th>
			';
		}
		
		$html .= '
						</tr>
		';
		
		unset($rr);
		foreach($list as $row){
			$html .= '
						<tr class="'.row_style(++$rr).'">
							<td>'.$rr.'</td>
			';
			foreach($headings as $heading){
				$html .= '
							<td>'.$row[$heading].'</td>
				';
			}
			$html .= '
						</tr>
			';
		}
		
		$html .= '
					</table></td>
				</tr>
				<tr>
					<td height="20"></td>
				</tr>
			</table>
		';
	
	}
	
	$html = attach_html_container('',$html);
	$to_list = 'CCBUSINESSANALYSIS@waridtel.co.ug';
	//$to_list = 'steven.ntambi@waridtel.co.ug';
	sendHTMLemail($to = $to_list,$bcc = '',$message = $html,$subject = 'Warid Website FAQ Population',$from = "DO NOT REPLY<ccnotify@waridtel.co.ug>");
}
	
?>