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
if (!defined('ClickTale_Root'))
{
	$pathinfo = pathinfo(__FILE__);
	define ("ClickTale_Root", $pathinfo["dirname"]);
}

require_once(ClickTale_Root."/ClickTale.Settings.php");
require_once(ClickTale_Root."/ClickTale.inc.php");
require_once(ClickTale_Root."/ClickTale.Logger.php");
?>

<?php
if (empty(ClickTale_Settings::Instance()->AllowDebug)) die("Debug mode is disabled.");
?>

<h1> ClickTale Debug </h1><br>

<?php

try
{
	$data = ClickTale_LoadScripts(ClickTale_Settings::Instance()->ScriptsFile); // Data contains data from the scripts file.
	foreach($data as $var => $value)
		echo "<b>$var</b><br>".htmlspecialchars($value)."<br>";
}
catch (Exception $ex)
{
	echo $ex->getMessage()."<br>";
	ClickTale_Logger::Write($ex->getMessage());
}

$settingsInstance = ClickTale_Settings::Instance();
foreach($settingsInstance as $var => $value)
	echo "<b>$var</b><br>$value<br>";
?>
<b>ClickTale_Logger::getFullPath()</b><br>
<?php echo ClickTale_Logger::getFullPath(); ?><br>
<b>Latest log</b><br>
<div style="border: solid;"><?php echo nl2br(ClickTale_Logger::Read()); ?></div>

<b>ClickTale_CheckCookieFlagForRecording()</b><br><?php echo ClickTale_CheckCookieFlagForRecording(); ?><br>

<b>ClickTale_IsAllowedIp()</b><br><?php echo ClickTale_IsAllowedIp(); ?><br>

<b>Default cache provider configuration</b><br>
<div style="border: solid;"><pre><?php print_r(ClickTale_Settings::Instance()->getCacheProviderConfig());  ?></pre</div>
