<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\MarketPlace\Controller\Marketplace\Shipping;

use Magento\Framework\App\Action\Context;

class SaveAddress extends \Magento\Framework\App\Action\Action
{
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontendUrl;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
    protected $marketplaceHelper;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \TCGCollective\MarketPlace\Helper\Data $marketplaceHelper
    ) {
        parent::__construct($context);

        $this->_frontendUrl = $frontendUrl;
        $this->_actionFlag = $context->getActionFlag();
        $this->sellerFactory = $sellerFactory;
        $this->session = $customerSession;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string|null
     */
    public function getFrontendUrl($route = '', $params = [])
    {
        return $this->_frontendUrl->getUrl($route, $params);
    }

    /**
     * @param $url
     */
    protected function _redirectUrl($url)
    {
        $this->getResponse()->setRedirect($url);
        $this->session->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->shipFrom = $this->_objectManager->get(\TCGCollective\MarketPlace\Model\Config\Source\ShipFrom::class);

        // $this->countryCollectionFactory = $this->_objectManager->get(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class);
        // $collection = $this->countryCollectionFactory->create();
        // // $collection->addFieldToSelect(['country_id', 'iso2_code', 'iso3_code'])->load();
        // $countries = $collection->toOptionArray();

        // // $result = [];

        // // foreach ($countries as $index => $item) {
        // //     if (!empty($item['value'])) {
        // //         $result[] = [$index => $item['label']];
        // //     }
        // // }

        // // // var_export($result);

        // // dd($countries);
        // // dd($this->ship)

        $customerSession = $this->session;
        $helper = $this->_objectManager->get(\Lof\MarketPlace\Helper\Data::class);
        if (!$customerSession->isLoggedIn()) {
            $this->_redirectUrl('catalog/dashboard');
            return;
        }
        $section = $this->getRequest()->getParam('section', '');
        $groups = $this->getRequest()->getPost('groups', []);

        if (strlen($section) > 0 && count($groups) > 0) {
            $sellerId = (int)$helper->getSellerId();
            try {
                $customerId = $customerSession->getId();
                $status = $this->sellerFactory->create()->load($customerId, 'customer_id')->getStatus();
                $this->_objectManager->get(\Lof\MarketPlace\Helper\Data::class)
                    ->saveShippingData($section, $groups, $sellerId);
                if ($section == "shipping") {
                    $countryId = $groups['address']['country_id'] ?? null;

                    // $_directoryHelper->getCountriesWithOptionalZip(true)


                    // $_directoryHelper = $this->_objectManager->get(\Magento\Directory\Helper\Data::class);

                    // dd($_directoryHelper->getCountriesWithOptionalZip(true));
                    
                    $shipFromCountryId = $this->shipFrom->getOptionIdByCode($countryId);

                    // dd($shipFromCountryId);

                    // CUSTOM: Update seller products ship from
                    $this->marketplaceHelper->updateSellerProductsShipFrom($shipFromCountryId);
                    
                    $this->messageManager->addSuccessMessage(__('Ship From/Origin Address has been saved.'));
                } else {
                    $this->messageManager->addSuccessMessage(__('The Shipping Methods has been saved.'));
                }
                if ($status == 0) {
                    $this->_redirect('catalog/shipping/methods');    
                } else {
                    $this->_redirect('*/*/address');
                }                
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/address');
                return;
            }
        }
        $this->_redirect('*/*/address');
    }
}
