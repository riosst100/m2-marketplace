<?php
namespace Lof\MarketPlace\Model\ResourceModel\RabbitmqImportNotification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Lof\MarketPlace\Model\RabbitmqImportNotification::class,
            \Lof\MarketPlace\Model\ResourceModel\RabbitmqImportNotification::class
        );
    }
}
