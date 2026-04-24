<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action;

/**
 * @api
 */
interface PerformedByInterface
{
    public function get(): string;
}
