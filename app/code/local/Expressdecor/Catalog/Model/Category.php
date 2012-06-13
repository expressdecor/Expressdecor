<?php

class Expressdecor_Catalog_Model_Category extends Mage_Catalog_Model_Category
{

	/**
     * Retrieve children ids comma separated
     * Fixed left navigation at front-end
     * @return string
     */
	public function getChildren()
	{
		//echo "<pre>"; print_r($this); echo "</pre>";
		//magento default code

		$strChildrenIds = implode(',', $this->getResource()->getChildren($this, false));

		//ED custom changes
		///////////////////////////////////////////////
		$conn = Mage::getSingleton('core/resource')->getConnection('core_read');

		$results = $conn->fetchAll("SELECT * FROM catalog_category_add_rel where entity_id=".$this->_getData('entity_id'));
		foreach($results as $row) {
			if(empty($strChildrenIds))
			$strChildrenIds = $row['child_entity_id'];
			else
			$strChildrenIds .= ",".$row['child_entity_id'];
		}
		///////////////////////////////////////////////

		return $strChildrenIds;

	}

}
