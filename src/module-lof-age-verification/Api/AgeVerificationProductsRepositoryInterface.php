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

namespace Lof\AgeVerification\Api;

interface AgeVerificationProductsRepositoryInterface
{
    /**
     * Save AgeVerificationProducts
     * @param \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
    );

    /**
     * Retrieve AgeVerificationProducts
     * @param string $customId
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customId);

    /**
     * Retrieve AgeVerificationProducts matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\AgeVerification\Api\Data\AgeVerificationProductsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete AgeVerificationProducts
     * @param \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
    );

    /**
     * Delete AgeVerificationProducts by ID
     * @param string $customId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customId);
}
