<?php
/**
 * Ifthenpay_Multibanco module dependency
 *
 * @category    Gateway Payment
 * @package     Ifthenpay_Multibanco
 * @author      Manuel Rocha
 * @copyright   Manuel Rocha (http://www.manuelrocha.biz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ifthenpay\Multibanco\Block\Checkout\Onepage\Success;

class MultibancoInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $_checkoutSession;

    public $_genRef = null;
    public $_ifthenpayMbHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_customerSession;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    public $_agreementFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Ifthenpay\Multibanco\Helper\GerarReferencias $genRef,
        \Ifthenpay\Multibanco\Helper\Data $ifthenpayMbHelper,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_agreementFactory = $agreementFactory;

        $this->_genRef = $genRef;
        $this->_ifthenpayMbHelper = $ifthenpayMbHelper;

        parent::__construct($context, $data);
    }

    public function isIfthenpayMultibancoPayment()
    {
        if ($this->getOrder()->getPayment()) {
            if ($this->getOrder()->getPayment()->getMethod() == 'ifthenpay_multibanco') {
                return true;
            }
        }
        return false;
    }

    public function getEntidade()
    {
        $orderId = $this->getOrder()->getRealOrderId();
        $ifthenpayPaymentData = $this->_ifthenpayMbHelper->getIfthenpayPaymentByOrderId($orderId);
        $entity = $ifthenpayPaymentData['entity'];

        return $entity;
    }

    public function getReferencia($comEspacos = false)
    {
        $orderId = $this->getOrder()->getRealOrderId();
        $ifthenpayPaymentData = $this->_ifthenpayMbHelper->getIfthenpayPaymentByOrderId($orderId);
        $reference = $ifthenpayPaymentData['reference'];
        $reference = substr($reference, 0, 3) . " " . substr($reference, 3, 3) . " " . substr($reference, 6, 3);

        return $reference;
    }

    public function getValor()
    {
        return $this->getOrder()->formatPrice($this->getOrder()->getGrandTotal());
    }

    public function getOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        return $order;
    }
}
