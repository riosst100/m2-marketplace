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

namespace Lof\MarketPlace\Block\Product;

use Magento\Catalog\Model\Category as CategoryModel;

class Import extends \Magento\Framework\View\Element\Template
{
    protected $_frontendUrl;
    protected $historyFactory;
    protected $categoryCollectionFactory;
    protected $sellerHelper;
    protected $storeManager;
    protected $timezone;


    const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = '*/product/bulkimporthistorydownload';

    /**
     * @var
     */
    protected $currentSeller = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * Export constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->session = $customerSession;
        $this->sellerFactory = $sellerFactory;
    }

    public function createDownloadUrlImportHistoryFile($fileName)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_frontendUrl = $objectManager->create(\Magento\Framework\UrlInterface::class);
        return $this->_frontendUrl->getUrl(self::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }

    public function getSeller() 
    {
        if (!$this->currentSeller && $this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $this->currentSeller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        }
        return $this->currentSeller;
    }

    public function getImportHistory($currentPage = 1, $pageSize = 5) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->historyFactory = $objectManager->create(\TCGCollective\MarketPlace\Model\HistoryFactory::class);

        $collection = $this->historyFactory->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $this->getSeller()->getId())
        ->setOrder('started_at', 'DESC');

        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }

    public function getTotalImportHistoryCount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->historyFactory = $objectManager->create(\TCGCollective\MarketPlace\Model\HistoryFactory::class);

        return $this->historyFactory->create()->getCollection()
        ->addFieldToFilter('seller_id', $this->getSeller()->getId())->getSize();
    }

    // public function getCategoriesData() 
    // {
    //     $data = [];

    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $this->categoryCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);

    //     $collection = $this->categoryCollectionFactory->create()
    //     ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'hide_on_product_creation'])
    //     // ->addFieldToFilter('is_active', 1)
    //     ->addAttributeToFilter('hide_on_product_creation', 0)
    //     // ->addFieldToFilter('level', ['gt' => 1])
    //     ->setOrder('position', 'ASC');

    //     // dd($collection->getSize());

    //     if ($collection->getSize()) {
    //         foreach ($collection as $categoryModel) {
                
    //             if ($categoryModel->getHideOnProductCreation()) {
    //                 continue;
    //             }

    //             if ((int)$categoryModel->getLevel() == 2) {
    //                 $categoryId = $categoryModel->getId();
    //                 if (isset($data[$parentId])) {
    //                     $data[$categoryId]['text'] = $categoryModel->getName();
    //                     $data[$categoryId]['position'] = $categoryModel->getPosition();
    //                 } else {
    //                     $data[$categoryId] = [
    //                         'position' => $categoryModel->getPosition(),
    //                         'text' => $categoryModel->getName(),
    //                         'children' => []
    //                     ];
    //                 }
    //             }

    //             if ((int)$categoryModel->getLevel() == 3) {
    //                 $parentId = (int)$categoryModel->getParentId();
    //                 // if ($categoryModel->getId() == 12) {
    //                 //     dd($data[$parentId]);
    //                 // }
    //                 if (isset($data[$parentId])) {
    //                     $data[$parentId]['children'][] = [
    //                         'id' => $categoryModel->getId(),
    //                         'text' => $categoryModel->getName()
    //                     ];
    //                 } else {
    //                     $data[$parentId] = [
    //                         'text' => '',
    //                         'children' => [
    //                             [
    //                                 'id' => $categoryModel->getId(),
    //                                 'text' => $categoryModel->getName()
    //                             ]
    //                         ]
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     usort($data, function($a, $b) {
    //         return $a['position'] <=> $b['position'];
    //     });


    //     // dd(print_r($data, true));

    //     // $data = [
    //     //     {
    //     //         "text": "Group 1", 
    //     //         "children" : [
    //     //             {
    //     //                 "id": 1,
    //     //                 "text": "Option 1.1"
    //     //             },
    //     //             {
    //     //                 "id": 2,
    //     //                 "text": "Option 1.2"
    //     //             }
    //     //         ]
    //     //     },
    //     //     { 
    //     //         "text": "Group 2", 
    //     //         "children" : [
    //     //             {
    //     //                 "id": 3,
    //     //                 "text": "Option 2.1"
    //     //             },
    //     //             {
    //     //                 "id": 4,
    //     //                 "text": "Option 2.2"
    //     //             }
    //     //         ]
    //     //     }
    //     // ];

    //     return !empty($data) ? json_encode(array_values($data)) : [];
    // }
    public function getCategoriesData() 
    {
        $data = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->categoryCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Seller::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $seller = $this->sellerHelper->getSeller();
        $storeId = null;
        if ($seller->getData('store_id')) {
            foreach ($seller->getData('store_id') as $key => $id){
                $storeId = $id;
            }
        }
        if(!$storeId){
            $storeId = $this->storeManager->getStore()->getId();
        }

        $storeRootCategory = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $matchingNamesCollection = $this->categoryCollectionFactory->create();
        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID]);

        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            if (
                ($storeRootCategory == CategoryModel::ROOT_CATEGORY_ID) ||
                str_contains($category->getPath(), '/'.$storeRootCategory.'/') ||
                str_ends_with($category->getPath(), '/'.$storeRootCategory)
            ){
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'hide_on_product_creation'])
            ->setStoreId($storeId);
        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        $collection->setOrder('position', 'ASC');
        if ($collection->getSize()) {
            foreach ($collection as $category) {
                if ($category->getHideOnProductCreation()) {
                    continue;
                }
                if ($category->getLevel() > 3) {
                    continue;
                }
                if (!$category->getIsActive()) {
                    continue;
                }

                $categoryById[$category->getId()]['text'] = $category->getName();
                $categoryById[$category->getId()]['id'] = $category->getId();
                $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
            }
        }

        $data = $categoryById[CategoryModel::TREE_ROOT_ID]['children'][0]['children'];

        return !empty($data) ? json_encode(array_values($data)) : [];
    }

    public function getUploadStatus($summary, $validationSummary = null) 
    {
        $data = [];

        if ($summary) {
            $summaryArr = explode(', ', $summary);
            foreach ($summaryArr as $summaryItem) {
                $summaryItemArr = explode(': ', $summaryItem);
                if ($summaryItemArr[0] == 'Created') {
                    $data['created'] = (int)$summaryItemArr[1];
                }
                if ($summaryItemArr[0] == 'Updated') {
                    $data['updated'] = (int)$summaryItemArr[1];
                }
                if ($summaryItemArr[0] == 'Deleted') {
                    $data['deleted'] = (int)$summaryItemArr[1];
                }
            }
        }

        if ($validationSummary) {
            $summaryArr = explode(', ', $validationSummary);
            foreach ($summaryArr as $summaryItem) {
                $summaryItemArr = explode(': ', $summaryItem);
                if ($summaryItemArr[0] == 'Error') {
                    $errorCount = (int)$summaryItemArr[1];
                    if ($errorCount > 0) {
                        $data['error'] = $errorCount;
                    }
                }
            }
        }

        // dd($data);

        return $data;
    }

    public function formatDateTime($dateTime)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->timezone = $objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        $dateTimeZone = $this->timezone->date(new \DateTime($dateTime))->format('Y-m-d H:i:s');


        return $dateTimeZone;
    }
}
