<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize\Anonymizer;

use Lof\Gdpr\Service\Anonymize\AnonymizerInterface;

final class NullValue implements AnonymizerInterface
{
    public function anonymize($value)
    {
        return null;
    }
}
