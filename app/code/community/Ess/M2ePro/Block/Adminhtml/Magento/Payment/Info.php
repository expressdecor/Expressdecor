<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Payment_Info extends Mage_Payment_Block_Info
{
    private $order = NULL;

    // ########################################

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('M2ePro/magento/order/payment/info.phtml');
    }

    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array(
            '_relative' => true,
            '_area' => 'adminhtml',
            '_package' => 'default',
            '_theme' => 'default'
        );

        return Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
    }

    // ########################################

    private function getAdditionalData($key = '')
    {
        $additionalData = @unserialize($this->getInfo()->getAdditionalData());

        if ($key === '') {
            return $additionalData;
        }

        return isset($additionalData[$key]) ? $additionalData[$key] : NULL;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            // do not replace registry with our wrapper
            if ($this->hasOrder()) {
                $this->order = $this->getOrder();
            }
            if (Mage::registry('current_order')) {
                $this->order = Mage::registry('current_order');
            }
            if (Mage::registry('order')) {
                $this->order = Mage::registry('order');
            }
        }

        return $this->order;
    }

    public function getPaymentMethod()
    {
        return (string)$this->getAdditionalData('payment_method');
    }

    public function getChannelOrderId()
    {
        return (string)$this->getAdditionalData('channel_order_id');
    }

    public function getChannelFinalFee()
    {
        return !$this->getIsSecureMode() ? (float)$this->getAdditionalData('channel_final_fee') : 0;
    }

    public function getChannelTitle()
    {
        $title = '';

        if ($this->getAdditionalData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $title = Ess_M2ePro_Helper_Component_Ebay::TITLE;
        } else if ($this->getAdditionalData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $title = Ess_M2ePro_Helper_Component_Amazon::TITLE;
        }

        return $title;
    }

    public function getTransactions()
    {
        $transactions = !$this->getIsSecureMode() ? $this->getAdditionalData('transactions') : array();

        return is_array($transactions) ? $transactions : array();
    }

    // ########################################

    public function toPdf()
    {
        $this->setTemplate('M2ePro/magento/order/payment/pdf.phtml');
        return $this->toHtml();
    }

    // ########################################
}