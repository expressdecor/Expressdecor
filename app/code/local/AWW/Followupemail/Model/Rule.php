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


class AWW_Followupemail_Model_Rule extends Mage_Core_Model_Abstract
{
    const VALIDATION_OK = 'Ok';

    protected $_validationMessage = false;
    protected $_stores = array(); // store IDs to which the rule is applicable
    protected $_categories = array(); // rule category IDs to one of which one of the products has to belong
    protected $_productTypes = array();
    protected $_products = array();
    protected $_orderStatus = false;
    protected $_validated = false;
    protected $_isValid = false;
    protected $_isTest = false;
    protected $_processor = false;

    /*
     * Class constructor
     */
    public function _construct()
    {
        $this->_init('followupemail/rule');
        $this->_getProcessor()->setIncludeProcessor(array($this, 'getInclude'));
    }

    protected function _beforeSave()
    {
        if ($this->getData('unsubscribed_customers') !== null && is_array($this->getData('unsubscribed_customers'))) {
            $this->setData('unsubscribed_customers', implode(',', $this->getData('unsubscribed_customers')));
        } else {
            $this->setData('unsubscribed_customers', '');
        }
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        if ($this->getData('unsubscribed_customers') !== null && is_string($this->getData('unsubscribed_customers'))) {
            $this->setData('unsubscribed_customers', explode(',', $this->getData('unsubscribed_customers')));
        } else {
            $this->setData('unsubscribed_customers', array());
        }
        return parent::_afterLoad();
    }

    public function unsubscribeCustomer($id)
    {
        $unsCustomers = $this->getData('unsubscribed_customers');
        if (!is_array($unsCustomers)) {
            $unsCustomers = array($id);
        } else {
            $unsCustomers[] = $id;
        }
        $this->setData('unsubscribed_customers', array_unique($unsCustomers));
        return $this;
    }

    protected function _getProcessor()
    {
        if (!$this->_processor)
            $this->_processor = Mage::getModel('followupemail/filter');
        return $this->_processor;
    }

