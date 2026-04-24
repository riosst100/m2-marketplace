<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Entity;

/**
 * @api
 */
interface EntityCheckerInterface
{
    /**
     * Checks whether or not the entity has pending orders within its relations
     *
     * @param int $entityId
     * @return bool
     */
    public function canErase(int $entityId): bool;
}
