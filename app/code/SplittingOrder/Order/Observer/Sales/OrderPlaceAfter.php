<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace SplittingOrder\Order\Observer\Sales;

use Exception;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \SplittingOrder\Order\Helper\Data $helper
    ) {
        $this->cartRepository = $cartRepository;
        $this->helpr = $helper;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $observer->getEvent()->getOrder()->getQuoteId();
        $quote = $this->cartRepository->get($quoteId);
     

        if (empty($quote->getParentId())) {
            $items = [];
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();
                $productBrand = ($item->getProduct()->getData('brand'))? $item->getProduct()->getData('brand') : 0 ;
                $productQty = $item->getQtyOrdered();
                $productSku = $item->getProduct()->getData('sku');
                $items[] = [
                    'id' => $item->getProduct()->getId(),
                    'brand' => $productBrand,
                    'qty' => $productQty,
                    'sku' => $productSku,
                ];
            }
            $key = 'brand';
            $return = array();
            foreach ($items as $v) {
                $return[$v[$key]][] = $v;
            }
            foreach($return as $array) {
                try {
                    $this->helpr->createOrder($order, $array);
                } catch (Exception $ex) {
                    //$logger->info($ex->getMessage());
                }
            }
        }
    }
}
