<?php
namespace Lof\MarketPlace\Model;

use Magento\Framework\Model\AbstractModel;

class RabbitmqImportDbNotificationDetail extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Lof\MarketPlace\Model\ResourceModel\RabbitmqImportDbNotificationDetail::class);
    }
}
