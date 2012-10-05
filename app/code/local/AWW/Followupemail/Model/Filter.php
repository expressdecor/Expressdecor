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


class AWW_Followupemail_Model_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /*
     * Foreach directive parameter names
     */
    const OBJECT_TO_ITERATE = 'var';
    const TEMPLATE_NAME = 'template';
    const ROW_ITEM_ALIAS_NAME = 'alias';
    const ROW_ITEM_NAME = 'row_item';

    /*
     * Foreach directive row number variable suffix
     */
    const ROW_NUMBER_SUFFIX = '_row_number';

    /*
     * Thumbnail directive parameter names
     */
    const THUMBNAIL_SOURCE = 'source';
    const THUMBNAIL_DIMENSION = 'size';

    /*
     * Thumbnail directive default dimension value
     */
    const THUMBNAIL_DIMENSION_DEFAULT = 56;

    /*
     * Unprocessed text marking quote
     */
    const FILTER_QUOTE_UNPROCESSED = '`';

    /*
     * Directive opening and closing brackets
     */
    const FILTER_DIRECTIVE_OPENING_BRACKETS = '{{';
    const FILTER_DIRECTIVE_CLOSING_BRACKETS = '}}';

    /*
     * @var array Contains filtering errors
     */
    protected $_errors = array();
    
    protected $_storeId = null;

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /*
     * @var array Condition signs
     */
    protected $_conditionSigns = array(
        '2 chars' => array('<=', '>=', '<>', '!=', '=='),
        '1 char' => array('<', '>', '='), 
        'text' => array(' not in ', ' in '),
        'single' => array(' not exists', ' exists', ' is not set', ' is set'),
    );


    /*
     * Class constructor
     */
    // public function __construct()
    // {
        // parent::__construct();
        // $this->_modifiers['formatPrice'] = array($this, 'modifierFormatPrice');
        // $this->_modifiers['formatDateTime'] = array($this, 'modifierFormatDateTime');
        // $this->_modifiers['formatDecimal'] = array($this, 'modifierFormatDecimal');
    // }

    /*
     * Returns all errors reported during filtering
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /*
     * Reports error and adds error message to internal error array
     * @param string $message Error message
     * @return string
     */
    protected function reportError($message)
    {
        $this->_errors[] = $message;
        return ' [ FILTER ERROR : '.$message.' ] ';
    }

    /*
     * Searches for nearest unquoted token
     * @param string @subject Subject to inspect
     * @param string @source Where to search in
     * @param int $from Start position
     * @return string $subject nearest to $from, counting single back quotes and skipping $subject when it's inside the opened quote
     */
    public function findNearestUnquoted($subject, $source, $from = 0)
    {
        $isQuoted = false;
        $currentPos = $from;
        do
        {
            if(false === $nearestPos = strpos($source, $subject, $currentPos)) return false;
            for(; $currentPos < $nearestPos; $currentPos++)
            {
                if('\\' == $char = substr($source, $currentPos, 1)) $currentPos++; // ignoring special chars
                elseif(self::FILTER_QUOTE_UNPROCESSED == $char) $isQuoted = !$isQuoted;
            }
            if(!$isQuoted) return $nearestPos;
            $currentPos++;
        } while(true);
    }

    /*
     * Searches for parameters
     * @param string $value String to inspect
     */
    protected function _findParams($value)
    {
        $char = substr($value, 0, 1);
        if('"' == $char || '\'' == $char) //if the first char is " then this is a string
        {
            $found = false;
            for($i = 2; $i < strlen($value); $i++)
                if('\\' == $c = substr($value, $i, 1)) $i++;
                elseif($char == $c)
                {
                    $found = true;
                    break;
                }
            if(!$found) return $value;
            $op1 = substr($value, 1, $i-1); // 1st operand
            $value = trim(substr($value, $i+1)); // reminder
            if(!$value) return substr($op1, 0, strlen($op1)-1); // the parameter is text in double quotes
            foreach($this->_conditionSigns as $conditionSignGroup)
                foreach($conditionSignGroup as $conditionSign)
                    if( ($conditionSign == substr($value, 0, strlen($conditionSign))) ||
                        ($conditionSign == substr(' '.$value, 0, strlen($conditionSign))))
                    {
                        $op2 = trim(substr($value, strlen($conditionSign)));
                        if($op2)
                        {
                            $char = substr($op2, 0, 1);
                            if('"' == $char || '\'' == $char)
                                if($char == substr($op2, strlen($op2)-1))
                                {
                                    $op2 = substr($op2, 1, strlen($op2)-2);
                                    $op2IsString = true;
                                }
                        }
                        return array(
                            'sign' => $conditionSign,
                            'op1' => $op1,
                            'op1 is string' => true,
                            'op2' => $op2,
                            'op2 is string' => isset($op2IsString));
                    }
            return false;
        }
        else
        {
            foreach($this->_conditionSigns as $conditionSignGroup)
                foreach($conditionSignGroup as $conditionSign)
                    if(false !== $pos = strpos($value, $conditionSign))
                    {
                        $op2 = trim(substr($value, $pos + strlen($conditionSign)));
                        if($op2)
                        {
                            $char = substr($op2, 0, 1);
                            if('"' == $char || '\'' == $char)
                                if($char == substr($op2, strlen($op2)-1))
                                {
                                    $op2 = substr($op2, 1, strlen($op2)-2);
                                    $op2IsString = true;
                                }
                        }
                        return array(
                            'sign' => $conditionSign,
                            'op1' => trim(substr($value, 0, $pos)),
                            'op1 is string' => false,
                            'op2' => $op2,
                            'op2 is string' => isset($op2IsString));
                        break 2;
                    }
        }
        return false;
    }

    /*
     * This is for future extension of the condition set
     * @todo add the condition sign to the $_conditionSigns and override the _parseAdditionalConditions
     */
    protected function _parseAdditionalConditions($conditionSign, $operand1, $operand2)
    {
//         return $operand1.$conditionSign.$operand2;
        return false;
    }

    /*
     * Parses condition
     * @param string $params Parameters tp parse
     */
    protected function _parseCondition($params)
    {
        $parsedCondition = $this->_findParams($params);

        if(is_bool($parsedCondition) and !$parsedCondition) 
            return (isset($this->_templateVars[$params])
                    ? $this->_templateVars[$params]
                    : $this->_getVariable($params, $params));
        elseif(is_string($parsedCondition) and !$parsedCondition)
            return $parsedCondition;
        elseif(!is_array($parsedCondition)) return false;

        $conditionSign = $parsedCondition['sign'];
        $operand1 = $parsedCondition['op1'];
        $operand2 = $parsedCondition['op2'];

        if($operand1 && !$parsedCondition['op1 is string']) 
            if(isset($this->_templateVars[$operand1])) $operand1 = $this->_templateVars[$operand1];
            else $operand1 = $this->_getVariable($operand1, $operand1);
        if($operand2 && !$parsedCondition['op2 is string'])
            if(isset($this->_templateVars[$operand2])) $operand2 = $this->_templateVars[$operand2];
            else $operand2 = $this->_getVariable($operand2, $operand2);

        switch($conditionSign)
        {
            case '<=': return $operand1 <= $operand2; break;
            case '>=': return $operand1 >= $operand2; break;
            case '<>':
            case '!=': return $operand1 != $operand2; break;
            case '=' :
            case '==': return $operand1 == $operand2; break;
            case '<' : return $operand1 < $operand2; break;
            case '>' : return $operand1 > $operand2; break;
            case ' not in ' : $not = true;
            case ' in ' : 
                if(!is_array($operand2)) return $this->reportError('The second operand is not an array');
                else $result = in_array($operand1, $operand2); 
                return isset($not) ? !$result : $result;
                break;
            case ' not exists' :
            case ' is not set' : $not = true;
            case ' exists' :
            case ' is set' :
                if($operand2) return 'The second operand is not allowed';
                if(!$operand1) return 'The first operand is required';
                $result = isset($this->_templateVars[$operand1])
                            ? $this->_templateVars[$operand1]
                            : $this->_getVariable($operand1, false); 
                return isset($not) ? !$result : $result;
                break;
            default : return $this->_parseAdditionalConditions($conditionSign, $operand1, $operand2);
        }
        return true;
    }

    /*
     * if overwritten directive
     * Usage:
     * {{if var_name1 condition var_name2}} ...[{{elseif ...}}... n times][ {{else}} ]... {{endif}}
     * {{if 'var_name1' condition "var_name2"}}
     * {{if var_name condition}}
     * {{if var_name}}
     */
    public function ifNewDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        $this->directiveStack[$level][] = array(
            'name'      => 'if',
            'startPos'  => $partStartPos,
            'result'    => $this->_parseCondition($directiveParams),
        );
        return '';
    }

    /*
     * elseif directive
     */
    public function elseifDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        if(!isset($this->directiveStack[$level]))
        {
            $this->reportError('ELSEIF is a first operand');
            return;
        }
        $info = end($this->directiveStack[$level]);
        if('if' != $info['name'] && 'elseif' != $info['name'])
        {
            $this->reportError('ELSEIF is not after if or elseif');
        }

        $result = false;
        while(false != $info && in_array($info['name'], array('if', 'elseif')))
        {
            if($info['result'])
            {
                $result = true;
                break;
            }
            $info = prev($this->directiveStack[$level]);
        }

        $this->directiveStack[$level][] = array(
            'name'      => 'elseif',
            'startPos'  => $partStartPos,
            'result'    => $result || $this->_parseCondition($directiveParams),
        );
        return '';
    }

    /*
     * else directive
     */
    public function elseDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        if(!isset($this->directiveStack[$level]) || !count($this->directiveStack[$level]))
        {
            $this->reportError('ELSE is not after if or elseif');
            return;
        }
        $info = end($this->directiveStack[$level]);
        if('if' != $info['name'] && 'elseif' != $info['name'])
        {
            $this->reportError('ELSE is not after if or elseif');
        }

        $this->directiveStack[$level][] = array(
            'name'      => 'else',
            'startPos'  => $partStartPos,
        );
        return '';
    }

    /*
     * endif directive
     */
    public function endifDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        if(!isset($this->directiveStack[$level]) || !count($this->directiveStack[$level]))
        {
            return $this->reportError('ENDIF closes nothing');
        }
        $info = end($this->directiveStack[$level]);
        if(!in_array($info['name'], array('if', 'elseif', 'else')))
        {
            return $this->reportError('ENDIF is not after if, elseif, or else');
        }

        $ifChainStackIds = array();
        $this->directiveStack[$level][] = array('startPos' => $partStartPos, 'name' => 'endif');
        $info = end($this->directiveStack[$level]);
        while(false != $info)
        {
            array_unshift($ifChainStackIds, key($this->directiveStack[$level]));
            if('if' == $info['name']) break;
            $info = prev($this->directiveStack[$level]);
        }

        $result = array();
        $first = $this->directiveStack[$level][reset($ifChainStackIds)]; 
        $result['deleteFromPos'] = $first['startPos'];

        $found = false;
        foreach($ifChainStackIds as $ifChainStackId)
        {
            $info = $this->directiveStack[$level][$ifChainStackId];
            if($found)
            {
                $result['insertLen'] = $info['startPos'] - $result['insertFromPos'];
                $found = 1;
                break;
            }
            else
                if(!isset($info['result']) || $info['result'])
                {
                    $result['insertFromPos'] = $info['startPos'];
                    $found = true;
                }
        }
        if(1 !== $found) return array('deleteFromPos' => $this->directiveStack[$level][$ifChainStackIds[0]]['startPos']);

        foreach($ifChainStackIds as $ifChainStackId)
            unset($this->directiveStack[$level][$ifChainStackId]);
        return $result;
    }

    /*
     * depend overwritten directive
     * Usage: {{depend var_name}} ... {{enddepend}}
     */
    public function dependNewDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        $this->directiveStack[$level][] = array(
            'name'      => 'depend',
            'startPos'  => $partStartPos,
            'result'    => $this->_parseCondition($directiveParams),
        );
        return '';
    }

    /*
     * endDepend overwritten directive
     * Usage: {{depend var_name}} ... {{enddepend}}
     */
    public function endDependDirective($directiveName, $directiveParams, $partStartPos, $level)
    {
        if(!isset($this->directiveStack[$level]) || !count($this->directiveStack[$level]))
        {
            return $this->reportError('ENDDEPEND closes nothing');
        }
        $info = end($this->directiveStack[$level]);
        if('depend' != $info['name'])
        {
            return $this->reportError('ENDDEPEND is not after depend');
        }
        if(!$info['result']) return array('deleteFromPos' => $info['startPos']);
        else return false;
    }


    /*
     * @var array Directive stack
     */
    protected $directiveStack = array(0 => 0);

    /*
     * @var int Current directive stack depth
     */
    protected $directiveStackLevel = 0;

    /*
     * @var array Complex directive part names
     */
    protected $complexDirectives = array(
        'if'        => 'ifNew',
        'else'      => 'else',
        'elseif'    => 'elseif',
        'endif'     => 'endif',
        'depend'    => 'dependNew',
        'enddepend' => 'endDepend');
    /*
     * Process overridden directive
     */
    protected function _processDirective($value, $partStartPos, $level)
    {
        $lenOpening = strlen(self::FILTER_DIRECTIVE_OPENING_BRACKETS);
        $valueStripped = trim(substr($value, $lenOpening, strlen($value) - strlen(self::FILTER_DIRECTIVE_CLOSING_BRACKETS) - $lenOpening));
        if(!$valueStripped) return $this->reportError('empty braces \{ \{  \} \}');
        $parts = explode(' ', $valueStripped, 2);
        $directiveName = strtolower($parts[0]);
        $directiveParams = (count($parts)>1) ? trim($parts[1]) : '';

        if('/' == substr($directiveName, 0, 1)) $directiveName = 'end'.substr($directiveName, 1); // replacing leading '/' with 'end'

        $result = '';

        try
        {
            if(array_key_exists($directiveName, $this->complexDirectives))
            {
                $result = call_user_func(
                    array($this, $this->complexDirectives[$directiveName].'Directive'), // callback function
                    $directiveName,
                    $directiveParams,
                    $partStartPos,
                    $level
                );
            }
            else
            {
                $callback = array($this, $directiveName.'Directive');
                if(is_callable($callback))
                    $result = call_user_func($callback, array($value, $directiveName, ' '.$directiveParams));
                else $result = "No such directive '$directiveName'";
            }
        }
        catch (Exception $e)
        {
            $this->reportError("Error processing directive '$value' on $partStartPos at level ".$level);
//             throw $e; 
        }

        return $result;
    }

    /**
     * Overriden function for processing {{skin url=""}} directives only with
     * using adminhtml area
     * @param array $construction
     * @return string
     */
    public function skinDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $params['_absolute'] = $this->_useAbsoluteLinks;

        $newParams = array(
            'area' => 'adminhtml',
            'package' => 'default'
        );
        if(isset($params['_area'])) {
            $newParams['area'] = $params['_area'];
            $newParams['package'] = '';
        }
        $_oldParams = Mage::getDesign()->setAllGetOld($newParams);
        Mage::getDesign()->setTheme('');
        $url = Mage::getDesign()->getSkinUrl($params['url'], $params);
        Mage::getDesign()->setAllGetOld($_oldParams);
        Mage::getDesign()->setTheme('');

        return $url;
    }

    /*
     * Filters value given
     * @param string $value Text to filter
     * @return string Filered text
     */
    public function filter($value)
    {
// the next two lines were moved here from __construct() due to compatibility issue to Magento version 1.2
        $this->_modifiers['formatPrice'] = array($this, 'modifierFormatPrice');
        $this->_modifiers['formatDateTime'] = array($this, 'modifierFormatDateTime');
        $this->_modifiers['formatDecimal'] = array($this, 'modifierFormatDecimal');

        $this->_errors = array();
        $currentPos = 0;
        $level = 0;
        $parts = array(0 => 0);
        do
        {
            $posClosing = $this->findNearestUnquoted(self::FILTER_DIRECTIVE_CLOSING_BRACKETS, $value, $currentPos);
            $posOpening = $this->findNearestUnquoted(self::FILTER_DIRECTIVE_OPENING_BRACKETS, $value, $currentPos);
            if((false === $posClosing) && $level)
            {
                $this->reportError('non-closed brackets on '.$level.' levels');
            }
            if((false === $posClosing) && (false === $posOpening)) break;
            if((false !== $posOpening) && $posOpening < $posClosing)
            {
                $level++;
                $parts[$level] = $posOpening;
                $currentPos = $posOpening + strlen(self::FILTER_DIRECTIVE_OPENING_BRACKETS);
            }
            else
            {
                $partStartPos = ($level >= 0) ? $parts[$level] : 0;
                $posClosing += strlen(self::FILTER_DIRECTIVE_CLOSING_BRACKETS);
                $directiveResult = $this->_processDirective(substr($value, $partStartPos, $posClosing - $partStartPos), $partStartPos, $level);
                if(is_array($directiveResult))
                {
                    if(isset($directiveResult['insertFromPos']) && isset($directiveResult['insertLen']))
                        $partToInsert = substr($value, $directiveResult['insertFromPos'], $directiveResult['insertLen']);
                    else $partToInsert = '';

                    $deleteFromPos  = isset($directiveResult['deleteFromPos']) ? $directiveResult['deleteFromPos'] : 999999;

                    $res = substr($value, 0, $deleteFromPos).$partToInsert;
                }
                else $res = substr($value, 0, $partStartPos).$directiveResult; // substr($value, 0, $partStartPos) is the part before directive
                $currentPos = strlen($res); // points at the end of the closing tag
                $value = $res.substr($value, $posClosing); // substr($value, $posClosing);  is the reminder
                $level--;
            }
        } while(true);
        return $value;
    }

    /*
     * Formats value given as Price using Mage_Core_Model_Store::formatPrice() method
     * @see Mage_Core_Model_Store::formatPrice()
     * @param string $value Value to format
     * @return string Formatted value
     */
    public function modifierFormatPrice($value) {
        if(isset($this->_templateVars['order']) && is_object($this->_templateVars['order']) && $this->_templateVars['order']->getData('order_currency_code'))
            Mage::app()->getStore($this->getStoreId())->setCurrentCurrencyCode($this->_templateVars['order']->getData('order_currency_code'));
        if(is_numeric($value))
            $value = Mage::app()->getStore($this->getStoreId())->formatPrice($value, false);
        return $value;
    }

    /*
     * Formats value given as Numeric using PHP number_format function
     * @see number_format()
     * @param string $value Value to format
     * @return string Formatted value
     */
    public function modifierFormatDecimal($value)
    {
        if(is_numeric($value))
        {
            $params = func_get_args();
            array_shift($params);
            if(!count($params))
                $value = number_format($value);
            elseif(count($params) == 1)
                $value = number_format($value, $params[0]);
            elseif(count($params) == 3)
                $value = number_format($value, $params[0], $params[1], $params[2]);
        }
        return $value;
    }

    /*
     * Formats value given as DateTime
     * @see date()
     * @param string $value Value to format
     * @return string Formatted value
     */
    public function modifierFormatDateTime($value)
    {
        $params = func_get_args();
        array_shift($params);
        $formatStr = implode(':', $params);
        switch($formatStr) {
            case 'full':
                $_coreFormat = Mage_Core_Model_Locale::FORMAT_TYPE_FULL;
                break;
            case 'long':
                $_coreFormat = Mage_Core_Model_Locale::FORMAT_TYPE_LONG;
                break;
            case 'medium':
                $_coreFormat = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM;
                break;
            case 'short':
                $_coreFormat = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT;
                break;
            default:
                $_coreFormat = null;
                break;
        }
        if($_coreFormat) {
            //new format date processing
            if(is_numeric($value)) $value = date('d-m-Y H:i:s', $value);
            Mage::app()->getLocale()->emulate($this->getStoreId());
            $_dateStr = Mage::helper('core')->formatDate($value, $_coreFormat);
            Mage::app()->getLocale()->revert();
            return $_dateStr;
        }

        if(is_numeric($value)) return date($formatStr, $value);

        $date = date_parse($value);
        if( false===$date
        ||  (   isset($date['error_count'])
            &&  $date['error_count']
            )
        )   return $value;

        return date($formatStr, mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));
    }

    /*
     * Iterates object or array given
     * Usage: {{foreach var="" template="" alias=""}}
     * @param array $construction Source construction
     */
    public function foreachDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        
        if(!isset($params[self::OBJECT_TO_ITERATE]) || !$params[self::OBJECT_TO_ITERATE])
            return $this->reportError('No object to iterate specified');
        if(!isset($params[self::TEMPLATE_NAME]) || !$params[self::TEMPLATE_NAME])
            return $this->reportError('No template specified');

        $var = $params[self::OBJECT_TO_ITERATE];

        if(is_string($var)) $var = explode(',', $var);
        elseif(!is_object($var) && !is_array($var)) return $this->reportError('The object can not be iterated');

        if(!count($var)) return false;

        $paramName = (isset($params[self::ROW_ITEM_ALIAS_NAME]) && $params[self::ROW_ITEM_ALIAS_NAME]) ? $params[self::ROW_ITEM_ALIAS_NAME] : self::ROW_ITEM_NAME;

        $result='';
        $rowNumber = 0;
        foreach($var as $varItem)
        {
            $rowNumber++;
            $this->_templateVars[$paramName] = $varItem;
            $this->_templateVars[$paramName.self::ROW_NUMBER_SUFFIX] = $rowNumber;
            $result .= $this->includeDirective(array('', '', ' template="'.$params[self::TEMPLATE_NAME].'"'));
        }
        unset($this->_templateVars[$paramName]); // is it necessary?

        return $result;
    }

    /*
     * Returns link to product thumbnail
     * Usage: {{thumbnail source="" size=""}}
     * @param array $construction Source construction
     */
    public function thumbnailDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);

        if(isset($params[self::THUMBNAIL_SOURCE]) && $params[self::THUMBNAIL_SOURCE])
        {
            if(!$source = $this->_getVariable($params[self::THUMBNAIL_SOURCE], false))
                return $this->reportError('No such object in thumbnail source');

            if(is_object($source))
            {
                if($source instanceof Mage_Catalog_Model_Product) $product = $source;
                else return $this->reportError('The object specified is not a product to take a thumbnail from');
            }
            elseif(is_scalar($source))
            {
                $product = Mage::getModel('catalog/product')->load($source);
                if($source != $product->getId())
                    return $this->reportError('There is no product with ID='.$source.' to take a thumbnail from');
            }
            else return $this->reportError('Wrong object type');
        }
        else return $this->reportError('No source parameter is specified; there is nowhere to take the thumbnail from');

        if(!$thumbnail = $product->getThumbnail()) return 'The product has no thumbnail';

        $imgDimension = isset($params[self::THUMBNAIL_DIMENSION]) ? $params[self::THUMBNAIL_DIMENSION] : self::THUMBNAIL_DIMENSION_DEFAULT;

        $url = Mage::helper('catalog/image')
                    ->init($product, 'thumbnail', $thumbnail)
                    ->resize($imgDimension);

        return $url;
    }

    /*
     * Unquotes expression
     * @param string
     * @return string
     */
    private static function unQuote($s)
    {
        if($s && in_array($openingQuote = substr($s, 0, 1), array('\'', '"', self::FILTER_QUOTE_UNPROCESSED)))
        {
            $s = substr($s, 1);
            if($s && ($openingQuote == substr($s, -1, 1)))
                $s = substr($s, 0, strlen($s)-1);
        }
        return stripslashes($s);
    }

    /*
     * Calculates expression
     * Usage: {{expr var operation var}}
     * @param array $construction Source construction
     */
    public function exprDirective($construction)
    {
        $parts = explode(' ', trim($construction[2]), 3);
        if(3 == count($parts))
        {
            $operand1 = self::unQuote(trim($parts[0]));
            $operation = trim($parts[1]);
            $operand2 = self::unQuote(trim($parts[2]));
        }
        else return $this->reportError('Operand(s) or operation sign missed!');

        $result = '';
        switch($operation)
        {
            case '-':
                if(is_numeric($operand1) && is_numeric($operand2)) $result = $operand1 - $operand2;
                else return $this->reportError('You cannot subtract strings!');
                break;

            case '+':
                if(is_numeric($operand1) && is_numeric($operand2)) $result = $operand1 + $operand2;
                else $result = $operand1.$operand2;
                break;

            case '*':
                if(is_numeric($operand1) && is_numeric($operand2)) $result = $operand1 * $operand2;
                else return $this->reportError('You cannot multiply strings!');
                break;

            case '/':
                if(is_numeric($operand1) && is_numeric($operand2))
                    if($operand2) $result = $operand1 / $operand2;
                    else return $this->reportError('Division by zero!');
                else return $this->reportError('You cannot divide strings!');
                break;
            case '%':
                if(is_numeric($operand1) && is_numeric($operand2))
                    if($operand2) $result = $operand1 % $operand2;
                    else return $this->reportError('Division by zero!');
                else return $this->reportError('You cannot divide strings!');
                break;
            case 'round':
                if(is_numeric($operand1) && is_numeric($operand2))
                    $result = round($operand1, $operand2);
                else return $this->reportError('You cannot round strings!');
                break;
            default:
                return $this->reportError('Unknown operation!');
        }
        return $result;
    }

    /*
     * Evaluates variable as expression
     * Usage: {{eval name}}
     * @param array $construction Source construction
     */
    public function evalDirective($construction)
    {
        if(($name = trim($construction[2])) && isset($this->_templateVars[$name]))
            return $this->filter($this->_templateVars[$name]);
    }

    /*
     * Unsets varuable
     * Usage: {{set var value}} {{set var 'long value containing spaces'}}
     * @param array $construction Source construction
     */
    public function unsetDirective($construction)
    {
        if($name = trim($construction[2]))
            unset($this->_templateVars[$name]);
    }

    /*
     * Assigns variable a value
     * Usage: {{set var value}} {{set var 'long value containing spaces'}}
     * @param array $construction Source construction
     */
    public function setDirective($construction)
    {
        $parts = explode(' ', trim($construction[2]), 2);
        if(!count($parts)) return;
        if(!$name = trim($parts[0])) return;
        if(1 == count($parts))
        {
            $this->_templateVars[$name] = '';
            return;
        }
        else $value = $parts[1];

        if($value && in_array($openingQuote = substr($value, 0, 1), array('\'', '"', self::FILTER_QUOTE_UNPROCESSED)))
        {
            $value = substr($value, 1);
            if($value && ($openingQuote == substr($value, -1, 1)))
                $value = substr($value, 0, strlen($value)-1);
        }
        $this->_templateVars[$name] = stripslashes($value);
    }
}
