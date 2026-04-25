<?php
namespace Lof\MarketPlace\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RabbitmqImportNotification extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('lof_marketplace_rabbitmq_import_notification', 'notif_id');
    }
}
