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
/* Copyright 2008, ClickTale LTD. */

define("ClickTale_DS", strstr( PHP_OS, "WIN") ? "\\" : "/");

require_once(ClickTale_Root."/PHPCompat.php");

require_once(ClickTale_Root."/ClickTale.Settings.php");
require_once(ClickTale_Root."/ClickTale.CacheFactory.php");


function ClickTale_IsAllowedIp()
{
	$addresses = str_replace(""," ", ClickTale_Settings::Instance()->AllowedAddresses);
	$addresses = explode(",", $addresses);
	if (empty($addresses))
		return false;
		
	foreach($addresses as $value)
	{
		$value = explode("/", $value);

		if (sizeof($value)<2)
			$mask = 0xFFFFFFFF;
		else
			$mask = ((0x00000001 << intval(@$value[1])) - 1) * (0x00000001 << (32 - intval(@$value[1])));
		
		if ((ClickTale_IpToInteger($_SERVER["REMOTE_ADDR"]) & $mask) == (ClickTale_IpToInteger($value[0]) & $mask))
			return true;
	}
	return false;
}

// Converts ip to hexadecimal integer.
function ClickTale_IpToInteger($ip)
{
	$ipArr = explode(".", $ip);
	
	for($i = 0; $i < 4; $i++)
		if (empty($ipArr[$i]))
			$ipArr[$i] = 0;
	
	$retVal  = intval($ipArr[0]) << 24;
	$retVal += intval($ipArr[1]) << 16;
	$retVal += intval($ipArr[2]) << 8;
	$retVal += intval($ipArr[3]); 
	
	return $retVal;
}

/// This function check a cookie value to control recording logic.
/// The main use of this is to check if the value of the WRUID cookie is set to 0 which means "ClickTale don't record user"
/// This allow us to save on processing and caching for users who don't fall into the "recording ratio".
/// The problem with this method is that client side code can change the value of the cookie, thus WRUID can be 0 at first 
/// and then changed for this page in the client side without the server side knowing about it. This usually means that the user is using our API
/// or doing some other fancy manipulation. In those cases, the user will want to not use this feature.
function ClickTale_CheckCookieFlagForRecording()
{
	$name = @ClickTale_Settings::Instance()->DoNotProcessCookieName;
	$expectedValue = @ClickTale_Settings::Instance()->DoNotProcessCookieValue;
	
	if (empty($name) || empty($expectedValue) || empty($_COOKIE[$name])) {
		return false;
	}
	
	$value = @$_COOKIE[$name];
	return $value == $expectedValue;
}

// Wraps with braces '(', ')' if already not wrapped.
// This in needed because we want preg_replace treat it as whole unit.
function ClickTale_NormalizeRegex($str)
{
	if (!empty($str) && $str[0] != '(' && $str[strlen($str) - 1] != ')')
	{
		$str = "(".$str.")";
	}
	return $str;
}

// Returns true if file changed from the last time or if it
// is the first call to this function.
function ClickTale_FileChanged($path)
{
	$key = "TimeStamp_".md5($path);

	$cacheProvider = ClickTale_CacheFactory::DefaultCacheProvider();
	$config = ClickTale_Settings::Instance()->getCacheProviderConfig();
	$config["DeleteAfterPull"] = false;
	
	if ($cacheProvider->exists($key, $config)) {
		$timestamp = $cacheProvider->pull($key, $config);
	} else {
		$timestamp = null;
	}
		
	//clearstatcache(); // Not necessary.
	if (empty($timestamp) or $timestamp != filemtime($path))
	{
		$config = ClickTale_Settings::Instance()->getCacheProviderConfig();
		$config["MaxCachedSeconds"] = false;
		$cacheProvider->store($key, filemtime($path), $config);
		return true;
	}
	return false;
}

function ClickTale_ValidateScriptsXMLFile($path)
{
	if (!file_exists($path)) 
		return "Could not find file: $path.";
	
	try {
		$xml = new DOMDocument();
		$xml->load($path);
	} catch(Exception $ex)
	{
		return $ex.message;
	}
	
	return true;
}

