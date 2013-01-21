<?php
class  Expressdecor_Sgrid_Model_Pdf_Items extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default {
	
	public function draw()
	{
		$order  = $this->getOrder();
		$item   = $this->getItem();
		$pdf    = $this->getPdf();
		$page   = $this->getPage();
		$lines  = array();
	
		// draw Product name
		$lines[0] = array(array(
				'text' => Mage::helper('core/string')->str_split($item->getName(), 45, true, true),
				'feed' => 35,
				'font' => 'bold'
		));
	
		// draw SKU
		$lines[0][] = array(
				'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 27),
				'feed'  => 410,
				'align' => 'right',
				'font' => 'bold'
		);
	
		// draw QTY
		$lines[0][] = array(
				'text'  => $item->getQty() * 1,
				'feed'  => 535,
				'align' => 'right',
				'font' => 'bold'
		);
	
		// custom options
		$options = $this->getItemOptions();
		if ($options) {
			foreach ($options as $option) {
				// draw options label
				$lines[][] = array(
						'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true),
						'font' => 'italic',
						'feed' => 35
				);
	
				if ($option['value']) {
					if (isset($option['print_value'])) {
						$_printValue = $option['print_value'];
					} else {
						$_printValue = strip_tags($option['value']);
					}
					$values = explode(', ', $_printValue);
					foreach ($values as $value) {
						$lines[][] = array(
								'text' => Mage::helper('core/string')->str_split($value, 30, true, true),
								'feed' => 40
						);
					}
				}
			}
		}
	
		$lineBlock = array(
				'lines'  => $lines,
				'height' => 20
		);
	
		$page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
		$this->setPage($page);
	}
}