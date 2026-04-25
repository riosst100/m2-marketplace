<?php
namespace Lof\MarketPlace\Controller\Marketplace\Shipping;

class Deactivate extends \Magento\Framework\App\Action\Action
{
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    protected $_frontendUrl;
    protected $session;
    protected $resultPageFactory;
    protected $sellerFactory;
    protected $_actionFlag;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_frontendUrl     = $frontendUrl;
        $this->_actionFlag      = $context->getActionFlag();
        $this->sellerFactory    = $sellerFactory;
        $this->session          = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function getFrontendUrl($route = '', $params = [])
    {
        return $this->_frontendUrl->getUrl($route, $params);
    }

    protected function _redirectUrl($url)
    {
        $this->getResponse()->setRedirect($url);
        $this->session->setIsUrlNotice(
            $this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED)
        );
    }

    public function execute()
    {
        $customerSession = $this->session;

        /** @var \Lof\MarketPlace\Helper\Data $helper */
        $helper = $this->_objectManager->get(\Lof\MarketPlace\Helper\Data::class);

        if (!$customerSession->isLoggedIn()) {
            $this->_redirectUrl('catalog/dashboard');
            return;
        }

        $section = 'shipping';
        $groups = [];

        try {
            $code = $this->getRequest()->getParam('id');
            $groups[$code]['active'] = 0;

            // Save shipping configuration
            $sellerId = (int)$helper->getSellerId();
            $helper->saveShippingData($section, $groups, $sellerId);

            // Update Seller Progress Step
            $customerId = $customerSession->getId();
            $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');            
            $seller->save();

            $this->messageManager->addSuccessMessage(__('The shipping method has been deactivated.'));
            $this->_redirect('*/*/methods');
            return;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/methods');
            return;
        }
    }
}
