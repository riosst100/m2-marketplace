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

namespace Lof\AgeVerification\Plugin\Framework\Pricing;

use Lof\AgeVerification\Helper\Data;
use Lof\AgeVerification\Model\AgeVerificationProducts;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;

class Render
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var AgeVerificationProducts
     */
    protected $ageVerificationProducts;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * Render constructor.
     * @param Data $helperData
     * @param State $state
     * @param CookieManager $cookieManager
     * @param EncoderInterface $jsonEncoder
     * @param AgeVerificationProducts $ageVerificationProducts
     */
    public function __construct(
        Data $helperData,
        State $state,
        CookieManager $cookieManager,
        EncoderInterface $jsonEncoder,
        AgeVerificationProducts $ageVerificationProducts
    ) {
        $this->cookieManager = $cookieManager;
        $this->ageVerificationProducts = $ageVerificationProducts;
        $this->jsonEncoder = $jsonEncoder;
        $this->helperData = $helperData;
        $this->state = $state;
    }

    /**
     * @param PricingRender $subject
     * @param callable $proceed
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     * @throws NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function aroundRender(
        PricingRender $subject,
        callable $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    ) {
        $additionalHtml = '';
        if ($this->canDisplay($saleableItem, $arguments) && $this->state->getAreaCode() != Area::AREA_ADMINHTML) {
            $additionalHtml = $this->customAddToCartButton($saleableItem);
        }

        return $proceed($priceCode, $saleableItem, $arguments) . $additionalHtml;
    }

    /**
     * @param $saleableItem
     * @return string
     * @throws NoSuchEntityException
     */
    private function customAddToCartButton($saleableItem)
    {
        $productId = 'lofav-product-button-' . $saleableItem->getId();
        return '<button data-role="lofav-button" id="' . $productId . '"
               style="display: none !important;"></button>
            <script>
                require([
                    "jquery",
                     "Lof_AgeVerification/js/lofavreplacebutton"
                ], function ($, lofavReplaceButton) {
                    $(document).ready(function() {
                        $("#' . $productId . '").lofavReplaceButton(' .
            $this->getButtonConfig($saleableItem)
            . ')
                    });
                });
            </script>';
    }

    /**
     * @param $saleableItem
     * @param $arguments
     * @return bool
     * @throws NoSuchEntityException
     */
    private function canDisplay($saleableItem, $arguments): bool
    {
        $isZone = (key_exists('zone', $arguments)
            && !in_array(
                $arguments['zone'],
                [PricingRender::ZONE_ITEM_LIST, PricingRender::ZONE_ITEM_VIEW]
            ));
        $isRequiredLoginAndValidAge = $this->helperData->isRequiredLoginAndValidAge($saleableItem);
        $isPreventPurchaseProduct = $this->helperData->isPreventPurchaseProduct($saleableItem);
        return $this->helperData->isEnabled()
            && ($saleableItem instanceof \Magento\Catalog\Model\Product)
            && $isPreventPurchaseProduct
            && !$isRequiredLoginAndValidAge
            && $this->checkCookie()
            || $isZone;
    }

    /**
     * @param $saleableItem
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
     * @param $saleableItem
     * @return string
     * @throws NoSuchEntityException
     */
    private function getButtonConfig($saleableItem): string
    {
        $this->ageVerificationProducts->addAgeVerificationToProduct($saleableItem);
        $config['buttonConfigData'] = [
            'addtocart_selector' => $this->helperData->getAddToCartSelector(),
            'verify_age' => $this->helperData->getAgeFromProduct($saleableItem),
            'product_item_selector' => $this->helperData->getProductItemSelector(),
            'html' => $this->helperData->addToCartButtonHtml($this->getJsonConfig($saleableItem))
        ];
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param $saleableItem
     * @return string
     * @throws NoSuchEntityException
     */
    private function getJsonConfig($saleableItem): string
    {
        $this->ageVerificationProducts->addAgeVerificationToProduct($saleableItem);
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
