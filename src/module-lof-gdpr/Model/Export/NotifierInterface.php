<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Export;

use Lof\Gdpr\Api\Data\ExportEntityInterface;

/**
 * @api
 */
interface NotifierInterface
{
    public function notify(ExportEntityInterface $exportEntity): void;
}
