<?php

namespace Lof\MarketPlace\Controller\Marketplace\Product;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $createProductHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \TCGCollective\MarketPlace\Helper\CreateProductHelper $createProductHelper
    ) {
        parent::__construct($context);
        $this->createProductHelper = $createProductHelper;
    }

    public function execute()
    {
        $myProductsUrl = $this->createProductHelper->processCreateProduct();
        // dd('ok');
        return $this->_redirect($myProductsUrl);
    }
}
