<?php
	class Expressdecorcom_Commercebug_IndexController extends Mage_Core_Controller_Front_Action
	{

		public function scanAction()
		{
			$h = Mage::helper('commercebug/corescan');
			// $h->createSnapshotFromBase('test_snap_shot');			
			
			foreach(glob('/Users/alanstorm/Documents/magento-archives/dir-*') as $file)
			{
				$version = preg_replace('%/Users/alanstorm/Documents/magento-archives/dir-magento-(.+?)\.tar\.bz2%','$1',$file);
				if(strlen($version) ==5)
				{
					$version .= '.0';
				}
				$h->createSnapshotFromFiles($version,
				$file .'/magento/app/code/core',
				$file .'/magento/lib',
				$file .'/magento/');
			}
			
// 			$h->createSnapshotFromFiles('1.4.1.1',
// 			'/Users/alanstorm/Documents/magento-archives/dir-magento-1.4.1.1.tar.bz2/magento/app/code/core',
// 			'/Users/alanstorm/Documents/magento-archives/dir-magento-1.4.1.1.tar.bz2/magento/lib',
// 			'/Users/alanstorm/Documents/magento-archives/dir-magento-1.4.1.1.tar.bz2/magento/');
			
			var_dump('done');
		}
		
		public function diffAction()
		{		
			Mage::getSingleton('commercebug/corescan')->defaultScan();						
			$block = $this->getLayout()->createBlock('commercebug/corescan');
			$this->loadLayout();
			$this->getLayout()->getBlock('content')->append($block);
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
			$this->renderLayout();
// 			foreach($diffs->diffs as $diff)
// 			{
// 				var_dump($diff->getData());
// 			}
			//var_dump(Mage::getVersion());
// 			$helper_diff = Mage::helper('commercebug/diff');
// 			$foo = array('one','The quick brown fox','three');
// 			$bar = array('one','two','three');			
// 			$output = $helper_diff->diff($foo, $bar);
// 			echo ("<pre>$output</pre>");
		}
		
		public function arborderAction()
		{
			//select task_id from task where task_id > 320 order by find_in_set(task_id,'324,322,321,323');
			
			$ids = array(16,18,17,19);
			$products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id',$ids);			

// 			$products->getSelect()->order("find_in_set(e.entity_id,'".implode(',',$ids)."')");
			$products->addOrder('e.entity_id', 'desc');
			foreach($products as $product)
			{
				var_dump($product->getEntityId());
				var_dump($product->getSku());
			}
			
		}

		
		public function indexAction()
		{
// 			foreach(Mage::getModel('review/review')->getCollection() as $review)
// 			{
// 				var_dump($review->getRatingSummary());
// 			}
			$this->loadLayout();
			
			$this->getLayout()->getMessagesBlock()->addError('This is in the messages block');

			Mage::getSingleton('core/session')->addError('An Error');
			Mage::getSingleton('core/session')->addWarning('A Warning');
			Mage::getSingleton('core/session')->addNotice ('A Notice');
			Mage::getSingleton('core/session')->addSuccess('A Success');    			
			
			
			
			$this->renderLayout();		
			return;

// 			var_dump(
// 			Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn()
// 			);
// 			$test = new stdClass();
// 			$test->foo = 'This is a test';
// 			$model = Mage::getSingleton('commercebug/jsonbroker')->jsonEncode($test);			
// 			$model = Mage::getSingleton('commercebug/jsonbroker')->jsonDecode($model);
// 			var_dump($model);
			
			exit("here");
// 			var_dump($this->getRequest()->getParams());
// 			exit;
			//will output the same as above
// 			try
// 			{
				echo $foo;
				echo "\nDone\n";
// 			}
// 			catch(Exception $e)
// 			{
// 				echo "\nPHP hates me, and will never call me, and still let the users
// 				see that god awful notice text. *cries* \n";
// 			}		
			exit;		
			$c = Mage::getResourceModel('sales/report_bestsellers_collection');			
			foreach($c as $item)
			{
				var_dump($item->getData());
			}
			
// 			var_dump(
// 				Mage::getModel('catalog/product')
// 				->getCollection()
// 				->addAttributeToSelect('*')
// 				->getFirstItem()
// 				->getData()
// 			);

// 			$resources = simplexml_load_string(Mage::getModel('admin/roles')
// 			->getResourcesTree()
// 			->asXml());			
// 			//header('Content-Type: text/xml');
// 			//echo $resources;
// 
// 			$nodes = $resources->xpath('//*[@aclpath]');			
// 			echo '<dl>';
// 			foreach($nodes as $node)
// 			{
// 				echo '<dt>' . (string)$node->title . '</dt>';
// 				//echo '<dd>' . $node->getAttribute('aclpath') . '</dd>';
// 			}
// 			echo '</dl>';

// 			$base_path = Mage::getBaseDir('base');
// 			var_dump($base_path);
// 		
// 			$etc_path = Mage::getBaseDir('etc');
// 			var_dump($etc_path);
	var_dump( Mage::getModuleDir('', 'Mage_Core') );
	var_dump( Mage::getModuleDir('etc', 'Mage_Core') );
	var_dump( Mage::getModuleDir('controllers', 'Mage_Core') );
	var_dump( Mage::getModuleDir('sql', 'Mage_Core') );
	var_dump( Mage::getModuleDir('locale', 'Mage_Core') );	

	var_dump('--------------------------------------------------');
	var_dump(Mage::getBaseDir('app'));
	var_dump(Mage::getBaseDir('base')   );
	var_dump(Mage::getBaseDir('code')   );
	var_dump(Mage::getBaseDir('design') );
	var_dump(Mage::getBaseDir('etc')    );
	var_dump(Mage::getBaseDir('lib')    );
	var_dump(Mage::getBaseDir('locale') );
	var_dump(Mage::getBaseDir('media')  );
	var_dump(Mage::getBaseDir('skin')   );
	var_dump(Mage::getBaseDir('var')    );
	var_dump(Mage::getBaseDir('tmp')    );
	var_dump(Mage::getBaseDir('cache')  );
	var_dump(Mage::getBaseDir('log')    );
	var_dump(Mage::getBaseDir('session'));
	var_dump(Mage::getBaseDir('upload') );
	var_dump(Mage::getBaseDir('export') );	
// 			$customers = Mage::getModel('customer/customer')
// 			->getCollection()
// 			->addFieldToFilter('website_id','1');;
// 			foreach($customers as $customer)
// 			{
// 				echo 'Setting ', $customer->getEmail(), "'s website ID<br />";
// 				$customer->setWebsiteId('2');				
// 				$customer->save();
// 			}
		
// 			$products = Mage::getModel('catalog/product')
// 			->getCollection();
// 			
// 			foreach($products as $product)
// 			{
// 				//var_dump($product->getSku());
// 			}
// 			
// 			$test = Mage::getResourceModel('samscatalog/product_collection');			
// 			var_dump($test);
// 			$this->loadLayout();
// 			$this->renderLayout();
		}
		
		public function sortedAction()
		{
			$resources = Mage::getModel('admin/roles') // api/roles
			->getResourcesTree();
			//->asXml();			
			$nodes = $resources->xpath('//*[@aclpath]');			
			$by_title 		= array();
			$by_resource 	= array();
			$resources		= array();
			$titles			= array();
			
			foreach($nodes as $node)
			{
				$titles[]										= (string)$node->title;
				$resources[]									= $node->getAttribute('aclpath');
				$by_title[(string)$node->title]					= $node->getAttribute('aclpath');
				$by_resource[$node->getAttribute('aclpath')]	= (string)$node->title;
			}
			sort($resources);
			sort($titles);

// 			var_dump($resources);
// 			var_dump($titles);
			
			echo '<dl>';			
			foreach($resources as $resource)
			{
				$title = $by_resource[$resource];
				echo '<dt style="font-weight:bold;">' . (string) $title . '</dt>';
				echo '<dd>' . $resource . '</dd>';				
			}
			echo '</dl>';	
			
// 			echo '<dl>';
// 			foreach($nodes as $node)
// 			{
// 				echo '<dt>' . (string)$node->title . '</dt>';
// 				echo '<dd>' . $node->getAttribute('aclpath') . '</dd>';
// 			}
			echo '</dl>';									
		}
		
		public function skuAction()
		{
			$c = Mage::getModel('api/roles')->getCollection();
			$c->addFieldToFilter(array(
			array('foo','bar'),
			),array(1));
// 			$c = Mage::getModel('')->getCollection();
// 			foreach($c as $item)
// 			{
// 				var_dump($item->getData());
// 			}
// 			var_dump('sdfasd');
		}
		
		public function indexagainAction()
		{
			$resources = Mage::getModel('api/roles')
			->getResourcesTree();
			
			header('Content-Type: text/xml');
			echo trim($resources->asXml());
		}
		
		public function fooAction()
		{
			$this->loadLayout();
			$this->renderLayout();		
		}
		
		public function productAction()
		{
			$product 		= Mage::getModel('catalog/product')->load(41);			
			$attribute_set 	= Mage::getModel('eav/entity_attribute_set')->load(
				$product->getAttributeSetId()
			);			
			var_dump($attribute_set->getAttributeSetName());
		}
		
		public function testSnapshotAction()
		{
			$model = Mage::getModel('commercebug/snapshot_name');
			var_dump($model);
		}
	}