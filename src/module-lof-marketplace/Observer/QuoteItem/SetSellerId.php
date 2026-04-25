<?php

/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\MarketPlace\Observer\QuoteItem;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class SetSellerId implements ObserverInterface
{
	/**
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getItem();
        
        $data = $this->registry->registry('preorder_graphql_input');
        if ($data) {
        	$quoteItem->setData('lof_seller_id', $data['lof_seller_id']);
        } elseif ($quoteItem->getLofSellerId()) {
        	$quoteItem->setData('lof_seller_id', $quoteItem->getLofSellerId());
    	}else {
        	$quoteItem->setData('lof_seller_id', $quoteItem->getProduct()->getSellerId());
        }
    }
}
