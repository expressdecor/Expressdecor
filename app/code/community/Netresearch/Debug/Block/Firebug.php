<?php

/**
 * block for displaying log-messages
 *
 * @category   Debug
 * @package    Netresearch_Debug
 * @author     Stephan Hoyer <stephan.hoyer@netresearch.de>
 * @copyright  Copyright (c) 2009 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Netresearch_Debug_Block_Firebug extends Mage_Core_Block_Abstract
{
    /**
     * There are some Problems with Firebug showing collapsed groups on outputing large objects/arrays.
     * If you deactivate the default collapse by changing this variable from true to false
     * all works well, but unfortuantly the objects are then not initialy collapsed!
     *
     * @var bool determines whether to subgroups should be collapsed by default
     * @access private
     */
    private $__collapseGroups = true;

    /**
     * generates html-cote for dumping console-log
     *
     * @return string html-code
     * @access protected
     */
    protected function _toHtml()
    {
        if (
            !$this->_beforeToHtml() ||
            !Mage::getStoreConfig('dev/firebug/enabled') ||
            !Mage::helper('core')->isDevAllowed() ||
            0
        ) {
            return '';
        }

        $log = Mage::helper('debug')->getLog();

        $output = "";

        foreach ($log as $entry) {
            $entry['message']['stack'] = $entry['stack'];
            $output .= $this->__group($entry['caption'], $entry['message'], false);
        }

        return
            "<script>\n".
                $output.
            "</script>\n";
    }


    /**
     * generates html-cote of last loged element for dumping console-log
     *
     * @return string html-code
     * @access protected
     */
    public function getLastLoggedAsHtml()
    {
        $log = Mage::helper('debug')->getLog();
        $entry = $log[sizeof($log)-1];
        $output = "";
        $entry['message']['stack'] = $entry['stack'];
        $output .= $this->__group($entry['caption'], $entry['message'], false);

        return
            "<script>\n".
                $output.
            "</script>\n";

    }

    /**
     * generates a grouped output with caption of the elements in the $content.
     * !! recursive function !!
     *
     * @param string $caption caption of the group
     * @param array $content
     * @param bool $collapsed determines if the group should be shown collapsed
     * @return string js-code for printing log to firebug
     */
    private function __group($caption, $content, $collapsed)
    {
        $normalLines = array();
        $return = "console.group".($collapsed ? "Collapsed" : "")."('".str_replace("'", "\'", $caption)."');\n";
        foreach ($content as $key => $element) {
            if(is_array($element) and count($element)>0) {
                if(count($normalLines)>0) {
                    $return .= "console.log('  ".implode("\\n  ", str_replace("'", "\'", $normalLines))."');\n";
                }
                $return .= $this->__group($key, $element, Mage::getStoreConfig('dev/firebug/collapse'));
                $normalLines = array();
            }
            else {
                $normalLines[] = $key;
            }
        }
        if(count($normalLines)>0) {
            $return .= "console.log('  ".implode("\\n  ", str_replace("'", "\'", $normalLines))."');\n";
        }
        $return .= "console.groupEnd();\n";
        return $return;
    }

}