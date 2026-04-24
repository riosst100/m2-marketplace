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

namespace Lof\AgeVerification\Model\Data;

use Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface;

class AgeVerificationProducts extends \Magento\Framework\Api\AbstractExtensibleObject implements AgeVerificationProductsInterface
{
    /**
     * Get custom_id
     * @return string|null
     */
    public function getCustomId()
    {
        return $this->_get(self::CUSTOM_ID);
    }

    /**
     * Set custom_id
     * @param string $customId
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setCustomId($customId)
    {
        return $this->setData(self::CUSTOM_ID, $customId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get use_custom
     * @return string|null
     */
    public function getUseCustom()
    {
        return $this->_get(self::USE_CUSTOM);
    }

    /**
     * Set use_custom
     * @param string $useCustom
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setUseCustom($useCustom)
    {
        return $this->setData(self::USE_CUSTOM, $useCustom);
    }

    /**
     * Get prevent_view
     * @return string|null
     */
    public function getPreventView()
    {
        return $this->_get(self::PREVENT_VIEW);
    }

    /**
     * Set prevent_view
     * @param string $isView
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setPreventView($isView)
    {
        return $this->setData(self::PREVENT_VIEW, $isView);
    }

    /**
     * Get prevent_purchase
     * @return string|null
     */
    public function getPreventPurchase()
    {
        return $this->_get(self::PREVENT_PURCHASE);
    }

    /**
     * Set prevent_purchase
     * @param string $isPurchase
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setPreventPurchase($isPurchase)
    {
        return $this->setData(self::PREVENT_PURCHASE, $isPurchase);
    }

    /**
     * Get verify_age
     * @return string|null
     */
    public function getVerifyAge()
    {
        return $this->_get(self::VERIFY_AGE);
    }

    /**
     * Set verify_age
     * @param string $age
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setVerifyAge($age)
    {
        return $this->setData(self::VERIFY_AGE, $age);
    }
}
