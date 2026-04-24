<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Api;

use Magento\Framework\Exception\LocalizedException;
use Lof\Gdpr\Api\Data\ActionContextInterface;
use Lof\Gdpr\Api\Data\ActionResultInterface;

/**
 * @api
 */
interface ActionInterface
{
    /**
     * @param ActionContextInterface $actionContext
     * @return ActionResultInterface
     * @throws LocalizedException
     */
    public function execute(ActionContextInterface $actionContext): ActionResultInterface;
}
