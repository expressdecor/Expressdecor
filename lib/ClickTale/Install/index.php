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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Install ClickTale PHP Integration script</title>
		<link rel="stylesheet" href="yui/reset-fonts-grids.css" />
		<link rel="stylesheet" href="yui/base-min.css" />
	</head>
	<style type="text/css">
		
		#hd {
			background-color: #612D7F;
			padding: 10px;
			color: white;
			font-size:167%;
			text-align: right;
		}
		#hd img {
			vertical-align: middle;
			float: left;
		}
		
		#bd {
			padding: 10px;
			border: 2px solid #612D7F;
			border-style: none solid;
		}
		
		#ft {
			background-color: #612D7F;
			padding: 10px 5px;
		}
		
		div.intro {
			border-bottom: 1px dotted gray;
		}
		
		h3 {
			margin-left: 10px;
		}
		
    .invalid img.validation-icon {
      background-color:red;
    }
    .valid img.validation-icon {
      background-color:green;
    }
	
	
	
	img.validation-icon {
		width: 10px;
		height: 10px;
	}
	
	</style>
	<body>
<div id="doc">
	<div id="hd">
		<img src="img/hd_logo.png" />PHP Integration Module Installation
		<div style="clear:both;"><!-- --></div>
	</div>
	<div id="bd">
		
	
	
	<?php

  require_once('api.inc.php');

  $validator = new ClickTaleInstallValidator();

  ?>
<div class="intro">
        <p>
		  This page will help you to set up ClickTale integration module in your website.
		  </p>
		  <p>
		  	Legend: 
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<span class="valid"><img src="img/g.gif" class="validation-icon" /> - valid</span>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span class="invalid"><img src="img/g.gif" class="validation-icon" /> - invalid. need your attention</span>
      </p>
</div>
<?php

function curProtocolAndDomain() {
 $val = 'http';
 if ($_SERVER["HTTPS"] == "on") {$val .= "s";}
 $val .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $val .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $val .= $_SERVER["SERVER_NAME"];
 }
 return $val;
}

$config = ClickTale_Settings::Instance();
$cacheConfig = $config->getCacheProviderConfig();
$cacheProvider = ClickTale_CacheFactory::DefaultCacheProvider();
$validatorsValues = array();


// get the actual dir
$actualDir = str_replace(array("%CacheToken%","%ClickTaleCacheUrl%"),
						array("",ClickTale_PathToUrl(ClickTale_Root)), $config->CacheFetchingUrl);
$actualDir = dirname($actualDir);
// remove the http(s) at the begining
$actualDir = preg_replace("=^[^\\:]+://[^/]+=", "", $actualDir);

// get the expected dir
$expectedDir = dirname(dirname($_SERVER["REQUEST_URI"].'?'));

$curDomain = curProtocolAndDomain();

$validatorsValues['logsdir'] = array(
    'validate' => $validator->UsingLogging(),
    'isvalid' => $validator->IsLogsDirectoryWriteable(),

    'title' => 'Logging Directory',
    'p_off' => "Logging is turned off",
    'p_on_valid' => "Logging is turned on and logs directory is writable. Logs will be written to: ".dirname($config->LogPathMask),
    'p_on_invalid' => "Logging is turned on but your logs directory at: ".dirname($config->LogPathMask)." is NOT writable"
);

$validatorsValues['fetchfromurl'] = array(
    'validate' => true,
    'isvalid' => $actualDir == $expectedDir,

    'title' => 'Fetch From Directory',
    'p_off' => "--",
    'p_on_valid' => "The \"fetch from\" directory seems to be correct. '$expectedDir' match the actual '$actualDir'",
    'p_on_invalid' => "There is a problem with the \"fetch from\" path. Expected: '$expectedDir' does not match the actual '$actualDir'<br />".
		"Try to set CacheFetchingUrl config to \"{$curDomain}{$expectedDir}/ClickTaleCache.php?t=%CacheToken%\"".
		"<br />This might not be an error at all. Please make sure the caching test below working as well"
);


global $xmlErros;
$xmlErrors = array();

function handle_xml_errors($errno, $errstr, $errfile, $errline, $errcontext )
{
	global $xmlErrors;
	switch ($errno) {
		case E_ERROR:
			$prefix = "ERROR";
			break;
		case E_WARNING:
			$prefix = "WARNING";
			break;
		case E_NOTICE:
			$prefix = "NOTICE";
			break;
		case E_USER_ERROR:
			$prefix = "USER ERROR";
			break;
		case E_USER_WARNING:
			$prefix = "USER WARNING";
			break;
		case E_USER_NOTICE:
			$prefix = "USER NOTICE";
			break;
		default:
			$prefix = "error($errno)";
			break;
    }
	array_push($xmlErrors, "ERROR: $errstr");
}

set_error_handler("handle_xml_errors");

$scriptsFileValidate = ClickTale_ValidateScriptsXMLFile(ClickTale_Settings::Instance()->ScriptsFile);

if($scriptsFileValidate !== true) {
	array_push($xmlErrors, $scriptsFileValidate);
}

restore_error_handler();


