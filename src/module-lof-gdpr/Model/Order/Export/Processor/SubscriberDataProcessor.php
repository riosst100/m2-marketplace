<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\Export\Processor;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Lof\Gdpr\Model\Entity\DataCollectorInterface;
use Lof\Gdpr\Model\Newsletter\Subscriber;
use Lof\Gdpr\Model\Newsletter\SubscriberFactory;

final class SubscriberDataProcessor extends AbstractDataProcessor
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        OrderRepositoryInterface $orderRepository,
        DataCollectorInterface $dataCollector
    ) {
        $this->subscriberFactory = $subscriberFactory;
        parent::__construct($orderRepository, $dataCollector);
    }

    protected function export(OrderInterface $order, array $data): array
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($order->getCustomerEmail());
        $data['subscriber'] = $this->collectData($subscriber);

        return $data;
    }
}
