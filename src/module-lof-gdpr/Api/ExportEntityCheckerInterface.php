<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api;

/**
 * @api
 */
interface ExportEntityCheckerInterface
{
    public function exists(int $entityId, string $entityType): bool;

    public function isExported(int $entityId, string $entityType): bool;
}
