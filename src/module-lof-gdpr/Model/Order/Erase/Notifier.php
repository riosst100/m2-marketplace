<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\Erase;

use Magento\Sales\Api\OrderRepositoryInterface;
use Lof\Gdpr\Api\Data\EraseEntityInterface;
use Lof\Gdpr\Model\Erase\NotifierInterface;
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

    public function notify(EraseEntityInterface $eraseEntity): void
    {
        $order = $this->orderRepository->get($eraseEntity->getEntityId());

        foreach ($this->senders as $sender) {
            $sender->send($order);
        }
    }
}
