<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\Delete\Processor;

use DateTime;
use Exception;
use Magento\Sales\Api\OrderRepositoryInterface;
use Lof\Gdpr\Api\EraseSalesInformationInterface;
use Lof\Gdpr\Service\Erase\ProcessorInterface;

final class OrderDataProcessor implements ProcessorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EraseSalesInformationInterface
     */
    private $eraseSalesInformation;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EraseSalesInformationInterface $eraseSalesInformation
    ) {
        $this->orderRepository = $orderRepository;
        $this->eraseSalesInformation = $eraseSalesInformation;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function execute(int $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        $lastActive = new DateTime($order->getUpdatedAt());

        if ($this->eraseSalesInformation->isAlive($lastActive)) {
            $this->eraseSalesInformation->scheduleEraseEntity((int) $order->getEntityId(), 'order', $lastActive);

            return true;
        }

        return $this->orderRepository->delete($order);
    }
}
