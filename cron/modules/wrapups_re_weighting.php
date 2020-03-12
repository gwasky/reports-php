<?php
function re_weight_wrap_up(){
	$myquerys = new custom_query();
	custom_query::select_db('reportscrm');
	
	//resetting the current weights ... NOT REALLY NECESSARY
	//$query = "update subsubcategory set weight = 9999";
	//$result = $myquerys->no_row($query);
	
	// GET THE ORDER WEIGHTED ORDER OF THE MOST RECENT WRAP UPS. WE ARE USING LAST 8 HOUR INTERVAL BUT YOU CAN CHANGE THIS IN THE QUERY
	$weighted_order_query = "
		SELECT
			subsubcategory.cat_id,
			subsubcategory.subcategory,
			subsubcategory.subsubcategory
		FROM
			subsubcategory
			Left outer Join reportsphonecalls ON reportsphonecalls.wrapupsubcat = subsubcategory.subcategory AND reportsphonecalls.subject = subsubcategory.subsubcategory
		where
			reportsphonecalls.createdon between DATE_SUB(NOW(), INTERVAL 8 HOUR) and NOW()
		GROUP BY
			subsubcategory.subcategory,subsubcategory.subsubcategory
		ORDER BY
			count(reportsphonecalls.subject) DESC
	";
	$weighted_order_wrapups = $myquerys->multiple($weighted_order_query);
	
	//ATTACH A WEIGHT TO THE WRAP UPS ABOVE. THE SMALLEST WEIGHT ON TO THE WRAP UP WITH THE HIGHEST COUNT
	foreach($weighted_order_wrapups as $row){
		$weighted[subjects][$row[cat_id].'<#>'.$row[subcategory]][$row[subsubcategory]] = ++$auto_incrementer[subjects];
		++$weighted[subcategories][$row[cat_id].'<#>'.$row[subcategory]];
		++$weighted[cat_ids][$row[cat_id]];
	}
	
	$report[weighted_list][subjects] = count($weighted_order_wrapups);
	$report[weighted_list][subcategories] = count($weighted[subcategories]);
	$report[weighted_list][cat_ids] = count($weighted[cat_ids]);
	
	
	//SETTING THE WEIGHT FOR WRAP UPS WITH ZERO COUNT IN THE SPECIFIRED INTERVAL ABOVE
	$unordered_start[subjects] = $auto_incrementer[subjects]+1;
	$unordered_start[subcategories] = $report[weighted_list][subcategories]+1;
	$unordered_start[cat_ids] = $report[weighted_list][cat_ids]+1;	
	
	$report[unordered_start][subjects] = $unordered_start[subjects];
	$report[unordered_start][subcategories] = $unordered_start[subcategories];
	$report[unordered_start][cat_ids] = $unordered_start[cat_ids];
	
	//print_r($weighted[subcategories]); echo "\n"; print_r($weighted[cat_ids]); echo "----------------------------------------------\n";
	
	//SORT THE SUBCATEGORY AND CATEGORY COUNTS IN ASCENDING ORDER
	arsort($weighted[subcategories]); 
	arsort($weighted[cat_ids]);
	
	//print_r($weighted[subcategories]); echo "\n"; print_r($weighted[cat_ids]); echo "----------------------------------------------\n";
	
	//REPLACEING THE NUMBER OF WRAP UPS WITH A WEIGHTING ORDER
	foreach($weighted[subcategories] as &$value){ $value = ++$incrementer;} unset($incrementer,$value);
	foreach($weighted[cat_ids] as &$value){ $value = ++$incrementer;} unset($incrementer,$value);
	
	//print_r($weighted[subcategories]); echo "\n"; print_r($weighted[cat_ids]);
	
	//GET ALL ACTIVE WRAP UPS FROM THE SUBSUBCATEGORY TABLE IN REPORTSPHONECALLS DB
	$wrapups_query = "
		SELECT
			subsubcategory.cat_id,
			subsubcategory.subcategory,
			subsubcategory.subsubcategory
		FROM
			subsubcategory
		WHERE
			subsubcategory.subject_status = 'active'
	";
	$wrapups = $myquerys->multiple($wrapups_query);
	
	//UPDATE SUBSUBCATEGORY TABLE WITH THE WEIGHTS CALCULATED ABOVE
	foreach($wrapups as $row){
		$base_lists[subcategories][$row[subcategory]] = $row[subcategory];
		$base_lists[cat_ids][$row[cat_id]] = $row[cat_id];
		//FOR SUBJECTS
		if($weighted[subjects][$row[cat_id].'<#>'.$row[subcategory]][$row[subsubcategory]] == ''){
			$query = "update subsubcategory set subsubcategory.weight = '".$unordered_start[subjects]."' where subsubcategory.subcategory = '".$row[subcategory]."' and subsubcategory.subsubcategory = '".$row[subsubcategory]."' and subsubcategory.subject_status = 'active'";
			
			$result = $myquerys->no_row($query);
			++$report[affect_counts][subjects][$result];
		}else{
			$query = "update subsubcategory set subsubcategory.weight = '".$weighted[subjects][$row[cat_id].'<#>'.$row[subcategory]][$row[subsubcategory]]."' where subsubcategory.subcategory = '".$row[subcategory]."' and subsubcategory.subsubcategory = '".$row[subsubcategory]."' and subsubcategory.subject_status = 'active'";

			$result = $myquerys->no_row($query);
			++$report[affect_counts][subjects][$result];
		}
		
		//FOR LOGGING PURPOSES
		//if($result != 1){$report[queries][] = "[".$result."] =>> ".$query;}
		
		//FOR SUBCATEGORIES
		if($updated_items[subcategories][$row[subcategory]] != TRUE){
			if($weighted[subcategories][$row[cat_id].'<#>'.$row[subcategory]] == ''){
				$query = "update subcategory set subcategory.weight = '".$unordered_start[subcategories]."' where subcategory.subcategory = '".$row[subcategory]."' and subcategory.sub_cat_status = 'active' and subcategory.cat_id='".$row[cat_id]."'";
				
				$result = $myquerys->no_row($query);
				++$report[affect_counts][subcategories][$result];
				$updated_items[subcategories][$row[subcategory]] = TRUE;
			}else{
				$query = "update subcategory set subcategory.weight = '".$weighted[subcategories][$row[cat_id].'<#>'.$row[subcategory]]."' where subcategory.subcategory = '".$row[subcategory]."' and subcategory.sub_cat_status = 'active' and subcategory.cat_id='".$row[cat_id]."'";
	
				$result = $myquerys->no_row($query);
				++$report[affect_counts][subcategories][$result];
				$updated_items[subcategories][$row[subcategory]] = TRUE;
			}
		}
		
		//FOR LOGGING PURPOSES
		//if($result != 1){$report[queries][] = "[".$result."] =>> ".$query;}
		
		//FOR CATEGORIES
		if($updated_items[cat_ids][$row[cat_id]] != TRUE){
			if($weighted[cat_ids][$row[cat_id]] == ''){
				$query = "update category set category.weight = '".$unordered_start[cat_ids]."' where category.category = '".$row[subcategory]."' and category.cat_status = 'active' and category.cat_id='".$row[cat_id]."'";
				
				$result = $myquerys->no_row($query);
				++$report[affect_counts][cat_ids][$result];
				$updated_items[cat_ids][$row[cat_id]] = TRUE;
			}else{
				$query = "update category set category.weight = '".$weighted[cat_ids][$row[cat_id]]."' where category.cat_status = 'active' and category.cat_id='".$row[cat_id]."'";
	
				$result = $myquerys->no_row($query);
				++$report[affect_counts][cat_ids][$result];
				$updated_items[cat_ids][$row[cat_id]] = TRUE;
			}
		}
		
		//FOR LOGGING PURPOSES
		//if($result != 1){$report[queries][] = "[".$result."] =>> ".$query;}
	}
	
	$report[base_list][subjects] = count($wrapups);
	$report[base_list][subcategories] = count($base_lists[subcategories]);
	$report[base_list][cat_ids] = count($base_lists[cat_ids]);
	
	print_r($report);
}
?>