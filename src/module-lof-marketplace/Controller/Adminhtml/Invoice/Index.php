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

namespace Lof\MarketPlace\Controller\Adminhtml\Invoice;

class Index extends \Lof\MarketPlace\Controller\Adminhtml\Invoice
{
    /**
     * Invoice list action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu("TCGCollective_MarketPlace::seller_invoices");
        $resultPage->getConfig()->getTitle()->prepend(__('Seller | Invoices'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Invoices'), __('Invoices'));
        $resultPage->addBreadcrumb(__('Seller'), __('Seller'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TCGCollective_MarketPlace::seller_invoices');
    }
}
