<?php
class Expressdecor_Tagsbanner_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout
extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout implements Varien_Data_Form_Element_Renderer_Interface
{
	 
	/**
	 * Generate url to get products chooser by ajax query
	 *
	 * @return string
	 */
	public function getTagsChooserUrl()
	{
		return $this->getUrl('*/*/tags', array('_current' => true));
	}
	
	
	/**
	 * Retrieve Display On options array.
	 * - Categories (anchor and not anchor)
	 * - Products (product types depend on configuration)
	 * - Generic (predefined) pages (all pages and single layout update)
	 *
	 * @return array
	 */
	protected function _getDisplayOnOptions()
	{
		$options = array();
		$options[] = array(
				'value' => '',
				'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('-- Please Select --'))
		);
		$options[] = array(
				'label' => Mage::helper('widget')->__('Categories'),
				'value' => array(
						array(
								'value' => 'anchor_categories',
								'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Anchor Categories'))
						),
						array(
								'value' => 'notanchor_categories',
								'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Non-Anchor Categories'))
						)
				)
		);
		foreach (Mage_Catalog_Model_Product_Type::getTypes() as $typeId => $type) {
			$productsOptions[] = array(
					'value' => $typeId.'_products',
					'label' => $this->helper('core')->jsQuoteEscape($type['label'])
			);
		}
		array_unshift($productsOptions, array(
				'value' => 'all_products',
				'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('All Product Types'))
		));
		$options[] = array(
				'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Products')),
				'value' => $productsOptions
		);
		$options[] = array(
				'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Generic Pages')),
				'value' => array(
						array(
								'value' => 'all_pages',
								'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('All Pages'))
						),
						array(
								'value' => 'pages',
								'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Specified Page'))
						)
						)
						); 
						$options[] = array(
								'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Tag Pages')),
								'value' => array(
										array(
												'value' => 'tags',
												'label' => $this->helper('core')->jsQuoteEscape(Mage::helper('widget')->__('Tag Pages'))
										)
								)
						);
						
						
						return $options;
						}

/**
* Generate array of parameters for every container type to create html template
*
* @return array
 */
public function getDisplayOnContainers(){
	
							$container = array();
							$container['anchor'] = array(
									'label' => 'Categories',
									'code' => 'categories',
									'name' => 'anchor_categories',
									'layout_handle' => 'default,catalog_category_layered',
									'is_anchor_only' => 1,
									'product_type_id' => ''
							);
							$container['notanchor'] = array(
									'label' => 'Categories',
									'code' => 'categories',
									'name' => 'notanchor_categories',
									'layout_handle' => 'default,catalog_category_default',
									'is_anchor_only' => 0,
									'product_type_id' => ''
							);
							$container['all_products'] = array(
									'label' => 'Products',
									'code' => 'products',
									'name' => 'all_products',
									'layout_handle' => 'default,catalog_product_view',
									'is_anchor_only' => '',
									'product_type_id' => ''
							);
							$container['tags'] = array(
									'label' => 'Tag Pages',
									'code' => 'tags',
									'name' => 'tags',
									'layout_handle' => 'default,cms_page',
									'is_anchor_only' => '',
									'product_type_id' => ''
							);
																		
							foreach (Mage_Catalog_Model_Product_Type::getTypes() as $typeId => $type) {
								$container[$typeId] = array(
										'label' => 'Products',
										'code' => 'products',
										'name' => $typeId . '_products',
										'layout_handle' => 'default,catalog_product_view,PRODUCT_TYPE_'.$typeId,
										'is_anchor_only' => '',
										'product_type_id' => $typeId
								);
							}
							return $container;
							}
								
						
}
