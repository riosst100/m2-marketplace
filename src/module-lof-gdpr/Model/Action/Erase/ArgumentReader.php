<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\Erase;

use Lof\Gdpr\Api\Data\ActionContextInterface;
use Lof\Gdpr\Api\Data\EraseEntityInterface;

final class ArgumentReader
{
    public const ERASE_ENTITY = 'erase_entity';

    public static function getEntity(ActionContextInterface $actionContext): ?EraseEntityInterface
    {
        return $actionContext->getParameters()[self::ERASE_ENTITY] ?? null;
    }
}
