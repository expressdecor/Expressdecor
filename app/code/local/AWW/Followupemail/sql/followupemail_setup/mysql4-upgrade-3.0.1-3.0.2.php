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


$installer = $this;
$installer->startSetup();

$coreConfig = Mage::getModel('core/config');
if($coreConfig instanceof Mage_Core_Model_Config)
try
{
    $tablePrefix = $coreConfig->getTablePrefix();
    if($tablePrefix)
    {
        $ruleTableName = $this->getTable('followupemail/rule');
        $queueTableName = $this->getTable('followupemail/queue');
        $linktrackingTableName = $this->getTable('followupemail/linktracking');

        $installer->run("
ALTER TABLE $queueTableName DROP FOREIGN KEY `FK_queue_to_rule`;
ALTER TABLE $queueTableName ADD CONSTRAINT `FK_queue_to_rule` FOREIGN KEY (`rule_id`) REFERENCES $ruleTableName (`id`) ON DELETE CASCADE;

ALTER TABLE $linktrackingTableName DROP FOREIGN KEY `FK_link_to_queue`;
ALTER TABLE $linktrackingTableName ADD CONSTRAINT `FK_link_to_queue` FOREIGN KEY (`rule_id`) REFERENCES $queueTableName (`id`) ON DELETE CASCADE;
        ");
    }
}
catch (Exception $e) { }

$installer->endSetup();
