<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Export;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Lof\Gdpr\Api\ExportEntityCheckerInterface;
use Lof\Gdpr\Api\ExportEntityManagementInterface;
use Lof\Gdpr\Api\ExportEntityRepositoryInterface;

/**
 * @api
 */
final class ExportEntityData
{
    /**
     * @var ExportEntityRepositoryInterface
     */
    private $exportEntityRepository;

    /**
     * @var ExportEntityManagementInterface
     */
    private $exportEntityManagement;

    /**
     * @var ExportEntityCheckerInterface
     */
    private $exportEntityChecker;

    public function __construct(
        ExportEntityRepositoryInterface $exportEntityRepository,
        ExportEntityManagementInterface $exportEntityManagement,
        ExportEntityCheckerInterface $exportEntityChecker
    ) {
        $this->exportEntityRepository = $exportEntityRepository;
        $this->exportEntityManagement = $exportEntityManagement;
        $this->exportEntityChecker = $exportEntityChecker;
    }

    /**
     * Export the entity to a file
     *
     * @param int $entityId
     * @param string $entityType
     * @return string
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function export(int $entityId, string $entityType): string
    {
        try {
            $exportEntity = $this->exportEntityRepository->getByEntity($entityId, $entityType);
        } catch (NoSuchEntityException $e) {
            $exportEntity = $this->exportEntityManagement->create($entityId, $entityType);
        }

        return $this->exportEntityChecker->isExported($entityId, $entityType)
            ? $exportEntity->getFilePath()
            : $this->exportEntityManagement->export($exportEntity);
    }
}
