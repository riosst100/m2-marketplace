<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\ResourceModel\ExportEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Model\ExportEntity;
use Lof\Gdpr\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(ExportEntity::class, ExportEntityResourceModel::class);
        $this->_setIdFieldName(ExportEntityInterface::ID);
    }
}
