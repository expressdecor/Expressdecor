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

try
{
    $ruleTableName = $this->getTable('followupemail/rule');

    $installer->run("
ALTER TABLE $ruleTableName 
ADD `sku` varchar(255) default NULL,
ADD `send_to_subscribers_only` tinyint(1) NOT NULL default '0',
ADD `customer_groups` varchar(255) default NULL,
ADD `anl_segments` text COMMENT 'Advanced Newsletter segments';
    ");

    $rules = $this->_conn->query('SELECT * FROM '.$ruleTableName)->fetchAll();

    foreach($rules as $rule)
    {
        $chain = unserialize($rule['chain']);
        foreach($chain as $key => $item)
            if(isset($item['DAYS_AFTER']))
            {
                $t = $item['DAYS_AFTER'];
                unset($chain[$key]['DAYS_AFTER']);
                $chain[$key]['BEFORE'] = ($t<0) ? -1 : 1;
                $t = abs($t);
                $chain[$key]['DAYS'] = floor($t);
                $t = ($t - floor($t)) * 24;
                $chain[$key]['HOURS'] = floor($t);
                $chain[$key]['MINUTES'] = (int)(($t - $chain[$key]['HOURS']) * 60);
            }

        $installer->run('UPDATE '.$ruleTableName.' SET chain="'.mysql_escape_string(serialize($chain)).'" WHERE id = '.$rule['id'].';');
    }

}
catch (Exception $e) { }

$installer->endSetup();
