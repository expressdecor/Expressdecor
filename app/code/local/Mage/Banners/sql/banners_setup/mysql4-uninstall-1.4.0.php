<?php
/**
 * Testimonial extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Usol
 * @package    Testimonial
 * @copyright  Copyright (c) 2009 Unitedsol.net
 */

/**
 * @category   Testimonial
 * @package    Testimonial
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE `{$this->getTable('banners')}`;
DROP TABLE `{$this->getTable('banners_store')}`;

");

$installer->endSetup(); 
