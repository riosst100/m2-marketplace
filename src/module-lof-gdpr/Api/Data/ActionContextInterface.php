<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api\Data;

interface ActionContextInterface
{
    public function getPerformedFrom(): string;

    public function getPerformedBy(): string;

    public function getParameters(): array;
}
