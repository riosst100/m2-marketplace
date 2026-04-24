<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\ResourceModel\EraseEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lof\Gdpr\Api\Data\EraseEntityInterface;
use Lof\Gdpr\Model\EraseEntity;
use Lof\Gdpr\Model\ResourceModel\EraseEntity as EraseEntityResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(EraseEntity::class, EraseEntityResourceModel::class);
        $this->_setIdFieldName(EraseEntityInterface::ID);
    }
}
