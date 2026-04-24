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

namespace Lof\AgeVerification\Api\Data;

interface AgeVerificationProductsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PREVENT_PURCHASE = 'prevent_purchase';
    const CUSTOM_ID = 'custom_id';
    const VERIFY_AGE = 'verify_age';
    const PREVENT_VIEW = 'prevent_view';
    const PRODUCT_ID = 'product_id';
    const USE_CUSTOM = 'use_custom';

    /**
     * Get custom_id
     * @return string|null
     */
    public function getCustomId();

    /**
     * Set custom_id
     * @param string $customId
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setCustomId($customId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsExtensionInterface $extensionAttributes
    );

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setProductId($productId);

    /**
     * Get use_custom
     * @return string|null
     */
    public function getUseCustom();

    /**
     * Set use_custom
     * @param string $useCustom
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setUseCustom($useCustom);

    /**
     * Get prevent_view
     * @return string|null
     */
    public function getPreventView();

    /**
     * Set prevent_view
     * @param string $isView
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setPreventView($isView);

    /**
     * Get prevent_purchase
     * @return string|null
     */
    public function getPreventPurchase();

    /**
     * Set prevent_purchase
     * @param string $isPurchase
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setPreventPurchase($isPurchase);

    /**
     * Get verify_age
     * @return string|null
     */
    public function getVerifyAge();

    /**
     * Set verify_age
     * @param string $age
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     */
    public function setVerifyAge($age);
}
