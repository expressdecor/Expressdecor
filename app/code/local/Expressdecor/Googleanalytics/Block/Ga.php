<?php
//(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(ga); last line of function
class Expressdecor_Googleanalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga {
	
	/**
	 * Render GA tracking scripts
	 *
	 * @return string
	 */
	protected function _toHtml() {
		if (! Mage::helper ( 'googleanalytics' )->isGoogleAnalyticsAvailable ()) {
			return '';
		}
		$accountId = Mage::getStoreConfig ( Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT );
		return '
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[

    var _gaq = _gaq || [];
' . $this->_getPageTrackingCode ( $accountId ) . '
' . $this->_getOrdersTrackingCode () . '

   (function() {
        var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
        ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';        
         var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);        
    })();
//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->';
	}

}