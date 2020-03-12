<?php
/*
--------------------
	TO BE EXECUTED ON CCBA02
--------------------
*/


function get_unavailable_songs_details($from, $to){
	$myquery = new custom_query();
	
	$query = "
		SELECT
			reportsphonecalls.crbt_requested AS request,
			COUNT(reportsphonecalls.crbt_requested) AS request_count
		FROM
			reportsphonecalls
		WHERE
			reportsphonecalls.subject = 'I want this song (Not available in rbt database)' AND
			reportsphonecalls.createdon BETWEEN '".$from."' AND '".$to."'
		GROUP BY
			reportsphonecalls.crbt_requested;	
	";
	
	$unavailble_song_list = $myquery->multiple($query, 'ccba02.reportscrm');
	
	return display_unavailable_songs($unavailble_song_list, $from, $to);
}

function display_unavailable_songs($song_list, $from, $to){
	$html = '
		<table width="779" border="0">
			<tr>
				<td colspan="3">List of Unavailable Songs in the rbt database from '.$from.'  to '.$to.'</td>
			</tr>
			<tr>
				<td width="218">Artist</td>
			  <td width="226">Song</td>
			  <td width="313">Number of Requests</td>
		  </tr>';
	foreach($song_list as $song_list_key){
		$artist_to_song = (explode("||", $song_list_key['request']));	
		$html .= '  
			<tr>
				<td>'.ucwords(strtolower($artist_to_song[0])).'</td>
				<td>'.ucwords(strtolower($artist_to_song[1])).'</td>
				<td>'.$song_list_key['request_count'].'</td>
			</tr>';
	}		
	$html .= '		
		</table>	
	';	
	
	return $html;
}

?>