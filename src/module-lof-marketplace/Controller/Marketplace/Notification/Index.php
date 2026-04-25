<?php
namespace Lof\MarketPlace\Controller\Marketplace\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Lof\MarketPlace\Helper\Data as MarketplaceHelper;

class Index extends Action
{
    protected $resultPageFactory;
    protected $marketplaceHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MarketplaceHelper $marketplaceHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    public function execute()
    {
        // Only allow logged-in seller
        if (!$this->marketplaceHelper->getSellerId()) {
            return $this->_redirect('lofmarketplace/seller/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Notifications'));
        return $resultPage;
    }
}
