<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Erase\Processor;

use Lof\Gdpr\Model\Config\Source\EraseComponents;
use Lof\Gdpr\Service\Erase\ProcessorInterface;
use Lof\Gdpr\Service\Erase\ProcessorResolverInterface;
use function array_column;

final class EraseProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorResolverInterface
     */
    private $eraseProcessorResolver;

    /**
     * @var EraseComponents
     */
    private $eraseComponents;

    public function __construct(
        ProcessorResolverInterface $eraseProcessorResolver,
        EraseComponents $eraseComponents
    ) {
        $this->eraseProcessorResolver = $eraseProcessorResolver;
        $this->eraseComponents = $eraseComponents;
    }

    public function execute(int $entityId): bool
    {
        foreach (array_column($this->eraseComponents->toOptionArray(), 'value') as $component) {
            $processor = $this->eraseProcessorResolver->resolve($component);
            if (!$processor->execute($entityId)) {
                return false;
            }
        }

        return true;
    }
}
