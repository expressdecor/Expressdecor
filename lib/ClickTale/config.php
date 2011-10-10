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

$config = array();

// Url pattern for getting a cached page
// use %CacheToken% as a placeholder for the token
// use %ClickTaleCacheUrl% can be used as the url to ClickTaleCache.php
$config['CacheFetchingUrl'] = "http://%ClickTaleCacheUrl%/ClickTaleCache.php?t=%CacheToken%";

// Cache provider name (for example "APC").
// Look into "ClickTale\ClickTale.CachingProviders" for all aviable providers.
//$config['CacheProvider'] = "MySQLMemory";
$config['CacheProvider'] = "FileSystem";

// The path(relative to ClickTale root directory) of the file that
// contains clicktale scripts.
$config['ScriptsFile'] = ClickTale_Root.ClickTale_DS."ClickTaleScripts.xml";

// When used with some CacheProviders, this will improve performance.
$config['CacheScriptsFile'] = true;

// Number of seconds to keep data in cache provider. Infinite if false or 0.
$config['MaxCachedSeconds'] = 60;

// Represents a path to the log file (relative to the "ClickTale\Logs" directory.
// "{0}" will be replaced with actual date. For disabling logging,
// set this param to false or empty string.
$config['LogPathMask'] = ClickTale_Root.ClickTale_DS."Logs".ClickTale_DS."Log_{0}.txt";

// If false, data will not be deleted after 1 request. Allows more than once fetch per data item.
$config['DeleteAfterPull'] = true;

$config['AllowDebug'] = false;

$config['DisableCache'] = false;

$config['DisableFilter'] = false;

// If there is a cookie with DoNotProcessCookieName and DoNotProcessCookieValue value
$config['DoNotProcessCookieName'] = "WRUID";

// then there will be no caching or filtering.
$config['DoNotProcessCookieValue'] = "0";

// A list of allowed ip masks separated by comma.
$config['AllowedAddresses'] = "75.125.82.64/26";

// The location where cached files will be stored (works with FileSystem provider).
$config['CacheLocation'] = ClickTale_Root.ClickTale_DS."Cache";
//$config['CacheLocation'] = "http://<username>:<password>@<host>:<port>/<db name>.<table name>";

// Must be integers. Measured in megabytes.
$config['MaxFolderSize'] = 50;

$config['SystemTempDir'] = sys_get_temp_dir();

// used to log the token generated in the cache phase
$config['LogCaching'] = false;
// used to log the token requested in the fetch phase
$config['LogFetching'] = false;


?>
