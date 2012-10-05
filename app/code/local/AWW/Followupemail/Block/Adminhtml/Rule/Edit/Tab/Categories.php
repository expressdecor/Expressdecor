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
 * @package    AW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_categoryIds;
    protected $_selectedNodes = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/categories.phtml');
    }

    protected function getCategoryIds()
    {
        $id = $this->getRequest()->getParam('id');
        return $id ? array_unique(explode(',', Mage::getModel('followupemail/rule')->load($id)->getCategoryIds())) : array();
    }

    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    public function getRootNode()
    {
        $root = parent::getRoot();
        if ($root && in_array($root->getId(), $this->getCategoryIds())) $root->setChecked(true);
        return $root;
    }

    protected function _getNodeJson($node, $level=1)
    {
        $item = parent::_getNodeJson($node, $level);
        $isParent = $this->_isParentSelectedCategory($node);

        if ($isParent) $item['expanded'] = true;

        if (in_array($node->getId(), $this->getCategoryIds())) $item['checked'] = true;

        return $item;
    }

    protected function _isParentSelectedCategory($node)
    {
        foreach ($this->_getSelectedNodes() as $selected) 
            if ($selected) {
                $pathIds = explode('/', $selected->getPathId());
                if (in_array($node->getId(), $pathIds)) {
                    return true;
                }
            }
        return false;
    }

    protected function _getSelectedNodes()
    {
        if ($this->_selectedNodes === null) {
            $this->_selectedNodes = array();
            foreach ($this->getCategoryIds() as $categoryId) {
                $this->_selectedNodes[] = $this->getRoot()->getTree()->getNodeById($categoryId);
            }
        }
        return $this->_selectedNodes;
    }

    public function getCategoryChildrenJson($categoryId)
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);

        if (!$node || !$node->hasChildren()) return '[]';

        $children = array();
        foreach ($node->getChildren() as $child) $children[] = $this->_getNodeJson($child);

        return Zend_Json::encode($children);
    }

    public function getLoadTreeUrl($expanded=null)
    {
        return $this->getUrl('*/*/categoriesJson', array('_current'=>true));
    }
}
