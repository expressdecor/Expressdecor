<?php
class Expressdecor_Sgrid_Model_Invoice extends Expressdecor_Sgrid_Model_Pdf_Abstract
{
 
	
	/**
	 * Draw header for item table
	 *
	 * @param Zend_Pdf_Page $page
	 * @return void
	 */
	protected function _drawHeader(Zend_Pdf_Page $page)
	{
		/* Add table head */
		$this->_setFontRegular($page, 14);
		$page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->setLineWidth(0.5);
		$page->drawRectangle(14, $this->y+5, 580, $this->y -15);
		$this->y -= 10;
		$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
	
		//columns headers
		$lines[0][] = array(
				'text' => Mage::helper('sales')->__('Products'),
				'feed' => 35
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('SKU'),
				'feed'  => 390,
				'align' => 'right'
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('Qty'),
				'feed'  => 535,
				'align' => 'right'
		);
	
	
		$lineBlock = array(
				'lines'  => $lines,
				'height' => 5
		);
	
		$this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y -= 20;
	}
	
	/**
	 * Return PDF document
	 *
	 * @param  array $invoices
	 * @return Zend_Pdf
	 */
	public function getPdf($invoices = array())
	{
		 
		$this->_beforeGetPdf();
		$this->_initRenderer('invoice');
	
		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);
		 
		foreach ($invoices as $invoice) {
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->emulate($invoice->getStoreId());
				Mage::app()->setCurrentStore($invoice->getStoreId());
			} 
			$page  = $this->newPage();
			$order = $invoice->getOrder();
			/* Add image */
			$this->insertLogo($page, $invoice->getStore());
			/* Add address */
			$this->insertAddress($page, $invoice->getStore());
			/* Add head */
			$this->insertOrder(
					$page,
					$order,
					Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
			);
			 
			/* Add document text and number */
		 	$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		 	$this->_setFontRegular($page, 10);
		 	$docHeader = $this->getDocHeaderCoordinates();
		 	
			/* Add table */
			$this->_drawHeader($page);
			/* Add body */

			$products= Mage::getSingleton('core/app')->getRequest()->getParam('products');	

				foreach ($invoice->getAllItems() as $item){
					if ($item->getOrderItem()->getParentItem()) {
						continue;
					}
				/* Draw item */					 
					if  (count($products)>0) {
						if (in_array($item->getProductId(), $products)) {
							$this->_drawItem($item, $page, $order);
							$page = end($pdf->pages);
						}
					} else {
						$this->_drawItem($item, $page, $order);
						$page = end($pdf->pages);
					}
				} 					
			/* Add totals */
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->revert();
			}
		}
		$this->_afterGetPdf();
		return $pdf;
	}
	
	/**
	 * Create new page and assign to PDF object
	 *
	 * @param  array $settings
	 * @return Zend_Pdf_Page
	 */
	public function newPage(array $settings = array())
	{
		/* Add new table head */
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->_getPdf()->pages[] = $page;
		$this->y = 800;
		if (!empty($settings['table_header'])) {
			$this->_drawHeader($page);
		}
		return $page;
	}
}