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

require_once(ClickTale_Root."/ClickTale.inc.php");

class ClickTale_Settings
{
	function __construct() 
	{
   	 	require(ClickTale_Root."/config.php");
		/// ====================================================
		/// Settings values
		/// ====================================================
		$this->CacheProvider = $config['CacheProvider'];
		$this->ScriptsFile = $config['ScriptsFile'];
		$this->CacheScriptsFile = $config['CacheScriptsFile'];
		$this->MaxCachedSeconds = $config['MaxCachedSeconds'];
		$this->LogPathMask = $config['LogPathMask'];
		$this->DeleteAfterPull = $config['DeleteAfterPull'];
		$this->AllowDebug = $config['AllowDebug'];
		$this->DisableCache = $config['DisableCache'];
		$this->DisableFilter = $config['DisableFilter'];
		$this->DoNotProcessCookieName = $config['DoNotProcessCookieName'];
		$this->DoNotProcessCookieValue = $config['DoNotProcessCookieValue'];
		$this->AllowedAddresses = $config['AllowedAddresses'];
		$this->CacheLocation = $config['CacheLocation'];
		$this->MaxFolderSize = $config['MaxFolderSize'];
		$this->CacheFetchingUrl = $config['CacheFetchingUrl'];
		$this->LogCaching = $config['LogCaching'];
		$this->LogFetching = $config['LogFetching'];
		/// ====================================================
		///
		/// ====================================================
		$this->SystemTempDir = $config['SystemTempDir'];
		
	    $this->config = $config;
	}

	public static $hadRuntimeError = false;

	public $CacheProvider;
	public $ScriptsFile;
	public $CacheScriptsFile;
	public $MaxCachedSeconds;
	public $LogPathMask;
	public $DeleteAfterPull;
	public $AllowDebug;
	public $DisableCache;
	public $DisableFilter;
	public $DoNotProcessCookieName;
	public $DoNotProcessCookieValue;
	public $AllowedAddresses;
	public $MaxFolderSize;
	public $Version = "1.0.0.0b";
	public $SystemTempDir;
	
	public $UseStaticHash = false;
	public $StaticHash = "test";
	
	public $CacheFetchingUrl;
	
	public $config;
	
	static private $instanceSettings = null;
		
	static public function Instance()
	{
		// If there is not already an instance of this class, 
		//   instantiate one.
		if (self::$instanceSettings == null){
			self::$instanceSettings = new ClickTale_Settings;
		}
 
		return self::$instanceSettings;
	}
	
	// Gets the default configuration based on the settings.
	public function getCacheProviderConfig()
	{
		return array
		(
			"MaxCachedSeconds" => $this->MaxCachedSeconds,
			"DeleteAfterPull" => $this->DeleteAfterPull,
			"CacheLocation" => $this->CacheLocation,
			"SystemTempDir" => $this->SystemTempDir,
			"MaxFolderSize" => $this->MaxFolderSize
		);
	}
	
	/*
	function __get($id) { return $this->items[ $id ]; }
	*/
}

?>
