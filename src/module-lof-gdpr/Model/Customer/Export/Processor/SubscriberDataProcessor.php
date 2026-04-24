<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Customer\Export\Processor;

use Lof\Gdpr\Model\Entity\DataCollectorInterface;
use Lof\Gdpr\Model\Newsletter\Subscriber;
use Lof\Gdpr\Model\Newsletter\SubscriberFactory;
use Lof\Gdpr\Service\Export\Processor\AbstractDataProcessor;

final class SubscriberDataProcessor extends AbstractDataProcessor
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        DataCollectorInterface $dataCollector
    ) {
        $this->subscriberFactory = $subscriberFactory;
        parent::__construct($dataCollector);
    }

    public function execute(int $customerId, array $data): array
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomerId($customerId);
        $data['subscriber'] = $this->collectData($subscriber);

        return $data;
    }
}
