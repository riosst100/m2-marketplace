<?php

namespace Lofmp\Faq\Controller\Marketplace\FaqQuestion;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lofmp\Faq\Model\ResourceModel\Question\CollectionFactory;

class Grid extends Action
{
    protected $resultJsonFactory;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $collection = $this->collectionFactory->create();

        $items = [];
        foreach ($collection as $item) {
            $items[] = $item->getData();
        }

        return $this->resultJsonFactory->create()->setData([
            'totalRecords' => $collection->getSize(),
            'items' => $items
        ]);
    }
}
