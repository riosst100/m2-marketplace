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

namespace Lof\AgeVerification\Plugin\Block\Product\View\Type;

use Magento\Framework\Exception\NoSuchEntityException;

class Configurable
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Lof\AgeVerification\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * Configurable constructor.
     * @param \Lof\AgeVerification\Helper\Data $helperData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Lof\AgeVerification\Helper\Data $helperData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->helperData = $helperData;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param callable $proceed
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        callable $proceed
    ): string {
        if (!$this->isNeedEncoder()) {
            return $proceed();
        }
        $config = $this->jsonDecoder->decode($proceed(), true);
        $config['popupConfigData'] = $this->getPopupConfig($subject);
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return bool
     */
    private function isNeedEncoder()
    {
        return $this->helperData->isEnabled()
            && $this->checkCookie();
    }

    /**
     * @return bool
     */
    public function checkCookie(): bool
    {
        if ($this->helperData->isRequiredLogin()) {
            if ($this->helperData->getCustomerIsLoggedIn()) {
                if (!$this->helperData->hasDobByCustomer()) {
                    return true;
                }
            }
        }

        return true;
    }

    /**
     * @param $subject
     * @return array
     * @throws NoSuchEntityException
     */
    public function getChildProductData($subject)
    {
        $childProducts = [];
        $allowProducts = $subject->getAllowProducts();
        $parentProduct = $subject->getProduct();
        $parentLock = $this->helperData->isPreventPurchaseProduct($parentProduct);
        $parentAge = $this->helperData->getAgeFromProduct($parentProduct);
        foreach ($allowProducts as $childProduct) {
            $childProductId = $childProduct->getId();
            $data = [
                'prevent_purchase' => $parentLock ? true : $this->helperData->isPreventPurchaseProduct($childProduct),
                'verify_age' => $parentLock ? $parentAge : $this->helperData->getAgeFromProduct($childProduct),
                'html' => $this->helperData->addToCartButtonHtml(
                    $this->getJsonConfig($parentLock ? $parentProduct : $childProduct)
                )
            ];
            $childProducts[$childProductId] = $data;
        }
        return $childProducts;
    }

    /**
     * @param $subject
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPopupConfig($subject): array
    {
        return [
            'lofavChildProductData' => $this->getChildProductData($subject),
            'addtocart_selector' => $this->helperData->getAddToCartSelector(),
            'button_confirm' => $this->helperData->getButtonConfirmText(),
            'popup_title' => $this->helperData->getPopupTitle($subject),
            'popup_desc' => $this->helperData->getPopupDescription($subject),
            'purchase_message' => $this->helperData->getPurchaseMessage(),
            'purchase_notice' => $this->helperData->getPurchaseNotice(),
//            'redirect_url' => $this->helperData->getButtonRedirectUrl(),
            'verify_type' => $this->helperData->getVerifyType(),
            'is_required_login' => $this->helperData->isRequiredLogin(),
            'customer_logged_in' => $this->helperData->getCustomerIsLoggedIn(),
            'customer_id' => $this->helperData->getCustomerId(),
            'has_dob_by_customer' => $this->helperData->hasDobByCustomer(),
            'dob_by_customer' => $this->helperData->getDobByCustomer(),
            'cookie_lifetime' => $this->helperData->getCookieLifetime(),
            'html' => $this->helperData->addToCartButtonHtml($this->getJsonConfig($subject->getProduct()))
        ];
    }

    /**
     * @param $saleableItem
     * @return string
     * @throws NoSuchEntityException
     */
    private function getJsonConfig($saleableItem): string
    {
//        $this->ageVerificationProducts->addAgeVerificationToProduct($saleableItem);
        $config['popupConfigData'] = [
            'cookie_lifetime' => $this->helperData->getCookieLifetime(),
            'popup_title' => $this->helperData->getPopupTitle($saleableItem),
            'popup_desc' => $this->helperData->getPopupDescription($saleableItem),
            'purchase_message' => $this->helperData->getPurchaseMessage(),
            'purchase_notice' => $this->helperData->getPurchaseNotice(),
            'button_confirm' => $this->helperData->getButtonConfirmText(),
//            'redirect_url' => $this->helperData->getButtonRedirectUrl(),
            'verify_type' => $this->helperData->getVerifyType(),
            'verify_age' => $this->helperData->getAgeFromProduct($saleableItem),
            'is_required_login' => $this->helperData->isRequiredLogin(),
            'customer_logged_in' => $this->helperData->getCustomerIsLoggedIn(),
            'customer_id' => $this->helperData->getCustomerId(),
            'has_dob_by_customer' => $this->helperData->hasDobByCustomer(),
            'dob_by_customer' => $this->helperData->getDobByCustomer(),
        ];
        return $this->jsonEncoder->encode($config);
    }
}
