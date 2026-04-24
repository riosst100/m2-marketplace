<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize;

/**
 * @api
 */
interface AnonymizerInterface
{
    /**
     * Anonymize the value
     *
     * @param mixed $value
     * @return mixed
     */
    public function anonymize($value);
}
