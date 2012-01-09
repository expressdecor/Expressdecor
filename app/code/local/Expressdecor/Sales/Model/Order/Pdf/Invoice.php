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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Expressdecor_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	public function getPdf($invoices = array())
	{
		$this->_beforeGetPdf();
		$this->_initRenderer('invoice');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10); //Alex changes 7/20

		foreach ($invoices as $invoice) {
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->emulate($invoice->getStoreId());
				Mage::app()->setCurrentStore($invoice->getStoreId());
			}
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$pdf->pages[] = $page;

			$order = $invoice->getOrder();

			/* Add image */
			$this->insertLogo($page, $invoice->getStore());

			/* Add address */
			$this->insertAddress($page, $invoice->getStore());

			/* Add head */
			$this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));


			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));//Alex changes 7/20
			$this->_setFontRegular($page,12);  //Alex changes 7/20
			$page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 480, 780, 'UTF-8'); //Alex changes 7/20
			$this->_setFontRegular($page);  //Alex changes 7/20


			/* Add table */
			$page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
			$page->setLineWidth(0.5);

			$page->drawRectangle(15, $this->y, 580, $this->y -15); //Alex changes 7/20
			$this->y -=10;

			/* Add table head */
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0)); //Alex changes 7/20
			$this->_setFontBold($page);  //Alex changes 7/20
			$page->drawText(Mage::helper('sales')->__('Products'), 35, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');
			$this->_setFontRegular($page);//Alex changes 7/20
			$this->y -=15;

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

			/*check for foubles if configurable*/
			$skus=array();
			/* Add body */
			foreach ($invoice->getAllItems() as $item){
				if ($item->getOrderItem()->getParentItem()) {
					continue;

				}
				
				if (!in_array($item->getOrderItem()->getSku(),$skus)) {


					if ($this->y < 15) {
						$page = $this->newPage(array('table_header' => true));
					}

					/* Draw item */
					$page = $this->_drawItem($item, $page, $order);
					array_push($skus,$item->getOrderItem()->getSku());
				}
			}


			/* Add totals */
			$page = $this->insertTotals($page, $invoice);



			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->revert();
			}

			////////////////////////////////////////////////////

			/***** Begin order comment modification *****/
			if($this->_getCustomerOrderComments($invoice)){
				$this->_setFontBold($page);
				$page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
				$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
				$page->setLineWidth(0.5);
				$page->drawRectangle(15, $this->y, 580, $this->y-15);
				$this->y -=10;
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0)); //Alex changes 7/20
				$this->_setFontBold($page);  //Alex changes 7/20
				$page->drawText('Order comments:', 25, $this->y, 'UTF-8');
				$this->_setFontRegular($page);//Alex changes 7/20
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
				$this->y -=20;

				$ar_comments = $this->_getCustomerOrderComments($invoice);

				foreach($ar_comments as $comment) {
					//wrap
					$comment = wordwrap($comment,118,"\n",false);
					$token = strtok($comment, "\n");

					while ($token != false) {
						$page->drawText($token, 25, $this->y, 'UTF-8');
						$this->y-=15;
						$token = strtok("\n");
					}

					$this->y -=5;
				}
				$this->_setFontRegular($page);
			}
			/***** end order comment modification *****/

			////////////////////////////////////////////////////
		}

		$this->_afterGetPdf();

		return $pdf;
	}


	protected function _getCustomerOrderComments($invoice)
	{
		$ar_comments = array();
		$_comments = $invoice->getCommentsCollection();
		foreach ($_comments as $comment) {
			array_push($ar_comments, $comment->getComment());
		}

		if(isset($ar_comments)) {
			return $ar_comments;
		}

		return false;
	}


}
