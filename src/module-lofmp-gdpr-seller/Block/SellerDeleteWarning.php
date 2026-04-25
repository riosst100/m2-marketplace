<?php
namespace Lofmp\GdprSeller\Block;

use Magento\Framework\View\Element\Template;
use Lof\MarketPlace\Helper\Data as SellerHelper;
use Lof\MarketPlace\Model\SellerFactory;

class SellerDeleteWarning extends Template
{
    protected $sellerHelper;
    protected $sellerFactory;

    public function __construct(
        Template\Context $context,
        SellerHelper $sellerHelper,
        SellerFactory $sellerFactory,
        array $data = []
    ) {
        $this->sellerHelper = $sellerHelper;
        $this->sellerFactory = $sellerFactory;
        parent::__construct($context, $data);
    }

    public function getDeleteWarning()
    {
        $sellerId = $this->sellerHelper->getSellerId();
        if (!$sellerId) {
            return false;
        }

        $seller = $this->sellerFactory->create()->load($sellerId);

        if (!$seller->getId()) {
            return false;
        }

        if ((int)$seller->getIsDeleteRequest() !== 1) {
            return false;
        }

        $deleteRequestAt = $seller->getDeleteRequestAt();
        if (!$deleteRequestAt) {
            return false;
        }

        $daysSetting =  (int) $this->_scopeConfig->getValue(
            'gdpr/general/delete_days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $requestTime = strtotime($deleteRequestAt);
        $deleteTime = strtotime("+{$daysSetting} days", $requestTime);

        $remaining = ceil(($deleteTime - time()) / 86400);
        if ($remaining < 0) {
            $remaining = 0;
        }

        return $remaining;
    }
}
