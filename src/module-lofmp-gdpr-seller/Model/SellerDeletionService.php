<?php
namespace Lofmp\GdprSeller\Model;

use Lof\Gdpr\Helper\Data as GdprHelper;
use Lof\MarketPlace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Psr\Log\LoggerInterface;

class SellerDeletionService
{
    protected $sellerCollectionFactory;
    protected $helper;
    protected $logger;

    public function __construct(
        SellerCollectionFactory $sellerCollectionFactory,
        GdprHelper $helper,
        LoggerInterface $logger
    ) {
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Run deletion logic
     */
    public function run()
    {
        $deleteDays = (int) $this->helper->getConfigGeneral('delete_days');
        $dateLimit = date('Y-m-d H:i:s', strtotime("-{$deleteDays} days"));

        $collection = $this->sellerCollectionFactory->create();
        $collection->addFieldToFilter('is_delete_request', 1);
        $collection->addFieldToFilter('delete_request_at', ['lteq' => $dateLimit]);

        $count = 0;

        foreach ($collection as $seller) {
            try {
                $seller->delete();
                $count++;
            } catch (\Exception $e) {
                $this->logger->critical("Seller delete failed ID {$seller->getId()}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
