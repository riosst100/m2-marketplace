<?php
namespace Lof\MarketPlace\Block\Seller;

use Magento\Framework\View\Element\Template;
use Lof\MarketPlace\Helper\Data as MarketplaceHelper;
use CoreMarketplace\MarketPlace\Model\ResourceModel\SellerNotifications\CollectionFactory as NotificationCollectionFactory;

class Notification extends Template
{
    /**
     * @var NotificationCollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * Cached notification collection
     */
    protected $notificationCollection = null;

    public function __construct(
        Template\Context $context,
        NotificationCollectionFactory $notificationCollectionFactory,
        MarketplaceHelper $marketplaceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->marketplaceHelper = $marketplaceHelper;
    }


    public function getNotifications()
    {
        if ($this->notificationCollection === null) {
            $sellerId = $this->marketplaceHelper->getSellerId();
            $collection = $this->notificationCollectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->setOrder('created_at', 'DESC');
            $this->notificationCollection = $collection;
        }
        return $this->notificationCollection;
    }

    /**
     * Count unread notifications
     *
     * @return int
     */
    public function getUnreadCount()
    {
        $sellerId = $this->marketplaceHelper->getSellerId();
        return $this->notificationCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('is_read', 0)
            ->count();
    }

    /**
     * Get mark-as-read URL
     */
    public function getMarkAsReadUrl($notifId)
    {
        return $this->getUrl('catalog/notification/read', ['id' => $notifId]);
    }

    /**
     * Prepare pagination
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $collection = $this->getNotifications();
        if ($collection && $collection->getSize()) {
            if (!$this->getChildBlock('notification.pager')) {
                $pager = $this->getLayout()->createBlock(
                    \Magento\Theme\Block\Html\Pager::class,
                    $this->getNameInLayout() . '.pager'
                )->setTemplate('Magento_Theme::html/pager.phtml')
                ->setCollection($collection);

                $pager->setAvailableLimit([5 => 5, 10 => 10, 20 => 20]);
                $pager->setShowPerPage(true);
                $this->setChild('pager', $pager);
            }
        }

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
