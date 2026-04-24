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

namespace Lof\AgeVerification\Model\ResourceModel;

class AgeVerificationProducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_ageverification_products', 'custom_id');
    }

    /**
     * Add age verification to product model
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addAgeVerificationToProduct($product)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['main_table' => $this->getMainTable()],
            [
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::USE_CUSTOM,
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::PREVENT_PURCHASE,
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::CUSTOM_ID,
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::PREVENT_VIEW,
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::VERIFY_AGE,
                \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::PREVENT_PURCHASE
            ]
        )->where('main_table.product_id = ?', $product->getId());

        $productData['age_verification'] = $connection->fetchRow($select);
        if ($productData) {
            $product->addData($productData);
        }

        return $this;
    }
}
