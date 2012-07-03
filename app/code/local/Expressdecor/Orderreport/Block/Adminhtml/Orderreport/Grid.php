<?php
class Expressdecor_Orderreport_Block_Adminhtml_Orderreport_Grid extends Mage_Adminhtml_Block_Report_Grid {
 
    public function __construct() {
    	 
        parent::__construct();
        $this->setId('reportsGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);       
        $this->setSubReportSize(false); 
        
    }
 
    protected function _prepareCollection() {
       	parent::_prepareCollection();     	
        $this->getCollection()->initReport('orderreport/orderreport');          
       return  $this; 
    }
 
    protected function _prepareColumns() {
    	$this->addColumn('order_date', array(
    			'header'    =>Mage::helper('orderreport')->__('Order Date'),
    			'align'     =>'right',
    			'index'     =>'created_at',
    			'type'      =>'datetime'
    	));
    	
    	
        $this->addColumn('order_increment_id', array(
            'header'    =>Mage::helper('orderreport')->__('Order #'),
            'align'     =>'right',
            'index'     =>'increment_id',
            'type'      =>'text'
        ));
                
        
        $this->addColumn('order_customerfirst', array(
        		'header'    =>Mage::helper('orderreport')->__('Customer First Name'),
        		'align'     =>'right',
        		'index'     =>'customer_firstname',
        		'type'      =>'text'
        ));
        
        $this->addColumn('order_customerlast', array(
        		'header'    =>Mage::helper('orderreport')->__('Customer Last Name'),
        		'align'     =>'right',
        		'index'     =>'customer_lastname',
        		'type'      =>'text'
        ));
        
        $this->addColumn('order_user', array(
        		'header'    =>Mage::helper('orderreport')->__('Order Created By'),
        		'align'     =>'right',
        		'index'     =>'track_user2',
        		'type'      =>'text'
        ));
        
        $this->addColumn('order_status', array(
        		'header'    =>Mage::helper('orderreport')->__('Status'),
        		'align'     =>'right',
        		'index'     =>'status_label',
        		'type'      =>'text'
        ));
        
        $this->addColumn('order_channel', array(
        		'header'    =>Mage::helper('orderreport')->__('Channel'),
        		'align'     =>'right',
        		'index'     =>'channel',
        		'type'      =>'text'
        ));
        
        
        $this->addColumn('order_skus', array(
        		'header'    =>Mage::helper('orderreport')->__('Items'),
        		'align'     =>'right',
        		'index'     =>'skus',
        		'type'      =>'text'
        ));

        $this->addColumn('grand_total', array(
        		'header' => Mage::helper('orderreport')->__('Amount'),
        		'align' => 'right',
        		'width'     =>'60px',
        		'index' => 'grand_total',
        		'type'  => 'number',
        	    'total' => 'sum',
        ));
        
        $this->addColumn('tax_amount', array(
            'header' => Mage::helper('orderreport')->__('Tax'),
            'align' => 'right',
        	'width'     =>'60px',
            'index' => 'tax_amount',
            'type'  => 'number',
            'total' => 'sum',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('orderreport')->__('CSV'));
   //     $this->addExportType('*/*/exportXml', Mage::helper('orderreport')->__('XML'));
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row) {
        return false;
    }
 
}