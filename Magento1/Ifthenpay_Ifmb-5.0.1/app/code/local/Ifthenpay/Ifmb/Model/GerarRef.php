<?php
class Ifthenpay_Ifmb_Model_GerarRef extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'ifmb';
    
	protected $_paymentMethod = 'ifmb';
    protected $_formBlockType = 'ifmb/form';
    protected $_infoBlockType = 'ifmb/info';
    protected $_allowCurrencyCode = array('EUR');
	
	
	protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = true;    
	protected $_order;
    
    public function getMensagem()
    {
    	return $this->getConfigData('mensagem');
    }
	
	public function getOrder2()
    {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }
	
	private function _getOrderId()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }
	
	public function assignData($data)
	{
		$eav_entity_type	= Mage::getModel('eav/entity_type')->loadByCode('order');
		$eav_entity_store	= Mage::getModel('eav/entity_store')->loadByEntityStore($eav_entity_type->getEntityTypeId(), $this->getQuote()->getStoreId());

		
		$paymentInfo = $this->getInfoInstance();
         if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
             $order_value = $paymentInfo->getOrder()->getGrandTotal();
         } else {
             $order_value = $paymentInfo->getQuote()->getGrandTotal();
         }
		 $order_id_full = $this->_getOrderId();
		 //$order_id_full = $eav_entity_store->getIncrementLastId() + $eav_entity_type->getIncrementPerStore();
		$order_id    = substr($order_id_full, -4, 4);
		
		/*Mage::log("Increment Last Id");
		if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
		Mage::log("oi");
		Mage::log(print_r($data,true));
        }else{
		Mage::log("oi2");
		Mage::log($this->_getOrderId());
		}
		Mage::log("Increment Last Id 2");
		Mage::log(print_r($eav_entity_type,true));
		Mage::log("Increment Last Id 3");
		Mage::log(print_r($eav_entity_store,true));
		
		$orders = Mage::getModel('sales/order')->getCollection() 
          ->setOrder('increment_id','DESC')
          ->setPageSize(1)
          ->setCurPage(1);

$orderId = $orders->getFirstItem()->getEntityId();
		Mage::log("Increment Last Id 4");
		Mage::log($orderId);
		 
		//$order_value = number_format($this -> getQuote() -> getGrandTotal(),2,'.','');
		$order_value = number_format($order_value,2,'.','');*/
		
		$ent_id      = $this->getConfigData('entidade');
		$subent_id   = $this->getConfigData('subentidade');

		$chk_str = sprintf('%05u%03u%04u%08u', $ent_id, $subent_id, $order_id, round($order_value*100));
		
		$chk_val = '';
		
		$chk_array = array(3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38, 89, 17, 73, 51);

		for ($i = 0; $i < 20; $i++)
		{
			$chk_int = substr($chk_str, 19-$i, 1);
			$chk_val += ($chk_int%10)*$chk_array[$i];
		}

		$chk_val %= 97;

		$chk_digits = sprintf('%02u', 98-$chk_val);

		$ifmb_save_conn = Mage::getSingleton('core/resource')->getConnection('core_write');
		$ifmb_save_conn->beginTransaction();
		$fields = array();
		$fields['order_id'] = $order_id_full;
		$fields['entidade'] = $ent_id;
		$fields['referencia'] = $subent_id . ' ' . substr($chk_str, 8, 3) . ' ' . substr($chk_str, 11, 1) . $chk_digits;
		$fields['referencia_sem_espacos'] = $subent_id . substr($chk_str, 8, 3) . substr($chk_str, 11, 1) . $chk_digits;
		$fields['valor'] = $order_value;
		$ifmb_save_conn->insert('ifthenpay_ifmb_callback',$fields);
		$ifmb_save_conn->commit();
		
		$info = $this->getInfoInstance();
		$info->setIfthenpayEntidade($ent_id)
			->setIfthenpayReferencia($subent_id . ' ' . substr($chk_str, 8, 3) . ' ' . substr($chk_str, 11, 1) . $chk_digits)
			->setIfthenpayMontante($order_value);

		return $this;
	}
    
    public function validate()
    {
        parent::validate();
		
		$paymentInfo = $this->getInfoInstance();
		
         if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
             $order_value = $paymentInfo->getOrder()->getGrandTotal();
         } else {
             $order_value = $paymentInfo->getQuote()->getGrandTotal();
         }
		

		$order_value = number_format($order_value,2,'.','');


		if ($order_value < 1) {
            Mage::throwException(Mage::helper('ifmb')->__('Impossível gerar referência MB para valores inferiores a 1 Euro.'));
        }
        if ($order_value >= 999999.99) {
            Mage::throwException(Mage::helper('ifmb')->__('O valor excede o limite para pagamento na rede MB'));
        }
        $currency_code = $this->_getCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('ifmb')->__('A moeda selecionada ('.$currency_code.') não é compatível com o Pagamento MB'));
        }
        return $this;
    }
    
	public function getQuote()
    {
		
        if (empty($this->_quote)) {            
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
	
	public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->getCheckout()->getOrder();
        }
        return $this->_order;
    }
	

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }
	
	

    /**
     * Currency code getter
     *
     * @return string
     */
    private function _getCurrencyCode()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
        return $info->getOrder()->getBaseCurrencyCode();
        } else {
        return $info->getQuote()->getBaseCurrencyCode();
        }
    }
    
	public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }else{
			$this->_checkout = Mage::getSingleton('adminhtml/session_quote');
		}
		
        return $this->_checkout;
    }

}