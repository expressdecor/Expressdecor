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


class AWW_Followupemail_Model_Queue extends Mage_Core_Model_Abstract
{
    /*
     * @var array Parameters
     */
    protected $_params = false;

    /*
     * Class constructor
     */
    public function _construct()
    {
        $this->_init('followupemail/queue');
    }

    /*
     * Loads email by its code
     * @param string $code
     * @return AWW_Followupemail_Model_Queue Self instance
     */
    public function loadByCode($code)
    {
        $id = $this->getResource()->getIdByCode($code);
        if(!$id) return false;
        return $this->load($id);
    }

    /*
     * Packing array parameter pair separator
     */
    const PARAM_SEPARATOR = '##';
    /*
     * Packing array name - value separator
     */
    const NAME_VALUE_SEPARATOR = '=';
    /*
     * Packing array value separator
     */
    const VALUE_SEPARATOR = ',';

    /*
     * Serializes array to text representation
     * @param array $data Array to serialize
     * @return string Serialized array
     */
    protected function packArray($data) {
        return base64_encode(serialize($data));
    }

    /*
     * Unserializes array after packArray()
     * @see AWW_Followupemail_Helper_Data::packArray()
     * @param string $data String to unserialize
     * @return array Unserialized data
     */
    protected function unPackArray($data) {
        if(self::isBase64Encoded($data))
            return unserialize(base64_decode($data));
        else
            return self::oldUnpackArray ($data);
    }

    /**
     * Old unpacking function
     * @param string $data
     * @return array
     */
    protected function oldUnpackArray($data) {
        $res = array();
        foreach(explode(self::PARAM_SEPARATOR, $data) as $str)
            if($str && !strpos($str, self::NAME_VALUE_SEPARATOR)===FALSE) {
                list($k, $v) = explode(self::NAME_VALUE_SEPARATOR, $str, 2);
                $res[$k] = (false === strpos($v, self::NAME_VALUE_SEPARATOR)) ? $v : explode(self::VALUE_SEPARATOR, $v);
                if (strpos($res[$k], self::VALUE_SEPARATOR)===0)
                    $res[$k] = substr($res[$k],strlen(self::VALUE_SEPARATOR));
            }
        return $res;
    }

