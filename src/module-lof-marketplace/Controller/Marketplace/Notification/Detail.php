<?php
namespace Lof\MarketPlace\Controller\Marketplace\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Lof\MarketPlace\Model\ResourceModel\RabbitmqImportDbNotificationDetail\CollectionFactory as DetailCollectionFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Lof\MarketPlace\Model\ResourceModel\RabbitmqImportNotification;
use Lof\MarketPlace\Model\RabbitmqImportNotificationFactory;

class Detail extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DetailCollectionFactory
     */
    protected $detailCollectionFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    protected $notificationFactory;
    protected $notificationResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DetailCollectionFactory $detailCollectionFactory,
        RedirectFactory $resultRedirectFactory,
        RabbitmqImportNotificationFactory $notificationFactory,
        RabbitmqImportNotification $notificationResource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->detailCollectionFactory = $detailCollectionFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->notificationFactory = $notificationFactory;
        $this->notificationResource = $notificationResource;
    }

    public function execute()
    {
        $notifId = (int) $this->getRequest()->getParam('id');
        if (!$notifId) {
            $this->messageManager->addErrorMessage(__('Invalid notification ID.'));
            return $this->resultRedirectFactory->create()->setPath('catalog/notification/index');
        }

        $collection = $this->detailCollectionFactory->create()
            ->addFieldToFilter('notif_id', $notifId);

        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('No details found for this notification.'));
            return $this->resultRedirectFactory->create()->setPath('catalog/notification/index');
        }

        if ($notifId) {
            $notif = $this->notificationFactory->create();
            $this->notificationResource->load($notif, $notifId);
            if ($notif->getId()) {
                $notif->setIsRead(1);
                $this->notificationResource->save($notif);
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Import Notification Detail'));

        return $resultPage;
    }
}
