<?php
namespace Lof\MarketPlace\Controller\Marketplace\Shipping;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Lof\MarketPlace\Model\SellerFactory;
use Lof\MarketPlace\Helper\Data as MarketplaceHelper;
use Lof\MarketPlace\Model\Config\Shipping\Methods\AbstractModel;

class SaveActivate extends Action
{
    protected $session;
    protected $sellerFactory;
    protected $helper;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        SellerFactory $sellerFactory,
        MarketplaceHelper $helper,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->session->isLoggedIn()) {
            $this->_redirectUrl('catalog/dashboard');
            return;
        }

        $method = $this->getRequest()->getParam('method'); // rajaongkir|table_rate|flat_rate

        if (!$method) {
            $this->messageManager->addErrorMessage(__('Invalid shipping method.'));
            return $resultRedirect->setPath('*/*/methods');
        }
        
        $sellerId = (int)$this->helper->getSellerId();
        $seller = $this->helper->getSellerById($sellerId);
        
        try {
            switch ($method) {

                case 'rajaongkir':
                    $apiKey = $this->getRequest()->getPost('api_key');

                    if (!$apiKey) {
                        $this->messageManager->addErrorMessage(__('API Key is required.'));
                        return $resultRedirect->setPath('*/*/methods');
                    }

                    $this->helper->saveShippingData(
                        AbstractModel::SHIPPING_SECTION,
                        ['rajaongkir' => ['api_key' => $apiKey, 'active' => 1]],
                        $sellerId
                    );

                    $seller->setRegistrationStep('finish');
                    $seller->setStatus(1);
                    $seller->save();

                    $this->messageManager->addSuccessMessage(__('RajaOngkir activated successfully.'));
                    return $resultRedirect->setPath('*/*/methods');


                case 'lofmptablerateshipping':
                    $this->helper->saveShippingData(
                        AbstractModel::SHIPPING_SECTION,
                        ['lofmptablerateshipping' => ['active' => 1]],
                        $sellerId
                    );

                    return $resultRedirect->setPath('lofmptablerateshipping/shipping/view');


                case 'lofmpflatrateshipping':
                    $this->helper->saveShippingData(
                        AbstractModel::SHIPPING_SECTION,
                        ['lofmpflatrateshipping' => ['active' => 1]],
                        $sellerId
                    );

                    return $resultRedirect->setPath('lofmpflatrateshipping/shipping/view');
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/methods');
    }
}
