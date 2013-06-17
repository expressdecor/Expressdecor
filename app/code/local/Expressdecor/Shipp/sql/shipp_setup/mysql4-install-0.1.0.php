<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('sales/quote_address'), 'signature_required', 'int(11)');

$installer->getConnection()
->addColumn($installer->getTable('sales/order_address'), 'signature_required', 'int(11)');

$installer->endSetup();