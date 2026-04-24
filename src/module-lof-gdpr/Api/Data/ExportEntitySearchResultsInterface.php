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
interface ExportEntitySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Retrieve the export entities list
     *
     * @return ExportEntityInterface[]
     */
    public function getItems(): array;

    /**
     * Set the export entities list
     *
     * @param ExportEntityInterface[] $items
     * @return ExportEntitySearchResultsInterface
     */
    public function setItems(array $items): ExportEntitySearchResultsInterface;
}
