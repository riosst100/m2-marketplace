<?php
namespace Lof\MarketPlace\Block\Seller\Notification;

use Magento\Framework\View\Element\Template;

class Detail extends Template
{
    protected $rabbitmqQueuesFactory;
    protected $helper;
    protected $mappingHelper;

    public function __construct(
        Template\Context $context,
        \CoreMarketplace\MarketPlace\Model\RabbitmqQueuesFactory $rabbitmqQueuesFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \CoreMarketplace\ProductAttributesLink\Helper\Data $mappingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rabbitmqQueuesFactory = $rabbitmqQueuesFactory;
        $this->helper = $helper;
        $this->mappingHelper = $mappingHelper;
    }

    public function getQueueStatusLabel($statusCode) 
    {
        if ($statusCode == "pending") {
            return 'In Queue';
        }

        if ($statusCode == "processing") {
            return 'On Process';
        }

        if ($statusCode == "done") {
            return 'Completed';
        }

        return '';
    }

    public function getAttributeLabel($attrCode) 
    {
        return $attrCode;
    }

    public function getCategoryNameById($categoryId) 
    {
        if ($categoryId) {
            $categoryData = $this->mappingHelper->getCachedCategoryById($categoryId);
            if ($categoryData) {
                return $categoryData['name'];
            }
        }

        return 'Unknown';
    }

    public function getDetails()
    {
        $sellerId = $this->helper->getSellerId();

        $collection = $this->rabbitmqQueuesFactory->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $sellerId)
        ->setOrder('created_at', 'DESC');

        return $collection;
    }

    public function getBackUrl()
    {
        return $this->getUrl('catalog/notification/index');
    }
}
