<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\PerformedBy;

use Lof\Gdpr\Model\Action\PerformedByInterface;

final class NotEmptyStrategy implements PerformedByInterface
{
    private const PERFORMED_BY = 'Unknown';

    /**
     * @var PerformedByInterface[]
     */
    private $performedByList;

    /**
     * @param PerformedByInterface[] $performedByList
     */
    public function __construct(
        array $performedByList
    ) {
        $this->performedByList = (static function (PerformedByInterface ...$performedByList) {
            return $performedByList;
        })(...$performedByList);
    }

    public function get(): string
    {
        foreach ($this->performedByList as $performedBy) {
            $performer = $performedBy->get();

            if (!empty($performer)) {
                return $performer;
            }
        }

        return self::PERFORMED_BY;
    }
}
