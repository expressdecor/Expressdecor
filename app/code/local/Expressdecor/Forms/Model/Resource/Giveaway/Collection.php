<?php 

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@expressdecor.com so we can send you a copy immediately.
 *
 * @author Alex Lukyanov
 * @copyright   Copyright (c) 2013 ExpressDecor. (http://www.expressdecor.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Created: May 15, 2013
 *
 */
class Expressdecor_Forms_Model_Resource_Giveaway_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

 
	/**
	 * Initialize collection
	 *
	 */
	public function _construct()
	{
		$this->_init('forms/giveaway');
	}

}