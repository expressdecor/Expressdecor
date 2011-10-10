<?php
/**
 * ClickTale - PHP Integration Module
 *
 * LICENSE
 *
 * This source file is subject to the ClickTale(R) Integration Module License that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.clicktale.com/Integration/0.2/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@clicktale.com so we can send you a copy immediately.
 *
 */
?>
<?php
	require("../ClickTaleTop.php");
	$config = ClickTale_Settings::Instance();
	$config->UseStaticHash = true;
	$config->StaticHash = "CacheTest";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>ClickTale integration :: Cache test</title>
	</head>
	<body>
		You should see this text in the right block<br />
		Some randomness: <?php echo rand(1, 5000); ?>
		<!-- No ClickTale -->
	</body>
</html>
<?php
	require("../ClickTaleBottom.php");
?>
