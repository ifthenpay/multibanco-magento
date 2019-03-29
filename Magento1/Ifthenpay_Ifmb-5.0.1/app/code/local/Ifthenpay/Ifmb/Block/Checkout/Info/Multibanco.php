<?php
/**
 * Display the simple and extended part payment monthly fee information
 * if activated in admin. The different display conditions are used in the
 * custom $_template
 */
class Ifthenpay_Ifmb_Block_Checkout_Success_Info_Multibanco
    extends Mage_Core_Block_Template
{
    protected $_template = 'ifmb/checkout/success.phtml';
	
	public function getMbInfo()
	{
		return "OIOI";
	}
	
}