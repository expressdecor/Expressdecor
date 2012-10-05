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

/**
 * phpunit --coverage-html ./report UnitTests
 */
class AWW_Followupemail_Test_Model_Config extends EcomDev_PHPUnit_Test_Case
{

    /**
    * Testing of set param
    *
    * @test
    * @loadFixture
    * @doNotIndexAll
    * @dataProvider dataProvider
    */
    public function setParam($testId, $name, $value)
    {
        $model = Mage::getModel('followupemail/config');
        $expected = $this->expected('id' . $testId);
        $model->load($name);
        $this->assertEquals(
            $expected->getValueBefore(),
            $model->getValue()
        );
        $model->setParam($name, $value);
        $model->load($name);
        $this->assertEquals(
            $expected->getValueAfter(),
            $model->getValue()
        );
    }

    /**
    * Testing of getting param
    *
    * @test
    * @loadFixture
    * @doNotIndexAll
    * @dataProvider dataProvider
    */
    public function getParam($testId, $name, $loaded, $default)
    {
        $model = Mage::getModel('followupemail/config');
        $expected = $this->expected('id' . $testId);
        if ($loaded)
            $model->load($name);
        $param = $model->getParam($name, $default);
        $this->assertEquals(
            $expected->getParam(),
            $param
        );
    }

}