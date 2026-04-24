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
interface AgreementSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Retrieve the agreement entities list
     *
     * @return AgreementInterface[]
     */
    public function getItems(): array;

    /**
     * Set the agreement entity list
     *
     * @param AgreementInterface[] $items
     * @return AgreementSearchResultsInterface
     */
    public function setItems(array $items): AgreementSearchResultsInterface;
}
