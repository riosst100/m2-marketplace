<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export\Processor\Entity\EntityValue;

use Lof\Gdpr\Model\Entity\DataCollectorInterface;
use Lof\Gdpr\Model\Entity\DocumentInterface;
use Lof\Gdpr\Model\Entity\EntityValueProcessorInterface;
use Lof\Gdpr\Model\Entity\MetadataInterface;
use function in_array;

final class EntityProcessor implements EntityValueProcessorInterface
{
    /**
     * @var DocumentInterface
     */
    public $document;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var DataCollectorInterface
     */
    private $dataCollector;

    public function __construct(
        DocumentInterface $document,
        MetadataInterface $metadata,
        DataCollectorInterface $dataCollector
    ) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->dataCollector = $dataCollector;
    }

    public function process($entity, string $key, $value): void
    {
        if (in_array($key, $this->metadata->getAttributes(), true)) {
            $this->document->addData($key, $this->dataCollector->collect($value));
        }
    }
}
