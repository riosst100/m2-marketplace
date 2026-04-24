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

use Lof\AgeVerification\Api\AgeVerificationProductsRepositoryInterface;
use Lof\AgeVerification\Api\Data\AgeVerificationProductsInterfaceFactory;
use Lof\AgeVerification\Api\Data\AgeVerificationProductsSearchResultsInterfaceFactory;
use Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts as ResourceAgeVerificationProducts;
use Lof\AgeVerification\Model\ResourceModel\AgeVerificationProducts\CollectionFactory as AgeVerificationProductsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class AgeVerificationProductsRepository implements AgeVerificationProductsRepositoryInterface
{

    /**
     * @var AgeVerificationProductsFactory
     */
    protected $ageVerificationProductsFactory;

    /**
     * @var ResourceAgeVerificationProducts
     */
    protected $resource;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var AgeVerificationProductsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var AgeVerificationProductsCollectionFactory
     */
    protected $ageVerificationProductsCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AgeVerificationProductsInterfaceFactory
     */
    protected $dataAgeVerificationProductsFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;


    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceAgeVerificationProducts $resource
     * @param AgeVerificationProductsFactory $ageVerificationProductsFactory
     * @param AgeVerificationProductsInterfaceFactory $dataAgeVerificationProductsFactory
     * @param AgeVerificationProductsCollectionFactory $ageVerificationProductsCollectionFactory
     * @param AgeVerificationProductsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceAgeVerificationProducts $resource,
        AgeVerificationProductsFactory $ageVerificationProductsFactory,
        AgeVerificationProductsInterfaceFactory $dataAgeVerificationProductsFactory,
        AgeVerificationProductsCollectionFactory $ageVerificationProductsCollectionFactory,
        AgeVerificationProductsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resource = $resource;
        $this->ageVerificationProductsFactory = $ageVerificationProductsFactory;
        $this->ageVerificationProductsCollectionFactory = $ageVerificationProductsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataAgeVerificationProductsFactory = $dataAgeVerificationProductsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
    ) {
        /* if (empty($ageVerificationProducts->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ageVerificationProducts->setStoreId($storeId);
        } */

        $ageVerificationProductsData = $this->extensibleDataObjectConverter->toNestedArray(
            $ageVerificationProducts,
            [],
            \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::class
        );

        $ageVerificationProductsModel = $this->ageVerificationProductsFactory->create()->setData($ageVerificationProductsData);

        try {
            $this->resource->save($ageVerificationProductsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ageVerificationProducts: %1',
                $exception->getMessage()
            ));
        }
        return $ageVerificationProductsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($customId)
    {
        $ageVerificationProduct = $this->ageVerificationProductsFactory->create();
        $this->resource->load($ageVerificationProduct, $customId);
        if (!$ageVerificationProduct->getCustomId()) {
            throw new NoSuchEntityException(__('AgeVerificationProducts with id "%1" does not exist.', $customId));
        }
        return $ageVerificationProduct->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ageVerificationProductsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\AgeVerification\Api\Data\AgeVerificationProductsInterface $ageVerificationProducts
    ) {
        try {
            $ageVerificationProductsModel = $this->ageVerificationProductsFactory->create();
            $this->resource->load($ageVerificationProductsModel, $ageVerificationProducts->getCustomId());
            $this->resource->delete($ageVerificationProductsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AgeVerificationProducts: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($customId)
    {
        return $this->delete($this->get($customId));
    }
}
