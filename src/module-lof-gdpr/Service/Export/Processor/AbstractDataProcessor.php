<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export\Processor;

use Lof\Gdpr\Model\Entity\DataCollectorInterface;
use Lof\Gdpr\Service\Export\ProcessorInterface;

abstract class AbstractDataProcessor implements ProcessorInterface
{
    /**
     * @var DataCollectorInterface
     */
    private $dataCollector;

    public function __construct(
        DataCollectorInterface $dataCollector
    ) {
        $this->dataCollector = $dataCollector;
    }

    protected function collectData(object $entity): array
    {
        return $this->dataCollector->collect($entity);
    }
}
