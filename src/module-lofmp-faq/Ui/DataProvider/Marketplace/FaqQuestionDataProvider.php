<?php

namespace Lofmp\Faq\Ui\DataProvider\Marketplace;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Lofmp\Faq\Model\ResourceModel\Question\CollectionFactory;

class FaqQuestionDataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $items = $this->collection->getItems();
        // dd($items);
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values(array_map(function ($item) {
                return $item->getData();
            }, $items))
        ];
    }
}
