<?php
//$html = '
require_once('includes/class.pog_base.php');
require_once('includes/class.database.php');
require_once('authorise.php');
require_once('crm.php');
require_dir_nest('objects/','');
require_once('includes/includes.php');

//print_r($_GET); echo "<br>"; print_r($_POST);

$html = script_action($_GET);

echo $html;

?>