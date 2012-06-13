<?php
class Expressdecor_Fixurl_Adminhtml_FixurlbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		Mage::getDesign()->setTheme('expressdecor');
		$this->loadLayout();
		$this->_title($this->__("Expressdecor.com :: update categories url"));
		$this->renderLayout();

	}

	public function postAction()
	{
		$post = $this->getRequest()->getPost();
		try {
			if (empty($post)) {
				Mage::throwException($this->__('Invalid request.'));
			}

			$urlmodel=Mage::getModel('core/url_rewrite')->getCollection()
			->addFieldToFilter('request_path', array(
			'like' => '%/%',
			))
			->addFieldToFilter('id_path', array(
			'like' => '%category%',
			))
			->addFieldToFilter('category_id', array(
			'gt' => 0,
			));

			$count=0;
			$inactive_cats=0;
			set_time_limit(0);

			foreach ($urlmodel as $url_id) {


				$cat_id=$url_id->getCategoryId();
				$req_path=$url_id->getRequestPath();

				$model=Mage::getModel('catalog/category')->load($cat_id);
				$status=$model->getIsActive();
				$product_id='';


				$ext=substr($req_path,strpos($req_path,'.'));

				// check if exists
				$check=Mage::getModel('core/url_rewrite')->getCollection()
				->addFieldToFilter('request_path', array(
				'eq' => $model->getUrlKey().$ext
				));

				//
				if ( ( $status==1) && (count($check->getAllIds()) < 1) ) {
					$url_id->setRequestPath($model->getUrlKey().$ext)->save();
					$count++;
				} elseif ( $status==1) {
					$warning.="Url for category # ".$cat_id." is not updated! This Url  already using by another page. <br>";
				}elseif ($status==0) {
					$inactive_cats++;
				}

				unset($model);
				unset($check);
			}

			$message= $this->__('Urls was updated successfully.<br> Updated: %s', $count);
			$message.= $this->__('<br> Inactive categories: %s',$inactive_cats);
			$message.= $this->__('<br> %s',$warning);
			Mage::getSingleton('adminhtml/session')->addSuccess($message);
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->indexAction();
	}
}