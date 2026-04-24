<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api;

use DateTime;
use Lof\Gdpr\Api\Data\EraseEntityInterface;

/**
 * @api
 * @todo should be internal? No use case of external usage.
 */
interface EraseSalesInformationInterface
{
    public function scheduleEraseEntity(int $entityId, string $entityType, DateTime $lastActive): EraseEntityInterface;

    public function isAlive(DateTime $lastActive): bool;
}
