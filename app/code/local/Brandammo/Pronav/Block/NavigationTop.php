<?php
class Brandammo_Pronav_Block_NavigationTop extends Mage_Catalog_Block_Navigation
{
   /**
    *
    * @var Brandammo_Pronav_Model_Pronav 
    */
   private $_pronavModel;
   private $_staticBlockModel;
   /**
    *
    * @var int
    */
   private $_storeId = 0;
   public function __construct ()
   {
      $this->_pronavModel = Mage::getModel('pronav/pronav');
      $this->_storeId = Mage::app()->getStore()->getStoreId();
   }
   public function getTopLevelItems ()
   {
      return $this->_pronavModel->getTopLevelItems($this->_storeId);
   }
   public function getNavData ()
   {
      return $this->_pronavModel->getItemsData($this->_storeId);
   }
   public function getNavConfigs ()
   {
   }
   public function getNavConfig ($key)
   {
      return Mage::getStoreConfig('pronav/pronavconfig/' . $key, $this->_storeId);
   }
   public function getStaticBlockIdentifier ($id)
   {
      return $this->_pronavModel->getBlockIdentifierByBlockId($id);
   }
}