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
interface EntityIteratorInterface
{
    /**
     * @param object $entity
     * @return void
     */
    public function iterate($entity): void;
}
