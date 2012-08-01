<?php

/**
 * Adminhtml newsletter subscribers grid block
*
* @category   Expressdecor
* @package    Expressdecor_Ednewsletter
* @author      Expressdecor Team 
*/
class Expressdecor_Ednewsletter_Block_Adminhtml_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		$this->addColumn('subscriber_source', array(
				'header'    => Mage::helper('newsletter')->__('Subscriber Source'),
				'index'     => 'source'
		));
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
		$this->addColumn('subscriber_date', array(
				'header'    => Mage::helper('newsletter')->__('Date'),
				'type'          => 'datetime',
				'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
				'format'       => $dateFormatIso,
				'index'     => 'datecreated'
		));
		//return parent::_prepareColumns();
	}
}
