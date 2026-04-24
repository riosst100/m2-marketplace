<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\ResourceModel\ActionEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lof\Gdpr\Api\Data\ActionEntityInterface;
use Lof\Gdpr\Model\ActionEntity;
use Lof\Gdpr\Model\ResourceModel\ActionEntity as ActionEntityResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(ActionEntity::class, ActionEntityResourceModel::class);
        $this->_setIdFieldName(ActionEntityInterface::ID);
    }
}