function ClickTale_LoadScripts($path)
{
	$defaultScriptsFileValues = array (
		"TopScript" => "",
		"BottomScript" => "",

		"TopDoNotReplaceCondition" => "",
		"BottomDoNotReplaceCondition" => "",
		"TopInsertAfter" => "",
		"BottomInsertBefore" => "</body>",
	);
	
	// We need this even if there is a cached scripts file 
	// because the timestamp needs to be checked.
	if (!file_exists($path)) 
		throw new Exception("Could not find file: $path."); 
	
	$key = "Data_".md5($path);
	$cacheProvider = ClickTale_CacheFactory::DefaultCacheProvider();
	
	// If available, get scripts data from cache. 
	if (ClickTale_Settings::Instance()->CacheScriptsFile && !ClickTale_FileChanged($path))
	{
		$config = ClickTale_Settings::Instance()->getCacheProviderConfig();
		// make sure we don't remove the cached scripts file after pull. This is done on a new copy of the config array
		$config["DeleteAfterPull"] = false;
		if ($cacheProvider->exists($key, $config))
		{
			$data = $cacheProvider->pull($key, $config);
			return unserialize($data);
		}
	} 

	try {
		
		$xml = new DOMDocument();
		$succ = $xml->load($path);
		
		if(!$succ) {
			ClickTale_Logger::Write("Error - Can't process the xml scripts file. Make sure it's valid XML.");
			ClickTale_Settings::$hadRuntimeError = true;
			$returnItems = $defaultScriptsFileValues;
		} else {
			// Find name=Top and name=Bottom elements using xpath.
			$xpath = new DOMXPath($xml);
			$top = $xpath->query("script[@name=\"Top\"]"); 
			$bottom = $xpath->query("script[@name=\"Bottom\"]");
		
			// name=Top and name=Bottom elements are mandatory.
			if (empty($top) || empty($bottom)) 
				throw new Exception("Scripts file must contain <script> elements. One with name=top and one with name=bottom."); 
			
			$returnItems = array
			(
				"TopScript" => $top->item(0)->textContent,
				"BottomScript" => $bottom->item(0)->textContent,
		
				"TopDoNotReplaceCondition" => @$top->item(0)->attributes->getNamedItem("DoNotReplaceCondition")->value,
				"BottomDoNotReplaceCondition" => @$bottom->item(0)->attributes->getNamedItem("DoNotReplaceCondition")->value,
				"TopInsertAfter" => @$bottom->item(0)->attributes->getNamedItem("InsertAfter")->value,
				"BottomInsertBefore" => @$bottom->item(0)->attributes->getNamedItem("InsertBefore")->value,
			);
			
			// Store in cache so we will not need to read from file each page request.
			if(ClickTale_Settings::Instance()->CacheScriptsFile) {
				$config = ClickTale_Settings::Instance()->getCacheProviderConfig();
				$config["MaxCachedSeconds"] = false;
				$cacheProvider->store($key, serialize($returnItems), $config);
			}
		}
	}
	catch (Exception $ex)
	{
		ClickTale_Settings::$hadRuntimeError = true;
		$returnItems = $defaultScriptsFileValues;
	}
	
	return $returnItems;
}

// Generates a string that represents a random hexacecimal number.
function ClickTale_RandHash($length)
{
// Another option: substr(md5(rand()), 0, 8); 

	$chars = 'abcdef0123456789';
    $string = '';
    for ($i = 0; $i < $length; $i++)
    {
        $pos = rand(0, strlen($chars)-1);
        $string .= $chars{$pos};
    }
    return $string;
}


// Converts physical path to url representation
function ClickTale_PathToUrl($path)
{
	// We use current url, file path and relation between them to extract info.
	$currentUrl = ClickTale_ForwardSlash($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]);
	$currentPath = ClickTale_ForwardSlash($_SERVER["SCRIPT_FILENAME"]);
	$path = ClickTale_ForwardSlash($path);
	
	$prefixLength = ClickTale_SharedPrefixLength($currentPath, $path); // Prefix of $currentPath.
	
	$relative = substr($path, $prefixLength - 1);
	
	$suffixLength = strlen($currentPath) - $prefixLength;  // Suffix of $currentPath.
	
	$root = substr($currentUrl, 0, strlen($currentUrl) - $suffixLength - 1);
	
	//return "_".$relative."_";
	
	//return "_".$_SERVER["SCRIPT_NAME"]."__".$_SERVER["PHP_SELF"]."_".$_SERVER["ORIG_PATH_INFO"]."_";
	
	return $root.$relative;
}

