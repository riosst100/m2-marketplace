<?php

namespace Lofmp\Faq\Model\Ui\Component\DataProvider;

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
        // dd($this->collection->getData());
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $this->collection->getData(),
        ];
    }
}
