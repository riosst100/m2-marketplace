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

namespace Lof\AgeVerification\Observer;

use Lof\AgeVerification\Ui\DataProvider\Product\Form\Modifier\AgeVerification;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @var \Lof\AgeVerification\Model\AgeVerificationProductsFactory
     */
    private $ageVerificationProductsFactory;

    /**
     * CatalogProductSaveAfterObserver constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Lof\AgeVerification\Model\AgeVerificationProductsFactory $ageVerificationProductsFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Lof\AgeVerification\Model\AgeVerificationProductsFactory $ageVerificationProductsFactory
    ) {
        $this->_messageManager = $messageManager;
        $this->ageVerificationProductsFactory = $ageVerificationProductsFactory;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        $params = $this->request->getParams();
        if (isset($params[AbstractModifier::DATA_SOURCE_DEFAULT][AgeVerification::DATA_FIELDSET])
            && $params[AbstractModifier::DATA_SOURCE_DEFAULT][AgeVerification::DATA_FIELDSET]) {
            $ageVerificationProduct = $this->ageVerificationProductsFactory->create();
            try {
                $data = $params[AbstractModifier::DATA_SOURCE_DEFAULT][AgeVerification::DATA_FIELDSET];
                $data['product_id'] = $productId;
                $ageVerificationProduct->addData($data)->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addErrorMessage($e->getMessage());
            }
        }
    }
}
