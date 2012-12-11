<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_License_Model extends Ess_M2ePro_Model_Abstract
{
    const LOCK_NO = 0;
    const LOCK_YES = 1;

    const MODE_NONE = 0;
    const MODE_TRIAL = 1;
    const MODE_LIVE = 2;

    const STATUS_NONE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_CLOSED = 3;

    const MESSAGE_TYPE_NOTICE = 0;
    const MESSAGE_TYPE_ERROR = 1;
    const MESSAGE_TYPE_WARNING = 2;
    const MESSAGE_TYPE_SUCCESS = 3;

    const IS_FREE_NO = 0;
    const IS_FREE_YES = 1;

    // ########################################

    public function obtainFreeRecord($email = NULL, $firstName = NULL, $lastName = NULL,
                                     $country = NULL, $city = NULL, $postalCode = NULL)
    {
        $requestParams = array(
            'valid_domain' => Mage::helper('M2ePro/Server')->getDomain(),
            'valid_ip' => Mage::helper('M2ePro/Server')->getIp(),
            'valid_directory' => Mage::helper('M2ePro/Server')->getBaseDirectory()
        );

        !is_null($email) && $requestParams['email'] = $email;
        !is_null($firstName) && $requestParams['first_name'] = $firstName;
        !is_null($lastName) && $requestParams['last_name'] = $lastName;
        !is_null($country) && $requestParams['country'] = $country;
        !is_null($city) && $requestParams['city'] = $city;
        !is_null($postalCode) && $requestParams['postal_code'] = $postalCode;

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $requestParams[strtolower($component).'_access'] = 1;
        }

        $response = Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
                            ->processVirtual('license','add','freeRecord',
                                              $requestParams);

        if (!isset($response['key'])) {
            return false;
        }

        $this->setKey($response['key']);

        Mage::getModel('M2ePro/License_Server')->updateStatus(true);

        return true;
    }

    // ----------------------------------------

    public function hasPaidComponents()
    {
        $requestParams = array(
            'components' => Mage::helper('M2ePro/Component')->getComponents()
        );

        $response = Mage::getModel('M2ePro/Connector_Server_Api_Dispatcher')
                            ->processVirtual('license','get','feeStatus',
                                              $requestParams);

        foreach ($response['components'] as $isFree) {
            if ($isFree === self::IS_FREE_NO) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    public function getKey()
    {
        $key = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key'
        );
        return !is_null($key) ? (string)$key : '';
    }

    public function setKey($key)
    {
        $key = strip_tags($key);
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',(string)$key
        );
        return true;
    }

    public function setKeyDefault($forceSet = false)
    {
        $key = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key'
        );
        if (is_null($key) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',''
            );
        }
    }

    //--------------------------

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain'
        );
        return !is_null($domain) ? (string)$domain : '';
    }

    public function setDomain($domain)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain',(string)$domain
        );
        return true;
    }

    public function setDomainDefault($forceSet = false)
    {
        $domain = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain'
        );
        if (is_null($domain) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain',''
            );
        }
    }

    //--------------------------

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip'
        );
        return !is_null($ip) ? (string)$ip : '';
    }

    public function setIp($ip)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip',(string)$ip
        );
        return true;
    }

    public function setIpDefault($forceSet = false)
    {
        $ip = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip'
        );
        if (is_null($ip) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip',''
            );
        }
    }

    //--------------------------

    public function getDirectory()
    {
        $directory = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory'
        );
        return !is_null($directory) ? (string)$directory : '';
    }

    public function setDirectory($directory)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory',(string)$directory
        );
        return true;
    }

    public function setDirectoryDefault($forceSet = false)
    {
        $directory = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory'
        );
        if (is_null($directory) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory',''
            );
        }
    }

    // ########################################

    public function getMode($component)
    {
        $mode = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','mode'
        );

        if (is_null($mode) || $mode === false || $mode === '') {
            return self::MODE_NONE;
        }

        if ((int)$mode == self::MODE_NONE) {
            return self::MODE_NONE;
        }
        if ((int)$mode == self::MODE_TRIAL) {
            return self::MODE_TRIAL;
        }
        if ((int)$mode == self::MODE_LIVE) {
            return self::MODE_LIVE;
        }

        return self::MODE_NONE;
    }

    public function isNoneMode($component)
    {
        return $this->getMode($component) == self::MODE_NONE;
    }

    public function isTrialMode($component)
    {
        return $this->getMode($component) == self::MODE_TRIAL;
    }

    public function isLiveMode($component)
    {
        return $this->getMode($component) == self::MODE_LIVE;
    }

    public function setMode($component,$mode)
    {
        $mode = (int)$mode;

        if ($mode != self::MODE_NONE && $mode != self::MODE_TRIAL && $mode != self::MODE_LIVE) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','mode',$mode
        );
        return true;
    }

    public function setModeDefault($component,$forceSet = false)
    {
        $mode = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','mode'
        );
        if (is_null($mode) || $forceSet) {
            $group = '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/';
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue($group,'mode',self::MODE_NONE);
        }
    }

    //--------------------------

    public function getStatus($component)
    {
        $status = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','status'
        );

        if (is_null($status) || $status === false || $status === '') {
            return self::STATUS_NONE;
        }

        if ((int)$status == self::STATUS_NONE) {
            return self::STATUS_NONE;
        }
        if ((int)$status == self::STATUS_ACTIVE) {
            return self::STATUS_ACTIVE;
        }
        if ((int)$status == self::STATUS_SUSPENDED) {
            return self::STATUS_SUSPENDED;
        }
        if ((int)$status == self::STATUS_CLOSED) {
            return self::STATUS_CLOSED;
        }

        return self::STATUS_NONE;
    }

    public function isNoneStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_NONE;
    }

    public function isActiveStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_ACTIVE;
    }

    public function isSuspendedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_SUSPENDED;
    }

    public function isClosedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_CLOSED;
    }

    public function setStatus($component,$status)
    {
        $status = (int)$status;

        if ($status != self::STATUS_NONE && $status != self::STATUS_ACTIVE &&
            $status != self::STATUS_SUSPENDED && $status != self::STATUS_CLOSED) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','status',$status
        );
        return true;
    }

    public function setStatusDefault($component,$forceSet = false)
    {
        $status = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','status'
        );
        if (is_null($status) || $forceSet) {
            $group = '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/';
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue($group,'status',self::STATUS_NONE);
        }
    }

    //--------------------------

    public function getTimeStampExpirationDate($component)
    {
        $date = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','expiration_date'
        );
        return is_null($date) || $date == ''
            ? Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*24 : (int)strtotime($date);
    }

    public function getTextExpirationDate($component,$withTime = false)
    {
        if ($withTime) {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component)
            );
        } else {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component),false,'Y-m-d'
            );
        }
    }

    public function getIntervalBeforeExpirationDate($component)
    {
        $timeStampCurrentDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $timeStampExpDate = $this->getTimeStampExpirationDate($component);

        if ($timeStampExpDate <= $timeStampCurrentDate) {
            return 0;
        }

        return $timeStampExpDate - $timeStampCurrentDate;
    }

    public function isExpirationDate($component)
    {
        return $this->getIntervalBeforeExpirationDate($component) == 0;
    }

    public function setExpirationDate($component,$date)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/',
            'expiration_date',(string)$date
        );
    }

    public function setExpirationDateDefault($component,$forceSet = false)
    {
        $date = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','expiration_date'
        );
        if (is_null($date) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/',
                'expiration_date',
                ''
            );
        }
    }

    //--------------------------

    public function getIsFree($component)
    {
        $isFree = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','is_free'
        );

        if (is_null($isFree) || $isFree === false || $isFree === '' ||
            !in_array((int)$isFree,array(self::IS_FREE_NO,self::IS_FREE_YES))) {
            return self::IS_FREE_NO;
        }

        return (int)$isFree;
    }

    public function isFreeEnabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_YES;
    }

    public function isFreeDisabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_NO;
    }

    public function setIsFree($component,$isFree)
    {
        $isFree = (int)$isFree;

        if ($isFree != self::IS_FREE_NO && $isFree != self::IS_FREE_YES) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','is_free',$isFree
        );
        return true;
    }

    public function setIsFreeDefault($component,$forceSet = false)
    {
        $isFree = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','is_free'
        );
        if (is_null($isFree) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/',
                'is_free',self::IS_FREE_NO
            );
        }
    }

    // ########################################

    public function getLock()
    {
        $lock = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock'
        );

        if (is_null($lock) || $lock === false || $lock === '') {
            return self::LOCK_NO;
        }

        if ((int)$lock == self::LOCK_NO) {
            return self::LOCK_NO;
        }
        if ((int)$lock == self::LOCK_YES) {
            return self::LOCK_YES;
        }

        return self::LOCK_NO;
    }

    public function isLock()
    {
        return $this->getLock() == self::LOCK_YES;
    }

    public function setLock($lock)
    {
        $lock = (int)$lock;

        if ($lock != self::LOCK_NO && $lock != self::LOCK_YES) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',$lock
        );
        return true;
    }

    public function setLockDefault($forceSet = false)
    {
        $lock = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock'
        );
        if (is_null($lock) || $forceSet) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',self::LOCK_NO
            );
        }
    }

    //--------------------------

    public function getMessages()
    {
        $messages = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages'
        );
        return !is_null($messages) && $messages != '' ? (array)json_decode((string)$messages,true) : array();
    }

    public function setMessages(array $messages)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages',json_encode($messages)
        );
        return true;
    }

    public function setMessagesDefault($forceSet = false)
    {
        $messages = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages'
        );
        if (is_null($messages) || $forceSet) {
           Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
               '/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages',json_encode(array())
           );
        }
    }

    // ########################################

    public function setDefaults($forceSet = false)
    {
        $this->setKeyDefault($forceSet);

        $this->setDomainDefault($forceSet);
        $this->setIpDefault($forceSet);
        $this->setDirectoryDefault($forceSet);

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $this->setModeDefault($component,$forceSet);
            $this->setStatusDefault($component,$forceSet);
            $this->setExpirationDateDefault($component,$forceSet);
            $this->setIsFree($component,$forceSet);
        }

        $this->setLockDefault($forceSet);
        $this->setMessagesDefault($forceSet);
    }

    // ########################################
}