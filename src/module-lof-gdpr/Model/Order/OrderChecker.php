<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Lof\Gdpr\Model\Config;
use Lof\Gdpr\Model\Entity\EntityCheckerInterface;
use function in_array;

final class OrderChecker implements EntityCheckerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Config $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    public function canErase(int $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);

        return in_array($order->getState(), $this->config->getAllowedStatesToErase(), true);
    }
}
