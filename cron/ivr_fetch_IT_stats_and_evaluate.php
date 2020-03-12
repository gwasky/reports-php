<?php
//error_reporting(E_ALL);
//error_reporting(E_WARNING | E_PARSE | E_ERROR);
error_reporting(E_ERROR | E_PARSE);

$root_dir = '/srv/www/htdocs/reports/';
require($root_dir.'cron/lib.php');

transfer_ivr_stats_and_evaluate();
?>