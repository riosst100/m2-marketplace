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
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;

class ValidateCartObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var CustomerCart
     */
    private $_cart;

    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CookieManager $cookieManager
     * @param CustomerCart $cart
     * @param Data $helperData
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CookieManager $cookieManager,
        CustomerCart $cart,
        Data $helperData
    ) {
        $this->cookieManager = $cookieManager;
        $this->_helperData = $helperData;
        $this->_messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->_cart = $cart;
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

        $ageCookie = $this->cookieManager->getCookie('Lof_AgeVerification');
        $controller = $observer->getControllerAction();

        foreach ($this->_cart->getQuote()->getAllVisibleItems() as $quoteItem) {
            $product = $quoteItem->getProduct();
            $result = $this->_helperData->isPreventPurchaseProduct($product);
            $ageVerify = $this->_helperData->getVerifyAge($product);
            if ($result && !$this->_helperData->isRequiredLoginAndValidAge($product) && ($ageCookie < $ageVerify)) {
                $this->_messageManager->addWarningMessage(__(
                    'You cannot add %1 to your shopping cart, %1 in the list of age restricted products.',
                    $quoteItem->getName()
                ));
                $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
            }
        }

        return $this;
    }
}
