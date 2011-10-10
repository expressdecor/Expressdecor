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

define("CLICKTALE_INTEGRATION_MODULE_DIRECTORY", dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);

require(CLICKTALE_INTEGRATION_MODULE_DIRECTORY."ClickTaleInit.php");
require(CLICKTALE_INTEGRATION_MODULE_DIRECTORY."ClickTale.inc.php");



class ClickTaleInstallValidator {

      function IsCacheDirectoryWriteable() {
         $config = ClickTale_Settings::Instance()->getCacheProviderConfig();
         return is_writeable($config["CacheLocation"]);
      }
      
      function UsingFileSystemCache() {
         $config = ClickTale_Settings::Instance();
         return $config->CacheProvider == "FileSystem";
      }
      
      function IsLogsDirectoryWriteable() {
         $config = ClickTale_Settings::Instance();
         return empty($config->LogPathMask) || is_writeable(dirname($config->LogPathMask));
      }
      
      function UsingLogging() {
         $config = ClickTale_Settings::Instance();
         return !empty($config->LogPathMask);
      }

}

?>
