<?php
namespace Lof\MarketPlace\Controller\Marketplace\Queues;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Lof\MarketPlace\Helper\Data as MarketplaceHelper;

class Index extends Action
{
    protected $resultPageFactory;
    protected $marketplaceHelper;
    protected $resource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MarketplaceHelper $marketplaceHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->resource = $resource;
    }

    public function execute()
    {
        $sellerId = $this->marketplaceHelper->getSellerId();

        // Only allow logged-in seller
        if (!$sellerId) {
            return $this->_redirect('lofmarketplace/seller/login');
        }
        
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('marketplace_seller_notifications');

        $currentUrlPath = $this->getCurrentUrlPath();

        // dd($currentUrlPath);

        $sql = "UPDATE {$tableName} 
            SET is_read = 1 
            WHERE seller_id = {$sellerId} 
            AND (url_path = '{$currentUrlPath}')";

        $connection->query($sql);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Products In Queue'));
        return $resultPage;
    }

    public function getCurrentUrlPath() {
        // Ambil bagian-bagian route
        $routeName      = $this->getRequest()->getRouteName();       // marketplace
        $controllerName = $this->getRequest()->getControllerName();  // catalog
        $actionName     = $this->getRequest()->getActionName();      // queues

        // Tapi kalau kamu mau lebih akurat sesuai route:
        $fullPath = $routeName . '/' . $controllerName . '/' . $actionName;

        return $fullPath;
    }
}
