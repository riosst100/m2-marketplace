<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\Export;

use Magento\Sales\Api\OrderRepositoryInterface;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Model\Export\NotifierInterface;
use Lof\Gdpr\Model\Order\Notifier\SenderInterface;

final class Notifier implements NotifierInterface
{
    /**
     * @var SenderInterface[]
     */
    private $senders;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param SenderInterface[] $senders
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        array $senders,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->senders = (static function (SenderInterface ...$senders): array {
            return $senders;
        })(...\array_values($senders));
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function notify(ExportEntityInterface $exportEntity): void
    {
        $order = $this->orderRepository->get($exportEntity->getEntityId());

        foreach ($this->senders as $sender) {
            $sender->send($order);
        }
    }
}
