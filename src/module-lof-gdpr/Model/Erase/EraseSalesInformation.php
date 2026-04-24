<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Erase;

use DateTimeImmutable;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime;
use Lof\Gdpr\Api\Data\EraseEntityInterface;
use Lof\Gdpr\Api\Data\EraseEntityInterfaceFactory;
use Lof\Gdpr\Api\EraseEntityRepositoryInterface;
use Lof\Gdpr\Api\EraseSalesInformationInterface;
use Lof\Gdpr\Model\Config;

final class EraseSalesInformation implements EraseSalesInformationInterface
{
    /**
     * @var EraseEntityInterfaceFactory
     */
    private $eraseEntityFactory;

    /**
     * @var EraseEntityRepositoryInterface
     */
    private $eraseEntityRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        EraseEntityInterfaceFactory $eraseEntityFactory,
        EraseEntityRepositoryInterface $eraseEntityRepository,
        Config $config
    ) {
        $this->eraseEntityFactory = $eraseEntityFactory;
        $this->eraseEntityRepository = $eraseEntityRepository;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @throws CouldNotSaveException
     */
    public function scheduleEraseEntity(int $entityId, string $entityType, \DateTime $lastActive): EraseEntityInterface
    {
        $dateTime = DateTimeImmutable::createFromMutable($lastActive);
        $scheduleAt = $dateTime->modify('+' . $this->config->getErasureSalesMaxAge() . + 'days');

        /** @var EraseEntityInterface $eraseEntity */
        $eraseEntity = $this->eraseEntityFactory->create();
        $eraseEntity->setEntityId($entityId);
        $eraseEntity->setEntityType($entityType);
        $eraseEntity->setState(EraseEntityInterface::STATE_PENDING);
        $eraseEntity->setStatus(EraseEntityInterface::STATUS_READY);
        $eraseEntity->setScheduledAt($scheduleAt->format(DateTime::DATETIME_PHP_FORMAT));

        $this->eraseEntityRepository->save($eraseEntity);

        return $eraseEntity;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function isAlive(\DateTime $dateTime): bool
    {
        return $dateTime > new \DateTime('-' . $this->config->getErasureSalesMaxAge() . 'days');
    }
}
