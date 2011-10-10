<?php
class Brandammo_Pronav_Block_Pronav extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPronav()     
     { 
        if (!$this->hasData('pronav')) {
            $this->setData('pronav', Mage::registry('pronav'));
        }
        return $this->getData('pronav');
        
    }
}