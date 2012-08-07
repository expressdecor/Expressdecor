<?php
class Expressdecor_Tagsbanner_Block_Adminhtml_Cms_Page_Widget_Chooser extends Mage_Adminhtml_Block_Cms_Page_Widget_Chooser
{

 
	/**
	 * Checkbox Check JS Callback
	 *
	 * @return string
	 */
	public function getCheckboxCheckCallback()
	{
		if ($this->getUseMassaction()) {
			return "function (grid, element) {
			$(grid.containerId).fire('product:changed', {element: element});
		}";
		}
	}
	
	
	/**
	 * Prepare columns for pages grid
	 *
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareColumns()
	{
		if ($this->getUseMassaction()) {
			$this->addColumn('page_id', array(
					'header_css_class' => 'a-center',
					'type'      => 'checkbox',
					'name'      => 'in_products',
					'inline_css' => 'checkbox entities',
					'field_name' => 'page_id',
					'values'    => $this->getSelectedProducts(),
					'align'     => 'center',
					'index'     => 'page_id',
					'use_index' => true,
			));
		}
		 
		return parent::_prepareColumns();
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/cms_page_widget/chooser', array('_current' => true,'uniq_id' => $this->getId(),
				'use_massaction' => $this->getUseMassaction(),));
	}
}