$validatorsValues['scriptsfile'] = array(
    'validate' => true,
    'isvalid' => empty($xmlErrors),

    'title' => 'Scripts xml file',
    'p_off' => "--",
    'p_on_valid' => "The scripts file seems to be valid",
    'p_on_invalid' => "The scripts file is invalid:<br />".implode("<br />", $xmlErrors)
);

$someInvalid = false;
foreach($validatorsValues as $ar) {
	$someInvalid = $someInvalid || ($ar['validate'] && !$ar['isvalid']);
}
?>

		<div class="section">
      		<?php
				$phpversion = phpversion();
				$validVersion = version_compare($phpversion, '5.2.1', ">=");
				$class = !$validVersion ? 'invalid' : 'valid';
			
			?>
           <h2 class="<?php echo $class;?>"><img src="img/g.gif" class="validation-icon" /> Versions</h2>
           
		   <div class="sub-section">
                <h3 class="valid"><img src="img/g.gif" class="validation-icon" /> Module Version</h3>
				<p>
					You are currently running ClickTale PHP Integration module version {{{Version}}} ({{{Date}}})
				</p>
           </div>
		   
           <div class="sub-section">
                <h3 class="<?php echo $class;?>"><img src="img/g.gif" class="validation-icon" /> PHP Version</h3>
				<?php if($validVersion): ?>
                <p>
					You are running on PHP version <?php echo $phpversion; ?>
                </p>
				<?php else: ?>
				<p>
					You are running on PHP version <?php echo $phpversion; ?>.<br />
					We do not support this version officially however it should still work correctly.<br />
					Please test before deploying to production environment. 
                </p>
				<?php endif;?>
           </div>
      </div>


      <div class="section">
      		<?php $class = $someInvalid ? 'invalid' : 'valid'; ?>
           <h2 class="<?php echo $class;?>"><img src="img/g.gif" class="validation-icon" /> Validation</h2>
           
           <?php foreach($validatorsValues as $key=>$val) : ?>
           <?php $class = ($val['validate'] && !$val['isvalid']) ? 'invalid' : 'valid'; ?>
           <div class="sub-section">
                <h3 class="<?php echo $class;?>"><img src="img/g.gif" class="validation-icon" /> <?php echo $val['title']; ?></h3>
                <p>
                <?php
                  if($val['validate']) {
                    if($val['isvalid']) {
                      echo $val['p_on_valid'];
                    } else {
                      echo $val['p_on_invalid'];
                    }
                  } else {
                    echo $val['p_off'];
                  }
                ?>
                </p>
           </div>
           <?php endforeach;?>
      </div>
	  
	  
	  
	  
	  <?php
	  
	  	$cacheConfigValid = $cacheProvider->is_config_valid($cacheConfig);
		$cacheValidation = $cacheProvider->config_validation($cacheConfig);
	  
	  ?>
	  
	  <div class="section">
	  		<?php $class = !$cacheConfigValid ? 'invalid' : 'valid'; ?>
           <h2 class="<?php echo $class;?>"><img src="img/g.gif" class="validation-icon" /> Cache engine check</h2>
		   
		   <ul>
		   	<li> You are using <?php echo $config->CacheProvider; ?> caching.</li>
		   	<?php foreach($cacheValidation as $msg):?>
			<li><?php echo $msg;?></li>
			<?php endforeach;?>
		   </ul>
		   
		   <?php if($cacheConfigValid): ;?>
		   <p>
		   	On the left there is a page which will be cached using the settings you specified in the integration module's config.php<br />
			You should see the same page in the right frame where this page is retrieved from the cache
		   </p>
           <script type="text/javascript">
           	function refreshCacheFetch() {
				<?php
					/*
					$path = $_SERVER["PHP_SELF"];
					$port = $_SERVER["SERVER_PORT"] == 80 ? "" : ":".$_SERVER["SERVER_PORT"];
					$path = $_SERVER["SERVER_NAME"].$port.dirname(dirname($path));
					*/
					$fetchFrom = str_replace(array("%CacheToken%","%ClickTaleCacheUrl%"), array("CacheTest",ClickTale_PathToUrl(ClickTale_Root)), $config->CacheFetchingUrl);
				?>
				var path = "<?php echo $fetchFrom; ?>";
				document.getElementById("cache-page-fetch").src = path;//../ClickTaleCache.php?t=CacheTest";
			}
           	function refreshCacheTest() {
				document.getElementById("cache-test-page").src = "cache_test_page.php";
				document.getElementById("cache-page-fetch").src = "waiting_for_cache.html";
				var delay = parseFloat(document.getElementById("delay-before-fetching").value) || 0;
				delay = Math.round(delay * 1000);
				
				setTimeout(refreshCacheFetch, delay);
			}
           </script>
           <div class="sub-section">
           		
           		<a href="javascript:refreshCacheTest();">Retest cache</a>
				and wait <input type="text" style="width: 100px;" id="delay-before-fetching" value="0.5" /> sec for fetching
				<br /><br />
                <iframe id="cache-test-page" src="cache_test_page.php" width="200" height="80"></iframe>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<iframe id="cache-page-fetch" src="waiting_for_cache.html" width="200" height="80"></iframe>
           </div>
		   <script type="text/javascript">
		   		refreshCacheTest();		   
		   	</script>
		   <?php endif;?>
		   
      </div>
	
	</div>
	<div id="ft"></div>
</div>		
		
	</body>
</html>
