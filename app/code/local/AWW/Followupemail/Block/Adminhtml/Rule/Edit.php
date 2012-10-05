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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'followupemail';
        $this->_controller = 'adminhtml_rule';
        
        $this->_updateButton('save', 'label', $this->__('Save Rule'));
        $this->_updateButton('save', 'onclick', 'save(this)');
        $this->_updateButton('delete', 'label', $this->__('Delete Rule'));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_addButton('sendtest_button', array(
            'label'     => $this->__('Save And Send Test Email'),
            'onclick'   => 'saveAndSendTest()',
            'class'  => 'save'
        ), -200);

        $this->_formScripts[] = "
function setRequired(elementId, required) {
    element = $(elementId);
    if(required)
        element.addClassName('required-entry');
    else
        element.removeClassName('required-entry');

    return element;
}

function checkSendToCustomer() {
    return setRequired('email_copy_to', !Boolean(parseInt($('email_send_to_customer').value)));
}

function toggleEditor() {
    if (tinyMCE.getInstanceById('followupemail_content') == null) {
        tinyMCE.execCommand('mceAddControl', false, 'followupemail_content');
    } else {
        tinyMCE.execCommand('mceRemoveControl', false, 'followupemail_content');
    }
}

function save(saveButton) {
    if(editForm.submit())
        saveButton.disabled = true;
}

function saveAndContinueEdit(){
    editForm.submit($('edit_form').action+'tab/'+followupemail_tabsJsTabs.activeTab.id);
}

function saveAndSendTest(){
    editForm.submit($('edit_form').action+'tab/'+followupemail_tabsJsTabs.activeTab.id+'/sendTest/1/');
}

function doBirthdayChanges(){
    if( $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY."' )
    {
        for(var i=0; i<maxItemsCount; i++)
            if($('chain_row_'+i+'_BEFORE') != null)
            {
                $('chain_row_'+i+'_BEFORE').options[1].disabled = '';
                $('chain_row_'+i+'_HOURS').style.display = 'none';
                $('chain_row_'+i+'_MINUTES').style.display = 'none';
            }
    }
    else
    {
        for(var i=0; i<maxItemsCount; i++)
            if($('chain_row_'+i+'_BEFORE') != null)
            {
                $('chain_row_'+i+'_BEFORE').value = $('chain_row_'+i+'_BEFORE').options[0].value;
                $('chain_row_'+i+'_BEFORE').options[1].disabled = 'disabled';
                $('chain_row_'+i+'_HOURS').style.display = '';
                $('chain_row_'+i+'_MINUTES').style.display = '';
            }
    }
}

function checkEventType(){
    doBirthdayChanges();
    if(
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW."' ||
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY."' ||
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LOGGED_IN."' ||
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LAST_ACTIVITY."' ||
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK."')
    {
        $('sale_amount_container').hide();
        $('product_type_ids').up(1).hide();
        $('sku').up(1).hide();
        $('followupemail_tabs_categories').style.display='none';
    }
    else if(
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_SHARED."' ||
        $('event_type').getValue() == '".AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_PRODUCT_ADD."')
    {
        $('sku').up(1).show();
        $('sale_amount_container').hide();
    }
    else {
        $('sku').up(1).show();
        $('sale_amount_container').show();
        $('product_type_ids').up(1).show();
        $('followupemail_tabs_categories').style.display='';
    }
}

function checkCouponEnabled() {
    if($('coupon_enabled'))
        return setRequired('coupon_sales_rule_id', Boolean(parseInt($('coupon_enabled').value)));
    else
        return false;
}

checkEventType();
checkSendToCustomer();
checkCouponEnabled();
";
    }

    public function getHeaderText()
    {
        $data = Mage::registry('followupemail_data');
        if ( $data && isset($data['title']) && $data['title'] ) 
            return $this->__("Edit Rule '%s'", $this->htmlEscape($data['title']));
        else return $this->__('Add Rule');
    }
}
