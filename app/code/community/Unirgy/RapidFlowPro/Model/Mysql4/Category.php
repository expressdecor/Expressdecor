<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
 * Special attributes:
 *
 * EXPORT ONLY:
 * category.path
 * category.name
 *
 * const.value
 *
 * product.store
 *
 * IMPORT/EXPORT:
 * stock.use_config_manage_stock
 * stock.manage_stock
 * stock.is_in_stock
 * stock.qty
 *
 * product.attribute_set
 * product.type
 * product.websites
 */

class Unirgy_RapidFlowPro_Model_Mysql4_Category
    extends Unirgy_RapidFlowPro_Model_Mysql4_Category_Abstract
{
    /**
     * actual export of formated product data to file
     */
    public function export()
    {
        $tune = Mage::getStoreConfig('urapidflow/finetune');
        if (!empty($tune['export_page_size']) && $tune['export_page_size']>0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');

        $this->_profile->activity('Preparing data');

        $this->_prepareAttributes($profile->getAttributeCodes());
        $this->_prepareSystemAttributes();

        $storeId = $profile->getStoreId();
        $this->_storeId = $storeId;

        $pUrl = Mage::getSingleton('catalog/url');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl('web');
        $mediaUrl = Mage::app()->getStore($storeId)->getBaseUrl('media');
        $mediaDir = Mage::getBaseDir('media');
        $imgModel = Mage::getModel('catalog/product_image');

        // main product table
        $table = $this->_t('catalog/category');
        // start select
        $upAttrId = $this->_attr('url_path', 'attribute_id');
        $select = $this->_read->select()->from(array('e'=>$table))
            ->join(array('up'=>$table.'_varchar'), "up.entity_id=e.entity_id and up.attribute_id={$upAttrId} and up.value<>'' and up.value is not null and up.store_id='{$storeId}'", array('url_path'=>'value'))
            ->order('path')
        ;
        $this->_attrJoined = array($upAttrId);

        $columns = $profile->getColumns();

        $defaultSeparator = $profile->getData('options/csv/multivalue_separator');
        if (!$defaultSeparator) {
            $defaultSeparator = '; ';
        }

        $this->_fields = array();
        $this->_fieldsCodes = array();
        if ($columns) {
            foreach ($columns as $i=>&$f) {
                if (empty($f['title'])) {
                    $f['title'] = $f['field'];
                }
                if (!empty($f['default']) && is_array($f['default'])) {
                    $f['default'] = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $f['default']);
                }
                $this->_fields[$f['title']] = $f;
                $this->_fieldsCodes[$f['field']] = true;
            }
            unset($f);
        } else {
            foreach ($this->_attributesByCode as $k=>$a) {
                $this->_fields[$k] = array('field' => $k, 'title' => $k);
                $this->_fieldsCodes[$k] = true;
            }
        }

        $condProdIds = $profile->getConditionsProductIds();
        if (is_array($condProdIds)) {
            $select->where('entity_id in (?)', $condProdIds);
        }

        $countSelect = clone $select;
        $countSelect->reset(Zend_Db_Select::FROM)->reset(Zend_Db_Select::COLUMNS)->from(array('e'=>$table), array('count(*)'));
        $count = $this->_read->fetchOne($countSelect);
        unset($countSelect);
        $profile->setRowsFound($count)->setStartedAt(now())
            ->sync(true, array('rows_found', 'started_at'), false);
        $profile->activity('Exporting');
#memory_get_usage();
        // open export file
        $profile->ioOpenWrite();

        // write headers to the file
        $headers = array();
        foreach ($this->_fields as $k=>$f) {
            $headers[] = !empty($f['alias']) ? $f['alias'] : $k;
        }
        $profile->ioWriteHeader($headers);

        // batch size
        // repeat until data available
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            // set limit for current page
            $select->limitPage($page+1, $this->_pageRowCount);
            // retrieve product entity data and attributes in filters
            $rows = $this->_read->fetchAll($select);
            if (!$rows) {
                break;
            }
            // fill $this->_entities associated by product id
            $this->_entities = array();
            foreach ($rows as $p) {
                $this->_entities[$p['entity_id']][0] = $p;
            }
            unset($rows);

            $this->_entityIds = array_keys($this->_entities);
            $this->_attrValueIds = array();
            $this->_attrValuesFetched = array();
            $this->_fetchAttributeValues($storeId, true);
            memory_get_usage(true);

            // format product data as needed
            foreach ($this->_entities as $id=>$p) {
                $csvRow = array();
                $value = null;
                foreach ($this->_fields as $k=>$f) {
                    $attr = $f['field'];
                    $inputType = $this->_attr($attr, 'frontend_input');

                    // retrieve correct value for current row and field
                    if (($v = $this->_attr($attr, 'force_value'))) {
                        $value = $v;
                    } elseif (!empty($this->_fieldAttributes[$attr])) {
                        $a = $this->_fieldAttributes[$attr];
                        $value = isset($p[$storeId][$a]) ? $p[$storeId][$a] : (isset($p[0][$a]) ? $p[0][$a] : null);
                    } else {
                        $value = isset($p[$storeId][$attr]) ? $p[$storeId][$attr] : (isset($p[0][$attr]) ? $p[0][$attr] : null);
                    }

                    // replace raw numeric values with source option labels
                    if (($inputType=='select' || $inputType=='multiselect') && ($options = $this->_attr($attr, 'options'))) {

                        if (!is_array($value)) {
                            $value = explode(',', $value);
                        }
                        foreach ($value as &$v) {
                            if ($v==='') {
                                continue;
                            }
                            if (!isset($options[$v])) {
                                $profile->addValue('num_warnings');
                                $logger->warning($this->__("Unknown option '%s' for category '%s' attribute '%s'", $v, $p[0]['url_path'], $attr));
                                continue;
                            }
                            $v = $options[$v];
                        }
                        unset($v);
                    }

                    // combine multiselect values
                    if (is_array($value)) {
                        $value = join(!empty($f['separator']) ? $f['separator'] : $defaultSeparator, $value);
                    }

                    // process special cases of loaded attributes
                    switch ($attr) {
                    // product url
                    case 'url_path':
                        if (!empty($f['format']) && $f['format']=='url') {
                            $value = $baseUrl.$value;
                        }
                        break;

                    case 'const.value':
                        $value = isset($f['default']) ? $f['default'] : '';
                        break;
                    }

                    switch ($this->_attr($attr, 'backend_type')) {
                    case 'decimal':
                        if (!is_null($value) && !empty($f['format'])) {
                            $value =  sprintf($f['format'], $value);
                        }
                        break;

                    case 'datetime':
                        if (!is_empty_date($value)) {
                            $value = date(!empty($f['format']) ? $f['format'] : 'Y-m-d H:i:s', strtotime($value));
                        }
                        break;
                    }

                    switch ($this->_attr($attr, 'frontend_input')) {
                    case 'media_image':
                        if ($value=='no_selection') {
                            $value = '';
                        }
                        if (!empty($f['format']) && $f['format']=='url' && !empty($value)) {
                            try {
                                $path = $imgModel->setBaseFile($value)->getBaseFile();
                                $path = str_replace($mediaDir . DS, "", $path);
                                $value = $mediaUrl . str_replace(DS, '/', $path);
                            } catch (Exception $e) {
                                $value = '';
                            }
                        }
                        break;
                    }

                    if ((is_null($value) || $value==='') && !empty($f['default'])) {
                        $value = $f['default'];
                    }

                    $csvRow[] = $value;
                }

                $csvRow = $this->_convertEncoding($csvRow);
                $profile->ioWrite($csvRow);
                $profile->addValue('rows_processed')->addValue('rows_success');
            } // foreach ($this->_entities as $id=>&$p)

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(now())->sync();

            $this->_checkLock();

            // stop repeating if this is the last page
            if (sizeof($this->_entities)<$this->_pageRowCount) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        } // while (true)
        $profile->ioClose();
    }

    public function import()
    {
        $tune = Mage::getStoreConfig('urapidflow/finetune');
        if (!empty($tune['import_page_size']) && $tune['import_page_size']>0) {
            $this->_pageRowCount = (int)$tune['import_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $dryRun = $profile->getData('options/import/dryrun');

        if (Mage::app()->isSingleStoreMode()) {
            $storeId = 0;
        } else {
            $storeId = $profile->getStoreId();
        }
        $this->_storeId = $storeId;
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_attributeSetId = $this->_getEntityType($this->_entityType, 'default_attribute_set_id');

        $useTransactions = $profile->getUseTransactions();

        $this->_profile->activity($this->__('Fetching number of rows'));

        $profile->ioOpenRead();
        $count = -1;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(now())
            ->sync(true, array('rows_found', 'started_at'), false);
        $profile->activity($this->__('Preparing data'));
        $profile->ioSeekReset();

        $this->_importPrepareColumns();
        $this->_prepareAttributes(array_keys($this->_fieldsCodes));
        $this->_prepareSystemAttributes();
        $this->_importValidateColumns();
        $this->_prepareCategories();

        $eventVars = array(
            'profile' => &$this->_profile,
            'old_data' => &$this->_entities,
            'new_data' => &$this->_newData,
            'url_paths' => &$this->_urlPaths,
            'attr_value_ids' => &$this->_attrValueIds,
            'valid' => &$this->_valid,
            'insert_entity' => &$this->_insertEntity,
            'change_attr' => &$this->_changeAttr,
        );

        $this->_profile->activity($this->__('Importing'));

        $this->_isLastPage = false;

        $i1 = $this->_pageRowCount;
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 2+$page*$this->_pageRowCount;
            try {
                $this->_checkLock();

                if ($useTransactions && !$dryRun) {
                    $this->_write->beginTransaction();
                }

                $this->_importResetPageData();
                $this->_importFetchNewData();
                $this->_importFetchOldData();
                $this->_fetchAttributeValues($storeId, true);
                $this->_importProcessNewData();

                $this->_checkLock();

                Mage::dispatchEvent('urapidflow_category_import_after_fetch', array('vars'=>$eventVars));
                $this->_importValidateNewData();
                Mage::dispatchEvent('urapidflow_category_import_after_validate', array('vars'=>$eventVars));
                $this->_importProcessDataDiff();
                Mage::dispatchEvent('urapidflow_category_import_after_diff', array('vars'=>$eventVars));

                if (!$dryRun) {
                    $this->_importSaveEntities();
                    $this->_importGenerateAttributeValues();

                    $this->_importSaveAttributeValues();
                }

                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                    ->setSnapshotAt(now())->sync();

                if ($useTransactions && !$dryRun) {
                    $this->_write->commit();
                }
            } catch (Exception $e) {
                if ($useTransactions && !$dryRun) {
                    $this->_write->rollback();
                }
#print_r($e);
                throw $e;
            }
            if ($this->_isLastPage) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }

        $profile->ioClose();
    }

    public function fetchSystemAttributes()
    {
        $this->_entityTypeId = $this->_getEntityType($this->_entityType, 'entity_type_id');
        $this->_prepareSystemAttributes();
        return $this->_attributesByCode;
    }

    protected function _cleanupValues($attr, &$oldValue, &$newValue)
    {
        // trying to work around PHP's weakly typed mess...
        if (!empty($attr['frontend_input'])) {
            switch ($attr['frontend_input']) {
            case 'media_image':
                if (!is_null($oldValue)) {
                    if ($oldValue=='no_selection') {
                        $oldValue = '';
                    }
                }
                break;

            case 'multiselect':
                if (is_null($oldValue)) {
                    $oldValue = array();
                }
                if ($newValue==='') {
                    $newValue = array();
                }
                break;
            }
        }
        if (!empty($attr['backend_type'])) {
            switch ($attr['backend_type']) {
            case 'int':
                if (!is_null($newValue) && !is_array($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                        $newValue = $this->_locale->getNumber($newValue);
                        if ($newValue != (int)$newValue) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid int value"));
                        } else {
                            $newValue = (int)$newValue;
                        }
                    }
                }
                if (!is_null($oldValue) && !is_array($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    } else {
                        $oldValue = (int)$oldValue;
                    }
                }
                break;

            case 'decimal':
                if (!is_null($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                        $newValue = $this->_locale->getNumber($newValue);
                        if (!is_numeric($newValue)) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid decimal value"));
                        } else {
                            $newValue *= 1.0;
                        }
                    }
                }
                if (!is_null($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    } else {
                        $oldValue *= 1.0;
                    }
                }
                break;

            case 'datetime':
                if (!is_null($newValue)) {
                    if ($newValue==='') {
                        $newValue = null;
                    } else {
                        $newValue = date("Y-m-d H:i:s", strtotime($newValue));
                        if (!$newValue) {
                            $this->_profile->addValue('num_errors');
                            $this->_profile->getLogger()->error($this->__("Invalid datetime value"));
                        }
                    }
                }
                if (!is_null($oldValue)) {
                    if ($oldValue==='') {
                        $oldValue = null;
                    }
                }
                break;

            case 'varchar': case 'text':
                if ($oldValue==='' && is_null($newValue)) {
                    $newValue = '';
                } elseif (is_null($oldValue) && $newValue==='') {
                    $newValue = null;
                } elseif (is_numeric($newValue)) {
                    $newValue = (string)$newValue;
                }
                break;
            }
        }
    }


    /**
     * retrieve attr record by id or code, with optional record field and value
     */
    protected function _attr($attribute, $field=null, $value=null)
    {
        if (is_numeric($attribute)) {
            $attr = $this->_attributesById[$attribute];
        } elseif (isset($this->_attributesByCode[$attribute])) {
            $attr = $this->_attributesByCode[$attribute];
        } else {
            return false;
        }
        if (!is_null($field) && !is_null($value)) {
            if ($field=='options_bytext') {
                $value = strtolower($value);
            }
            return isset($attr[$field][$value]) ? $attr[$field][$value] : false;
        } elseif (!is_null($field)) {
            return isset($attr[$field]) ? $attr[$field] : false;
        } else {
            return $attr;
        }
    }
}