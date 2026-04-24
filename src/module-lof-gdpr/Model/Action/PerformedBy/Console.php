<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\PerformedBy;

use Lof\Gdpr\Model\Action\PerformedByInterface;

final class Console implements PerformedByInterface
{
    private const PERFORMED_BY = 'console';

    public function get(): string
    {
        return self::PERFORMED_BY;
    }
}
