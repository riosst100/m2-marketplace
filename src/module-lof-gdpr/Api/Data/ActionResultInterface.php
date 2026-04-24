<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api\Data;

use DateTime;

interface ActionResultInterface
{
    public function getPerformedAt(): DateTime;

    public function getState(): string;

    public function getMessage(): string;

    public function getResult(): array;
}
