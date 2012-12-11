<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Collection_Component_Parent_Abstract
    extends Ess_M2ePro_Model_Mysql4_Collection_Component_Abstract
{
    protected $childMode = NULL;

    // ########################################

    public function __construct($resource = NULL)
    {
        if (is_object($resource) && ($resource instanceof Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract)) {
            /** @var $resource Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
            $this->setChildMode($resource->getChildMode());
        }

        parent::__construct($resource);
    }

    // ########################################

    public function setChildMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->childMode = $mode;
        return $this;
    }

    public function getChildMode()
    {
        return $this->childMode;
    }

    // ########################################

    protected function _initSelect()
    {
        $temp = parent::_initSelect();

        if (is_null($this->childMode)) {
            return $temp;
        }

        /** @var $resource Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
        $resource = $this->getResource();

        $componentTable = $resource->getChildTable();
        $componentPk = $resource->getChildPrimary();

        $this->getSelect()->join(
            array('second_table'=>$componentTable),
            "`second_table`.`".$componentPk."` = `main_table`.`id`"
        );
        $this->getSelect()->where("`main_table`.`component_mode` = '".$this->childMode."'");

        return $temp;
    }

    public function getItems()
    {
        $temp = parent::getItems();

        foreach ($temp as $item) {
            /** @var $item Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
            $item->setChildMode($this->childMode);
        }

        return $temp;
    }

    public function getFirstItem()
    {
        /** @var $item Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract */
        $item = parent::getFirstItem();
        $item->setChildMode($this->childMode);

        return $item;
    }

    // ########################################
}