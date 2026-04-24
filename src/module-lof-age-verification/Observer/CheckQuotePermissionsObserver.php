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
 * @package    Lof_AgeVerification
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\AgeVerification\Observer;

use Lof\AgeVerification\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;

class CheckQuotePermissionsObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var CookieManager
     */
    protected $cookieManager;

    /**
     * SalesQuoteItemQtySetAfterObserver constructor.
     * @param Data $helperData
     * @param CookieManager $cookieManager
     */
    public function __construct(
        Data $helperData,
        CookieManager $cookieManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        if (!$this->_helperData->isEnablePurchaseConditions()) {
            return $this;
        }

        $quote = $observer->getEvent()->getCart()->getQuote();
        $allQuoteItems = $quote->getAllItems();
        $ageCookie = $this->cookieManager->getCookie('Lof_AgeVerification');

        foreach ($allQuoteItems as $quoteItem) {
            $product = $quoteItem->getProduct();
            $result = $this->_helperData->isPreventPurchaseProduct($product);
            $ageVerify = $this->_helperData->getVerifyAge($product);
            if ($result && !$this->_helperData->isRequiredLoginAndValidAge($product) && ($ageCookie < $ageVerify)) {
                $quoteItem->setDisableAddToCart(true);
                if ($quoteItem->getDisableAddToCart() && !$quoteItem->isDeleted()) {
                    $quote->removeItem($quoteItem->getQuoteId());
                    $quote->deleteItem($quoteItem);
                    $quote->setHasError(
                        true
                    )->addMessage(
                        __(
                            'You cannot add %1 to your shopping cart, %1 in the list of age restricted products.',
                            $quoteItem->getName()
                        )
                    );
                }
            }
        }

        return $this;
    }
}
