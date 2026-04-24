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

interface AgeVerificationProductsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AgeVerificationProducts list.
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface[]
     */
    public function getItems();

    /**
     * Set custom_id list.
     * @param \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
