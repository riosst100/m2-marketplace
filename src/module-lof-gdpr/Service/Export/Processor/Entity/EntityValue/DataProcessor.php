<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export\Processor\Entity\EntityValue;

use Lof\Gdpr\Model\Entity\DocumentInterface;
use Lof\Gdpr\Model\Entity\EntityValueProcessorInterface;
use Lof\Gdpr\Model\Entity\MetadataInterface;
use function in_array;

final class DataProcessor implements EntityValueProcessorInterface
{
    /**
     * @var DocumentInterface
     */
    public $document;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    public function __construct(
        DocumentInterface $document,
        MetadataInterface $metadata
    ) {
        $this->document = $document;
        $this->metadata = $metadata;
    }

    public function process($entity, string $key, $value): void
    {
        if (in_array($key, $this->metadata->getAttributes(), true)) {
            $this->document->addData($key, $value);
        }
    }
}
