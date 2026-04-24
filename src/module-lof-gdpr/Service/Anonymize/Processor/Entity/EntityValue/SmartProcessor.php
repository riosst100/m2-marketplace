<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize\Processor\Entity\EntityValue;

use Lof\Gdpr\Model\Entity\DocumentInterface;
use Lof\Gdpr\Model\Entity\EntityValueProcessorInterface;
use Lof\Gdpr\Service\Anonymize\AnonymizerFactory;
use Lof\Gdpr\Service\Anonymize\AnonymizerInterface;
use Lof\Gdpr\Service\Anonymize\MetadataInterface;
use function in_array;

final class SmartProcessor implements EntityValueProcessorInterface
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
     * @var AnonymizerFactory
     */
    private $anonymizerFactory;

    public function __construct(
        DocumentInterface $document,
        MetadataInterface $metadata,
        AnonymizerFactory $anonymizerFactory
    ) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->anonymizerFactory = $anonymizerFactory;
    }

    public function process($entity, string $key, $value): void
    {
        if (in_array($key, $this->metadata->getAttributes(), true)) {
            $this->document->addData($key, $this->resolveAnonymizer($key)->anonymize($value));
        }
    }

    private function resolveAnonymizer(string $key): AnonymizerInterface
    {
        return $this->anonymizerFactory->get(
            $this->metadata->getAnonymizerStrategiesByAttributes()[$key] ?? AnonymizerFactory::DEFAULT_ANONYMIZER
        );
    }
}
