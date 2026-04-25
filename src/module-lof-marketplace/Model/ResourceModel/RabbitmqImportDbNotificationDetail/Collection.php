<?php
namespace Lof\MarketPlace\Model\ResourceModel\RabbitmqImportDbNotificationDetail;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Lof\MarketPlace\Model\RabbitmqImportDbNotificationDetail::class,
            \Lof\MarketPlace\Model\ResourceModel\RabbitmqImportDbNotificationDetail::class
        );
    }
}
