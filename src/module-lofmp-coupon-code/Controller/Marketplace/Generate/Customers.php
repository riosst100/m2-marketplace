<?php
namespace Lofmp\CouponCode\Controller\Marketplace\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class Customers extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    protected $sellerFactory;
    protected $session;
    protected $currentSeller = null;

    public function __construct(
        Context $context,
        CustomerCollectionFactory $customerCollectionFactory,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resource,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
        $this->sellerFactory = $sellerFactory;
        $this->session = $customerSession;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Customers: execute');

        $result = $this->resultJsonFactory->create();
        try {
            $collection = $this->customerCollectionFactory->create();
            $collection->addAttributeToSelect(['email', 'firstname', 'lastname']);

            // Optional: support seller_id param to filter customers who ordered items from a seller
            $sellerId = $this->getSellerId();
            $logger->info('Seller ID: ' . $sellerId);
            if ($sellerId) {
                $conn = $this->resource->getConnection();
                $soTable  = $conn->getTableName('sales_order');
                $soiTable = $conn->getTableName('sales_order_item');

                // $collection->getSelect()
                //     ->join(
                //         ['so' => $soTable],
                //         'so.customer_id = e.entity_id',
                //         []
                //     )
                //     ->join(
                //         ['soi' => $soiTable],
                //         // adjust column name if your seller id column differs
                //         'soi.order_id = so.entity_id AND soi.lof_seller_id = ' . (int)$sellerId,
                //         []
                //     )
                //     ->group('e.entity_id');
                $collection->getSelect()
                ->join(
                    ['so' => 'sales_order'], // Alias for sales_order table
                    'so.customer_id = e.entity_id', // Join condition (customer to orders)
                    [] // Columns to select from sales_order (empty because we only need the join)
                )
                ->join(
                    ['soi' => 'sales_order_item'], // Alias for sales_order_item table
                    'soi.order_id = so.entity_id AND soi.lof_seller_id = ' . $this->getSellerId(), // Join condition (orders to items and filter by seller_id)
                    [] // Columns to select from sales_order_item (empty because we only need the join)
                )
                ->columns(['transaction_count' => new \Zend_Db_Expr('COUNT(DISTINCT so.entity_id)')]) // Add total orders count
                ->group('e.entity_id'); // Group by customer ID
            }
            $logger->info('SQL Query Cust: ' . $collection->getSelect()->__toString());
            // limit page size for performance
            // $collection->setPageSize(1000);

            $options = [];
            foreach ($collection as $customer) {
                $email = (string)$customer->getEmail();
                $name = trim($customer->getFirstname() . ' ' . $customer->getLastname());
                $label = $email;
                if ($name) {
                    $label = $email . ' (' . $name . ')';
                }
                $options[] = ['value' => $email, 'label' => $label];
            }

            return $result->setData($options);
        } catch (\Exception $e) {
            $logger->err('Error in Customers: ' . $e->getMessage());
            return $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @return bool|int|mixed
     */
    public function getSellerId()
    {
        if ($this->session->isLoggedIn()) {
            $seller = $this->getSeller();
            $sellerId = $seller ? $seller->getSellerId() : 0;
            return $sellerId;
        }

        return false;
    }

    /**
     * Get Seller by customer
     *
     * @return Object
     */
    public function getSeller()
    {
        if (!$this->currentSeller && $this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $this->currentSeller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        }
        return $this->currentSeller;
    }

    /**
     * ACL check - change resource id to match your module ACL if different
     *
     * @return bool
     */
    // protected function _isAllowed()
    // {
    //     // adjust ACL resource id if your module defines a different one
    //     return $this->_authorization->isAllowed('Lofmp_CouponCode::generate');
    // }
}
