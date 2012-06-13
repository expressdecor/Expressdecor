<?php

class Expressdecor_Sales_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{
    public function getPdf($shipments = array())
    {
    	//parent::getPdf($shipments = array());
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $order = $shipment->getOrder();

            /* Add image */
            $this->insertLogo($page, $shipment->getStore());

            /* Add address */
            $this->insertAddress($page, $shipment->getStore());

            /* Add head */
            $this->insertOrder($page, $shipment, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId()), 'packingslip');

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));  //Alex changes 7/21
            $this->_setFontRegular($page,12); //Alex changes 7/21
            $page->drawText(Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId(), 460, 780, 'UTF-8');   //Alex changes 7/21

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);


            /* Add table head */
            $page->drawRectangle(15, $this->y, 580, $this->y-15);   //Alex changes 7/21
            $this->y -=10;
            $this->_setFontBold($page);  //Alex changes 7/20
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0)); //Alex changes 7/20
            $page->drawText(Mage::helper('sales')->__('Qty'), 25, $this->y, 'UTF-8');   //Alex changes 7/21
            $page->drawText(Mage::helper('sales')->__('Products'), 60, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 440, $this->y, 'UTF-8');   //Alex changes 7/21
            $this->_setFontRegular($page);//Alex changes 7/20
            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            
            foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y<15) {
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }
        }

        $this->_afterGetPdf();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

  
}
