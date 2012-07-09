<?php
class Expressdecor_Tagsbanner_Model_Widget_Instance extends Mage_Widget_Model_Widget_Instance
{
	
	const SINGLE_CMS_PAGE_LAYOUT_HANDLE    = 'cms_page_{{ID}}';
	const SINGLE_CMS_PAGE_LAYOUT_HANDLE_INSTANT   = 'cms_page_ed';
	
	/**
	 * Internal Constructor
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->_init('widget/widget_instance');
		$this->_layoutHandles = array(
				'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
				'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
				'all_products' => self::PRODUCT_LAYOUT_HANDLE,
				'all_pages' => self::DEFAULT_LAYOUT_HANDLE,
				'tags' =>  self::SINGLE_CMS_PAGE_LAYOUT_HANDLE_INSTANT,
		);
		$this->_specificEntitiesLayoutHandles = array(
				'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
				'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
				'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANLDE,
				'tags'=> self::SINGLE_CMS_PAGE_LAYOUT_HANDLE,
		);
	
		foreach (Mage_Catalog_Model_Product_Type::getTypes() as $typeId => $type) {
			$layoutHandle = str_replace('{{TYPE}}', $typeId, self::PRODUCT_TYPE_LAYOUT_HANDLE);
			$this->_layoutHandles[$typeId . '_products'] = $layoutHandle;
			$this->_specificEntitiesLayoutHandles[$typeId . '_products'] = self::SINGLE_PRODUCT_LAYOUT_HANLDE;
		}
	}
	
}