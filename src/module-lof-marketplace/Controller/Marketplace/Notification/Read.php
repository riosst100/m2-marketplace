<?php
namespace Lof\MarketPlace\Controller\Marketplace\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Lof\MarketPlace\Model\ResourceModel\RabbitmqImportNotification;
use Lof\MarketPlace\Model\RabbitmqImportNotificationFactory;

class Read extends Action
{
    protected $notificationFactory;
    protected $notificationResource;

    public function __construct(
        Context $context,
        RabbitmqImportNotificationFactory $notificationFactory,
        RabbitmqImportNotification $notificationResource
    ) {
        parent::__construct($context);
        $this->notificationFactory = $notificationFactory;
        $this->notificationResource = $notificationResource;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $notif = $this->notificationFactory->create();
            $this->notificationResource->load($notif, $id);
            if ($notif->getId()) {
                $notif->setIsRead(1);
                $this->notificationResource->save($notif);
            }
        }
        return $this->_redirect('catalog/notification/index');
    }
}
