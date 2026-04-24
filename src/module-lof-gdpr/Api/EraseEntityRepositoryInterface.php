<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Lof\Gdpr\Api\Data\EraseEntityInterface;
use Lof\Gdpr\Api\Data\EraseEntitySearchResultsInterface;

/**
 * @api
 */
interface EraseEntityRepositoryInterface
{
    /**
     * Save erase entity scheduler
     *
     * @param EraseEntityInterface $eraseEntity
     * @return EraseEntityInterface
     * @throws CouldNotSaveException
     */
    public function save(EraseEntityInterface $eraseEntity): EraseEntityInterface;

    /**
     * Retrieve erase entity scheduler by ID
     *
     * @param int $eraseId
     * @return EraseEntityInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $eraseId): EraseEntityInterface;

    /**
     * Retrieve erase entity scheduler by entity
     *
     * @param int $entityId
     * @param string $entityType
     * @return EraseEntityInterface
     * @throws NoSuchEntityException
     */
    public function getByEntity(int $entityId, string $entityType): EraseEntityInterface;

    /**
     * Retrieve erase entity schedulers list by search filter criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EraseEntitySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): EraseEntitySearchResultsInterface;

    /**
     * Delete erase entity scheduler
     *
     * @param EraseEntityInterface $eraseEntity
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function delete(EraseEntityInterface $eraseEntity): bool;
}
