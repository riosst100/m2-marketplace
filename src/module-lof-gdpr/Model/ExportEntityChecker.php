<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Lof\Gdpr\Api\ExportEntityCheckerInterface;
use Lof\Gdpr\Api\ExportEntityRepositoryInterface;

final class ExportEntityChecker implements ExportEntityCheckerInterface
{
    /**
     * @var ExportEntityRepositoryInterface
     */
    private $exportEntityRepository;

    public function __construct(
        ExportEntityRepositoryInterface $exportEntityRepository
    ) {
        $this->exportEntityRepository = $exportEntityRepository;
    }

    public function exists(int $entityId, string $entityType): bool
    {
        try {
            return (bool) $this->exportEntityRepository->getByEntity($entityId, $entityType)->getExportId();
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    public function isExported(int $entityId, string $entityType): bool
    {
        try {
            $entity = $this->exportEntityRepository->getByEntity($entityId, $entityType);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return $entity->getExportedAt() !== null && $entity->getFilePath() !== null;
    }
}
