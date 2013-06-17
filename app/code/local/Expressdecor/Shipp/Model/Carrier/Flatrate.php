<?php
/// IT DOESNT WORK - LOOK at Tinybrick
/*class Expressdecor_Shipping_Model_Carrier_Flatrate  extends Mage_Shipping_Model_Carrier_Flatrate
    implements Mage_Shipping_Model_Carrier_Interface
{

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
            if($nGrandTotal >= 100) {
                $shippingPrice = '0.00';                                
                //$method->setMethodTitle("Fixed"); //Google checkout problem was Free Shipping same TinyBrick order edit 
            }
            
            if($customPrice > 0) {
                $shippingPrice = $customPrice;                                
               // $method->setMethodTitle("Fixed");//Google checkout problem was  Freight Shipping Cost
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

}
*/