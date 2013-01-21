<?php
class Expressdecor_Sgrid_Block_Sales_Order_Grid extends Expressdecor_Adminhtml_Block_Sales_Order_Grid
{
 
	
/*	protected function _addColumnFilterToCollection($column)
    {
    	if ($column->getId() == 'is_kraus') {
    		 
    		$value=$column->getFilter()->getValue();
    		
    	    //$increments=array();
    	/*if ($value){
    		$this->filter=( ($value=='Yes') ? 1 : 2);
    		
    		foreach ($this->getCollection()->getItems() as $key=>$order) {
    			$show=0; //Default Kraus
    			foreach ($order->getItemsCollection() as $Item_key=>$item) {
    				$product=Mage::getModel('catalog/product')->load($item->getProductId());
    				if($product->getManufacturer() != 150){
    					$show=1; // Not Kraus
    				}
    			}
 			 
    			if ($value=='Yes' && $show==1){    				
    				array_push($increments,$order->getIncrementId());
    				//$this->getCollection()->addFieldToFilter('`main_table`.entity_id', array('neq' => $order->getId()));    
    				 
    			} elseif ($value=='No' && $show==0) {
    				array_push($increments,$order->getIncrementId());
    				//$this->getCollection()->addFieldToFilter('`main_table`.entity_id', array('neq' => $order->getId()));    
    			}
    			 
    		}*/
    	//	$this->getCollection()->addFieldToFilter('`main_table`.entity_id', array('eq' => '9324'));
    	 //	print_r($column->getFilter()->getCondition());
    		 
    		// $this->getCollection()->addFieldToFilter('entity_id', array('eq' => 9324));
    		//  $this->getCollection()->getSelect()->where('entity_id=50074');
    	//	$this->setCollection($collection);
    		// echo $this->getCollection()->getSelect(); 
    		// $this->increment_ids=implode(',',$increments);
    		// echo "a";
    		// echo $this->filter;
    		//} else {
    		//	$this->increment_ids=null;
    		//	$this->filter=null;
    		//}
    		
    		
    		//$column->setFilterIndex('increment_id');
    		//$column->setCondition(array('eq'=>'ED10012382'));
    		
    		//return $this;
    		//parent::_addColumnFilterToCollection($column);
    	//} else {  
    //		parent::_addColumnFilterToCollection($column);
   //	}
   // }	*/
    	/*protected function _prepareCollection()
    	{
    		echo "a";
    		$increment_ids=null;
    		$increments=array();
    		 
    		$collection = Mage::getResourceModel($this->_getCollectionClass());
    		//Expressdecor_Adminhtml_Block_Sales_Order_Grid
    		
    		$filter   = $this->getParam($this->getVarNameFilter(), null);
     
    		if (is_string($filter)) {
    			$data = $this->helper('adminhtml')->prepareFilterString($filter);
    			$filter_value=$data['is_kraus']; 
    		}
    		//$collection=Expressdecor_Adminhtml_Block_Sales_Order_Grid::_prepareCollection();
    		//$collection=$this->getCollection();
    		$collection->setPageSize((int) $this->getParam($this->getVarNameLimit(), 20));
    		//Mage_Adminhtml_Block_Widget_Grid::_preparePage();
    		echo $collection->getSelect(); 
    		//print_r($data['is_kraus']);
    		echo   $filter_value;
    	 	if ($filter_value){
    			//$filter=( ($filter_value=='Yes') ? 1 : 2);
    			//print_r(get_class_methods($collection->getColumn));
    			foreach ($collection->getItems() as $key=>$order) { 
    				$show=0; //Default Kraus
    				foreach ($order->getItemsCollection() as $Item_key=>$item) {
    					$product=Mage::getModel('catalog/product')->load($item->getProductId());
    					if($product->getManufacturer() != 150){
    						$show=1; // Not Kraus
    					}
    				}
    					 
    				if ($filter_value=='Yes' && $show==1){
    					array_push($increments,$order->getIncrementId());    				 
    						
    				} elseif ($filter_value=='No' && $show==0) {
    					array_push($increments,$order->getIncrementId());    				 
    				}
    			
    			}
    		}
    		$increment_ids=implode(',',$increments);
    	 
    		echo $increment_ids;
    		//print_r($this->getColumn('is_kraus')->getData());die();
    		    		    		
    		 
    	 
    	//$this->_addColumnFilterToCollection($column);
    	   
    		if (!empty($increment_ids)){
    			//$collection->getSelect()->where('increment_id',array('nin'=> $increment_ids));
    			$collection->addFieldToFilter('`main_table`.increment_id',array('nin'=> $increments));
    		}
    		echo $collection->getSelect();
    		//die();
    		$this->setCollection($collection);
    	//	echo $collection->getSelect();
    		//die();
    		return Expressdecor_Adminhtml_Block_Sales_Order_Grid::_prepareCollection();
    	}*/
    	
    	
 		
 	
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		$this->removeColumn('status');
		$this->addColumnAfter('status', array(
				'header' => Mage::helper('sales')->__('Status'),
				'index' => 'status',
				'type'  => 'options',
				'width' => '70px',
				'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
				'renderer'=>'Expressdecor_Sgrid_Block_Sales_Order_Renderer_Status'
		),'grand_total');
		
		/*$this->addColumnAfter('is_kraus', array(
				'header' => Mage::helper('sales')->__('Only Kraus?'),
				'index' => 'is_kraus',
				'type'  => 'options',
				'width' => '70px',
				'options' => array('Yes'=>'Yes', 'No'=>'No'),
				'filter_index'=> 'increment_id',
				'renderer'=>'Expressdecor_Sgrid_Block_Sales_Order_Renderer_Kraus'
		),'status');*/
		
		$this->sortColumnsByOrder();		
	}
}