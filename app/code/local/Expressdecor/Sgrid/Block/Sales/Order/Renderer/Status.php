<?php
class Expressdecor_Sgrid_Block_Sales_Order_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
	
   public function render(Varien_Object $row)
    {    	
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            /*Color status*/
            $show=0; // not  show default all
            foreach ($row->getItemsCollection() as $item) {            
            	$product=Mage::getModel('catalog/product')->load($item->getProductId());
            	if($product->getManufacturer() != 150)
            		$show=1;
            	}
            	 //not will be shown only if one non kraus
        	/*Color status*/
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
            	/*Color Status*/
            	//150 Kraus manufaturerId
            	if($show && $options[$value]=='Complete') {
            		$options[$value]='<font style="background-color:#00ff00;">'.$this->escapeHtml($options[$value]).'</font>';
            	}elseif ($show) {
            		$options[$value]='<font style="background-color:yellow;">'.$this->escapeHtml($options[$value]).'</font>';
            	}
            	
            	return $options[$value];
            	/*Color status*/
               // return $this->escapeHtml($options[$value]);
            } elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }
}