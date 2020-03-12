<?php

function generate_mmr_report($period){

	if($period == ''){ return "NO DATE INPUT"; }

	$html = '<div class="section_head">WRAP UPS</div>';

	$html .= top_decreasing_increasing_wrapups($period);
	
	$html .= '<div class="section_head">TELESALES</div>';
	
	$html .= generate_telesales_mmr($period);
	
	return $html;
}

?>