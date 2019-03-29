<?php
$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `ifthenpay_ifmb_callback` (`id` int(11) NOT NULL AUTO_INCREMENT, `order_id` int(22) NOT NULL, `entidade` int(11) NOT NULL, `referencia` varchar(11) NOT NULL, `referencia_sem_espacos` varchar(9) NOT NULL, `valor` decimal(10,2) NOT NULL, `check_mb` varchar(50) DEFAULT NULL, PRIMARY KEY (`id`))");

$installer->run("CREATE TABLE IF NOT EXISTS `ifthenpay_ifmb_config` (`id` int(11) NOT NULL AUTO_INCREMENT, `antiphishing` varchar(50) DEFAULT NULL, PRIMARY KEY (`id`))");

$installer->addAttribute('order_payment', 'ifthenpay_entidade', array('type'=>'varchar'));
$installer->addAttribute('order_payment', 'ifthenpay_referencia', array('type'=>'varchar'));
$installer->addAttribute('order_payment', 'ifthenpay_montante', array('type'=>'varchar'));

$installer->addAttribute('quote_payment', 'ifthenpay_entidade', array('type'=>'varchar'));
$installer->addAttribute('quote_payment', 'ifthenpay_referencia', array('type'=>'varchar'));
$installer->addAttribute('quote_payment', 'ifthenpay_montante', array('type'=>'varchar'));
$installer->endSetup();

if (Mage::getVersion() >= 1.1) {
    $installer->startSetup();    
	$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_payment'), 'ifthenpay_entidade', 'VARCHAR(255) NOT NULL');
	$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_payment'), 'ifthenpay_referencia', 'VARCHAR(255) NOT NULL');
	$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_payment'), 'ifthenpay_montante', 'VARCHAR(255) NOT NULL');
    $installer->endSetup();
}