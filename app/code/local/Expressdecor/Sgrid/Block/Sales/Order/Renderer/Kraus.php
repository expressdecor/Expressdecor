<?php
class Expressdecor_Sgrid_Block_Sales_Order_Renderer_Kraus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {

	public function render(Varien_Object $row)
	{		
		$options = $this->getColumn()->getOptions();
		$show=0; // Kraus default  
		foreach ($row->getItemsCollection() as $item) {
			$product=Mage::getModel('catalog/product')->load($item->getProductId());
			if($product->getManufacturer() != 150)
				$show=1;
		}
		 
		
		//not will be shown only if one non kraus		 
		//150 Kraus manufaturerId
		if($show) {
			$value='No';
		}else  {
			$value='Yes';
		}				 
		return $value;			 		
	}
}