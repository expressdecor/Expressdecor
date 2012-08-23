<?php
class Expressdecor_Bannersales_Model_Syncbanners {	
	
	/**
	 *  Getting all banners and check if they related to Catalog or Shopping cart Rules
	 *  And make Banners Active or InActive 
	 *	Banner will be Active if One of Rule (Catalog or Price ) Will be active
	 * @param unknown_type $schedule
	 */
	public function scheduledsync($schedule="")
	{ 
		$banner_model =Mage::getModel('enterprise_banner/banner')->getCollection();
		 foreach ($banner_model as $banner ) {
		 	 $banner_change_status=0;
			 $banner_status=$banner->getIsEnabled();
		 	 
		  	 $catalog_rule=$this->getRelatedCatalogRule($banner->getId());
		 	 $shopping_cart_rule=$this->getRelatedSalesRule($banner->getId());
		 	 
		 	 $today_date= strtotime(date('Y-m-d'));
		 	 // Catalog Rules
		 	 foreach ($catalog_rule as $catalog_rule_id) {
		 	 	 $catalog_rule_item=Mage::getModel('catalogrule/rule')->load($catalog_rule_id);
		 	 	 $active=$catalog_rule_item->getIsActive();
		 	 	 $to_date="";
		 	 	 $to_date=strtotime($catalog_rule_item->getToDate());  
		 	 	 if (!$to_date){
		 	 	 	$to_date=strtotime('+1 day');
		 	 	 }
		 	 	 if ($active) {
		 	 	 	if ($to_date>$today_date){ // Makes Banner Active
		 	 	 		if ($banner_status!=1) {
		 	 	 			$banner_status=1;
		 	 	 			$banner_change_status=1;
		 	 	 		}
		 	 	 	} else {//Makes Banners Inactive if date expired
		 	 	 		if ($banner_status!=0) {
		 	 	 			$banner_status=0;
		 	 	 			$banner_change_status=1;
		 	 	 		}
		 	 	 	}
		 	 	 }else { // Makes Banner Inactive
		 	 	 	if ($banner_status!=0) {
		 	 	 		$banner_status=0;
		 	 	 		$banner_change_status=1;
		 	 	 	}
		 	 	 }		 	 	 
		 	 }
		 	 
		 	 if ( ($banner_change_status==1 && $banner_status==0) || ($banner_change_status==0) ) { // Only if status changed to InActive or If status Wasn't changed
			 	 //Sales rules 	
		 	 	foreach ($shopping_cart_rule as $shopping_cart_rule_id) {
			 	 	$sales_rule_item=Mage::getModel('salesrule/rule')->load($shopping_cart_rule_id);
			 	 	$active=$sales_rule_item->getIsActive();
			 	 	$to_date="";
			 	 	$to_date=strtotime($sales_rule_item->getToDate());
			 	 	if (!$to_date){
			 	 		$to_date=strtotime('+1 day');  
			 	 	}
					if ($active) {
						if ($to_date>$today_date){ // Makes Banner Active
							if ($banner_status!=1) {
								$banner_status=1;
								$banner_change_status=1;
							}
						} else { //Makes Banners Inactive if date expired
		 	 	 			if ($banner_status!=0) {
		 	 	 				$banner_status=0;
		 	 	 				$banner_change_status=1;
		 	 	 			}
		 	 	 		}	
					}else { // Makes Banner Inactive
						if ($banner_status!=0) {
							$banner_status=0;
							$banner_change_status=1;
						}
					}		 	 			 	 	 		 	 	
			 	 }
		 	 } //end if 0 1
		 	 if ($banner_change_status==1){ //Save new status
		 	 	$banner->setIsEnabled($banner_status)->save();
		 	 }
		 	 		 	
		 }
	}
	
	/**
	 * Get catalog rule that associated to banner
	 *
	 * @param int $bannerId
	 * @return array
	 */
	public function getRelatedCatalogRule($bannerId)
	{
		$adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
		$select = $adapter->select()
		->from(Mage::getSingleton('core/resource')->getTableName('enterprise_banner/catalogrule'), array())
		->where('banner_id = ?', $bannerId);
	
		$select->join(
					array('rules' => Mage::getSingleton('core/resource')->getTableName('catalogrule/rule')),
					Mage::getSingleton('core/resource')->getTableName('enterprise_banner/catalogrule') . '.rule_id = rules.rule_id',
					array('rule_id')
		);
		
		$rules = $adapter->fetchCol($select);
		return $rules;
	}
	
	/**
	 * Get Sales rule that associated to banner
	 *
	 * @param int $bannerId
	 * @return array
	 */
	public function getRelatedSalesRule($bannerId)
	{
		$adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
		$select = $adapter->select()
		->from(Mage::getSingleton('core/resource')->getTableName('enterprise_banner/salesrule'), array())
		->where('banner_id = ?', $bannerId);
	
		$select->join(
				array('rules' => Mage::getSingleton('core/resource')->getTableName('salesrule/rule')),
				Mage::getSingleton('core/resource')->getTableName('enterprise_banner/salesrule') . '.rule_id = rules.rule_id',
				array('rule_id')
		);
	
		$rules = $adapter->fetchCol($select);
		return $rules;
	}
	
}