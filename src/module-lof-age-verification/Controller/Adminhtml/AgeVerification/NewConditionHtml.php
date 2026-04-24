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
 * @package    Lof_AgeVerification
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\AgeVerification\Controller\Adminhtml\AgeVerification;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Magento\Backend\App\Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Lof\AgeVerification\Model\ProductPurchaseFactory
     */
    protected $_productPurchaseFactory;

    /**
     * NewConditionHtml constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory
     */
    public function __construct(
        Context $context,
        \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory
    ) {
        parent::__construct($context);
        $this->_productPurchaseFactory = $productPurchaseFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $productPurchase = $this->_productPurchaseFactory->create();
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)->setRule($productPurchase)
            ->setPrefix('purchase_conditions');


        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
