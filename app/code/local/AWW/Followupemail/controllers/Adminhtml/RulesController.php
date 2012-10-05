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


class AWW_Followupemail_Adminhtml_RulesController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
        if (Mage::helper('followupemail')->checkVersion('1.4'))
            $this->_title('Follow Up Rules Manager');
        return $this->loadLayout()->_setActiveMenu('followupemail/items');
    }

    public function indexAction() {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        if (Mage::helper('followupemail')->checkVersion('1.4')) {
            $this->_title('Follow Up Rules Manager');
            $this->_title('Rule Edit');
        }
        $session = Mage::getSingleton('adminhtml/session');
        $ruleId = $this->getRequest()->getParam('id');
        $data = Mage::getModel('followupemail/rule')->load($ruleId)->getData();

        if(!empty($data) || $ruleId == 0)
        {
            $sessionData = Mage::getSingleton('adminhtml/session')->getFollowupemailData(true);
            if(is_array($sessionData)) $data = array_merge($data, $sessionData);
            $session->setFollowupemailData(false);

            if( isset($data['cancel_events'])
            && !is_array($data['cancel_events'])
            )   $data['cancel_events'] = explode(',', $data['cancel_events']);
            else
                $data['cancel_events'] = array();

            if(!$ruleId)
                $data['store_ids'] = array(0);

            if(     !isset($data['customer_groups'])
                ||  !$data['customer_groups']
                ||  !count(is_array($data['customer_groups'])
                        ? $data['customer_groups']
                        : ($data['customer_groups'] = explode(',', $data['customer_groups'])))
                ||  (in_array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_ALL, $data['customer_groups'])
                        && count($data['customer_groups']) > 1)
            )   $data['customer_groups'] = array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_ALL);

            if(Mage::helper('followupemail')->canUseAN())
            {
                if( !isset($data['anl_segments'])
                ||  !$data['anl_segments']
                ||  !count(is_array($data['anl_segments'])
                        ? $data['anl_segments']
                        : ($data['anl_segments'] = explode(',', $data['anl_segments']))
                    )
                ||  (   in_array(AWW_Followupemail_Model_Mysql4_Rule::ADVANCED_NEWSLETTER_SEGMENTS_ALL, $data['anl_segments'])
                        && count($data['anl_segments']) > 1
                    )
                )   $data['anl_segments'] = array(AWW_Followupemail_Model_Mysql4_Rule::ADVANCED_NEWSLETTER_SEGMENTS_ALL);
            }
            elseif(!isset($data['anl_segments'])) $data['anl_segments'] = false;

            if(!isset($data['chain'])) $data['chain'] = array();
            elseif(!is_array($data['chain'])) $data['chain'] = @unserialize($data['chain']);

            if(!isset($data['product_type_ids']) || !$data['product_type_ids'])
                $data['product_type_ids'] = array('all');
            elseif(is_string($data['product_type_ids']))
                $data['product_type_ids'] = explode(',', $data['product_type_ids']);

            if(!isset($data['sale_amount_condition'])
            && !isset($data['sale_amount_value'])   // if this is not a redirect from saveAction
            &&  isset($data['sale_amount'])
            &&  $data['sale_amount']
            &&  is_array($saleAmount = explode(AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_SEPARATOR, $data['sale_amount'], 2))
            &&  count($saleAmount)>1
            ) {
                $data['sale_amount_condition'] = array_search($saleAmount[0], AWW_Followupemail_Model_Source_Rule_Saleamount::getConditions());
                if(!$data['sale_amount_condition']) $data['sale_amount_condition'] = '0';
                $data['sale_amount_value'] = $saleAmount[1];
            }
            else
            {
                $data['sale_amount_condition'] = '0';
                $data['sale_amount_value'] = '';
            }

            if(!isset($data['test_objects'])) $data['test'] = array();
            elseif(!isset($data['test']))
                $data['test'] = unserialize($data['test_objects']);

            Mage::register('followupemail_data', $data);

            $this->loadLayout();
            $this->_setActiveMenu('followupemail/items');

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit'))
                ->_addLeft($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tabs'));

            $this->renderLayout();
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('The rule does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction($sendTest = false)
    {
        $session = Mage::getSingleton('adminhtml/session');
        if($data = $this->getRequest()->getPost())
        {
            if(!isset($data['cancel_events'])) $data['cancel_events'] = '';
            else $data['cancel_events'] = implode(',', $data['cancel_events']);

             if(isset($data['category_ids']))
                 $data['category_ids'] = implode(',', array_unique(explode(' ', trim(str_replace(',', ' ', $data['category_ids'])))));
            if(!isset($data['product_type_ids']) || in_array('all', $data['product_type_ids']))
                $data['product_type_ids'] = AWW_Followupemail_Model_Source_Product_Types::PRODUCT_TYPE_ALL;
            else $data['product_type_ids'] = implode(',', $data['product_type_ids']);

            if(!isset($data['store_ids'])) $data['store_ids'] = '';
            elseif(is_array($data['store_ids'])) $data['store_ids'] = implode(',', $data['store_ids']);

            // sku
            if(!isset($data['sku'])) $data['sku'] = array();
            else $data['sku'] = explode(',', $data['sku']);
            foreach($data['sku'] as $k => $v)
                if(!$v = trim($v)) unset($data['sku'][$k]);
                else $data['sku'][$k] = $v;
            $data['sku'] = implode(',', $data['sku']);

            // customer groups
            if(!isset($data['customer_groups'])) $data['customer_groups'] = '';
            if(!is_array($data['customer_groups']))
                $data['customer_groups'] = explode(',', $data['customer_groups']);
            if(    !count($data['customer_groups'])
                || (    in_array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_ALL, $data['customer_groups'])
                    &&  count($data['customer_groups']) > 1)
            )   $data['customer_groups'] = array(AWW_Followupemail_Model_Source_Customer_Group::CUSTOMER_GROUP_ALL);
            if(is_array($data['customer_groups']))
                $data['customer_groups'] = implode(',', $data['customer_groups']);

            // Advanced Newsletters segments
            if(!isset($data['anl_segments'])) $data['anl_segments'] = '';
            if(Mage::helper('followupemail')->canUseAN())
            {
                if(!is_array($data['anl_segments'])) $data['anl_segments'] = explode(',', $data['anl_segments']);

                if(     !count($data['anl_segments'])
                    || (in_array(AWW_Followupemail_Model_Mysql4_Rule::ADVANCED_NEWSLETTER_SEGMENTS_ALL, $data['anl_segments'])
                        &&  count($data['anl_segments']) > 1)
                )   $data['anl_segments'] = array(AWW_Followupemail_Model_Mysql4_Rule::ADVANCED_NEWSLETTER_SEGMENTS_ALL);
            }
            if(is_array($data['anl_segments']))
                $data['anl_segments'] = implode(',', $data['anl_segments']);

            // sale amount
            if(!isset($data['sale_amount_condition']) || !$data['sale_amount_condition']) $data['sale_amount_condition'] = '';
            $data['sale_amount_value'] = isset($data['sale_amount_value']) ? trim($data['sale_amount_value']) : '';
            if($data['sale_amount_value'] || $data['sale_amount_condition'])
                $data['sale_amount'] = AWW_Followupemail_Model_Source_Rule_Saleamount::getCondition($data['sale_amount_condition']).AWW_Followupemail_Model_Source_Rule_Saleamount::CONDITION_SEPARATOR.$data['sale_amount_value'];
            else $data['sale_amount'] = '';

            $data['test_objects'] = serialize($data['test']);

            // chain processing
            if(!isset($data['chain'])) $data['chain'] = array();
            else
            {
                foreach($data['chain'] as $key => $value)
                {
                    if(isset($value['delete']))
                    {
                        if($value['delete']) unset($data['chain'][$key]);
                        else unset($data['chain'][$key]['delete']);
                    }
                }
                foreach ($data['chain'] as $key => $value)
                    if(false === strpos($value['TEMPLATE_ID'], AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_SEPARATOR))
                    {
                        $session->addError($this->__('Please select template'));
                        $session->setFollowupemailData($data);
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'tab' => 'details'));
                        return;
                    }

                foreach($data['chain'] as $k => $v)
                {
                    $data['chain'][$k]['DAYS'] = trim($data['chain'][$k]['DAYS']);
                    if($data['chain'][$k]['DAYS'] && !is_numeric($data['chain'][$k]['DAYS']))
                    {
                        $session->addError($this->__('The quantity of days in the chain is not a number'));
                        $session->setFollowupemailData($data);
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'tab' => 'details'));
                        return;
                    }
                }

                if(count($data['chain']) > 1)
                {
                    // sorting
                    $chainSorted = array();
                    foreach($data['chain'] as $k => $v)
                        $chainSorted[$v['BEFORE']*($v['DAYS']*1440+$v['HOURS']*60+$v['MINUTES'])*10000 + mt_rand(0,9999)] = $k;

                    ksort($chainSorted, SORT_NUMERIC);

                    $chain = array();
                    foreach($chainSorted as $k => $v)
                        $chain[] = $data['chain'][$v];

                    $data['chain'] = $chain;
                }
            }

            if($this->getRequest()->getParam('coupon_enabled')) {
                $couponExpireDays = $this->getRequest()->getParam('coupon_expire_days');
                if(!$couponExpireDays || !intval($couponExpireDays)) {
                    $session->addError($this->__('Coupon expire days value must be integer and equals or greater than 1'));
                    $session->setFollowupemailData($data);
                    return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'tab' => 'followupemail_tabs_coupons'));
                } 
                if($couponExpireDays<1) {
                    $session->addError($this->__('Coupon expire days value can\'t be less than 1'));
                    $session->setFollowupemailData($data);
                    return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'tab' => 'followupemail_tabs_coupons'));
                }
                $couponCode = $this->getRequest()->getParam('coupon_prefix');
                if($couponCode && !preg_match('/^[a-zA-Z0-9]*$/', $couponCode)) {
                    $session->addError($this->__('The following symbols are allowed to be used in the \'Coupon Code Prefix\' field: a-z 0-9'));
                    $session->setFollowupemailData($data);
                    return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'tab' => 'followupemail_tabs_coupons'));
                }
                $this->getRequest()->setParam('coupon_expire_days', intval($couponExpireDays));
            }
            
            $data['chain'] = serialize($data['chain']);

            $data['id'] = $this->getRequest()->getParam('id');
            
            $model = Mage::getModel('followupemail/rule');
            $model->setData($data);
            try
            {
                $model->save();

                $session->setFollowupemailData(false);
                $session->addSuccess($this->__('Item was successfully saved'));

                if($this->getRequest()->getParam('sendTest'))
                {
                    if(!$data['test_recipient'])
                    {
                        $session->addError($this->__('To send a test message you have to fill up the \'Test recipient\' field'));
                            $this->_redirect('*/*/edit', array('id' => $model->getId(), 'tab' => 'followupemail_tabs_sendtest'));
                        return;
                    }
                    if($model->sendTestEmail($data['test']))
                        $session->addSuccess($this->__('Test email was successfully sent'));
                    else
                        $session->addError($this->__('Error sending test message'));
                }

                if($tab = $this->getRequest()->getParam('tab'))
                {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'tab' => $sendTest?$sendTest:$tab));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) 
            {
                Mage::logException($e);
                $session->addError($e->getMessage());
                $session->setFollowupemailData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $session->addError($this->__('Cannot find data to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if($this->getRequest()->getParam('id')>0)
            try
            {
                Mage::getModel('followupemail/rule')
                    ->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The rule was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e)
            {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $followupemailIds = $this->getRequest()->getParam('followupemail');
        if(!is_array($followupemailIds))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        }
        else
        {
            try
            {
                foreach ($followupemailIds as $followupemailId)
                {
                    Mage::getModel('followupemail/rule')
                        ->setId($followupemailId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($followupemailIds)));
            }
            catch (Exception $e)
            {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $followupemailIds = $this->getRequest()->getParam('followupemail');
        if(!is_array($followupemailIds))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        }
        else
            try
            {
                foreach ($followupemailIds as $followupemailId)
                {
                    $followupemail = Mage::getSingleton('followupemail/rule')
                        ->load($followupemailId)
                        ->setIsActive($this->getRequest()->getParam('status'))
                        ->save();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($followupemailIds)));
            }
            catch (Exception $e)
            {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'followupemail.csv';
        $content    = $this->getLayout()->createBlock('followupemail/adminhtml_followupemail_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'followupemail.xml';
        $content    = $this->getLayout()->createBlock('followupemail/adminhtml_followupemail_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function categoriesAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_categories')->toHtml()
        );
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('followupemail/items');
    }
}
