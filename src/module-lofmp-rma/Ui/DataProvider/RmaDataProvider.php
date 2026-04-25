<?php
namespace Lofmp\Rma\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Lofmp\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Lofmp\Rma\Helper\Data as RmaHelper;
use Lofmp\Rma\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

class RmaDataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $rmaHelper;
    protected $helper;
    protected $itemCollectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RmaHelper $rmaHelper,
        \Lof\MarketPlace\Helper\Data $helper,
        ItemCollectionFactory $itemCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->rmaHelper = $rmaHelper;
        $this->helper = $helper;
        $this->itemCollectionFactory = $itemCollectionFactory;

        $this->collection->addFilterToMap('order_increment_id', 'order.increment_id');
        $this->collection->addFilterToMap('customer_name', 'customer.firstname');
        // $this->collection->addFilterToMap('customer_lastname', 'customer.lastname');
        $this->collection->addFilterToMap('customer_email', 'customer.email');
        $this->collection->addFilterToMap('status_name', 'status.name');

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $sellerId = $this->helper->getSellerId();
        $sellerRmaList = $this->collection
            ->addFieldToFilter('seller_id', $sellerId)
            ->setOrder('created_at', 'DESC')
            ->getData();

        $items = [];
        foreach ($sellerRmaList as $rma) {
            $itemCollection = $this->itemCollectionFactory->create()
                ->addFieldToFilter('rma_id', $rma['rma_id']);

            $totalQty = 0;
            foreach ($itemCollection as $item) {
                $totalQty += (int)$item->getQtyRequested();
                // OR use ->getQtyReturned()
            }
            // dd($rma);
            $rma['total_qty'] = $totalQty;
            $rma['customer_name'] = trim(($rma['customer_firstname'] ?? '') . ' ' . ($rma['customer_lastname'] ?? ''));
            // $rma['status_name'] = $this->rmaHelper->getStatusName($rma['status_id'] ?? '');
            $items[] = $rma;
        }
        
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }
}
