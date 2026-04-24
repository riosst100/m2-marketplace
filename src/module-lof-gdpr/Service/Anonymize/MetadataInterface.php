<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize;

use Lof\Gdpr\Model\Entity\MetadataInterface as EntityMetadataInterface;

/**
 * @api
 */
interface MetadataInterface extends EntityMetadataInterface
{
    public function getAnonymizerStrategiesByAttributes(?string $scopeCode = null): array;
}
