<?php

namespace Lof\MarketPlace\Model\Queue;

class ProductImportDbConsumer
{
    protected $createProductHelper;

    public function __construct(
        \TCGCollective\MarketPlace\Helper\CreateProductHelper $createProductHelper
    ) {
        $this->createProductHelper = $createProductHelper;
    }

    public function process(string $message)
    {
        return $this->createProductHelper->processCreateProductRabbitMq($message);
    }
}
