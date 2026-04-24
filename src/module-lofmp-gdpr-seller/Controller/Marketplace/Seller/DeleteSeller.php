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
/**
 * Class Account
 *
 * @package Lofmp\GdprSeller\Controller\Marketplace\Seller
 */
class DeleteSeller extends AbstractAccount
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var HelperSeller
     */
    private $helperSeller;

    /**
     * @var Seller
     */
    private $seller;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param HelperSeller $helperSeller
     * @param Seller $seller
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $registry,
        LoggerInterface $logger,
        HelperSeller $helperSeller,
        Seller $seller,
        Data $helper
    ) {
        $this->_customerSession    = $customerSession;
        $this->registry            = $registry;
        $this->logger              = $logger;
        $this->helperSeller        = $helperSeller;
        $this->_helper             = $helper;
        $this->seller              = $seller;
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
        if (!$this->_helper->allowDeleteSeller() || !$this->_customerSession->isLoggedIn() || !$this->canDeleteSeller()) {
            $this->messageManager->addErrorMessage(__('Permission denied.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        try {

            $sellerId = $this->helperSeller->getSellerId();
            if ($sellerId) {
                $this->seller->load($sellerId)->delete();
            }
            $resultRedirect->setPath('/customer/account/');
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something wrong while deleting your seller profile. Please contact the store owner.'));
            $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }

    /**
     * @return array|mixed
     */
    public function canDeleteSeller() {
        return $this->_helper->getConfigGeneral('allow_delete_seller');
    }
}