    /*
     * Initializes internal variables
     */
    public function prepareData()
    {
        $this->_categories = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getCategoryIds()));
        $this->_stores = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getStoreIds()));
        $this->_productTypes = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getProductTypeIds()));
        $this->_products = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getProductIds()));
        $this->_sku = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getSku()));
        $this->_anlSegments = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getAnlSegments()));
        $this->_customerGroups = AWW_Followupemail_Helper_Data::noEmptyValues(explode(',', $this->getCustomerGroups()));

        $len = strlen(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX);
        $this->_orderStatus = (AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX ==
            substr($this->getEventType(), 0, $len)) ? substr($this->getEventType(), $len) : false;
    }

    /*
     * Loads itself
     */
    public function load($id, $field = null)
    {
        $this->_validated = false;
        $this->_isValid = false;

        parent::load($id);
        $this->_afterLoad();
        $this->prepareData();

        return $this;
    }

    /*
     * Checks whether to send emails based on current setting and customer subscription
     * @param string $email Email to inspect
     * @return bool Check result
     */
    protected function _sendToSubscribersOnly($email)
    {
        if ($this->getSendToSubscribersOnly()) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if (!$subscriber
                || !$subscriber->getId()
                || Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED != $subscriber->getSubscriberStatus()
            ) {
                //try to load AN subscriber
                if (Mage::helper('followupemail')->canUseAN()) {
                    $subscriber = Mage::getModel('advancednewsletter/api')->getSubscriber($email);
                    if ($subscriber
                        && $subscriber->getData()
                        && $subscriber->getStatus() == AWW_Advancednewsletter_Model_Subscriber::STATUS_SUBSCRIBED
                    ) return true;
                    return false;
                } else return false;
            }
        }
        return true;
    }

    /*
     * Validates rule basing on AN segment selected
     * @param string $email Email to inspect
     * @return bool Check result
     */
    protected function _validateByAdvancedNewsletterSegments($email)
    {
        if ($this->getSendToSubscribersOnly()
            && count($this->_anlSegments)
            && !in_array(AWW_Followupemail_Model_Mysql4_Rule::ADVANCED_NEWSLETTER_SEGMENTS_ALL, $this->_anlSegments)
            && Mage::helper('followupemail')->canUseAN()
        ) {
            $subscriber = Mage::getModel('advancednewsletter/api')->getSubscriber($email);
            if (!array_intersect($this->_anlSegments, $subscriber->getData('segments_codes')))
                return 'Advanced Newsletter segments does not match';
        }

        return true;
    }

    /*
     * Validates rule basing on customer group
     * @param Mage_Core_Customer_Model_Customer $customer Customer to inspect
     * @return bool Check result
     */
    protected function _validateByCustomerGroup($customer)
    {
        if (!count($this->_customerGroups)
            || in_array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_ALL, $this->_customerGroups)
        ) return true;

        if ($customer) {
            $customerGroup = $customer->getGroupId();
            if ($customerGroup && !in_array($customerGroup, $this->_customerGroups))
                return 'The customer group "' . $customerGroup . '" does not match rule groups';
        }
        elseif (!in_array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_NOT_REGISTERED, $this->_customerGroups))
            return 'The customer is not registered and customer group NOT_REGISTERED is not in the rule conditions';

        return true;
    }

    /*
     * Validates rule basing on customer
     * @param array $params Parameters to inspect
     * @return bool Check result
     */
    public function validateByCustomer($params)
    {
        if (!isset($params['customer_email'])) return 'No customer email available';

        if (!$this->_sendToSubscribersOnly($params['customer_email']))
            return 'The email ' . $params['customer_email'] . ' is not subscribed';

        if (true !== $result = $this->_validateByAdvancedNewsletterSegments($params['customer_email'])) return $result;

        if (true !== $result = $this->_validateByCustomerGroup(isset($params['customer']) ? $params['customer'] : false)) return $result;

        if (count($this->_stores) && isset($params['store_id']) && $params['store_id']
            && !in_array($params['store_id'], $this->_stores)
        ) return 'Wrong store';

        return true;
    }

    /*
     * Validates rule basing on sale amount condition
     * @param array $params Parameters to inspect
     * @param string $source Order or cart to inspect
     * @return bool Check result
     */
    protected function _checkSaleAmount($params, $source)
    {
        $parts = explode(AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_SEPARATOR, $this->getSaleAmount(), 2);
        if (count($parts) < 2) return true;

        list($condition, $value) = $parts;
        if (!$condition || !$value) return true;

        if (isset($params[$source])) {
            $saleAmount = $params[$source]->getGrandTotal();

            $currencyModel = Mage::getModel('directory/currency');
            $_baseCurrency = $currencyModel->getConfigBaseCurrencies();
            if ($_baseCurrency && is_array($_baseCurrency)) {
                $_baseCurrency = $_baseCurrency[0];
                $_currencyTo = $params[$source]->getData('order_currency_code');
                if (is_null($_currencyTo)) {
                    $_currencyTo = $params[$source]->getData('store_currency_code');
                }
                $saleAmount = Mage::helper('followupemail')->convertPrice($saleAmount, $_currencyTo, $_baseCurrency);
            }
        }
        else return 'No source for sale amount condition';
        if ($saleAmount === false) return 'Order currency convertation error';

        switch (array_search($condition, AWW_Followupemail_Model_Source_Rule_Saleamount::getConditions())) // switch($condition)
        {
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_EQ  :
                $result = ($saleAmount == $value);
                break;
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_GT  :
                $result = ($saleAmount > $value);
                break;
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_EGT :
                $result = ($saleAmount >= $value);
                break;
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_LT  :
                $result = ($saleAmount < $value);
                break;
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_ELT :
                $result = ($saleAmount <= $value);
                break;
            case AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_NE  :
                $result = ($saleAmount != $value);
                break;
            default :
                return 'Unknown condition';
        }
        return (true === $result) ? true : ('Sale amount is not ' . $condition . ' ' . $value . ' (' . $saleAmount . ')');
    }

    /*
     * Validates rule basing on order or cart properties
     * @param array $params Parameters to inspect
     * @param string $source Order or cart to inspect
     * @return bool Check result
     */
    public function validateOrderOrCart($params, $source)
    {
        if (true !== ($result = $this->validateByProduct($params))) return $result;

        // for future rule cart/order condition check
        // if(true !== $result = $this->_checkSaleAmount($params, $source)) return $result;
        $result = $this->_checkSaleAmount($params, $source);

        return $result;
    }

    /*
     * Validates rule basing on product properties
     * @param array $params Parameters to inspect
     * @return bool Check result
     */
    public function validateByProduct($params)
    {
        if (count($this->_stores) && isset($params['store_id']) && $params['store_id']
            && !in_array($params['store_id'], $this->_stores)
        ) return 'No such store in condition';

        if (isset($params['product_type_ids']))
            $params['product_type_ids'] = AWW_Followupemail_Helper_Data::noEmptyValues($params['product_type_ids']);
        if (isset($params['category_ids']))
            $params['category_ids'] = AWW_Followupemail_Helper_Data::noEmptyValues($params['category_ids']);
        if (isset($params['product_ids']))
            $params['product_ids'] = AWW_Followupemail_Helper_Data::noEmptyValues($params['product_ids']);

        if (count($this->_productTypes)
            && !in_array(AWW_Followupemail_Model_Source_Product_Types::PRODUCT_TYPE_ALL, $this->_productTypes)
            && isset($params['product_type_ids']) && count($params['product_type_ids'])
        ) {
            $productTypeCondition = false;
            foreach ($params['product_type_ids'] as $productTypeId)
                if (in_array($productTypeId, $this->_productTypes)) {
                    $productTypeCondition = true;
                    break;
                }
            if (!$productTypeCondition) return 'Product type(s) doesn\'t match';
        }

        // category rule
        if (count($this->_categories)
            && isset($params['category_ids']) && count($params['category_ids'])
        ) {
            foreach ($params['category_ids'] as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                foreach (explode('/', $category->getPath()) as $catParentId)
                    if (in_array($catParentId, $this->_categories))
                        return 'Product category doesn\'t match';
            }
        }

        // SKU rule
        if ($this->_sku) {
            $_skus = array();
            if (isset($params['order']) && $params['order']) {
                foreach ($params['order']->getItemsCollection() as $item)
                    $_skus[] = $item->getSku();
            }
            if ($_skus) $params['sku'] = $_skus;

            if (count($this->_sku) && isset($params['sku']) && count($params['sku']) && !array_intersect($this->_sku, $params['sku']))
                return Mage::helper('followupemail')->__('SKUs doesn\'t match');
        }

        //both check with sku and product types
        if (count($this->_productTypes) && !in_array(AWW_Followupemail_Model_Source_Product_Types::PRODUCT_TYPE_ALL, $this->_productTypes)
            && isset($params['product_type_ids']) && count($params['product_type_ids'])
            && count($this->_sku) && isset($params['sku']) && count($params['sku'])
        ) {
            $bothSKUAndTypeCondition = false;
            for ($i = 0; $i < count($params['product_type_ids']); $i++) {
                $_product = array(
                    'type' => isset($params['product_type_ids'][$i]) ? $params['product_type_ids'][$i] : null,
                    'sku' => isset($params['sku'][$i]) ? $params['sku'][$i] : null
                );
                if (!is_null($_product['type']) && !is_null($_product['sku'])
                    && in_array($_product['type'], $this->_productTypes)
                    && in_array($_product['sku'], $this->_sku)
                ) {
                    $bothSKUAndTypeCondition = true;
                    break;
                }
            }
            if (!$bothSKUAndTypeCondition) return 'Products SKU\'s and types doesn\'t match';
        }

        // product rule
        // if( count($this->_products)
        // && isset($params['product_ids']) && count($params['product_ids']))
        // {
        // $productCondition = false;
        // foreach($params['product_ids'] as $productId)
        // if(in_array($productId, $this->_products))
        // {
        // $productCondition = true;
        // break;
        // }
        // if(!$productCondition) return 'Product ID(s) doesn\'t match';
        // }

        return true;
    }

    /*
     * Returns validation message
     * @deprecated since 3.2.0
     * @return string
     */
    public function getValidationMessage()
    {
        return $this->_validationMessage;
    }

    /*
     * Validates rule basing on parameters passed by invoking internal _validate function and puts validation into internal validation message variable
     * @param array $params Parameters to inspect
     * @return bool Check result
     */
    public function validate($params)
    {
        // $params = $this->_createObjects($params, array());

        $this->_isValid = (true === ($result = $this->_validate($params)));
        $this->_validationMessage = $this->_isValid ? self::VALIDATION_OK : $result;

        return $this->_isValid;
    }

    /*
     * Validates rule basing on product properties
     * @param array $params Parameters to inspect
     * @return bool|string Check result
     */
    protected function _validate($params)
    {
        $this->_validated = true;

        if (true !== $res = $this->validateByCustomer($params)) return $res;

        // MSS check
        $mssRuleId = false;
        if (Mage::helper('followupemail')->isMSSInstalled()
            && $mssRuleId = $this->getMssRuleId()
        ) {
            if (isset($params['customer'])) {
                if (!Mage::getModel('marketsuite/filter')->checkRule($params['customer'], $mssRuleId))
                    return 'MSS rule d=' . $mssRuleId . ' validation failed';
                $mssRuleId = false; // preventing further MSS checks
            }
        }

        // Check is customer is unsubscribed for this rule
        if (isset($params['customer']) && $params['customer']->getId()) {

            if (in_array($params['customer']->getId(), $this->getData('unsubscribed_customers'))) {
                return Mage::helper('followupemail')->__('Customer with ID %s is unsubscribed from rule %s', $params['customer']->getId(), $this->getId());
            }
        }

        switch ($this->getEventType()) {
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_SHARED :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_PRODUCT_ADD :
                return $this->validateByProduct($params);
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW :
                if ($mssRuleId
                    && isset($params['quote'])
                    && !Mage::getModel('marketsuite/filter')->checkRule($params['quote'], $mssRuleId)
                ) return 'MSS rule d=' . $mssRuleId . ' validation failed';

                return $this->validateOrderOrCart($params, 'quote');
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LOGGED_IN :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LAST_ACTIVITY :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW_SUBSCRIPTION :
                // return $this->validateByCustomer($params);
                return true;
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK :
                // return $this->validateByCustomer($params);
                return true;
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_GROUP_CHANGED:
                return $this->validateByCustomer($params);
                break;

            default :
                if ($this->_orderStatus) {
                    if ($mssRuleId
                        && isset($params['order'])
                        && !Mage::getModel('marketsuite/filter')->checkRule($params['order'], $mssRuleId)
                    ) return 'MSS rule d=' . $mssRuleId . ' validation failed';

                    return $this->validateOrderOrCart($params, 'order');
                }
                break;
        }
        return 'Unknown event';
    }

    /*
     * Returns processed template
     * Called from AWW_Followupemail_Model_Filter::foreachDirective()
     * @param string $templateCode Template code
     * @param array $variables Template processor variables
     * @return string Template processed
     */
    public function getInclude($templateCode, array $variables)
    {
        $processor = $this->_getProcessor();
        $processor->setVariables($variables);
        $template = $this->_getTemplate($templateCode);
        return $processor->filter($template['content']);
    }

    /*
     * Returns email template
     * @param string $templateId Template code with source
     * @param int $storeId Store ID
     * @return array Template fields
     */
    protected function _getTemplate($templateId, $storeId = null)
    {
        if (is_null($storeId)) $storeId = Mage::app()->getStore()->getId();

        $templateName = substr($templateId,
            false !== ($pos = strpos($templateId, AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_SEPARATOR))
                ? $pos + 1 : 0);

        if (!$pos) {
            Mage::getSingleton('followupemail/log')->logError('No template source specified in "' . $templateId . '"', $this);
            return false;
        }
        else {
            switch ($src = substr($templateId, 0, $pos)) {
                case AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_EMAIL :
                    $template = $this->getResource()->getTemplateContent('core/email_template', $templateName);
                    break;

                case AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_NEWSLETTER :
                    $template = $this->getResource()->getTemplateContent('newsletter/template', $templateName);
                    break;

                default :
                    Mage::getSingleton('followupemail/log')->logError('Wrong template source specified as "' . $src . '" in "' . $templateId . '"', $this);
                    return false;
            }

            if (!$template) return false;

            $sender = Mage::getStoreConfig('followupemail/general/sender', $storeId);
            $template['sender_name'] = $this->getData('sender_name') ? $this->getData('sender_name') : Mage::getStoreConfig("trans_email/ident_$sender/name", $storeId);
            $template['sender_email'] = $this->getData('sender_email') ? $this->getData('sender_email') : Mage::getStoreConfig("trans_email/ident_$sender/email", $storeId);

            return $template;
        }
    }

    /*
     * Returns email content
     * @param array $objects Objects to process the email
     * @param string $templateId Template code
     * @return array Email fields
     */
    protected function _getContent($objects, $templateId)
    {
        if (!$content = $this->_getTemplate($templateId, $objects['store_id'])) return false;

        if ($this->getSenderName()) $content['sender_name'] = $this->getSenderName();
        if ($this->getSenderEmail()) $content['sender_email'] = $this->getSenderEmail();

	        //Alex   
        		$items=$objects['order']->getAllItems();
        		$param_val="<table>";
        		foreach ($items as $itemId => $item)
        		{        
        			if ($item->getProduct()->getTypeId() =='simple') {
        			$image_url = Mage::helper('catalog/image')
        			->init($item->getProduct(), 'image', $item->getProduct()->getImage())
        			->resize(70);        			
        			$param_val.='<tr>';
        			$param_val.='<td><img src="'.$image_url.'" /></td>';
        			$param_val.='<td>'.$item->getProduct()->getName().'</td>';
        			$product_id=$item->getProduct()->getId();        			         			 	
        				$configurable_product_model = Mage::getModel('catalog/product_type_configurable');
        				$parentId = $configurable_product_model->getParentIdsByChild($product_id);        				
        				if(isset($parentId[0])){        					         			
        					//if this product has a parent        					
        					$product_id =$parentId[0];        					         	 
        				}        			        			       			
        			$param_val.='<td><a target="_blank" href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'review/product/list/id/'. $product_id.'/#review-form" >Review</a></td>';        			 
        			$param_val.='</tr>';
        			}
        		}
        		$param_val.="</table>";
        		$objects['product_grid']=$param_val;
        		//Alex
        
        $this->_getProcessor()->setVariables($objects);  
        $this->_getProcessor()->setStoreId($objects['store_id']);

        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $objects['store_id']),
            'area' => Mage_Core_Model_Design_Package::DEFAULT_AREA,
            'store' => Mage::app()->getStore($objects['store_id'])
        ));

        foreach ($content as $k => $v)
            $content[$k] = $this->_getProcessor()->filter($v);

        foreach ($this->_getProcessor()->getErrors() as $error)
            Mage::getSingleton('followupemail/log')->logError('Template processor : ' . $error, $this);

        Mage::getDesign()->setAllGetOld($currentDesign);

        return $content;
    }


    /*
     * Creates customer
     * @param array $objects Objects to create from
     * @return array $objects Objects complemented with customer object created
     */
    protected function _createCustomer($objects)
    {
        if (!isset($objects['customer'])) {
            if (isset($objects['customer_id']) && $objects['customer_id']) {
                $objects['customer'] = Mage::getModel('customer/customer')->load($objects['customer_id']);
            }
            elseif (isset($objects['customer_email']) && $objects['customer_email']) {
                $objects['customer'] = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->loadByEmail($objects['customer_email']);
                $objects['customer_id'] = $objects['customer']->getId();
                if (!$objects['customer_id']) unset($objects['customer']);
            }
        }

        $infoSource = false;

        if (isset($objects['customer'])) {
            $infoSource = $objects['customer'];
            if (!isset($objects['customer_email'])) $objects['customer_email'] = $objects['customer']->getEmail();
        } elseif (isset($objects['order'])) {
            if (!isset($objects['customer_email'])) $objects['customer_email'] = $objects['order']->getCustomerEmail();
            $infoSource = AWW_Followupemail_Helper_Data::getOrderAddress($objects['order'], 'billing');
            if (!$infoSource)
                $infoSource = AWW_Followupemail_Helper_Data::getOrderAddress($objects['order'], 'shipping');
        } elseif (isset($objects['quote']) && $objects['quote'] instanceof Mage_Sales_Model_Q) {
            $_quote = $objects['quote'];
            if ($_quote->getCustomerId()) {
                $infoSource = $objects['customer'] = $_quote->getCustomer();
            }
        }
        if (false != $infoSource) {
            $middlename = $infoSource->getMiddlename();
            $objects['customer_name'] = $infoSource->getFirstname() . ' ' . ($middlename ? $middlename . ' ' : '') . $infoSource->getLastname();
        }
        if (!isset($objects['customer_name']))
            if (isset($objects['customer_email']))
                $objects['customer_name'] = substr($objects['customer_email'], 0, false !== ($pos = strpos($objects['customer_email'], '@')) ? $pos : 999);
            else $objects['customer_name'] = 'Friend';

        $objects['customerName'] = $objects['customer_name'];

        return $objects;
    }

    /*
     * Creates objects necessary to process the rule
     * @param array $params Initial parameters
     * @param array $objects Additional initial parameters
     * @return array Objects created
     */
    protected function _createObjects($params, $objects = array())
    {
        $objects = array_merge($params, $objects);

        // product
        if (!isset($objects['product']))
            if (isset($objects['product_id']) && $objects['product_id'])
                $objects['product'] = Mage::getModel('catalog/product')->load($objects['product_id']);

        // order
        if (!isset($objects['order']))
            if (isset($objects['order_id']) && $objects['order_id'])
                $objects['order'] = Mage::getModel('sales/order')->reset()->load($objects['order_id']);
            elseif (isset($objects['order_increment_id']) && $objects['order_increment_id'])
                $objects['order'] = Mage::getModel('sales/order')->reset()->loadByIncrementId($objects['order_increment_id']);
        if (isset($objects['order']) && !$objects['order']->getId()) unset($objects['order']);
        if (isset($objects['order'])) {
            if (!isset($objects['customer_id'])) $objects['customer_id'] = $objects['order']->getCustomerId();
            
            
            /* write products to order items */
            foreach ($objects['order']->getAllVisibleItems() as $item) {                
                if ($item->getProductType() == 'grouped') {
                    $buyRequest = @unserialize($item->getData('product_options'));
                    if (is_array($buyRequest)) {
                        $productId = $buyRequest['info_buyRequest']['super_product_config']['product_id'];
                    } else {
                        $productId = $item->getProductId();
                    }
                } else {
                    $productId = $item->getProductId();
                }
                $objects['order']->getItemById($item->getId())->setProduct(Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($productId));
            }
            /* */
            
            
        }


        // quote
        if (!isset($objects['quote']))
            if (isset($objects['quote_id']) && $objects['quote_id'])
                $objects['quote'] = Mage::getModel('sales/quote')
                    ->setSharedStoreIds(array_keys(Mage::app()->getStores()))
                    ->load($objects['quote_id']);
            elseif (isset($objects['customer_id']))
                $objects['quote'] = Mage::getModel('sales/quote')->loadByCustomer($objects['customer_id']);

        $objects = $this->_createCustomer($objects);

        if (isset($objects['quote']))
            if (!$objects['quote']->getId()) unset($objects['quote']);
            else {
                if ($customerId = $objects['quote']->getCustomerId()) {
                    $objects['customer_id'] = $objects['quote']->getCustomerId();
                    $objects = $this->_createCustomer($objects);
                    $objects['quote']->setCustomerName($objects['customerName']);
                }
                $objects['quote']->setTotalDiscount($objects['quote']->getSubtotal() - $objects['quote']->getSubtotalWithDiscount());

                foreach ($objects['quote']->getAllVisibleItems() as $item)
                    $objects['quote']->getItemById($item->getId())
                        ->setProduct(Mage::getModel('catalog/product')
                        ->setStoreId($item->getStoreId())
                        ->load($item->getProductId()));

                $objects['cart'] = $objects['quote'];
            }

        // wishlist
        if (!isset($objects['wishlist']))
            if (isset($objects['wishlist_id']) && $objects['wishlist_id'])
                $objects['wishlist'] = Mage::getModel('wishlist/wishlist')->load($objects['wishlist_id']);
            elseif (isset($objects['wishlist_sharing_code']) && $objects['wishlist_sharing_code'])
                $objects['wishlist'] = Mage::getModel('wishlist/wishlist')->loadByCode($objects['wishlist_sharing_code']);
            elseif (isset($objects['customer']))
                $objects['wishlist'] = Mage::getModel('wishlist/wishlist')->loadByCustomer($objects['customer']);

        if (isset($objects['wishlist']))
            foreach ($objects['wishlist']->getItemCollection() as $item)
                $objects['wishlist']->getItemCollection()->getItemById($item->getId())->setProduct(Mage::getModel('catalog/product')->load($item->getProductId()));

        // store
        if (!isset($objects['store'])) {
            if (!isset($objects['store_id']) || !$objects['store_id'])
                if (isset($objects['customer']) && $objects['customer']) {
                    $objects['store_id'] = $objects['customer']->getStoreId();
                    if ($objects['store_id'] == 0) {
                        $storeIds = Mage::getModel('core/website')->load($objects['customer']->getWebsiteId())->getStoreIds();
                        if ($storeIds) {
                            $objects['store_id'] = array_pop($storeIds);
                        }
                    }
                } elseif (isset($objects['order']) && $objects['order'])
                    $objects['store_id'] = $objects['order']->getStoreId();
                elseif (isset($objects['quote']) && $objects['quote'])
                    $objects['store_id'] = $objects['quote']->getStoreId();
                elseif (isset($objects['wishlist']) && $objects['wishlist'])
                    $objects['store_id'] = $objects['wishlist']->getStore()->getId();
            if (!isset($objects['store_id']) or is_null($objects['store_id']))
                $objects['store_id'] = Mage::app()->getStore()->getId();

            if (isset($objects['store_id']))
                $objects['store'] = Mage::getModel('core/store')->load($objects['store_id']);
        }

        if (Mage::helper('followupemail')->checkVersion('1.9') && isset($objects['customer']) && $objects['customer'] instanceof Mage_Customer_Model_Customer) {
            if (($rewards = Mage::getModel('enterprise_reward/reward'))) {
                $rewards->setCustomer($objects['customer'])
                    ->setWebsiteId($objects['customer']->getWebsiteId())
                    ->loadByCustomer();
                $objects['reward'] = $rewards;
            }
        }

        return $objects;
    }

    /*
     * Process rule
     * @param array $params Initial parameters
     * @param array $objects Additional initial parameters
     * @return bool Processing result
     */
    public function process($params, $objects = array())
    {
        $objects = $this->_createObjects($params, $objects);

        if (isset($objects['order']) && is_object($objects['order']) && $objects['order']->status)
            $objects['order']->status = '"' . Mage::getSingleton('sales/order_config')->getStatusLabel($objects['order']->status) . '"';

        if (!$this->_validated) $this->validate($objects);
        if ($this->_isValid) {
            Mage::getSingleton('followupemail/log')->logSuccess('rule id=' . $this->getId() . ' validation OK', $this);

            if (!($this->getChain() && count($chain = unserialize($this->getChain())))) {
                Mage::getSingleton('followupemail/log')->logWarning('rule id=' . $this->getId() . ' has no chain or the chain is empty: "' . $this->getChain() . '"', $this);
                return true;
            }

            $queue = Mage::getModel('followupemail/queue');
            $sequenceNumber = 1;
            foreach ($chain as $chainItem) {
                // Generate coupon if it needed
                if ($this->getCouponEnabled()) {
                    unset($objects['has_coupon']);
                    //get content of current email template
                    $emailTemplate = $this->_getTemplate($chainItem['TEMPLATE_ID']);
                    $emailTemplateContent = $emailTemplate['content'];

                    // checking for presence standard coupon variable ( {{var coupon.code}})
                    $pattern2 = '|{{\s*var\s+coupon.code\s*}}|u';

                    if (preg_match_all($pattern2, $emailTemplateContent, $matches) > 0) {
                        $coupon = Mage::helper('followupemail/coupon')->createNew($this);
                        Mage::getSingleton('followupemail/log')->logSuccess('New coupon ' . $coupon->getCouponCode() . ' is created {' . print_r($coupon->getData(), TRUE) . '}');
                        $objects['coupon'] = $coupon;
                        Mage::getSingleton('followupemail/log')->logSuccess('Coupon ' . $coupon->getCouponCode() . ' used {' . print_r($coupon->getData(), TRUE) . '}');
                    }

                    // checking for presence extended coupon variable ( {{var coupons.__ALIAS__.code}})
                    $pattern1 = '|{{\s*var\s+coupons.(.*).code\s*}}|u';

                    if (preg_match_all($pattern1, $emailTemplateContent, $matches) > 0) {

                        // using object for access to variables from AWW_Followupemail_Model_Filter::filter()
                        $coupons = new Varien_Object();

                        foreach ($matches[1] as $couponId) {
                            $coupon = Mage::helper('followupemail/coupon')->createNew($this);
                            Mage::getSingleton('followupemail/log')->logSuccess('New coupon ' . $coupon->getCouponCode() . ' is created {' . print_r($coupon->getData(), TRUE) . '}');
                            Mage::getSingleton('followupemail/log')->logSuccess('Coupon ' . $coupon->getCouponCode() . ' used {' . print_r($coupon->getData(), TRUE) . '}');

                            $coupons->setData($couponId, $coupon);
                        }

                        $objects['coupons'] = $coupons;
                    }
                }
                $objects['has_coupon'] = isset($objects['coupon']);

                $objects['sequence_number'] = $sequenceNumber;

                $objects['time_delay'] = $chainItem['DAYS'] * 1440 + $chainItem['HOURS'] * 60 + $chainItem['MINUTES'];
                $objects['time_delay_text'] = Mage::helper('followupemail')->getTimeDelayText($chainItem['DAYS'], $chainItem['HOURS'], $chainItem['MINUTES']);

                $code = AWW_Followupemail_Helper_Data::getSecurityCode();
                $objects['security_code'] = $code;
                $objects['url_resume'] = $objects['store']->getUrl('followupemail/index/resume', array('code' => $code));
                $objects['url_unsubscribe'] = $objects['store']->getUrl('followupemail/index/unsubscribe', array('code' => $code));
                
                
               if(isset($objects['order'])) {
                    $paymentBlock = Mage::helper('payment')->getInfoBlock($objects['order']->getPayment())->setIsSecureMode(true);
                    $paymentBlock->getMethod()->setStore($params['store_id']);             
                    $objects['payment_html'] = $paymentBlock->toHtml(); 
               }
                 
                if (!$content = $this->_getContent($objects, $chainItem['TEMPLATE_ID'])) {
                    Mage::getSingleton('followupemail/log')->logError("rule id={$this->getId()} has invalid templateId=" . $chainItem['TEMPLATE_ID'] . " in sequenceNumber=$sequenceNumber", $this);
                } else {
                    $queue->add(
                        $code,
                        $sequenceNumber,
                        $content['sender_name'],
                        $content['sender_email'],
                        $objects['customer_name'],
                        ($this->_isTest) ? $this->getTestRecipient() : $objects['customer_email'], //$customerEmail,
                        $this->getId(),
                        time() + $objects['time_delay'] * 60,
                        $content['subject'],
                        $content['content'],
                        $objects['object_id'],
                        $params
                    );
                }

                $sequenceNumber++;
            }
            return true;
        }
        Mage::getSingleton('followupemail/log')->logWarning('rule id=' . $this->getId() . ' is not valid for event=' . $this->getEventType() . ' reason="' . $this->_validationMessage . '" objectId=' . (isset($objects['object_id']) ? $objects['object_id'] : 'none') . ', params="' . AWW_Followupemail_Helper_Data::printParams($params), $this);

        return false;
    }

    /*
     * Process rule of 'Customer birthday' type
     * @param array $params Initial parameters
     * @param string $templateId Email template code
     * @param int $timeDelay Time delay (in days)
     * @param int $sequenceNumber Sequence number in current chain
     * @return bool Processing result
     */
    public function processBirthday($params, $templateId, $timeDelay, $sequenceNumber)
    {
        $objects = $this->_createObjects($params, array());

        if (!$this->_validated) $this->validate($objects);
        if ($this->_isValid) {
            if (!(isset($params['customer_id']) && $params['customer_id'])) {
                Mage::getSingleton('followupemail/log')->logError('rule id=' . $this->getId() . ' processing error: customer_id is not set on birthday event, email="' . $objects['customer_email'] . '"', $this);
                return false;
            }

            // Generate coupon if it needed
            if ($this->getCouponEnabled()) {

                //get content of current email template
                $emailTemplate = $this->_getTemplate($templateId);
                $emailTemplateContent = $emailTemplate['content'];


                // checking for presence standard coupon variable ( {{var coupon.code}})
                $pattern2 = '|{{\s*var\s+coupon.code\s*}}|u';

                if (preg_match_all($pattern2, $emailTemplateContent, $matches) > 0) {
                    $coupon = Mage::helper('followupemail/coupon')->createNew($this);
                    Mage::getSingleton('followupemail/log')->logSuccess('New coupon ' . $coupon->getCouponCode() . ' is created {' . print_r($coupon->getData(), TRUE) . '}');
                    $objects['coupon'] = $coupon;
                    Mage::getSingleton('followupemail/log')->logSuccess('Coupon ' . $coupon->getCouponCode() . ' used {' . print_r($coupon->getData(), TRUE) . '}');
                }

                // checking for presence extended coupon variable ( {{var coupons.__ALIAS__.code}})
                $pattern1 = '|{{\s*var\s+coupons.(.*).code\s*}}|u';

                if (preg_match_all($pattern1, $emailTemplateContent, $matches) > 0) {

                    // using object for access to variables from AWW_Followupemail_Model_Filter::filter()
                    $coupons = new Varien_Object();

                    foreach ($matches[1] as $couponId) {
                        $coupon = Mage::helper('followupemail/coupon')->createNew($this);
                        Mage::getSingleton('followupemail/log')->logSuccess('New coupon ' . $coupon->getCouponCode() . ' is created {' . print_r($coupon->getData(), TRUE) . '}');
                        Mage::getSingleton('followupemail/log')->logSuccess('Coupon ' . $coupon->getCouponCode() . ' used {' . print_r($coupon->getData(), TRUE) . '}');

                        $coupons->setData($couponId, $coupon);
                    }

                    $objects['coupons'] = $coupons;
                }

            }
            $objects['has_coupon'] = isset($objects['coupon']);

            $queue = Mage::getModel('followupemail/queue');

            $objects['sequence_number'] = $sequenceNumber;
            $objects['time_delay'] = (int)$timeDelay;
            $objects['time_delay_absolute'] = abs((int)$timeDelay);
            $objects['time_delay_text'] = Mage::helper('followupemail')
                ->getTimeDelayText($timeDelay, 0, 0, ($timeDelay < 0) ? -1 : 1);

            $code = AWW_Followupemail_Helper_Data::getSecurityCode();
            $objects['security_code'] = $code;
            $objects['url_resume'] = $objects['store']->getUrl('followupemail/index/resume', array('code' => $code));
            $objects['url_unsubscribe'] = $objects['store']->getUrl('followupemail/index/unsubscribe', array('code' => $code));

            if (!$content = $this->_getContent($objects, $templateId)) {
                Mage::getSingleton('followupemail/log')->logError("rule id={$this->getId()} has invalid templateId=" . $templateId . " in sequenceNumber=$sequenceNumber", $this);
                return false;
            }

            $storedParams = $params;
            unset($storedParams['object_id']);

            $queue->add(
                $code,
                $sequenceNumber,
                $content['sender_name'],
                $content['sender_email'],
                $objects['customer_name'],
                ($this->_isTest) ? $this->getTestRecipient() : $objects['customer_email'], //$customerEmail,
                $this->getId(),
                time(),
                $content['subject'],
                $content['content'],
                $params['object_id'],
                $storedParams
            );
            return true;
        }
        Mage::getSingleton('followupemail/log')->logWarning("rule id={$this->getId()} is not valid for birthday event of customerId={$params['customer_id']}, params=" . AWW_Followupemail_Helper_Data::printParams($params), $this);
        return false;
    }

    /*
     * Sends test email
     * @param array $params Initial parameters
     * @param array $objects Additional initial parameters
     * @return bool Processing result
    */
    public function sendTestEmail($params, $objects = array())
    {
        $this->_validated = true;
        $this->_isValid = true;
        $this->_validationMessage = '';
        $this->prepareData();
        $this->setCancelEvents('');
        $this->_isTest = true;

        switch ($this->getEventType()) {
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_SHARED :
                $params['object_id'] = $params['wishlist_id'];
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_PRODUCT_ADD :
                $params['object_id'] = $params['product_id'];
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW :
                $params['object_id'] = $params['quote_id'];
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LOGGED_IN :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LAST_ACTIVITY :
            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY :
                $params['object_id'] = $params['customer_id'] ? $params['customer_id'] : $params['customer_email'];
                break;

            case AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK :
                $params['object_id'] = $params['resume_code'];
                break;

            default :
                $params['object_id'] = $params['order_increment_id'];
        }
        if (AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY == $this->getEventType()) {
            $params['customer_id'] = $params['object_id'];
            $params['store_id'] = Mage::app()->getStore()->getId();
            $result = true;
            $sequenceNumber = 1;
            foreach (unserialize($this->getChain()) as $chain) {
                $result = $result && $this->processBirthday($params, $chain['TEMPLATE_ID'], $chain['DAYS'], $sequenceNumber);
                $sequenceNumber++;
            }
        }
        else $result = $this->process($params, $objects);

        return $result;
    }
}
