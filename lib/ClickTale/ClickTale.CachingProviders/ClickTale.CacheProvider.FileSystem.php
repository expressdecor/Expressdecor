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
require_once(ClickTale_Root."/ClickTale.Logger.php");

class ClickTale_CacheProvider_FileSystem extends ClickTale_CacheProvider_BaseCacheProvider
{
	// Stores in cache. Overrides existing data.
	public function store($key, $value, $config)
	{
		if (!empty($config["MaxFolderSize"])) {
			$this->clean($config);
		}
		
		$fp = fopen($this->path($key, $config), 'ab');
        if (is_resource($fp)) {
            flock ($fp, LOCK_EX);
            ftruncate($fp, 0);
            fseek($fp, 0);

            fwrite($fp, $value);
            fclose($fp);
            clearstatcache();
        }
	}

	// Returns FALSE if key does not exist. 
	public function pull($key, $config)
	{
		$deleteAfterPull = $config["DeleteAfterPull"];

		if (!$this->exists($key, $config)) {
			throw new Exception("File system cache provider was not able to find file $key ");
		}
		
        $fp = fopen($this->path($key, $config), 'rb');
        if (is_resource($fp)) {
            flock ($fp, LOCK_SH);

            $value = fread($fp, filesize($this->path($key, $config)));
            flock($fp, LOCK_UN); 
			fclose($fp);
			
			if (!empty($deleteAfterPull))
				$this->remove($key, $config);
			
			//$this->clean($config);
			
			return $value;
        }
	}
	
	public function remove($key, $config)
	{
		if ($this->exists($key, $config))
			unlink($this->path($key, $config));
	}
	
	public function exists($key, $config)
	{
		if (file_exists($this->path($key, $config)))
			return true;
		else
			return false;
	}
	
	// Constructs a path out of the key.
	private function path($key, $config)
	{
		if (empty($config["CacheLocation"])) {
			// mkdir
			return SystemTempDir.ClickTale_DS."ClickTale.Cache".ClickTale_DS.$key;
		} else {
			return $config["CacheLocation"].ClickTale_DS.$key;
		}
	}
	
	private function clean($config) 
	{
		$maxFolderSize = intval($config["MaxFolderSize"]) * 1024 * 1024; // The config value is in mega bytes.
		//throw new Exception("Test");
		$directoryPath = $this->path("", $config);
		
		if (!$this->exists("", $config) || !is_dir($directoryPath)) {
			throw new Exception("Error cleaning cache directory");
		}
		
		// Calculate the current size of all files
		$size = 0;
		foreach (new DirectoryIterator($directoryPath) as $file) {
			if (true === $file->isFile()) {
				$size += filesize ($file->getPathName()); 
			}
		}
		
		if ($size >= $maxFolderSize) {
			foreach (new DirectoryIterator($directoryPath) as $file) {
				if (true === $file->isFile()) {
					unlink($file->getPathName());
				}
			}
		}
	}
	
	
	public function is_config_valid($config)
	{
		return is_writeable($config["CacheLocation"]);
	}
	
	public function config_validation($config)
	{
		if($this->is_config_valid($config)) {
			return array(
				"Cache directory is writable"
			);
		} else {
			return array(
				"Cache directory at: {$config['CacheLocation']} is NOT writable"
			);
		}
	}
}

?>
