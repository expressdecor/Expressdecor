<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<?php require(DIR_WS_INCLUDES . 'meta_tags.php'); ?>
<title><?php echo META_TAG_TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>" />
<meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>" />
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php if (isset($javascript) && file_exists(DIR_WS_JAVASCRIPT . basename($javascript))) { require(DIR_WS_JAVASCRIPT . basename($javascript)); } ?>
</head>
<body <?php echo $body_attributes; ?>>
<?php require(DIR_WS_CONTENT . $content . '.tpl.php'); ?>
</body>
</html>

