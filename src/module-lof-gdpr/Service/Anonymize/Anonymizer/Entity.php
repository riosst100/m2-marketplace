<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize\Anonymizer;

use Exception;
use InvalidArgumentException;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\TypeResolver;
use Lof\Gdpr\Model\Entity\DataCollectorInterface;
use Lof\Gdpr\Service\Anonymize\AnonymizerInterface;
use function gettype;
use function is_object;
use function sprintf;

final class Entity implements AnonymizerInterface
{
    /**
     * @var DataCollectorInterface
     */
    private $dataCollector;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    public function __construct(
        DataCollectorInterface $dataCollector,
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool
    ) {
        $this->dataCollector = $dataCollector;
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function anonymize($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(
                sprintf('Argument "$entity" must be an object, type "%s" given.', gettype($entity))
            );
        }

        $hydrator = $this->hydratorPool->getHydrator($this->typeResolver->resolve($entity));
        $hydrator->hydrate($entity, $this->dataCollector->collect($entity));

        return $entity;
    }
}
