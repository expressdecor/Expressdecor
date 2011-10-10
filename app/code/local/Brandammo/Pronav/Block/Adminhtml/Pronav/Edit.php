<?php

class Brandammo_Pronav_Block_Adminhtml_Pronav_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'pronav';
        $this->_controller = 'adminhtml_pronav';
        
        $this->_updateButton('save', 'label', Mage::helper('pronav')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('pronav')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('pronav_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'pronav_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'pronav_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('pronav_data') && Mage::registry('pronav_data')->getId() ) {
            return Mage::helper('pronav')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('pronav_data')->getName()));
        } else {
            return Mage::helper('pronav')->__('Add ProNav Item');
        }
    }
}