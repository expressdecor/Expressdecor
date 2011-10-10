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

abstract class  ClickTale_CacheProvider_BaseCacheProvider
{
	/**
	 * Stores $value in the cache in the $key palace
	 * Will override the value if the key is already defined
	 * @param string $key
	 * @param string $value
	 * @param array $config
	 */
	abstract public function store($key, $value, $config);
	
	/**
	 * 
	 * @return string The cached page matching $key, or FALSE if there is no page
	 * for the given key
	 * @param object $key
	 * @param object $config
	 */
	abstract public function pull($key, $config);
	
	/**
	 * Remove a cached page
	 * @param object $key
	 * @param object $config
	 */
	abstract public function remove($key, $config);
	
	/**
	 * Check if there is a cached page for the given $key
	 * @return True if page exists, False otherwise
	 * @param object $key
	 * @param object $config
	 */
	abstract public function exists($key, $config);
	
	/**
	 * For installation purposes, check if the settings for this cache are valid
	 * (like accessible directory..)
	 * @return boolean True if the $config is valid, false otherwise
	 * @param array $config
	 */
	abstract public function is_config_valid($config);
	
	/**
	 * Return array of validation messages
	 * (directory is writeable..)
	 * @return array Array of messages to display the user
	 * @param array $config
	 */
	abstract public function config_validation($config);
	
	public function refresh($key, $config) {
		return null;
	}
}

?>
