<?php

namespace Lofmp\Quickrfq\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\UrlInterface;

class Product extends Column
{
    protected $productRepository;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['product_sku'])) {
                continue;
            }

            try {
                $product = $this->productRepository->get($item['product_sku']);
                $url = $this->urlBuilder->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getId()]
                );

                $item[$fieldName] =
                    '<a href="' . $url . '" target="_blank">'
                    . $product->getName()
                    . '</a>';
            } catch (\Exception $e) {
                $item[$fieldName] = $item['product_sku'];
            }
        }

        return $dataSource;
    }
}
