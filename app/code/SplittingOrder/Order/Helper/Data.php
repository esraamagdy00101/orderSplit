<?php
namespace SplittingOrder\Order\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
     /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Magento\Catalog\Model\Product $product
    * @param Magento\Framework\Data\Form\FormKey $formKey $formkey,
    * @param Magento\Quote\Model\Quote $newquote,
    * @param Magento\Customer\Model\CustomerFactory $customerFactory,
    * @param Magento\Sales\Model\Service\OrderService $orderService,
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $newquote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository  
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->newquote = $newquote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }
 
    /**
     * Create Order On Your Store
     * 
     * @return array
     * 
    */
    public function createOrder($mainOrder ,$orderData) {
        
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($mainOrder->getCustomerEmail());
       
        $newquote=$this->newquote->create();
        $newquote->setStore($store);
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $newquote->setCurrency();
        $newquote->assignCustomer($customer);
 
        //add items in quote
        foreach($orderData as $brandArray){
            $product = $this->productRepository->get($brandArray["sku"]);
            $params = array(
                'form_key' => $this->_formkey->getFormKey(),
                'product' => $brandArray["sku"], 
                'qty'   => $brandArray["qty"],                
            );
            $newquote->addProduct(
                $product,
                intval($brandArray["qty"])
            );
            $newquote->save();
        }
 
        //Set Address to quote
        $newquote->getBillingAddress()->addData((array)$mainOrder->getShippingAddress());
        $newquote->getShippingAddress()->addData((array)$mainOrder->getShippingAddress());
 
        // Collect Rates and Set Shipping & Payment Method
        $payment = $mainOrder->getPayment();
        $method = $payment->getMethod();
 
        $shippingAddress=$newquote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($mainOrder->getShippingMethod()); 
        $newquote->setShippingMethod($mainOrder->getShippingMethod());
        $newquote->setInventoryProcessed(false);
        $newquote->setparentId($mainOrder->getIncrementId()); 
        $newquote->save();
 
        $newquote->getPayment()->importData(['method' => $method]);
 
        // Collect Totals & Save Quote
        $newquote->collectTotals()->save();
        
 
        // Create Order From Quote
        $order = $this->quoteManagement->submit($newquote);
        
        $order->setEmailSent(0);
        $order->setParentId($mainOrder->getIncrementId());
        $order->save();
        
    }
}
 
?>