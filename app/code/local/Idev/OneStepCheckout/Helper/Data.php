<?php
class Idev_OneStepCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function setCustomerComment($observer)
    {
        $enable_comments = Mage::getStoreConfig('onestepcheckout/exclude_fields/enable_comments');
        $enableFeedback = Mage::getStoreConfig('onestepcheckout/feedback/enable_feedback');

        if($enable_comments)	{
            $orderComment = $this->_getRequest()->getPost('onestepcheckout_comments');
            $orderComment = trim($orderComment);

            if ($orderComment != "")
            {
                $observer->getEvent()->getOrder()->setOnestepcheckoutCustomercomment($orderComment);
            }
        }

        if($enableFeedback){

            $feedbackValues = unserialize(Mage::getStoreConfig('onestepcheckout/feedback/feedback_values'));
            $feedbackValue = $this->_getRequest()->getPost('onestepcheckout-feedback');
            $feedbackValueFreetext = $this->_getRequest()->getPost('onestepcheckout-feedback-freetext');

            if(!empty($feedbackValue)){
                if($feedbackValue!='freetext'){
                    $feedbackValue = $feedbackValues[$feedbackValue]['value'];
                } else {
                    $feedbackValue = $feedbackValueFreetext;
                }

                $observer->getEvent()->getOrder()->setOnestepcheckoutCustomerfeedback($feedbackValue);
            }

        }
    }

    public function isRewriteCheckoutLinksEnabled()
    {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

    /**
     * If we are using enterprise wersion or not
     * @return boolean
     */
    public function isEnterPrise(){
        return (str_replace('.', '', Mage::getVersion()) > 1600) ? true : false;
    }
}
