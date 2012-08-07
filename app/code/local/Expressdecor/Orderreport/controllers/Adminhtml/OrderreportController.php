<?php
class Expressdecor_Orderreport_Adminhtml_OrderreportController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
		
	}

	public function exportCsvAction() {
		$fileName = 'report_orders.csv';
		$content = $this->getLayout()->createBlock('orderreport/adminhtml_orderreport_grid')
		->getCsv();
		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction() {
		$fileName = 'report_orders.xml';
		$content = $this->getLayout()->createBlock('orderreport/adminhtml_orderreport_grid')
		->getXml();
		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK', '');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}

}