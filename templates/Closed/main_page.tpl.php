<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php /* echo HTML_PARAMS; */ ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<?php require(DIR_WS_INCLUDES . 'meta_tags.php'); ?>
<title>Closed</title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
</head>
<body style="background-color: black;">
<!--
"Closed" template for osC created by Paul Mathot
2003/12/29
-->
<!-- warnings //-->
<?php require(DIR_WS_INCLUDES . 'warnings.php'); ?>
<!-- warning_eof //-->
<?php
// include i.e. template switcher in every template
if(bts_select('common', 'common_top.php')) include (bts_select('common', 'common_top.php')); // BTSv1.5
include(DIR_WS_BOXES . 'languages.php');;
?>
<h1 style="position: absolute; left: 38%; top: 38%; color: red;">Sorry we're Closed</h1>
<h4 style="position: absolute; left: 15px; bottom: 15px; color: yellow;">An easy way to close your shop (and open it again) whenever you like:<br>set "Closed" as the default template in admin.</h4>

</body>
</html>
