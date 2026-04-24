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

namespace Lof\AgeVerification\Ui\DataProvider\Product\Form\Modifier;

use Lof\AgeVerification\Helper\Data;
use Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts\CollectionFactory as AgeVerificationProductsCollectionFactory;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface;
use Magento\Framework\App\RequestInterface;

class AgeVerification extends AbstractModifier
{
    const DATA_FIELDSET = 'age_verification';

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var AgeVerificationProductsCollectionFactory
     */
    private $ageVerificationProductsCollectionFactory;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * AgeVerification constructor.
     * @param RequestInterface $request
     * @param LocatorInterface $locator
     * @param AgeVerificationProductsCollectionFactory $ageVerificationProductsCollectionFactory
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request,
        LocatorInterface $locator,
        AgeVerificationProductsCollectionFactory $ageVerificationProductsCollectionFactory
    ) {
        $this->helperData = $helperData;
        $this->locator = $locator;
        $this->_request = $request;
        $this->ageVerificationProductsCollectionFactory = $ageVerificationProductsCollectionFactory;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $ageVerificationProduct = $this->ageVerificationProductsCollectionFactory->create()
            ->addFieldToFilter('product_id', $modelId)->getFirstItem();
        $ageVerificationProductData = $modelId ? $this->getData($ageVerificationProduct) : [];
        if (!empty($ageVerificationProductData)) {
            $data[$modelId][self::DATA_SOURCE_DEFAULT][self::DATA_FIELDSET] = $ageVerificationProductData;
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->helperData->isEnabled()) {
            $meta[self::DATA_FIELDSET]['arguments']['data']['config'] = [
                'disabled' => true,
                'visible' => false
            ];
        }

        return $meta;
    }

    /**
     * @param $ageVerificationProduct
     * @return mixed
     */
    private function getData($ageVerificationProduct)
    {
        $result = $ageVerificationProduct->getData();
        $result[AgeVerificationProductsInterface::VERIFY_AGE] = $ageVerificationProduct->getVerifyAge();
        $result[AgeVerificationProductsInterface::CUSTOM_ID] = $ageVerificationProduct->getCustomId();
        $result[AgeVerificationProductsInterface::PREVENT_PURCHASE] = $ageVerificationProduct->getPreventPurchase();
        $result[AgeVerificationProductsInterface::PREVENT_VIEW] = $ageVerificationProduct->getPreventView();
        $result[AgeVerificationProductsInterface::PRODUCT_ID] = $ageVerificationProduct->getProductId();
        $result[AgeVerificationProductsInterface::USE_CUSTOM] = $ageVerificationProduct->getUseCustom();
        return $result;
    }
}
