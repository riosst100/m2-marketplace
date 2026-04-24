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
interface EntityValueProcessorInterface
{
    /**
     * @param object $entity
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function process($entity, string $key, $value): void;
}
