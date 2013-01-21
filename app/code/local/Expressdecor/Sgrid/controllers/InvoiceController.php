<?php
class Expressdecor_Sgrid_InvoiceController extends  Mage_Adminhtml_Controller_Action {
 
	/**
	 * Create pdf for current invoice
	 */
	public function printAction()
	{		
		$this->_title($this->__('Sales'))->_title($this->__('Invoices for Vendors'));
		 
		$invoice = false;
		$itemsToInvoice = 0;
		$invoiceId = $this->getRequest()->getParam('invoice_id');
		$orderId = $this->getRequest()->getParam('order_id');
		 
		if ($invoiceId) {
			$invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
			if (!$invoice->getId()) {
				$this->_getSession()->addError($this->__('The invoice no longer exists.'));
				return false;
			}
		}
		
		Mage::register('current_invoice', $invoice);
		 
		/*print action*/
	    if ($invoiceId) {	    	
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {                          	               
            	$pdf = Mage::getModel('sgrid/invoice')->getPdf(array($invoice));
            	$order=Mage::getModel('sales/order')->load($orderId);
            	$order->addStatusHistoryComment('Vendor Invoice Printed')->save();
                $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }                
	}
	
	public function NewInvoiceAction(){
		$id = (int) $this->getRequest()->getParam('order_id');

		$this->loadLayout();
		$this->_addBreadcrumb(Mage::helper('sgrid')->__('Form'), Mage::helper('sgrid')->__('Upload Invoice'));
		$this->getLayout()->getBlock('content')->append(
				$this->getLayout()->createBlock('sgrid/sales_order_tab_vendor_form')
				->setOrderid($id)								 
		);
		
		$uploader=new Mage_Adminhtml_Block_Media_Uploader();
		$uploader->getConfig()
		->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('sgrid/invoice/save',array('order_id'=>$id)))
		->setFileField('inv')
		->setFilters(array(
				'images' => array(
						'label' => Mage::helper('adminhtml')->__('Files (.doc, .pdf)'),
						'files' => array('*.pdf', '*.doc')
				)
		))
		;
		$this->getLayout()->getBlock('content')->append(
				$this->getLayout()->createBlock($uploader)
		);
		
		$this->renderLayout();
 
	}
	public function saveAction(){
		
		$id = (int) $this->getRequest()->getParam('order_id');
		$dirname='vendor_invoice';
		$path=Mage::getBaseDir('media') . DS .$dirname;
		
		try {
		$params = $this->getRequest()->getParams();
		 		
		if (!file_exists ( $path)) {
			mkdir($path,0777);
		}		 
		
		$uploader = new Mage_Core_Model_File_Uploader('inv');
		$uploader->setAllowedExtensions(array('pdf','doc'));
 
		$uploader->setAllowRenameFiles(true);
		//$uploader->setFilesDispersion(true);
		$result = $uploader->save($path);								
	 
      		 /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            $name=$result['file'];
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
		/*Save value into DB*/
        $model=Mage::getModel('sgrid/sgrid')->setOrderId($id)->setFilename($name)->save();
        /*Add comment*/
        $order=Mage::getModel('Sales/Order')->load($id);
        $order->addStatusHistoryComment('Vendor Invoice Uploaded')->save();
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	/**
	 * Generate shipments grid for ajax request
	 */
	public function getInvoicesTabAction()
	{
		$id = (int) $this->getRequest()->getParam('order_id');		 
		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('sgrid/sales_order_tab_vendor')->setOrderid($id)->toHtml()
		);
	}
	
	/**
	 * Download Invoice
	 */
	public function downloadAction(){
		
		$session = Mage::getSingleton('admin/session');
		if ($session->isFirstPageAfterLogin()) {
			$this->_redirect($session->getUser()->getStartupPageUrl());
			return $this;
		}
		 
		$id = $this->getRequest()->getParam('id');
		$model=Mage::getModel('sgrid/sgrid')->load($id);
		$invoice=$model->getFilename();
		$dirname='vendor_invoice';
		
		$path=Mage::getBaseDir('media').DS.$dirname.DS;
	
		if($invoice &&  $session->isLoggedIn() ){
 
			$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', 'application/octet-stream', true)
			->setHeader('Content-Length',  filesize($path.$invoice), true)
			->setHeader('Content-Disposition', 'attachment; filename="'.$invoice.'"', true)
			->setHeader('Last-Modified', date('r'), true);
			
			$this->getResponse()->clearBody();
			$this->getResponse()->sendHeaders();		 
			$ioAdapter = new Varien_Io_File(); 
			$ioAdapter->cd($path); 
			$ioAdapter->streamOpen($invoice, 'r');
			while ($buffer = $ioAdapter->streamRead()) {
				print $buffer;
			}
			$ioAdapter->streamClose();
			exit(0);
		} else {
			$this->_forward('noRoute');
		}
	}
	
	public function ChooseproductsAction(){
		$id = (int) $this->getRequest()->getParam('order_id');
	
		$this->loadLayout();
		$this->_addBreadcrumb(Mage::helper('sgrid')->__('Form'), Mage::helper('sgrid')->__('Choose Products'));
		$this->getLayout()->getBlock('content')->append(
				$this->getLayout()->createBlock('sgrid/sales_order_product_product')
				->setOrderid($id)
		);

	
		$this->renderLayout();
	
	}
	
}