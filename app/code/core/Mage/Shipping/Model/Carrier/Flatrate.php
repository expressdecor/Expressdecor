<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Flat rate shipping model
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shipping_Model_Carrier_Flatrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'flatrate';
    protected $_isFixed = true;

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $freeBoxes = 0;
        $customPrice = 0;
        if ($request->getAllItems()) {
            //Mage::cDebug('shipping', print_r($request, TRUE));
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeBoxes += $item->getQty() * $child->getQty();
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
                
                $_product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                if($_product->getResource()->getAttribute('custom_ship_price')->getFrontend()->getValue($_product) > 0)
                    $customPrice += ($item->getQty() * $_product->getResource()->getAttribute('custom_ship_price')->getFrontend()->getValue($_product));
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');
        if ($this->getConfigData('type') == 'O') { // per order
            $shippingPrice = $this->getConfigData('price');
        } elseif ($this->getConfigData('type') == 'I') { // per item
            $shippingPrice = ($request->getPackageQty() * $this->getConfigData('price')) - ($this->getFreeBoxes() * $this->getConfigData('price'));
        } else {
            $shippingPrice = false;
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('flatrate');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('flatrate');
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';                                
            }
            
            
            //added changes
            ///////////////////////////////////////////////
            $nGrandTotal = $request->getPackageValue();
/* changed for new year*/
            if($nGrandTotal >= 0) {
                $shippingPrice = '0.00';                                
                $method->setMethodTitle("Free Shipping");
            }
            
            if($customPrice > 0) {
                $shippingPrice = $customPrice;                                
                $method->setMethodTitle("Freight Shipping Cost");
            }               
            ///////////////////////////////////////////////

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            
            if($request->getDestCountryId() != 'US') {
                $method->setMethodTitle("Once your order is submitted, you will be promptly notified of the additional shipping costs via e-mail.");
            }
            elseif(in_array($request->getDestRegionId(),array(52,2,21))) {
                $method->setMethodTitle("Once your order is submitted, you will be promptly notified of the additional shipping costs via e-mail.");
            }
            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        return array('flatrate'=>$this->getConfigData('name'));
    }

}
