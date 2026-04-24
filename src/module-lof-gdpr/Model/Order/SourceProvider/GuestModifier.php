<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Order\SourceProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Lof\Gdpr\Model\Config;
use Lof\Gdpr\Model\Entity\SourceProvider\ModifierInterface;

final class GuestModifier implements ModifierInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function apply(Collection $collection, Filter $filter): void
    {
        $collection->addFieldToFilter(OrderInterface::CUSTOMER_ID, ['null' => true]);
        $collection->addFieldToFilter(OrderInterface::CUSTOMER_IS_GUEST, ['eq' => 1]);
        $collection->addFieldToFilter(OrderInterface::STATE, ['in' => $this->config->getAllowedStatesToErase()]);
    }
}
