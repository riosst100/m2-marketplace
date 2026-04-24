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
declare(strict_types=1);

namespace Lof\AgeVerification\Helper;

use Lof\AgeVerification\Model\AgeVerificationProducts;
use Lof\AgeVerification\Model\Config\Source\ConfigData;
use Lof\AgeVerification\Model\ProductPurchaseFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var AgeVerificationProducts
     */
    private $ageVerificationProducts;

    /**
     * @var ProductPurchaseFactory
     */
    private $_productPurchaseFactory;

    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * Data constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param HttpContext $httpContext
     * @param Registry $coreRegistry
     * @param AgeVerificationProducts $ageVerificationProducts
     * @param ProductPurchaseFactory $productPurchaseFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        HttpContext $httpContext,
        Registry $coreRegistry,
        AgeVerificationProducts $ageVerificationProducts,
        ProductPurchaseFactory $productPurchaseFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->ageVerificationProducts = $ageVerificationProducts;
        $this->_productPurchaseFactory = $productPurchaseFactory;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * @param $key
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        return $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $path
     * @param null $id
     * @return bool
     */
    public function hasFlagConfig($path, $id = null): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $id);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_MODULE_STATUS, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getProductConditions($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_CONDITIONS, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getPurchaseConditions($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PURCHASE_CONDITIONS, $storeId);
    }

    /**
     * @param null $product
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPopupTitle($product = null, $storeId = null): string
    {
        $title = $this->getConfig(ConfigData::XML_PATH_POPUP_TITLE, $storeId);
        $age = $this->getVerifyAge($product, $storeId);

        return str_replace('{age}', $age, $title);
    }

    /**
     * @param null $product
     * @param null $storeId
     * @return string|string[]
     * @throws NoSuchEntityException
     */
    public function getPopupDescription($product = null, $storeId = null)
    {
        $desc = $this->getConfig(ConfigData::XML_PATH_POPUP_DESCRIPTION, $storeId);
        $age = $this->getVerifyAge($product, $storeId);

        return str_replace('{age}', $age, $desc);
    }

    /**
     * @param null $storeId
     * @throws NoSuchEntityException
     */
    public function getButtonCancelText($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CANCEL_TEXT, $storeId) ?: __('Cancel');
    }

    /**
     * @param null $storeId
     * @throws NoSuchEntityException
     */
    public function getButtonConfirmText($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CONFIRM_TEXT, $storeId) ?: __('Confirm');
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getButtonRedirectUrl($storeId = null)
    {
        $homepage = $this->_urlInterface->getBaseUrl();
        $urlConfig = $this->getConfig(ConfigData::XML_PATH_REDIRECT_URL, $storeId);

        return $urlConfig ? str_replace('{homepage}', $homepage, $urlConfig) : '#';
    }

    /**
     * @param null $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAppliedCategoryIds($storeId = null): array
    {
        $categoryIds = $this->getConfig(ConfigData::XML_PATH_APPLY_TO_CATEGORY, $storeId);
        return $categoryIds ? $this->convertToArray((string)$categoryIds) : [];
    }

    /**
     * @param null $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCmsPageIdentifiers($storeId = null): array
    {
        $cmsPages = $this->getConfig(ConfigData::XML_PATH_APPLY_TO_CMS_PAGES, $storeId);
        return $cmsPages ? $this->convertToArray((string)$cmsPages) : [];
    }

    /**
     * @param null $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAppliedStoreIds($storeId = null): array
    {
        $storeView = $this->getConfig(ConfigData::XML_PATH_STORE_VIEW, $storeId);
        return $storeView ? $this->convertToArray((string)$storeView) : [];
    }

    /**
     * Convert string value to array
     *
     * @param string $value
     * @return array
     */
    private function convertToArray(string $value): array
    {
        return strlen($value) ? explode(',', $value) : [];
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaBaseUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getPopupIcon($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_POPUP_ICON, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getTextColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_TEXT_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getBackgroundColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_BACKGROUND_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getOverlayColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_OVERLAY_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getButtonCancelTextColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CANCEL_TEXT_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getButtonCancelBackgroundColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CANCEL_BACKGROUND_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getButtonConfirmTextColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CONFIRM_TEXT_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string | null
     * @throws NoSuchEntityException
     */
    public function getButtonConfirmBackgroundColor($storeId = null): ?string
    {
        return $this->getConfig(ConfigData::XML_PATH_BUTTON_CONFIRM_BACKGROUND_COLOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getCookieLifetime($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_COOKIE_LIFETIME, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isRequiredLogin($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_REQUIRED_LOGIN, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getVerifyType($storeId = null): ?string
    {
        if ($this->isRequiredLogin()) {
            if (!$this->getCustomerIsLoggedIn()) {
                return \Lof\AgeVerification\Model\Config\Source\PopupVerifyType::TYPE_REQUIRE_LOGIN;
            }
            return \Lof\AgeVerification\Model\Config\Source\PopupVerifyType::TYPE_DOB;
        }
        return $this->getConfig(ConfigData::XML_PATH_VERIFY_TYPE, $storeId);
    }

    /**
     * @param null $product
     * @param null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getVerifyAge($product = null, $storeId = null): ?string
    {
        $age = $this->getConfig(ConfigData::XML_PATH_VERIFY_AGE, $storeId);

        if ($product !== null) {
            $age = $this->getAgeFromProduct($product);
        } elseif ($this->getCurrentProduct() !== null) {
            $age = $this->getAgeFromProduct($this->getCurrentProduct());
        }

        return $age ?: '18';
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnableCmsPages($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_ENABLE_CMS_PAGES, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnableCategoryPages($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_ENABLE_CATEGORY_PAGES, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnableProductDetailConditions($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_ENABLE_PRODUCT_DETAIL_CONDITIONS, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnablePurchaseConditions($storeId = null): bool
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_ENABLE_PRODUCT_PURCHASE_CONDITIONS, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAddToCartSelector($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_ADDTOCART_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductItemSelector($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_ITEM_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPurchaseNotice($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_PURCHASE_NOTICE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPurchaseMessage($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_PURCHASE_MESSAGE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLoginNotice($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_LOGIN_NOTICE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPreventNotice($storeId = null): string
    {
        return $this->getConfig(ConfigData::XML_PATH_PREVENT_NOTICE, $storeId);
    }

    /**
     * @param $productRule
     * @param $conditions
     * @return mixed
     */
    public function getProductRule($productRule, $conditions)
    {
        $productRule->setConditions([]);
        $productRule->setConditionsSerialized($conditions);
        return $productRule;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @param $dob
     * @return string
     */
    public function getAgeByCustomer($dob): string
    {
        $today = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $diff = date_diff(date_create($dob), date_create($today));
        return $diff->format('%y');
    }

    /**
     * @return bool
     */
    public function hasDobByCustomer(): bool
    {
        $dob = $this->httpContext->getValue('dob');
        return !($dob === false);
    }

    /**
     * @return mixed|null
     */
    public function getDobByCustomer()
    {
        $dob = $this->httpContext->getValue('dob');

        if ($dob) {
            return $dob;
        }
        return null;
    }

    /**
     * @param null $product
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isRequiredLoginAndValidAge($product = null): bool
    {
        if ($this->isRequiredLogin()) {
            if ($this->getCustomerIsLoggedIn()) {
                $dob = $this->httpContext->getValue('dob');
                $verifyAge = $product != null ? $this->getAgeFromProduct($product) : $this->getVerifyAge($product);
                if ($dob) {
                    $age = $this->getAgeByCustomer($dob);
                    if ($age >= $verifyAge) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getCustomerIsLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function getCustomerId()
    {
        $customerId = $this->httpContext->getValue('customer_id');
        if ($customerId) {
            return $customerId;
        }
        return null;
    }

    /**
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isPreventPurchaseProduct($product): bool
    {
        if (!$this->isEnablePurchaseConditions()) {
            return false;
        }

        $this->ageVerificationProducts->addAgeVerificationToProduct($product);
        if ($product->getData('age_verification') && !!$product->getData('age_verification')['use_custom']) {
            return !!$product->getData('age_verification')['prevent_purchase'];
        }

        $productRule = $this->getProductRule(
            $this->_productPurchaseFactory->create(),
            $this->getPurchaseConditions()
        );

        return $productRule->validate($product);
    }

    /**
     * @param $product
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getAgeFromProduct($product): ?string
    {
        $this->ageVerificationProducts->addAgeVerificationToProduct($product);
        if ($product->getData('age_verification') && !!$product->getData('age_verification')['use_custom']) {
            return $product->getData('age_verification')['verify_age'];
        }

        return $this->getConfig(ConfigData::XML_PATH_VERIFY_AGE);
    }

    /**
     * Return current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param $jsonData
     * @return string
     */
    public function addToCartButtonHtml($jsonData): string
    {
        return '<button type="button" class="lofav-button action primary"
                    title="' . __('Age Verification') . '" data-config=\'' . htmlspecialchars(($jsonData), ENT_QUOTES,
                'UTF-8') . '\'>
                   <span>' . __('Age Verification') . '</span>
            </button>';
    }
}
