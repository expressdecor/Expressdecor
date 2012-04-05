<?php
//(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(ga); last line of function
class Expressdecor_Googleanalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga {
	
    protected function _getPageTrackingCode($accountId)
    {
        $optPageURL = trim($this->getPageName());
        if ($optPageURL && preg_match('/^\/.*/i', $optPageURL)) {
            $optPageURL = "'{$this->jsQuoteEscape($optPageURL)}'";
        }
        // the code compatible with google checkout shortcut (it requires a global pageTracker variable)
        return "
    // the global variable is created intentionally
    var pageTracker =_gat._getTracker('{$this->jsQuoteEscape($accountId)}');
    pageTracker._trackPageview({$optPageURL});

";
    }
	
	  /**
     * Render information about specified orders and their items
     *
     * @see http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("pageTracker._addTrans('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
                $order->getIncrementId(), Mage::app()->getStore()->getFrontendName(), $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(), $order->getBaseShippingAmount(),
                $this->jsQuoteEscape($address->getCity()),
                $this->jsQuoteEscape($address->getRegion()),
                $this->jsQuoteEscape($address->getCountry())
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("pageTracker._addItem('%s', '%s', '%s', '%s', '%s', '%s');",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(), $item->getQtyOrdered()
                );
            }
            $result[] = "pageTracker._trackTrans();";
        }
        return implode("\n", $result);
    }
    
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
     
' . $this->_getPageTrackingCode ( $accountId ) . '
' . $this->_getOrdersTrackingCode () . '

  
//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->';
	}

}