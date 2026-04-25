<?php
namespace Lofmp\GdprSeller\Controller\Marketplace\Seller;

use Exception;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Lof\Gdpr\Helper\Data;
use Psr\Log\LoggerInterface;
use Lof\MarketPlace\Helper\Data as HelperSeller;
use Lof\MarketPlace\Model\Seller;
use Lof\MarketPlace\Model\ResourceModel\Order\CollectionFactory as SellerOrderCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class DeleteSeller extends AbstractAccount
{
    protected $_customerSession;
    protected $registry;
    protected $logger;
    protected $_helper;
    protected $helperSeller;
    protected $seller;
    protected $sellerOrderCollectionFactory;
    protected $orderRepository;

    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $registry,
        LoggerInterface $logger,
        HelperSeller $helperSeller,
        Seller $seller,
        Data $helper,
        SellerOrderCollectionFactory $sellerOrderCollectionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_customerSession              = $customerSession;
        $this->registry                      = $registry;
        $this->logger                        = $logger;
        $this->helperSeller                  = $helperSeller;
        $this->_helper                       = $helper;
        $this->seller                        = $seller;
        $this->sellerOrderCollectionFactory  = $sellerOrderCollectionFactory;
        $this->orderRepository               = $orderRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->_helper->allowDeleteSeller() ||
            !$this->_customerSession->isLoggedIn() ||
            !$this->canDeleteSeller()
        ) {
            $this->messageManager->addErrorMessage(__('Permission denied.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $sellerId = $this->helperSeller->getSellerId();

            if ($sellerId) {

                // -----------------------------------------------------
                // Check if seller has pending / processing orders
                // -----------------------------------------------------
                if (!$this->canSellerBeDeleted($sellerId)) {
                    $this->messageManager->addErrorMessage(
                        __('You cannot request account deletion because you still have active or incomplete orders.')
                    );
                    return $resultRedirect->setPath('*/*/');
                }

                // -----------------------------------------------------
                // Submit delete request
                // -----------------------------------------------------
                $seller = $this->seller->load($sellerId);
                $seller->setData('is_delete_request', 1);
                $seller->setData('delete_request_at', date('Y-m-d H:i:s'));
                $seller->save();

                $deleteDays = (int) $this->_helper->getConfigGeneral('delete_days');

                $this->messageManager->addSuccessMessage(__(
                    'Your seller deletion request has been submitted. Your account will be permanently deleted in %1 day(s).',
                    $deleteDays
                ));
            }

            return $resultRedirect->setPath('/customer/account/');

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong while submitting your deletion request.'));
            return $resultRedirect->setPath('*/*/');
        }
    }

    public function canDeleteSeller()
    {
        return $this->_helper->getConfigGeneral('allow_delete_seller');
    }

    /**
     * 🔍 Check if seller has incomplete orders
     */
    private function canSellerBeDeleted($sellerId)
    {
        // Order statuses that allow deletion
        $allowedStatuses = ['complete', 'closed', 'canceled'];

        $collection = $this->sellerOrderCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId);

        foreach ($collection as $sellerOrder) {
            try {
                $order = $this->orderRepository->get($sellerOrder->getOrderId());
                if (!in_array($order->getStatus(), $allowedStatuses)) {
                    return false; // Block deletion
                }
            } catch (\Exception $e) {
                // If order cannot be loaded → treat it as active to be safe
                return false;
            }
        }

        return true; // All orders complete/closed/cancelled
    }
}
