<?php
require_once('/srv/www/htdocs/resources/LOADER.php');
LOAD_RESOURCE('DATABASES');

require_once('includes/dropdowns.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv"Script-Content-Type" content="text/javascript">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Expires" content="0"> <!-- disable caching -->
    <title>Choose Agents</title>
    <script type="text/javascript">
    function makeSelection(frm, id) {
      if(!frm || !id)
        return;
      var elem = frm.elements[id];
      if(!elem)
        return;
      var val = elem.options[elem.selectedIndex].value;
      opener.targetElement.value = val;
      this.close();
    }
    </script>
</head>
<body>
<form id="frm" name="frm" action="#">
<span>Agent: </span>
<?
	echo display_telesale_dropdown();
?>
<input type="button" value="Select Name" onclick="makeSelection(this.form, 'agent');">
</form>
</body>
</html>