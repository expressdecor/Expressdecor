<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
* Class config
*
* @category   Mage
* @package    Mage_Connect
* @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Maged_Model_Config_Enterprise extends Maged_Model_Config_Abstract implements Maged_Model_Config_Interface
{

    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->load();
    }

    /**
     * Get Auth data from config
     * @param Mage_Connect_Config $config
     * @return array auth data
     */
    private function _getAuth($config)
    {
        $auth = $config->__get('auth');
        $auth = explode('@', $auth);
        return $auth;
    }

    /**
     * Set data for Settings View
     *
     * @param Mage_Connect_Config $config
     * @param Maged_View $view
     * @return null
     */
    public function setInstallView($config, $view)
    {
        $root_channel = $this->get('root_channel');
        $view->set('channel_logo', $root_channel);
        $view->set('channel_steps', $view->template($root_channel . '/install_steps.phtml'));
        $view->set('channel_notice', $view->template($root_channel . '/install_notice.phtml'));
        $view->set('channel_protocol_fields', $view->template($root_channel . '/auth.phtml'));
    }


    /**
     * Set data for Settings View
     * @param Mage_Connect_Config $config
     * @param Maged_View $view
     * @return null
     */
    public function setSettingsView($config, $view)
    {
        $auth = $this->_getAuth($config);
        if ($auth) {
            $auth = explode('@', $config->__get('auth'));
            $view->set('auth_username', isset($auth[0]) ? $auth[0] : '');
            $view->set('auth_password', isset($auth[1]) ? $auth[1] : '');
        }
        $view->set('channel_protocol_fields', $view->template($this->get('root_channel') . '/auth.phtml'));
    }

    /**
     * Set session data for Settings
     * @param Mage_Connect_Config $config Config object
     * @param mixed $session Session object
     * @return null
     */
    public function setSettingsSession($config, $session)
    {
        $auth = $this->_getAuth($config);
        if (isset($auth[0]) && isset($auth[1]) && !empty($auth[0])) {
            $session->set('auth', array(
                'username' => $auth[0],
                'password' => $auth[1],
            ));
        } else {
            $session->set('auth', array());
        }
    }

    /**
     * Get root channel URI
     *
     * @return string Root channel URI
     */
    public function getRootChannelUri(){
        if (!$this->get('root_channel_uri')) {
            $this->set('root_channel_uri', 'connect20.magentocommerce.com/enterprise');
        }
        return $this->get('root_channel_uri');
    }

    /**
     * Set config data from POST
     *
     * @param Mage_Connect_Config $config Config object
     * @param array $post post data
     * @return null
     */
    public function setPostData($config, &$post)
    {
        if (!empty($post['auth_username']) and isset($post['auth_password'])) {
            $post['auth'] = $post['auth_username'] .'@'. $post['auth_password'];
        } else {
            $post['auth'] = '';
        }
        if(!is_null($config)){
            $config->auth = $post['auth'];
        }
    }
    
    /**
     * Set additional command options
     *
     * @param Mage_Connect_Config $config Config object
     * @param array $options
     * @return null
     */
    public function setCommandOptions($config, &$options)
    {
        $auth = $this->_getAuth($config);
        $options['auth'] = array(
                'username' => $auth[0],
                'password' => $auth[1],
        );
    }
}
?>
