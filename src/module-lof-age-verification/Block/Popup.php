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

namespace Lof\AgeVerification\Block;

use Lof\AgeVerification\Helper\Data;
use Lof\AgeVerification\Model\ProductPurchaseFactory;
use Lof\AgeVerification\Model\AgeVerificationProducts;
use Magento\Catalog\Model\Category;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Page
     */
    protected $cmsPages;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var AgeVerificationProducts
     */
    protected $ageVerificationProducts;

    /**
     * Popup constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param Page $cmsPages
     * @param Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory
     * @param AgeVerificationProducts $ageVerificationProducts
     * @param Registry $coreRegistry
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CookieManager $cookieManager,
        Page $cmsPages,
        Data $helperData,
        StoreManagerInterface $storeManager,
        RuleFactory $catalogRuleFactory,
        AgeVerificationProducts $ageVerificationProducts,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cookieManager = $cookieManager;
        $this->ageVerificationProducts = $ageVerificationProducts;
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->helperData = $helperData;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->cmsPages = $cmsPages;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function _toHtml()
    {
        if (!$this->canDisplay()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canDisplay(): bool
    {
        return $this->helperData->isEnabled()
            && $this->isAppliedStore()
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
        return $this->cookieManager->getCookie('Lof_AgeVerification') != '1';
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAppliedStore()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $appliedStoreIds = $this->helperData->getAppliedStoreIds();
        if (!$storeId) {
            return false;
        }
        return in_array($storeId, $appliedStoreIds);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAppliedCategory(): bool
    {
        $appliedCategoryIds = $this->helperData->getAppliedCategoryIds();
        $categoryId = $this->getCurrentCategory() === null ? null : $this->getCurrentCategory()->getId();

        if ($this->helperData->isRequiredLoginAndValidAge()) {
            return false;
        }

        if (!$categoryId) {
            return false;
        }

        if (!$this->helperData->isEnableCategoryPages()) {
            return false;
        }


        return in_array($categoryId, $appliedCategoryIds);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateProductDetail(): bool
    {
        $product = $this->helperData->getCurrentProduct() === null ? null : $this->helperData->getCurrentProduct();
        if (!$product) {
            return false;
        }

        if ($this->helperData->isRequiredLoginAndValidAge($product)) {
            return false;
        }

        $this->ageVerificationProducts->addAgeVerificationToProduct($product);
        if ($product->getData('age_verification') && !!$product->getData('age_verification')['use_custom']) {
            return !!$product->getData('age_verification')['prevent_view'];
        }

        if (!$this->helperData->isEnableProductDetailConditions()) {
            return false;
        }

        $productRule = $this->helperData->getProductRule(
            $this->catalogRuleFactory->create(),
            $this->helperData->getProductConditions()
        );

        return $productRule->getConditions()->validate($product);
    }

    /**
     * Retrieve current category model object
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultPopupIcon()
    {
        if ($this->helperData->getPopupIcon()) {
            return $this->helperData->getMediaBaseUrl() .
                'lofageverification/design/'
                . $this->helperData->getPopupIcon();
        }
        return $this->getViewFileUrl('Lof_AgeVerification::images/icon/icon.png');
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('lof_ageverification/ajax/showageverificationPopup');
    }

    /**
     * @return Page
     */
    public function getCurrentPages()
    {
        return $this->cmsPages;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'can_display' => $this->canDisplay(),
            'addtocart_selector' => $this->helperData->getAddToCartSelector(),
            'is_applied_category' => $this->isAppliedCategory(),
            'is_required_login' => $this->helperData->isRequiredLogin(),
            'customer_logged_in' => $this->helperData->getCustomerIsLoggedIn(),
            'customer_id' => $this->helperData->getCustomerId(),
            'has_dob_by_customer' => $this->helperData->hasDobByCustomer(),
            'dob_by_customer' => $this->helperData->getDobByCustomer(),
            'is_applied_product_detail' => $this->validateProductDetail(),
            'current_cms_identifier' => $this->getCurrentPages()->getIdentifier() != null
                ? $this->getCurrentPages()->getIdentifier() : null,
            'cms_page_identifiers' => $this->helperData->getCmsPageIdentifiers() != null
                ? $this->helperData->getCmsPageIdentifiers() : null,
            'enable_cms_pages' => $this->helperData->isEnableCmsPages(),
            'verify_type' => $this->helperData->getVerifyType(),
            'redirect_url' => $this->helperData->getButtonRedirectUrl(),
            'verify_age' => $this->helperData->getVerifyAge(),
            'cookie_lifetime' => $this->helperData->getCookieLifetime(),
            'ajaxUrl' => $this->getAjaxUrl()
        ]);
    }
}
