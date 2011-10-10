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

require_once(ClickTale_Root."/ClickTale.CachingProviders/ClickTale.CacheProvider.BaseCacheProvider.php");
require_once(ClickTale_Root."/ClickTale.Settings.php");

class ClickTale_CacheProvider_APC extends ClickTale_CacheProvider_BaseCacheProvider
{
	// Stores in cache. Overrides existing data.
	public function store($key, $value, $config)
	{
		$maxCachedSeconds = $config["MaxCachedSeconds"];

		if (!empty($maxCachedseconds))
			apc_store($key, $value, $maxCachedSeconds);
		else
			apc_store($key, $value);
	}
	
	// Returns FALSE if key does not exist. 
	public function pull($key, $config)
	{
		$deleteAfterPull = $config["DeleteAfterPull"];

		$value = apc_fetch($key);
		if (!empty($deleteAfterPull))
			$this->remove($key, $config);
		return $value;
	}
	
	public function remove($key, $config)
	{
		apc_delete($key);
	}
	
	public function exists($key, $config)
	{
		if (apc_fetch($key) != false)
			return true;
		else
			return false;
	}
	
	public function is_config_valid($config)
	{
		$valid = extension_loaded("apc");
		return $valid;
	}
	
	public function config_validation($config)
	{
		if($this->is_config_valid($config)) {
			return array(
				"Your configuration seem to be valid"
			);
		} else {
			return array(
				"PHP integration module seems to be mis-copnfigured or the ".
				"APC extension is not operational"
			);
		}
	}
	
}

?>
