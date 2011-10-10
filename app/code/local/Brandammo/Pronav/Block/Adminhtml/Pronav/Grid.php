<?php
class Brandammo_Pronav_Block_Adminhtml_Pronav_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('pronavGrid');
      $this->setDefaultSort('pronav_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('pronav/pronav')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('pronav_id', array(
          'header'    => Mage::helper('pronav')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'pronav_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('pronav')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn('url_key', array(
          'header'    => Mage::helper('pronav')->__('URL Key'),
          'align'     =>'left',
          'index'     => 'url_key',
      ));
      
      $this->addColumn('index', array(
          'header'    => Mage::helper('pronav')->__('Item Index'),
          'align'     =>'left',
          'index'     => 'index',
      ));
      
      $this->addColumn('li_css_id', array(
          'header'    => Mage::helper('pronav')->__('Item CSS ID'),
          'align'     =>'left',
          'index'     => 'li_css_id',
      ));
      
      $this->addColumn('li_css_class', array(
          'header'    => Mage::helper('pronav')->__('Item CSS Class'),
          'align'     =>'left',
          'index'     => 'li_css_class',
      ));
      
      $this->addColumn('css_id', array(
          'header'    => Mage::helper('pronav')->__('Link CSS ID'),
          'align'     =>'left',
          'index'     => 'css_id',
      ));
      
      $this->addColumn('css_class', array(
          'header'    => Mage::helper('pronav')->__('Link CSS Class'),
          'align'     =>'left',
          'index'     => 'css_class',
      ));
      
      $this->addColumn('store_id', array(
          'header'    => Mage::helper('pronav')->__('Store ID'),
          'align'     =>'left',
          'index'     => 'store_id',
      ));
      
      
      $this->addColumn('static_block', array(
          'header'    => Mage::helper('pronav')->__('Static Block'),
          'align'     =>'left',
          'index'     => 'static_block',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('pronav')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('pronav')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('pronav')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('pronav')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('pronav')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('pronav_id');
        $this->getMassactionBlock()->setFormFieldName('pronav');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('pronav')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('pronav')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('pronav/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('pronav')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('pronav')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}