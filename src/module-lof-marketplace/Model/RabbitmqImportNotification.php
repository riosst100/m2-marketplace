<?php
namespace Lof\MarketPlace\Model;

use Magento\Framework\Model\AbstractModel;

class RabbitmqImportNotification extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Lof\MarketPlace\Model\ResourceModel\RabbitmqImportNotification::class);
    }
}
