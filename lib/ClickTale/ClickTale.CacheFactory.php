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

require_once(ClickTale_Root."/ClickTale.Settings.php");

final class ClickTale_CacheFactory
{	
	// Returns defaulrt provider
	public static function &DefaultCacheProvider()
	{
		return ClickTale_CacheFactory::CacheProvider(ClickTale_Settings::Instance()->CacheProvider);
	}
	
	// Returns default provider
	private static function &CacheProvider($providerName)
	{
		// We use include because we don't want to break the site if file does not exist. 
		//if (!@include_once(ClickTale_Root."/ClickTale.CachingProviders/ClickTale.CacheProvider.$providerName.php"))
		if (!include_once(ClickTale_Root.ClickTale_DS."ClickTale.CachingProviders".ClickTale_DS."ClickTale.CacheProvider.$providerName.php"))
			throw new Exception("CacheFactory could not find $providerName provider.");  

		$class = 'ClickTale_CacheProvider_' . $providerName;		
		if (class_exists($class))
		{
//			$ref = &new $class($options);
			$ref = new $class($options);
	        return $ref;
		}
		else
		{
			throw new Exception("CacheFactory could not find $class class.");  
		}
	}
}

?>
