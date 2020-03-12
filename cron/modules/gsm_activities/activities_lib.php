<?php
function list_columns($columns){

	$column_list = "";
	
	foreach($columns as $colunm){
		$column_list .= $colunm;
		++$count;
		if($count < count($columns)) { $column_list .= ","; } 
	}
	
	return $column_list;
}

function generic_period_query($period,$date,$column='reportscrm.createdon'){
	switch($period){
		case 'year':
			$query .= "	".$column." between '".substr($date,0,4)."-01-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'month':
			$query .= "	".$column." between '".substr($date,0,7)."-01 00:00:00' and '".$date." 23:59:59' ";
			break;
		case 'day':
		default:
			$query .= "	".$column." between '".$date." 00:00:00' and '".$date." 23:59:59' ";
			break;
	}
	
	return $query;
}

function generate_generic_stats($date,$period,$columns,$db_ref,$date_col='`Date Created`',$limit=5){
	$myquery = new custom_query();
	//custom_query::select_db($db_ref[db]);
	
	$query = "
		SELECT
			".list_columns($columns).",
			count(".end($columns).") as `count`
		FROM
			".$db_ref[table]."
		WHERE
			".generic_period_query($period,$date,$column=$date_col)." AND
			TRIM(".end($columns).") != ''
		GROUP BY
			".list_columns($columns)."
		ORDER BY
			count(".end($columns).") DESC
		LIMIT
			".$limit."
	";
	
	//exit($query."<br>");
	
	//$as = $myquery->multiple($query); print_r($as); echo "<br><br>";
	
	return $myquery->multiple($query,$db_ref[db]);
}
?>