// Gets the length of the the first shared part between two strings
function ClickTale_SharedPrefixLength($a, $b)
{
	$length = 0;
	while(strlen($a) > $length && strlen($b) > $length && $a[$length] == $b[$length])
		$length++;
		
	return $length;
}
/*
// Gets the length of the last shared part  of two strings
function ClickTale_SharedSuffixLength($a, $b)
{
	$length = 0;
	$ia = strlen($a);
	$ib = strlen($b);
	while($ia > 0 && $ib > 0 && $a[$ia] == $b[$ib])
	{
		$length++;
		$ia--;
		$ib--;
	}	
	return $length;
}
*/
// Convert all slashes to '/'
function ClickTale_ForwardSlash($url)
{
	$urlArr = explode("\\", $url);
	
	foreach($urlArr as $key => $value) 
	{ 
		if(empty($value)) 
			unset($urlArr[$key]); 
	}
	return implode("/", $urlArr); 

	//return  str_ireplace("\\", "/", $url); // This is not correct. Some paths may already have double back-slashes.
}





/**
 * Inserts ClickTale script to the buffered page.
 * @param string $buffer The buffered page before injecting ClickTale scripts
 * @param array $data Hash of the ClickTale script to inject into the buffer
 * @param string $hash A key used to save this page in the cache
 * @return string The buffer with the ClickTale scripts injected into it (if required)
 */
function ClickTale_Filter($buffer, $data, $hash)
{
	$settings = ClickTale_Settings::Instance();
	$config = $settings->getCacheProviderConfig();
	  
	// create token replacements arrays
	$tokens = array();
	
	$tokens["%FetchFromUrl%"] = str_replace(array("%CacheToken%","%ClickTaleCacheUrl%"),
		array($hash,ClickTale_PathToUrl(ClickTale_Root)), $settings->CacheFetchingUrl);
	$tokens["\\"] = "\\\\";
	$tokens["$"] = "\\$";
	  
	$tokenKeys = array_keys($tokens);
	$tokenValues = array_values($tokens);
	
	$topDoNotReplaceWasFound = !empty($data["TopDoNotReplaceCondition"]) &&
		preg_match(ClickTale_NormalizeRegex($data["TopDoNotReplaceCondition"]), $buffer);
		
	$bottomDoNotReplaceWasFound = !empty($data["BottomDoNotReplaceCondition"]) &&
		preg_match(ClickTale_NormalizeRegex($data["BottomDoNotReplaceCondition"]), $buffer);
	
	if (!empty($data["TopScript"]) && !$topDoNotReplaceWasFound)
	{
		$data["TopScript"] = str_replace($tokenKeys, $tokenValues, $data["TopScript"]);
		$topInsertAfter = ClickTale_NormalizeRegex($data["TopInsertAfter"]);
		if (empty($topInsertAfter)) {
			$topInsertAfter = "(<body.*?>)"; // Default value.
		}
		$topInsertAfter = str_replace("/", "\\/", $topInsertAfter);
		$buffer = preg_replace("/$topInsertAfter/i", "$0".$data["TopScript"], $buffer, 1);
	}	
	
	if (!empty($data["BottomScript"]) && !$bottomDoNotReplaceWasFound)
	{
		$data["BottomScript"] = str_replace($tokenKeys, $tokenValues, $data["BottomScript"]);
		$bottomInsertBefore = ClickTale_NormalizeRegex($data["BottomInsertBefore"]);
		if (empty($bottomInsertBefore)) {
			$bottomInsertBefore = "(</body>)"; // Default value.
		}
		$bottomInsertBefore = str_replace("/", "\\/", $bottomInsertBefore);
		$buffer = preg_replace("/$bottomInsertBefore/i", $data["BottomScript"]."$0", $buffer, 1);
	}
	
	return $buffer;
}


