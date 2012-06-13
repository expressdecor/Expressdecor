<?php

class Mage_Banners_Block_Adminhtml_Banners_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('bannersGrid');
      $this->setDefaultSort('banners_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('banners/banners')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('banners_id', array(
          'header'    => Mage::helper('banners')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'banners_id',
      ));

	 $this->addColumn('filethumbgrid', array(
          'header'    => Mage::helper('banners')->__('Thumbnail'),
          'align'     =>'center',
          'index'     => 'filethumbgrid',
		  'type'      => 'text',
		  'width'     => '150px',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('banners')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
	  
	  	  $this->addColumn('sort_order', array(
          'header'    => Mage::helper('banners')->__('Sort Order'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'sort_order',
      ));
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('banners')->__('Status'),
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
                'header'    =>  Mage::helper('banners')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('banners')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('banners')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('banners')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banners_id');
        $this->getMassactionBlock()->setFormFieldName('banners');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('banners')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('banners')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('banners/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('banners')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('banners')->__('Status'),
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