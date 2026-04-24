<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Erase\ProcessorResolver;

use Lof\Gdpr\Service\Erase\MetadataInterface;
use Lof\Gdpr\Service\Erase\ProcessorInterface;
use Lof\Gdpr\Service\Erase\ProcessorResolverFactory;
use Lof\Gdpr\Service\Erase\ProcessorResolverInterface;

final class ProcessorResolverStrategy implements ProcessorResolverInterface
{
    /**
     * @var ProcessorResolverFactory
     */
    private $processorResolverFactory;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    public function __construct(
        ProcessorResolverFactory $processorResolverFactory,
        MetadataInterface $metadata
    ) {
        $this->processorResolverFactory = $processorResolverFactory;
        $this->metadata = $metadata;
    }

    public function resolve(string $component): ProcessorInterface
    {
        return $this->processorResolverFactory->get($this->metadata->getComponentProcessor($component))->resolve($component);
    }
}
