<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Export;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NotFoundException;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Model\Archive\MoveToArchive;
use Lof\Gdpr\Model\Config;
use Lof\Gdpr\Service\Export\ProcessorFactory;
use Lof\Gdpr\Service\Export\RendererFactory;
use function sha1;
use const DIRECTORY_SEPARATOR;

final class ExportToFile
{
    /**
     * @var ProcessorFactory
     */
    private $exportProcessorFactory;

    /**
     * @var RendererFactory
     */
    private $exportRendererFactory;

    /**
     * @var MoveToArchive
     */
    private $archive;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ProcessorFactory $exportProcessorFactory,
        RendererFactory $exportRendererFactory,
        MoveToArchive $archive,
        Config $config
    ) {
        $this->exportProcessorFactory = $exportProcessorFactory;
        $this->exportRendererFactory = $exportRendererFactory;
        $this->archive = $archive;
        $this->config = $config;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @return string|null
     * @throws FileSystemException
     * @throws NotFoundException
     */
    public function export(ExportEntityInterface $exportEntity): ?string
    {
        $exporter = $this->exportProcessorFactory->get($exportEntity->getEntityType());
        $fileName = $this->prepareFileName($exportEntity);
        $data = $exporter->execute($exportEntity->getEntityId(), []);
        foreach ($this->config->getExportRendererCodes() as $rendererCode) {
            $filePath = $this->archive->prepareArchive(
                $this->exportRendererFactory->get($rendererCode)->saveData($fileName, $data),
                $fileName . '.zip'
            );
        }

        return $filePath ?? null;
    }

    private function prepareFileName(ExportEntityInterface $exportEntity): string
    {
        return 'gdpr' .
            DIRECTORY_SEPARATOR .
            sha1($exportEntity->getEntityType() . $exportEntity->getExportId()) .
            DIRECTORY_SEPARATOR .
            $exportEntity->getFileName();
    }
}
