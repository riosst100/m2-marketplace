<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Api\Data\ExportEntityInterfaceFactory;
use Lof\Gdpr\Api\ExportEntityCheckerInterface;
use Lof\Gdpr\Api\ExportEntityManagementInterface;
use Lof\Gdpr\Api\ExportEntityRepositoryInterface;
use Lof\Gdpr\Model\Export\ExportToFile;

final class ExportEntityManagement implements ExportEntityManagementInterface
{
    /**
     * @var ExportEntityInterfaceFactory
     */
    private $exportEntityFactory;

    /**
     * @var ExportEntityRepositoryInterface
     */
    private $exportEntityRepository;

    /**
     * @var ExportEntityCheckerInterface
     */
    private $exportEntityChecker;

    /**
     * @var ExportToFile
     */
    private $exportToFile;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ExportEntityInterfaceFactory $exportEntityFactory,
        ExportEntityRepositoryInterface $exportEntityRepository,
        ExportEntityCheckerInterface $exportEntityChecker,
        ExportToFile $exportToFile,
        Config $config
    ) {
        $this->exportEntityFactory = $exportEntityFactory;
        $this->exportEntityRepository = $exportEntityRepository;
        $this->exportEntityChecker = $exportEntityChecker;
        $this->exportToFile = $exportToFile;
        $this->config = $config;
    }

    public function create(int $entityId, string $entityType, ?string $fileName = null): ExportEntityInterface
    {
        if ($this->exportEntityChecker->exists($entityId, $entityType)) {
            throw new AlreadyExistsException(
                new Phrase(
                    'An export entity already exists for the entity type "%1" with ID "%2".',
                    [$entityType, $entityId]
                )
            );
        }

        /** @var ExportEntityInterface $exportEntity */
        $exportEntity = $this->exportEntityFactory->create();
        $exportEntity->setEntityId($entityId);
        $exportEntity->setEntityType($entityType);
        $exportEntity->setFileName($fileName ?? $this->config->getExportFileName());
        $exportEntity = $this->exportEntityRepository->save($exportEntity);

        return $exportEntity;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function export(ExportEntityInterface $exportEntity): ExportEntityInterface
    {
        $exportEntity->setFilePath($this->exportToFile->export($exportEntity));
        $exportEntity->setExpiredAt(
            (new \DateTime('+' . $this->config->getExportLifetime() . 'minutes'))->format(DateTime::DATETIME_PHP_FORMAT)
        );
        $exportEntity->setExportedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        $this->exportEntityRepository->save($exportEntity);

        return $exportEntity;
    }
}
