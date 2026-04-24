<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\Notifier;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * @api
 */
interface SenderInterface
{
    public function send(OrderInterface $order): void;
}
