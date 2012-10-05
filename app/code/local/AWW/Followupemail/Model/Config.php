<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AWW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Model_Config extends Mage_Core_Model_Abstract
{
    /*
     * Last time FUE job ran
     */
    const LAST_EXEC_TIME = 'last_exec_time';

    /*
     * Last time FUE daily job ran
     */
    const LAST_EXEC_TIME_DAILY = 'last_exec_time_daily';

    /*
     * Last time FUE email sender ran
     */
    const EMAIL_SENDER_LAST_EXEC_TIME = 'email_sender_last_exec_time';


    public function _construct()
    {
        $this->_init('followupemail/config');
    }

    /*
     * Sets paramter name - value pair
     * @param string $name Parameter name
     * @param string @value Parameter value
     * @return AWW_Followupemail_Model_Config Self instance
     */
    public function setParam($name, $value)
    {
        $exists = $this->load($name)->getName();
        $this->setName($name)->setValue($value);
        if($exists) $this->save();
        else $this->getResource()->addParam($name, $value);
        return $this;
    }

    /*
     * Reads paramter value pair
     * @param string $name Parameter name
     * @param bool $default Default parameter value
     * @return Parameter value
     */
    public function getParam($name, $default = false)
    {
        if($name != $this->getName()) $this->load($name);
        if($name == $this->getName()) return $this->getValue();
        else return $default;
    }

}