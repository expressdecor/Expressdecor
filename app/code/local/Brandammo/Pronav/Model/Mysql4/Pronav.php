<?php

class Brandammo_Pronav_Model_Mysql4_Pronav extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the pronav_id refers to the key field in your database table.
        $this->_init('pronav/pronav', 'pronav_id');
    }
}