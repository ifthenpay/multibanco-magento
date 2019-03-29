<?php
class Ifthenpay_Ifmb_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
    {
		$mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('ifmb/form/mark.phtml');
		
        $this->setTemplate('ifmb/form/form.phtml')
			 ->setMethodLabelAfterHtml($mark->toHtml())
		;
		parent::_construct();
    }
}