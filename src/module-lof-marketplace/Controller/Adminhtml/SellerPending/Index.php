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

namespace Lof\MarketPlace\Controller\Adminhtml\SellerPending;

class Index extends \Lof\MarketPlace\Controller\Adminhtml\SellerPending
{
    /**
     * Seller list action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu("CoreMarketplace_MarketPlace::seller_verification");
        $resultPage->getConfig()->getTitle()->prepend(__('Seller Verification'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Seller Verification'), __('Seller Verification'));
        $resultPage->addBreadcrumb(__('Seller Verification'), __('Seller Verification'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CoreMarketplace_MarketPlace::seller_verification');
    }
}
