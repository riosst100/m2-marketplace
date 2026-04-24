<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export\Processor;

use Lof\Gdpr\Service\Export\ProcessorInterface;
use function array_combine;
use function array_keys;
use function array_reduce;
use function array_values;

final class CompositeProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        array $processors
    ) {
        $this->processors = (static function (ProcessorInterface ...$processors): array {
            return $processors;
        })(...array_values($processors));

        $this->processors = array_combine(array_keys($processors), $this->processors);
    }

    public function execute(int $entityId, array $data): array
    {
        return array_reduce(
            $this->processors,
            static function (array $data, ProcessorInterface $processor) use ($entityId): array {
                return $processor->execute($entityId, $data);
            },
            $data
        );
    }
}
