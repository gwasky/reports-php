<?
require_once('crm_gib.php');

switch($_GET[report]){
	case 'home':
		$result_html = home_page();
		break;
	case 'leads':
		$result_html = generate_leads_report($_POST[from], $_POST[to]);
		if($_POST[excel] != ''){
			generate_excel_file($result_html);
		}
		break;
	case 'sales_report':
		$result_html = generate_sales_partner_product($_POST[from], $_POST[to],$_POST[name],$_POST[status]);
		if($_POST[excel] != ''){
			generate_excel_file($result_html);
		}
		break;
	case 'partner_sales_report_by_date':
		$result_html = generate_sales_per_customer_by_date($_POST[from], $_POST[to],$_POST[name],$_POST[status]);
		if($_POST[excel] != ''){
			generate_excel_file($result_html);
		}
		break;
	 case 'purchases_report':
		$result_html = generate_purchases_partner_product($_POST[from], $_POST[to],$_POST[name],$_POST[status]);
		if($_POST[excel] != ''){
			generate_excel_file($result_html);
		}
		break;
	 case 'purchases_report_by_date':
		$result_html = generate_purchases_per_customer_by_date($_POST[from], $_POST[to],$_POST[name],$_POST[status]);
		if($_POST[excel] != ''){
			generate_excel_file($result_html);
		}
		break;
	default:
		break;
} 

$html = '
	<div style="font-size:14px; font-weight:bold; background-color:#009; color:#FFF; line-height:20px; padding-left:4px;" align="centre">'.
	str_replace(array('_'),' ',strtoupper($_GET[report]))
	.'</div>
	';
$html .= generate_form($_GET[report]);
$html .= $result_html;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
th {
	font-weight: normal;
	text-align:left;
	vertical-align:top;
	background:#009;
	color:#FFF;
	border-right:#CCC 1px solid;
	padding:2px;
}

body,
.select,
.textbox{
	font-size:9px;
	font-family:Verdana, Geneva, sans-serif;
}

.values{
	text-align:right;
	font-size: 9px;
	white-space:nowrap;
	border-bottom:#333333 1px dashed;
	border-right:#333333 1px dashed;
}

.red_values{
	background-color: #AE0000;
	color:#FFF;
	text-align:right;
	font-size: 9px;
	white-space:nowrap;
	border-bottom:#333333 1px dashed;
}

.text_values{
	vertical-align:top;
	text-align:left;
	padding-left:1px;
	font-size: 9px;
	line-height:12px;
	white-space:nowrap;
	border-bottom:#333333 1px dashed;
	border-right:#333333 1px dashed;
}

.wrap_text{
	vertical-align:top;
	text-align:left;
	padding-left:1px;
	font-size: 9px;
	border-bottom:#333333 1px dashed;
	border-right:#333333 1px dashed;
	width:20%;
}

.form_bar{
	background-color:#CCC;
	background-color:#00C;
}

form_td{
	white-space:nowrap;
}

.menu_link{
	line-height:15px;
}

/* Sortable tables */
table.sortable thead {
    background-color:#eee;
    color:#666666;
    font-weight: bold;
    cursor: default;
}
</style>
<!-- Java -->
<script type="text/javascript" src="includes/common/js/sigslot_core.js"></script>
<script src="includes/common/js/base.js" type="text/javascript"></script>
<script src="includes/common/js/utility.js" type="text/javascript"></script>
<script type="text/javascript" src="includes/wdg/classes/MXWidgets.js"></script>
<script type="text/javascript" src="includes/wdg/classes/MXWidgets.js.php"></script>
<script type="text/javascript" src="includes/wdg/classes/Calendar.js"></script>
<script type="text/javascript" src="includes/wdg/classes/SmartDate.js"></script>
<script type="text/javascript" src="includes/wdg/calendar/calendar_stripped.js"></script>
<script type="text/javascript" src="includes/wdg/calendar/calendar-setup_stripped.js"></script>
<link rel="stylesheet" type="text/css" href="css/menu.css">
<script src="includes/resources/calendar.js"></script>
<script src="js/sort.js"></script>
<script type="text/javascript">
function makeSelection(frm, id) {
      if(!frm || !id)
        return;
      targetElement = frm.elements[id];
      var handle = window.open('get_partner.php');
    }
function makeSupplierSelection(frm, id) {
      if(!frm || !id)
        return;
      targetElement = frm.elements[id];
      var handle = window.open('get_supplier.php');
    }
</script>
<link href="includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<title>UNIOIL Reporting Platform</title>
</head>
<body>
	<!--BEGIN TOP COL-->
	<div style=" float:left; width:99.3%; padding:0px 4px 0px 4px;">
    <img src="logo/logo.gif" alt="logo" />
    </div>
<div>
	<!--BEGIN LEFT COL-->
    <div style="border: #CCCCCC 1px solid; float:left; width:10%; padding:0px 4px 0px 4px;">
    <div>
    <? echo generate_links($_GET[report]); ?>
    </div>
    </div>
    <!--END LEFT COL-->
    <!--BEGIN RIGHT COL-->
    <div style="float:right; width:88%; border: #CCCCCC 1px solid;">
    <div style="padding:4px;">
    	<? echo $html; ?>
    </div>
    </div>
	<!--END RIGHT COL-->
</div>
<!--IMPORTANT--><div style="clear:both;"></div><!--IMPORTANT-->

<div><? echo display_footer(); ?></div>

</body>
</html>