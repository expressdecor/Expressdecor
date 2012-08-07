<?php
class Expressdecor_Orderreport_Model_Orderreport extends  Mage_Reports_Model_Mysql4_Order_Collection
{
	function __construct() {
	 	 parent::__construct(); 
	}

 
	
    public function setDateRange($from, $to) {
   	$this->_reset()
   	->addAttributeToSelect('*')
   	->addAttributeToFilter('main_table.created_at', array('from' => $from, 'to' => $to));
   	$this->getSelect()->distinct(true)   	
   					  ->group('main_table.entity_id')
        			  ->joinInner(array(
   								'i' => 'sales_flat_order_status_history'),
   								'i.parent_id = main_table.entity_id',array('track_user2'=>'i.track_user')
   							     )
   					  ->joinInner(array(
    			  		 		'it' => 'sales_flat_order_item'),
     			 		 	  	'it.order_id = main_table.entity_id',array('skus'=>new Zend_Db_Expr('group_concat(it.sku)'))
     			 	  			 )    
   					  ->joinInner(array(
   								'st' => 'sales_order_status'),
   								'st.status = main_table.status',array('status_label'=>'st.label')
   					  );
	   	
   	//echo $this->getSelect()->__toString();
		return $this;
	}

	public function setStoreIds($storeIds)
	{
		return $this;
	}
	
 
}