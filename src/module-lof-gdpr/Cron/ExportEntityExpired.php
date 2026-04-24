<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Cron;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Api\ExportEntityRepositoryInterface;
use Lof\Gdpr\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Delete all expired export entities
 */
final class ExportEntityExpired
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ExportEntityRepositoryInterface
     */
    private $exportEntityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        LoggerInterface $logger,
        Config $config,
        ExportEntityRepositoryInterface $exportEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->exportEntityRepository = $exportEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(): void
    {
        if ($this->config->isModuleEnabled() && $this->config->isExportEnabled()) {
            $this->searchCriteriaBuilder->addFilter(
                ExportEntityInterface::EXPIRED_AT,
                (new \DateTime())->format(DateTime::DATE_PHP_FORMAT),
                'lteq'
            );

            try {
                $exportList = $this->exportEntityRepository->getList($this->searchCriteriaBuilder->create());

                foreach ($exportList->getItems() as $exportEntity) {
                    $this->exportEntityRepository->delete($exportEntity);
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
            }
        }
    }
}
