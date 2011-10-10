<?php

class Expressdecor_Sales_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
	/**
     * Draw item line
     *
     */
	public function draw()
	{
		$order  = $this->getOrder();
		$item   = $this->getItem();
		$pdf    = $this->getPdf();
		$page   = $this->getPage();
		$lines  = array();

		// draw Product name
		$lines[0] = array(array(
		'text' => Mage::helper('core/string')->str_split($item->getName(), 45, true, true), //Alex changes 7/20
		'feed' => 20,
		));

		// draw SKU
		$lines[0][] = array(
		'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
		'feed'  => 255,
		'font'  => 'bold' //Alex changes 7/20
		);

		// draw QTY
		$lines[0][] = array(
		'text'  => $item->getQty()*1,
		'feed'  => 435
		);

		// draw Price
		$lines[0][] = array(
		'text'  => $order->formatPriceTxt($item->getPrice()),
		'feed'  => 410, //Alex changes 7/20
		'font'  => 'bold',
		'align' => 'right'
		);

		// draw Tax
		$lines[0][] = array(
		'text'  => $order->formatPriceTxt($item->getTaxAmount()),
		'feed'  => 500, //Alex changes 7/20
		'font'  => 'bold',
		'align' => 'right'
		);

		// draw Subtotal
		$lines[0][] = array(
		'text'  => $order->formatPriceTxt($item->getRowTotal()),
		'feed'  => 575, //Alex changes 7/20
		'font'  => 'bold',
		'align' => 'right'
		);

		// custom options
		$options = $this->getItemOptions();
		if ($options) {
			foreach ($options as $option) {
				// draw options label
				$lines[][] = array(
				'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
				'font' => 'italic',
				'feed' => 20
				);

				if ($option['value']) {
					$_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
					$values = explode(', ', $_printValue);
					foreach ($values as $value) {
						$lines[][] = array(
						'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
						'font'  => 'bold', //Alex changes 7/20
						'feed' => 25
						);
					}
				}
			}
		}

		$lineBlock = array(
		'lines'  => $lines,
		'height' => 11 //Alex changes 7/20
		);

		$page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
		$this->setPage($page);
	}
}
