 <?php 
 
 require_once 'Mage/Widget/controllers/Adminhtml/Widget/InstanceController.php';
 
 class Expressdecor_Tagsbanner_Adminhtml_Widget_InstanceController extends Mage_Widget_Adminhtml_Widget_InstanceController {
 	
 	/**
 	 * Set body to response
 	 *
 	 * @param string $body
 	 */
 	private function setBody($body)
 	{
 		Mage::getSingleton('core/translate_inline')->processResponseBody($body);
 		$this->getResponse()->setBody($body);
 	}
 	
 	
 	public function tagsAction()
 	{
 		$selected = $this->getRequest()->getParam('selected', '');
 		$chooser = $this->getLayout()
 		->createBlock('adminhtml/cms_page_widget_chooser')
 		->setName(Mage::helper('core')->uniqHash('tags_grid_'))
 		->setUseMassaction(true)
 		->setSelectedProducts(explode(',', $selected));
 		/* @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer */
 		$serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
 		$serializer->initSerializerBlock($chooser, 'getSelectedTags', 'selected_tags', 'selected_tags');
 		$this->setBody($chooser->toHtml().$serializer->toHtml());
 	}
 	
 }
 