    /**
     * Checking is string base64 encoded
     * @param string $data
     * @return bool
     */
    protected function isBase64Encoded($data) {
        return (bool) preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data);
    }

    protected function _afterLoad() {
        $this->_params =self::unPackArray($this->getData('params'));
        if(!self::isBase64Encoded($this->getData('params')))
            $this->save();
        return parent::_afterLoad();
    }

    protected function _beforeSave()
    {
        $this->setData('params', self::packArray($this->_params));
        return parent::_beforeSave();
    }

    /*
     * Adds email to queue
     * @param
     * @return int Added email ID
     */
    public function add($code, $sequenceNumber, $senderName, $senderEmail, $recipientName, $recipientEmail, $ruleId, $scheduledAt, $subject, $content, $objectId, $params)
    {
        $scheduledAt = date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $scheduledAt);

        $this
            ->setId(null)
            ->setCode($code)
            ->setCreatedAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
            ->setScheduledAt($scheduledAt)
            ->setSendAt('NULL')
            ->setSequenceNumber($sequenceNumber)
            ->setSenderName($senderName)
            ->setSenderEmail($senderEmail)
            ->setRecipientName($recipientName)
            ->setRecipientEmail($recipientEmail)
            ->setSubject($subject)
            ->setContent($content)
            ->setStatus('R')
            ->setRuleId($ruleId)
            ->setObjectId($objectId)
            ->setParams($params)
            ->save();

        Mage::getSingleton('followupemail/log')->logSuccess("email added id={$this->getId()} seq.No=$sequenceNumber email=$recipientEmail name=$recipientName ruleId=$ruleId objectId=$objectId code=$code scheduledAt=$scheduledAt GMT", $this);

        return $this->getId();
    }


    /*
     * Returns parameter by name
     * @param string $name Parameter name
     * @return mixed Parameter value
     */
    public function getParam($name)
    {
        return array_key_exists($name, $this->_params) ? $this->_params[$name] : false;
    }

    /*
     * Sets parameter
     * @param string $name Parameter name
     * @param mixed $value Value to set
     * @return AWW_Followupemail_Model_Queue Self instance
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /*
     * Returns parameters array
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /*
     * Sets parameters
     * @param array $value Parameters
     * @return AWW_Followupemail_Model_Queue Self instance
     */
    public function setParams($value)
    {
        $this->_params = $value;
        return $this;
    }

    /*
     * Cancels email in queue
     * @return AWW_Followupemail_Model_Queue Self instance
     */
    public function cancel()
    {
        $this->setStatus('C')
            ->setSentAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
            ->save();
        return $this;
    }

    /*
     * Sends email
     * @return bool|Exception Sending result
     */
    public function send()
    {
        if (!$this->getId()) return false;
        $email = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        $subject = htmlspecialchars($this->getSubject());
        $message = nl2br(htmlspecialchars($this->getContent()));
        $sender = array(
            'name' => strip_tags($this->getSenderName()),
            'email' => strip_tags($this->getSenderEmail())
        );
        $name = array(
            'name' => $this->getRecipientName(),
            'email' => $this->getRecipientEmail());
        $email->setReplyTo($sender['email']);
        $email->setSenderName($sender['name']);
        $email->setSenderEmail($sender['email']);
        $from = $this->getResource()->getFromFields($this->getRuleId());
        $_copyTo = Mage::helper('followupemail')->explodeEmailList($from['email_copy_to']);
        if (!isset($from['email_send_to_customer'])) $from['email_send_to_customer'] = true;
        $email->setTemplateSubject($subject);
        $email->setTemplateText($this->getContent());
        $email->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()));
        if(empty($_copyTo))
            $recipients = array($name['email']);
        foreach ($_copyTo as $bccEmail) {
            if ($from['email_send_to_customer']) {
                $recipients = array($name['email']);
                $email->addBcc($bccEmail);
            } else {
                if (empty($recipients)) {
                    $recipients = $bccEmail;
                } else if ($recipients != $bccEmail) {
                    $email->addBcc($bccEmail);
                }
            }
        }

        $result = $email->send(
            $recipients,
            $name['name'],
            array(
                'name' => $name['name'],
                'email' => $name['email'],
                'subject' => $subject,
                'message' => $message
            )

        );
        $translate->setTranslateInline(true);
        if ($result) {
            $this->setStatus('S')
                ->setSentAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->save();

            Mage::getSingleton('followupemail/log')->logSuccess('email id=' . $this->getId() . ' sent OK', $this);
        } else {
            $this->setStatus('F')
                ->setSentAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->save();

            Mage::getSingleton('followupemail/log')->logError('email id=' . $this->getId() . ' sending error, ', $this);
        }
        return $result;
    }

    /**
     * Deprecated
     */
  /*  public function send()
    {
        if(!$this->getId()) return false;

        //Integration with CustomSMTP
        $customSMTPSettings = AWW_Followupemail_Helper_Data::getCustomSMTPSettings();
        if(!($customSMTPSettings === FALSE)) {
            $customSMTPHost = Mage::getStoreConfig(AWW_Customsmtp_Helper_Config::XML_PATH_SMTP_HOST);
            $customSMTPTransport = new Zend_Mail_Transport_Smtp($customSMTPHost, $customSMTPSettings);
        }

        $mail = new Zend_Mail('utf-8');

        $from = $this->getResource()->getFromFields($this->getRuleId());
        if(!isset($from['email_send_to_customer'])) $from['email_send_to_customer'] = true;

        try
        {
            $mail
                ->setBodyHtml($this->getContent())
                ->setFrom($this->getSenderEmail(), $this->getSenderName())
                ->setSubject($this->getSubject());

            $_copyTo = Mage::helper('followupemail')->explodeEmailList($from['email_copy_to']);

            if($from['email_send_to_customer'])
                $mail->addTo($this->getRecipientEmail(), $this->getRecipientName());
            elseif(count($_copyTo) && !isset($customSMTPTransport)) {
                $mail->addTo(array_shift($_copyTo));
            }

            foreach($_copyTo as $bccEmail)
                $mail->addBcc($bccEmail);

            if(isset($customSMTPTransport))
                $mail->send($customSMTPTransport);
            else
                $mail->send();

            $this->setStatus('S')
                ->setSentAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->save();

            Mage::getSingleton('followupemail/log')->logSuccess('email id='.$this->getId().' sent OK', $this);
        }
        catch (Exception $e)
        {
            $this->setStatus('F')
                ->setSentAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->save();

            Mage::getSingleton('followupemail/log')->logError('email id='.$this->getId().' sending error, '.$e->__toString(), $this);

            return $e;
        }
        return true;
    }*/
}
