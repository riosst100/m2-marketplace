<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Customer\Notifier;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * @api
 */
interface SenderInterface
{
    public function send(CustomerInterface $customer): void;
}
