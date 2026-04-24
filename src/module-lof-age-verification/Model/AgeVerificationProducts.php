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

namespace Lof\AgeVerification\Model;

use Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface;
use Lof\AgeVerification\Api\Data\AgeVerificationProductsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class AgeVerificationProducts extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'lof_ageverification_products';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AgeVerificationProductsInterfaceFactory
     */
    protected $ageverificationproductsDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AgeVerificationProductsInterfaceFactory $ageverificationproductsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts $resource
     * @param \Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AgeVerificationProductsInterfaceFactory $ageverificationproductsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts $resource,
        \Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts\Collection $resourceCollection,
        array $data = []
    ) {
        $this->ageverificationproductsDataFactory = $ageverificationproductsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ageverificationproducts model with ageverificationproducts data
     * @return AgeVerificationProductsInterface
     */
    public function getDataModel()
    {
        $ageverificationproductsData = $this->getData();

        $ageverificationproductsDataObject = $this->ageverificationproductsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ageverificationproductsDataObject,
            $ageverificationproductsData,
            AgeVerificationProductsInterface::class
        );

        return $ageverificationproductsDataObject;
    }

    /**
     * @param $product
     * @return $this
     */
    public function addAgeVerificationToProduct($product)
    {
        $this->getResource()->addAgeVerificationToProduct($product);
        return $this;
    }
}
