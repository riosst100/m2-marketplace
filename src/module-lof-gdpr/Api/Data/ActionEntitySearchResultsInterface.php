<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ActionEntitySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Retrieve the action entity list
     *
     * @return ActionEntityInterface[]
     */
    public function getItems(): array;

    /**
     * Set the action entity list
     *
     * @param ActionEntityInterface[] $items
     * @return ActionEntitySearchResultsInterface
     */
    public function setItems(array $items): ActionEntitySearchResultsInterface;
}