function ClickTale_ProcessOutput_inject($buffer)
{
	if (ClickTale_CheckCookieFlagForRecording()) 
		return $buffer;
	try
	{
		// Data contains data from the scripts file.
		$data = ClickTale_LoadScripts(ClickTale_Settings::Instance()->ScriptsFile); 
	}
	catch (Exception $ex)
	{
		ClickTale_Logger::Write($ex->getMessage());
		return $buffer;
	}
	
	$settings = ClickTale_Settings::Instance();
	
	// Cache
	try
	{
		if (empty($settings->DisableCache))
		{
			$hash = $settings->UseStaticHash ? $settings->StaticHash : ClickTale_RandHash(20);
			$cacheProvider = ClickTale_CacheFactory::DefaultCacheProvider();
			$config = ClickTale_Settings::Instance()->getCacheProviderConfig();
			$hashlen = strlen($hash);
			if($hashlen <= 4) {
				$tok = "hash";
			} else {
				$tok = substr($hash, $hashlen - 4);
			}
			if(!empty($settings->LogCaching)) {
				ClickTale_Logger::Write("BeginCache '$tok' for ".$_SERVER["REQUEST_URI"]);
			}
			$cacheProvider->store($hash, $buffer, $config);
			if(!empty($settings->LogCaching)) {
				ClickTale_Logger::Write("EndCache '$tok' for ".$_SERVER["REQUEST_URI"]);
			}
		}
	}
	catch (Exception $ex)
	{
		ClickTale_Logger::Write($ex->getMessage());
		return $buffer;
	}
	
	
	// Filter
	if (empty(ClickTale_Settings::Instance()->DisableFilter))
	{
		$buffer = ClickTale_Filter($buffer, $data, $hash);
	}
	
	return $buffer;
}

function ClickTale_HasGZipEncodingHeader()
{
	foreach(headers_list() as $i => $header)
	{
		if(preg_match("/\\s*Content-Encoding:\\s*gzip\\s*/i", $header)) {
			return true;
		}
	}
	return false;
}

// error handler used to not prevent the page content from displaying
function ClickTale_BufferErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
	return true;
}

function ClickTale_ProcessOutput($buffer) {
	$bufferCopy = $buffer;
	set_error_handler("ClickTale_BufferErrorHandler");
	
	try {
		$gzip = false;
		// decode gzipped content if there is gzip encoding header
		if(ClickTale_HasGZipEncodingHeader()) {
			$gzip = true;
			$buffer = gzdecode($buffer);
			// log this
			//ClickTale_Logger::Write("Decoding gzipped buffer. Might affect performance.");
		}
		
		$buffer = ClickTale_ProcessOutput_inject($buffer);
		
		// re-encode the buffer in gzip encoding if we decoded it before
		if($gzip) {
			$buffer = gzencode($buffer);
		}
	} catch (Exception $ex){
		ClickTale_Settings::$hadRuntimeError = true;
	}
	
	restore_error_handler();
	if(ClickTale_Settings::$hadRuntimeError) {
		return $bufferCopy."<!-- ClickTale PHP Integration module failed to inject ClickTale script -->";
	} else {
		return $buffer;
	}
}




function ClickTale_DebugBuffers()
{	
	// if we don't have the callback registered here, then we have a problem
	if(!in_array("ClickTale_callback", ob_list_handlers())) {
		ClickTale_Logger::Write("The output buffer callback does not exist in the buffers stack.");
	}
	
	if(!ClickTale_Settings::Instance()->AllowDebug) {
		return;
	}
	
	ClickTale_Logger::Write("Serialized Buffer Hanlders: ".join(", ", ob_list_handlers()));

	function clicktale_stringify_buffer_status($level, $stats)
	{
		$status = $stats['status'];
		switch($stats['status']){
			case 0:
				$status = "start";
				break;
			case 1:
				$status = "cont";
				break;
			case 2:
				$status = "end";
				break;
		}
		
		return "Buffer {$stats['name']} level: {$level} status: $status del: {$stats['del']} type: {$stats['type']}";
	}
	
	foreach(ob_get_status(true) as $level => $clicktale_buffer)
	{
		ClickTale_Logger::Write("Serialized Buffer Status: ".clicktale_stringify_buffer_status($level, $clicktale_buffer));
		if($clicktale_buffer["name"] == "ob_gzhandler") {
			ClickTale_Logger::Write("Warning: You are using ob_gzhandler. This prevents ClickTale from injecting scripts");
		}
	}
}


?>
