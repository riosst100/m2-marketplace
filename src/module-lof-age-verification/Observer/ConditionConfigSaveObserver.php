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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class ConditionConfigSaveObserver implements ObserverInterface
{
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var \Lof\AgeVerification\Model\ProductPurchaseFactory
     */
    protected $productPurchaseFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Lof\AgeVerification\Helper\Data
     */
    private $_helperData;

    /**
     * ProductConditionsConfigSaveObserver constructor.
     * @param \Lof\AgeVerification\Helper\Data $helperData
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory
     * @param \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory
     * @param Json|null $serializer
     */
    public function __construct(
        \Lof\AgeVerification\Helper\Data $helperData,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory,
        \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_helperData = $helperData;
        $this->productPurchaseFactory = $productPurchaseFactory;
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->_configFactory = $configFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            Json::class
        );
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return;
        }

        $configData = $observer->getData('configData');
        $request = $observer->getRequest();
        $rule = $request->getParam('rule');
        $section = $request->getParam('section');
        if (($section == 'lofageverification')) {
            if (isset($rule['conditions'])) {
                $catalogRule = $this->catalogRuleFactory->create();
                $productConditions['conditions'] = $rule['conditions'];
                $catalogRule->loadPost($productConditions);
                $productConditionConfig = $this->serializer->serialize($catalogRule->getConditions()->asArray());
            }

            if (isset($rule['purchase_conditions'])) {
                $productPurchase = $this->productPurchaseFactory->create();
                $purchaseConditions['conditions'] = $rule['purchase_conditions'];
                $productPurchase->loadPost($purchaseConditions);
                $purchaseConditionConfig = $this->serializer->serialize(
                    $productPurchase->getConditions()->asArray()
                );
            }

            $configData['groups']['verification_configuration']['groups']['product_detail']['fields']
            ['conditions']['value'] = $productConditionConfig;
            $configData['groups']['verification_configuration']['groups']['product_purchase']['fields']
            ['purchase_conditions']['value'] = $purchaseConditionConfig;
            unset($configData['groups']['design']);
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();
        }
    }
}
