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

namespace Lof\MarketPlace\Block\Seller;

use Magento\Catalog\Model\Category as CategoryModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Dashboard extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Lof\MarketPlace\Model\Seller
     */
    protected $_sellerFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Lof\MarketPlace\Model\Amount
     */
    protected $amount;

    /**
     * @var \Lof\MarketPlace\Model\Amounttransaction
     */
    protected $amounttransaction;

    /**
     * @var \Lof\MarketPlace\Model\Orderitems
     */
    protected $orderitems;

    /**
     * @var \Lof\MarketPlace\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Sale\Model\Order
     */
    protected $_order;

    /**
     * @var \Lof\MarketPlace\Model\SellerProduct
     */
    protected $product;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localList;

    /**
     * @var string
     */
    protected $_columnDate = 'main_table.created_at';

    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Report
     */
    protected $_helperReport;

    protected $request;

    protected $websiteCollectionFactory;
    protected $categoryRepository;

    protected $currencyCodes = [];

    protected $preOrderFactory;
    protected $rfqFactory;
    protected $favoriteSellerFactory;
    protected $couponFactory;
    protected $messageFactory;

    /**
     * Dashboard constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Lof\MarketPlace\Model\Seller $sellerFactory
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param \Lof\MarketPlace\Model\Amount $amount
     * @param \Lof\MarketPlace\Model\Amounttransaction $amounttransaction
     * @param \Lof\MarketPlace\Model\Orderitems $orderitems
     * @param \Lof\MarketPlace\Model\Order $order
     * @param \Magento\Sales\Model\Order $_order
     * @param \Lof\MarketPlace\Model\SellerProduct $product
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Lof\MarketPlace\Helper\Report $helperReport
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MarketPlace\Model\Seller $sellerFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Lof\MarketPlace\Model\Amount $amount,
        \Lof\MarketPlace\Model\Amounttransaction $amounttransaction,
        \Lof\MarketPlace\Model\Orderitems $orderitems,
        \Lof\MarketPlace\Model\Order $order,
        \Magento\Sales\Model\Order $_order,
        \Lof\MarketPlace\Model\SellerProduct $product,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Lof\MarketPlace\Helper\Report $helperReport,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Lof\PreOrder\Model\PreOrderFactory $preOrderFactory,
        \Lof\Quickrfq\Model\QuickrfqFactory $rfqFactory,
        \Lofmp\FavoriteSeller\Model\SubscriptionFactory $favoriteSellerFactory,
        \Lofmp\CouponCode\Model\CouponFactory $couponFactory,
        \Lof\MarketPlace\Model\MessageFactory $messageFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceFactory = $resourceFactory;
        $this->_order = $_order;
        $this->amounttransaction = $amounttransaction;
        $this->product = $product;
        $this->orderitems = $orderitems;
        $this->order = $order;
        $this->session = $customerSession;
        $this->amount = $amount;
        $this->_helper = $helper;
        $this->_coreRegistry = $registry;
        $this->_sellerFactory = $sellerFactory;
        $this->_resource = $resource;
        $this->_date = $date;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_localList = $localeLists;
        $this->_helperReport = $helperReport;
        $this->request = $request;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->preOrderFactory = $preOrderFactory;
        $this->rfqFactory = $rfqFactory;
        $this->favoriteSellerFactory = $favoriteSellerFactory;
        $this->couponFactory = $couponFactory;
        $this->messageFactory = $messageFactory;

        parent::__construct($context);
    }

    public function getCategoriesData() 
    {
        $data = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->categoryCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Seller::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $this->catalogHelper = $objectManager->create(\TCGCollective\MarketPlace\Helper\Catalog::class);

        $seller = $this->sellerHelper->getSeller();
        // dd($seller);
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
        
        $sellerUrl = $seller->getUrlKey();
        $sellerMainCategoryIds = $this->catalogHelper->getSellerMainCategoryIds($sellerUrl);
        $allowedChildIds = $this->catalogHelper->getSellerCategoryIds($sellerUrl);
        // dd($sellerMainCategoryIds);
        
        $dataAll = $categoryById[CategoryModel::TREE_ROOT_ID]['children'][0]['children'];
        $data = [];
        foreach($dataAll as $index => $category) {
            if (!in_array($category['id'], $sellerMainCategoryIds)) {
                continue;
            }

            if (!empty($category['children'])) {
                $category['children'] = array_values(array_filter(
                    $category['children'],
                    function ($child) use ($allowedChildIds) {
                        return in_array($child['id'], $allowedChildIds);
                    }
                ));
            }

            $data[] = $category;
        }
        
        // dd(json_encode(array_values($data)));
        return !empty($data) ? json_encode(array_values($data)) : [];
    }

    public function getUnreadMessage() 
    {
        $collection = $this->messageFactory->create()
        ->getCollection();

        $collection->addFieldToFilter('main_table.owner_id', $this->getSellerId());
        $collection->addFieldToFilter('detail.receiver_id', $this->getSellerId());
        $collection->addFieldToFilter('detail.is_read', 0);

        // $collection = $this->filterOrderByWebsiteCode($collection);
        $collection->getSelect()
        ->join(
            ['detail' => $collection->getTable('lof_marketplace_message_detail')],
            'main_table.message_id = detail.message_id',
            []
        );

        $total = $collection->getSize();

        return $total ?: 0;
    }

    public function getActiveCoupon() 
    {
        $collection = $this->couponFactory->create()
        ->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());
        $collection->addFieldToFilter('rule.is_active', 1);

        // $collection = $this->filterOrderByWebsiteCode($collection);
        $collection->getSelect()
        ->join(
            ['coupon' => $collection->getTable('salesrule_coupon')],
            'main_table.coupon_id = coupon.coupon_id',
            []
        )
        ->join(
            ['rule' => $collection->getTable('salesrule')],
            'coupon.rule_id = rule.rule_id',
            []
        );

        $total = $collection->getSize();

        return $total ?: 0;
    }

    public function getReportUrl($urlPath) {
        $websiteCode = $this->getWebsiteCode();
        
        $url = $this->getUrl($urlPath);
        if ($websiteCode) {
            $url .= 'country/'. $websiteCode;
        }

        return $url;
    }

    public function getDraftProducts() 
    {
        /** @var \Lof\Marketplace\Model\ResourceModel\SellerProduct\Collection $collection */
        $collection = $this->product->getCollection();
        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

        $connection = $collection->getResource()->getConnection();

        /** ambil entity_type_id dan attribute_id */
        $entityTypeId = $connection->fetchOne("
            SELECT entity_type_id 
            FROM eav_entity_type 
            WHERE entity_type_code = 'catalog_product'
        ");

        $isPublishedAttrId = $connection->fetchOne("
            SELECT attribute_id 
            FROM eav_attribute 
            WHERE attribute_code = 'publish_status'
            AND entity_type_id = {$entityTypeId}
        ");

        $categoryNameAttrId = $connection->fetchOne("
            SELECT attribute_id 
            FROM eav_attribute 
            WHERE attribute_code = 'name'
            AND entity_type_id = (
                SELECT entity_type_id 
                FROM eav_entity_type 
                WHERE entity_type_code = 'catalog_category'
            )
        ");

        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::COLUMNS);

        $select
            ->join(
                ['p' => $collection->getTable('catalog_product_entity')],
                'p.entity_id = main_table.product_id',
                []
            )
            ->joinLeft(
                ['is_pub' => $collection->getTable('catalog_product_entity_int')],
                "is_pub.entity_id = p.entity_id AND is_pub.attribute_id = {$isPublishedAttrId}",
                []
            )
            ->join(
                ['ccp' => $collection->getTable('catalog_category_product')],
                'ccp.product_id = p.entity_id',
                []
            )
            ->join(
                ['ccev' => $collection->getTable('catalog_category_entity_varchar')],
                "ccev.entity_id = ccp.category_id AND ccev.attribute_id = {$categoryNameAttrId}",
                ['category_name' => 'ccev.value','category_id' => 'ccp.category_id']
            )
            // 👉 ambil produk yg tidak punya record is_published, atau value != 1
            ->where('(is_pub.entity_id IS NULL OR is_pub.value != 1)')
            ->columns(['product_count' => new \Zend_Db_Expr('COUNT(DISTINCT p.entity_id)')])
            ->group('ccev.value')
            ->order('product_count DESC');

        $results = $connection->fetchAll($select);
        // dd($results);

        // dd($results);
        return $results;
    }

    public function getPublishedProducts() 
    {
        /** @var \Lof\Marketplace\Model\ResourceModel\SellerProduct\Collection $collection */
$collection = $this->product->getCollection();
$collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

$connection = $collection->getResource()->getConnection();

/** Dapatkan attribute_id dari is_published (produk) */
$isPublishedAttrId = $connection->fetchOne("
    SELECT attribute_id 
    FROM eav_attribute 
    WHERE attribute_code = 'publish_status'
      AND entity_type_id = (
        SELECT entity_type_id 
        FROM eav_entity_type 
        WHERE entity_type_code = 'catalog_product'
      )
");

/** Dapatkan attribute_id dari nama kategori */
$categoryNameAttrId = $connection->fetchOne("
    SELECT attribute_id 
    FROM eav_attribute 
    WHERE attribute_code = 'name'
      AND entity_type_id = (
        SELECT entity_type_id 
        FROM eav_entity_type 
        WHERE entity_type_code = 'catalog_category'
      )
");

$select = $collection->getSelect();
$select->reset(\Zend_Db_Select::COLUMNS);

$select
    ->join(
        ['p' => $collection->getTable('catalog_product_entity')],
        'p.entity_id = main_table.product_id',
        []
    )
    ->join(
        ['is_pub' => $collection->getTable('catalog_product_entity_int')],
        "is_pub.entity_id = p.entity_id AND is_pub.attribute_id = {$isPublishedAttrId}",
        []
    )
    ->join(
        ['ccp' => $collection->getTable('catalog_category_product')],
        'ccp.product_id = p.entity_id',
        []
    )
    ->join(
        ['ccev' => $collection->getTable('catalog_category_entity_varchar')],
        "ccev.entity_id = ccp.category_id AND ccev.attribute_id = {$categoryNameAttrId}",
        ['category_name' => 'ccev.value']
    )
    ->where('is_pub.value = ?', 1) // hanya produk yang dipublish
    ->columns(['product_count' => new \Zend_Db_Expr('COUNT(DISTINCT p.entity_id)')])
    ->group('ccev.value')
    ->order('product_count DESC');

$results = $connection->fetchAll($select);
// dd($results);
return $results;

    }

    public function getLowStockProducts() 
    {
        /** @var \Lof\Marketplace\Model\ResourceModel\SellerProduct\Collection $collection */
$collection = $this->product->getCollection();
$collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

$connection = $collection->getResource()->getConnection();
$categoryNameAttrId = $connection->fetchOne("
    SELECT attribute_id 
    FROM eav_attribute 
    WHERE attribute_code = 'name'
      AND entity_type_id = (
        SELECT entity_type_id 
        FROM eav_entity_type 
        WHERE entity_type_code = 'catalog_category'
      )
");

$select = $collection->getSelect();
$select->reset(\Zend_Db_Select::COLUMNS); // reset biar gak ambil kolom bawaan collection

$select
    ->join(
        ['p' => $collection->getTable('catalog_product_entity')],
        'p.entity_id = main_table.product_id',
        []
    )
    ->join(
        ['si' => $collection->getTable('cataloginventory_stock_item')],
        'si.product_id = p.entity_id',
        []
    )
    ->join(
        ['ccp' => $collection->getTable('catalog_category_product')],
        'ccp.product_id = p.entity_id',
        []
    )
    ->join(
        ['ccev' => $collection->getTable('catalog_category_entity_varchar')],
        "ccev.entity_id = ccp.category_id AND ccev.attribute_id = {$categoryNameAttrId}",
        ['category_name' => 'ccev.value']
    )
    ->where('main_table.seller_id = ?', (int) $this->getSellerId())
    ->where('si.qty <= 5')
    ->columns([
        'low_stock_count' => new \Zend_Db_Expr('COUNT(DISTINCT p.entity_id)')
    ])
    ->group('ccev.value')
    ->order('low_stock_count DESC');

$results = $connection->fetchAll($select);
// dd($results);
return $results;

    }

    // TOP PRODUCTS
    public function getTopProducts() 
    {
        $amount = 0;

        $collection = $this->amounttransaction->getCollection();
        
        // Filter by seller
        $collection->addFieldToFilter('seller_id', $this->getSellerId());
        $collection->addFieldToFilter('main_table.amount', ['neq' => null]);
        // $collection->addFieldToFilter('transaction_id', 1);

        // // Filter by date
        // // $collection = $this->applyDateFilter($collection);

        // // Apply custom filter
        // $collection->applyCustomFilter();

        // // Filter transactions by country
        $collection = $this->filterTransactionByWebsiteCode($collection);
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        // $connection = $collection->getResource()->getConnection();

        /** @var \Magento\Framework\App\ResourceConnection $resource */
// $connection = $resource->getConnection();

/** @var \Magento\Framework\App\ResourceConnection $resource */
// $connection = $resource->getConnection();

/** @var \Lof\Marketplace\Model\ResourceModel\AmountTransaction\Collection $collection */
// $collection = $this->amountTransactionCollectionFactory->create(); // atau ->getCollection()

$connection = $collection->getResource()->getConnection();

$select = $collection->getSelect();
$select->reset(\Zend_Db_Select::COLUMNS);

// JOIN ke sales_order
$select->join(
    ['o' => $connection->getTableName('sales_order')],
    "o.entity_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(main_table.description, 'order #', -1), ',', 1) AS UNSIGNED)",
    [] // tidak ambil kolom dari sales_order
);

// JOIN ke sales_order_item
$select->join(
    ['oi' => $connection->getTableName('sales_order_item')],
    'oi.order_id = o.entity_id',
    [
        'product_id'   => 'oi.product_id',
        'product_name' => 'oi.name',
    ]
);

// Hanya ambil parent item (hindari duplikat configurable)
$select->where('oi.parent_item_id IS NULL');

// Hitung total_sales per produk
$select->columns([
    'total_sales' => new \Zend_Db_Expr('SUM(oi.row_total)'),
    'currency'   => 'o.base_currency_code'
]);

$select->group('oi.product_id');
$select->order('total_sales DESC');
$select->limit(2);

// Eksekusi query
$result = $connection->fetchAll($select);

// dd($result);


// dd($result);

        
        return $result;
    }

    public function truncateText($text, $maxChars = 30) {
        if (strlen($text) <= $maxChars) {
            return $text;
        }
        return substr($text, 0, $maxChars - 3) . '...';
    }

    // TOP CATEGORIES
    public function getTopCategories() 
    {
        $amount = 0;

        $collection = $this->amounttransaction->getCollection();
        
        // Filter by seller
        $collection->addFieldToFilter('seller_id', $this->getSellerId());
        $collection->addFieldToFilter('main_table.amount', ['neq' => null]);
        // $collection->addFieldToFilter('transaction_id', 1);

        // // Filter by date
        // // $collection = $this->applyDateFilter($collection);

        // // Apply custom filter
        // $collection->applyCustomFilter();

        // // Filter transactions by country
        $collection = $this->filterTransactionByWebsiteCode($collection);
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $connection = $collection->getResource()->getConnection();

$select = $collection->getSelect();
$select->reset(\Zend_Db_Select::COLUMNS);

// ambil attribute_id untuk nama kategori
$categoryNameAttrId = (int) $connection->fetchOne("
    SELECT attribute_id 
    FROM eav_attribute 
    WHERE attribute_code = 'name'
      AND entity_type_id = (
        SELECT entity_type_id 
        FROM eav_entity_type 
        WHERE entity_type_code = 'catalog_category'
      )
");

// join ke tabel sales_order, sales_order_item, dan category product
$select
    ->join(
        ['o' => $connection->getTableName('sales_order')],
        "o.entity_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(main_table.description, 'order #', -1), ',', 1) AS UNSIGNED)",
        [] // tidak ambil kolom dari sales_order
    )
    ->join(
        ['oi' => $connection->getTableName('sales_order_item')],
        'oi.order_id = o.entity_id',
        [] // ambil kolom nanti di ->columns
    )
    ->join(
        ['ccp' => $connection->getTableName('catalog_category_product')],
        'ccp.product_id = oi.product_id',
        []
    )
    ->join(
        ['ccev' => $connection->getTableName('catalog_category_entity_varchar')],
        "ccev.entity_id = ccp.category_id AND ccev.attribute_id = {$categoryNameAttrId}",
        []
    );

// pilih kolom hasil akhir
$select->columns([
    'category_name' => 'ccev.value',
    'total_sales'   => new \Zend_Db_Expr('SUM(oi.row_total)'),
    'currency'   => 'o.base_currency_code'
]);

$select->group('ccev.value');
$select->order('total_sales DESC');
$select->limit(2);

// ambil hasil
$results = $connection->fetchAll($select);
// dd($results);

        
        return $results;
    }

    // NET REVENUE
    public function getNetRevenue() 
    {
        $amount = 0;

        $collection = $this->amounttransaction->getCollection();
        
        // Filter by seller
        $collection->addFieldToFilter('seller_id', $this->getSellerId());
        $collection->addFieldToFilter('main_table.amount', ['neq' => null]);

        // Filter by date
        // $collection = $this->applyDateFilter($collection);

        // Apply custom filter
        // $collection->applyCustomFilter();

        // Filter transactions by country
        $collection = $this->filterTransactionByWebsiteCode($collection);

        // $collection->getSelect()->columns(['base_currency_code' => 'so.base_currency_code']);
        // dd($collection->getData());
        // dd($collection->getSize());
        $newAmountnewAmount = [];
        foreach ($collection as $transaction) {
            // dd($transaction);
            $currency = $transaction->getBaseCurrencyCode();
            // dd($currency);
            $newAmount = $transaction->getAmount();
            // dd($newAmount);
            if ($newAmount) {
                if (!$this->getWebsiteCode()) {
                    if ($currency) {
                        $newAmount = $this->convertToUsd($newAmount, $currency);
                    }
                }
                $amount += $newAmount;
            }

            $newAmountnewAmount[] = $newAmount;
        }

        // dd($newAmountnewAmount);

        return $this->_helper->getPriceFomat(
            $amount, 
            $this->getCurrencyCode()
        );
    }

    public function convertToUsd($amount, $originalCurrency) {
        $currencyFactory = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Directory\Model\CurrencyFactory::class);

        // ambil model currency SGD
        $fromCurrency = $currencyFactory->create()->load($originalCurrency);

        // convert ke USD (misal 100 SGD)
        $price = $fromCurrency->convert($amount, 'USD');

        return $price;
    }

    public function getWebsiteCode() 
    {
        $websiteCode = $this->request->getParam('country');
        // dd($websiteCode);
        if ($websiteCode) {
            return $websiteCode;
        }

        // return $this->storeManager->getStore()->getWebsite()->getCode();
        return null;
    }

    public function filterTransactionByWebsiteCode($collection, $joinSw = false) 
    {
        $select = $collection->getSelect();

            // Tambahin kolom virtual "order_id" hasil extract dari description
            $select->columns([
                'order_id' => new \Zend_Db_Expr(
                    "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(main_table.description, 'order #', -1), ',', 1) AS UNSIGNED)"
                )
            ]);

            // Join ke sales_order
            $select->join(
                ['so' => $collection->getTable('sales_order')],
                'so.entity_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(main_table.description, "order #", -1), ",", 1) AS UNSIGNED)',
                ['base_currency_code','increment_id', 'created_at', 'status', 'store_id']
            );

        $websiteCode = $this->getWebsiteCode();
        if ($websiteCode) {
            

            // Join ke store & website
            if (!$joinSw) {
                $select->join(
                    ['st' => $collection->getTable('store')],
                    'so.store_id = st.store_id',
                    ['store_code' => 'code', 'website_id']
                )->join(
                    ['sw' => $collection->getTable('store_website')],
                    'st.website_id = sw.website_id',
                    ['website_code' => 'code']
                );
            }

                // Filter by website code
            $select->where('sw.code = ?', $websiteCode);
        }
        // dd($this->getRequest()->getParams());
        $dateRange = $this->getRequest()->getParam('date');
        $now = $this->_timezoneInterface->date();

        $todayEnd = $now->format('d/m/Y');
        $last7Start  = (clone $now)->modify('-6 days')->format('d/m/Y');
        $last14Start = (clone $now)->modify('-13 days')->format('d/m/Y');
        $last30Start = (clone $now)->modify('-29 days')->format('d/m/Y');

        if ($dateRange && $dateRange !== 'All Periods') {
            $today      = __('Today (%1)', $now->format('d/m/Y'));
            $yesterday  = __('Yesterday (%1)', 
                            (clone $now)->modify('-1 day')->format('d/m/Y')
                        );
            $last_7     = __('Last 7 days (%1 - %2)', $last7Start, $todayEnd);
            $last_14    = __('Last 14 days (%1 - %2)', $last14Start, $todayEnd);
            $last_30    = __('Last 30 days (%1 - %2)', $last30Start, $todayEnd);
            $this_month = __('This Month (%1)', $now->format('F'));
            $last_month = __('Last Month (%1)', 
                            (clone $now)->modify('-1 month')->format('F')
                        );
            $this_year  = __('This Year (%1)', $now->format('Y'));
            $last_year  = __('Last Year (%1)', 
                            (clone $now)->modify('-1 year')->format('Y')
                        );
            $custom     = 'Custom Date Range';

            if ($dateRange == $today) {
                $fromDate = date('Y-m-d 00:00:00');
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $yesterday) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $toDate   = date('Y-m-d 23:59:59', strtotime('-1 day'));
            } elseif ($dateRange == $last_7) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_14) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-14 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_30) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $this_month) {
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            } elseif ($dateRange == $last_month) {
                $fromDate = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $toDate   = date('Y-m-t 23:59:59', strtotime('-1 month'));
            } elseif ($dateRange == $this_year) {
                $fromDate = date('Y-01-01 00:00:00');
                $toDate   = date('Y-12-31 23:59:59');
            } elseif ($dateRange == $last_year) {
                $fromDate = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $toDate   = date('Y-12-31 23:59:59', strtotime('-1 year'));
            } elseif ($dateRange == $custom) {
                $fromDateParam = $this->getRequest()->getParam('from_date');
                $toDateParam   = $this->getRequest()->getParam('to_date');

                if ($fromDateParam && $toDateParam) {
                    $fromDate = date('Y-m-d 00:00:00', strtotime($fromDateParam));
                    $toDate   = date('Y-m-d 23:59:59', strtotime($toDateParam));
                }
            } else {
                // Default ke bulan berjalan
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            }

            // $fromDate = '2025-09-01 00:00:00';
            // $toDate   = '2025-12-01 23:59:59';
            $websiteCode = $this->getWebsiteCode();
            if (!$websiteCode) {
                // $collection->getSelect()
                // ->join(
                //     ['so' => $collection->getTable('sales_order')],
                //     'main_table.order_id = so.entity_id',
                //     ['increment_id', 'created_at', 'store_id'] // field dari sales_order
                // );
            }            

            $collection->getSelect()->where(
                "main_table.created_at >= ?",
                $fromDate
            )->where(
                "main_table.created_at <= ?",
                $toDate
            );
        }

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            // dd($categoryId);

            $select = $collection->getSelect();
            
            $soTable  = $collection->getTable('sales_order');
            $soiTable = $collection->getTable('sales_order_item');
            $ccpTable = $collection->getTable('catalog_category_product');
            $cceTable = $collection->getTable('catalog_category_entity');
            
            $select->where(
                "EXISTS (
                    SELECT 1
                    FROM {$soiTable} AS soi
                    INNER JOIN {$soTable} ON sales_order.entity_id = soi.order_id
                    INNER JOIN {$ccpTable} AS ccp ON soi.product_id = ccp.product_id
                    INNER JOIN {$cceTable} AS cce ON ccp.category_id = cce.entity_id
                    WHERE soi.order_id = sales_order.entity_id
                    AND cce.entity_id = ?
                )",
                (int)$categoryId
            );
        }
        // die($collection->getSelect());
        return $collection;
    }

    public function getOrderToShip() 
    {
        $collection = $this->order->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());
        $collection->addFieldToFilter('main_table.status', 'processing');

        $collection = $this->filterOrderByWebsiteCode($collection);

        $total = $collection->getSize();

        return $total ?: 0;
    }

    // TOTAL ORDERS
    public function getTotalOrder()
    {
        $collection = $this->order->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

        $collection = $this->filterOrderByWebsiteCode($collection);

        $total = $collection->getSize();
        return $total ?: 0;
    }

    public function filterOrderByWebsiteCode($collection) 
    {
        $websiteCode = $this->getWebsiteCode();
        if ($websiteCode) {
            $collection->getSelect()
            ->join(
                ['so' => $collection->getTable('sales_order')],
                'main_table.order_id = so.entity_id',
                ['increment_id', 'created_at', 'store_id'] // field dari sales_order
            )
            ->join(
                ['st' => $collection->getTable('store')],
                'so.store_id = st.store_id',
                ['store_code' => 'code', 'website_id']
            )
            ->join(
                ['sw' => $collection->getTable('store_website')],
                'st.website_id = sw.website_id',
                ['website_code' => 'code']
            )
            ->where('sw.code = ?', $websiteCode); // ganti sesuai website_code
        }

        $dateRange = $this->getRequest()->getParam('date');
        $now = $this->_timezoneInterface->date();

        $todayEnd = $now->format('d/m/Y');
        $last7Start  = (clone $now)->modify('-6 days')->format('d/m/Y');
        $last14Start = (clone $now)->modify('-13 days')->format('d/m/Y');
        $last30Start = (clone $now)->modify('-29 days')->format('d/m/Y');

        if ($dateRange && $dateRange !== 'All Periods') {
            $today      = __('Today (%1)', $now->format('d/m/Y'));
            $yesterday  = __('Yesterday (%1)', 
                            (clone $now)->modify('-1 day')->format('d/m/Y')
                        );
            $last_7     = __('Last 7 days (%1 - %2)', $last7Start, $todayEnd);            
            $last_14    = __('Last 14 days (%1 - %2)', $last14Start, $todayEnd);
            $last_30    = __('Last 30 days (%1 - %2)', $last30Start, $todayEnd);
            $this_month = __('This Month (%1)', $now->format('F'));
            $last_month = __('Last Month (%1)', 
                            (clone $now)->modify('-1 month')->format('F')
                        );
            $this_year  = __('This Year (%1)', $now->format('Y'));
            $last_year  = __('Last Year (%1)', 
                            (clone $now)->modify('-1 year')->format('Y')
                        );
            $custom     = 'Custom Date Range';

            if ($dateRange == $today) {
                $fromDate = date('Y-m-d 00:00:00');
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $yesterday) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $toDate   = date('Y-m-d 23:59:59', strtotime('-1 day'));
            } elseif ($dateRange == $last_7) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_14) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-14 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_30) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $this_month) {
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            } elseif ($dateRange == $last_month) {
                $fromDate = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $toDate   = date('Y-m-t 23:59:59', strtotime('-1 month'));
            } elseif ($dateRange == $this_year) {
                $fromDate = date('Y-01-01 00:00:00');
                $toDate   = date('Y-12-31 23:59:59');
            } elseif ($dateRange == $last_year) {
                $fromDate = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $toDate   = date('Y-12-31 23:59:59', strtotime('-1 year'));
            } elseif ($dateRange == $custom) {
                $fromDateParam = $this->getRequest()->getParam('from_date');
                $toDateParam   = $this->getRequest()->getParam('to_date');

                if ($fromDateParam && $toDateParam) {
                    $fromDate = date('Y-m-d 00:00:00', strtotime($fromDateParam));
                    $toDate   = date('Y-m-d 23:59:59', strtotime($toDateParam));
                }
            } else {
                // Default ke bulan berjalan
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            }

            // $fromDate = '2025-09-01 00:00:00';
            // $toDate   = '2025-12-01 23:59:59';
            $websiteCode = $this->getWebsiteCode();
            if (!$websiteCode) {
                $collection->getSelect()
                ->join(
                    ['so' => $collection->getTable('sales_order')],
                    'main_table.order_id = so.entity_id',
                    ['increment_id', 'created_at', 'store_id'] // field dari sales_order
                );
            }            

            $collection->getSelect()->where(
                "so.created_at >= ?",
                $fromDate
            )->where(
                "so.created_at <= ?",
                $toDate
            );
        }        

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            // dd($categoryId);
            
            $soiTable = $collection->getTable('sales_order_item');
            $ccpTable = $collection->getTable('catalog_category_product');
            $cceTable = $collection->getTable('catalog_category_entity');

            $collection->getSelect()->where(
                "EXISTS (
                    SELECT 1
                    FROM {$soiTable} AS soi
                    INNER JOIN {$ccpTable} AS ccp ON soi.product_id = ccp.product_id
                    INNER JOIN {$cceTable} AS cce ON ccp.category_id = cce.entity_id
                    WHERE soi.order_id = main_table.order_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
            );
        }

        return $collection;
    }

    // TOTAL PRE ORDERS
    public function getTotalPreOrder()
    {
        $collection = $this->preOrderFactory->create()->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

        $collection = $this->filterOrderByWebsiteCode($collection);

        $total = $collection->getSize();
        return $total ?: 0;
    }

    // TOTAL OFFERS
    public function getTotalOffers()
    {
        $collection = $this->rfqFactory->create()->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

        $collection = $this->filterOfferByWebsiteCode($collection);

        $total = $collection->getSize();
        return $total ?: 0;
    }

    public function getActiveOffers()
    {
        $collection = $this->rfqFactory->create()->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());
        $collection->addFieldToFilter(
            'main_table.status',
            [
                ['eq' => 'Processing'],
                ['eq' => 'New']
            ]
        );

        $collection = $this->filterOfferByWebsiteCode($collection);

        $total = $collection->getSize();
        return $total ?: 0;
    }

    public function filterOfferByWebsiteCode($collection) 
    {
        $websiteCode = $this->getWebsiteCode();
        if ($websiteCode) {
            $collection->getSelect()
            ->join(
                ['sw' => $collection->getTable('store_website')],
                'main_table.website_id = sw.website_id',
                ['website_code' => 'code']
            )
            ->where('sw.code = ?', $websiteCode); // ganti sesuai website_code
        }

        $dateRange = $this->getRequest()->getParam('date');
        $now = $this->_timezoneInterface->date();

        $todayEnd = $now->format('d/m/Y');
        $last7Start  = (clone $now)->modify('-6 days')->format('d/m/Y');
        $last14Start = (clone $now)->modify('-13 days')->format('d/m/Y');
        $last30Start = (clone $now)->modify('-29 days')->format('d/m/Y');

        if ($dateRange && $dateRange !== 'All Periods') {
            $today      = __('Today (%1)', $now->format('d/m/Y'));
            $yesterday  = __('Yesterday (%1)', 
                            (clone $now)->modify('-1 day')->format('d/m/Y')
                        );
            $last_7     = __('Last 7 days (%1 - %2)', $last7Start, $todayEnd);
            $last_14    = __('Last 14 days (%1 - %2)', $last14Start, $todayEnd);
            $last_30    = __('Last 30 days (%1 - %2)', $last30Start, $todayEnd);
            $this_month = __('This Month (%1)', $now->format('F'));
            $last_month = __('Last Month (%1)', 
                            (clone $now)->modify('-1 month')->format('F')
                        );
            $this_year  = __('This Year (%1)', $now->format('Y'));
            $last_year  = __('Last Year (%1)', 
                            (clone $now)->modify('-1 year')->format('Y')
                        );
            $custom     = 'Custom Date Range';

            if ($dateRange == $today) {
                $fromDate = date('Y-m-d 00:00:00');
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $yesterday) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $toDate   = date('Y-m-d 23:59:59', strtotime('-1 day'));
            } elseif ($dateRange == $last_7) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_14) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-14 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_30) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $this_month) {
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            } elseif ($dateRange == $last_month) {
                $fromDate = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $toDate   = date('Y-m-t 23:59:59', strtotime('-1 month'));
            } elseif ($dateRange == $this_year) {
                $fromDate = date('Y-01-01 00:00:00');
                $toDate   = date('Y-12-31 23:59:59');
            } elseif ($dateRange == $last_year) {
                $fromDate = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $toDate   = date('Y-12-31 23:59:59', strtotime('-1 year'));
            } elseif ($dateRange == $custom) {
                $fromDateParam = $this->getRequest()->getParam('from_date');
                $toDateParam   = $this->getRequest()->getParam('to_date');

                if ($fromDateParam && $toDateParam) {
                    $fromDate = date('Y-m-d 00:00:00', strtotime($fromDateParam));
                    $toDate   = date('Y-m-d 23:59:59', strtotime($toDateParam));
                }
            } else {
                // Default ke bulan berjalan
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            }

            // $fromDate = '2025-09-01 00:00:00';
            // $toDate   = '2025-12-01 23:59:59';
            
            $collection->getSelect()->where(
                "main_table.create_date >= ?",
                $fromDate
            )->where(
                "main_table.create_date <= ?",
                $toDate
            );
        }

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            // dd($categoryId);
            
            $soiTable = $collection->getTable('sales_order_item');
            $ccpTable = $collection->getTable('catalog_category_product');
            $cceTable = $collection->getTable('catalog_category_entity');

            $collection->getSelect()->where(
                "EXISTS (
                    SELECT 1
                    FROM {$ccpTable} AS ccp
                    INNER JOIN {$cceTable} AS cce ON ccp.category_id = cce.entity_id
                    WHERE ccp.product_id = main_table.product_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
                // ,
                // (int)$categoryId
            );

        }
        
        return $collection;
    }

    // TOTAL SUBSCRIBERS
    public function getTotalSubscribers()
    {
        $collection = $this->favoriteSellerFactory->create()
        ->getCollection();

        $collection->addFieldToFilter('main_table.seller_id', $this->getSellerId());

        $collection = $this->filterFollowersByWebsiteCode($collection);

        $total = $collection->getSize();
        return $total ?: 0;
    }

    public function filterFollowersByWebsiteCode($collection) 
    {
        $websiteCode = $this->getWebsiteCode();
        if ($websiteCode) {
            $collection->getSelect()
            ->join(
                ['sw' => $collection->getTable('store_website')],
                'main_table.website_id = sw.website_id',
                ['website_code' => 'code']
            )
            ->where('sw.code = ?', $websiteCode); // ganti sesuai website_code
        }

        $dateRange = $this->getRequest()->getParam('date');
        $now = $this->_timezoneInterface->date();

        $todayEnd = $now->format('d/m/Y');
        $last7Start  = (clone $now)->modify('-6 days')->format('d/m/Y');
        $last14Start = (clone $now)->modify('-13 days')->format('d/m/Y');
        $last30Start = (clone $now)->modify('-29 days')->format('d/m/Y');

        if ($dateRange && $dateRange !== 'All Periods') {
            $today      = __('Today (%1)', $now->format('d/m/Y'));
            $yesterday  = __('Yesterday (%1)', 
                            (clone $now)->modify('-1 day')->format('d/m/Y')
                        );
            $last_7     = __('Last 7 days (%1 - %2)', $last7Start, $todayEnd);
            $last_14    = __('Last 14 days (%1 - %2)', $last14Start, $todayEnd);
            $last_30    = __('Last 30 days (%1 - %2)', $last30Start, $todayEnd);
            $this_month = __('This Month (%1)', $now->format('F'));
            $last_month = __('Last Month (%1)', 
                            (clone $now)->modify('-1 month')->format('F')
                        );
            $this_year  = __('This Year (%1)', $now->format('Y'));
            $last_year  = __('Last Year (%1)', 
                            (clone $now)->modify('-1 year')->format('Y')
                        );
            $custom     = 'Custom Date Range';

            if ($dateRange == $today) {
                $fromDate = date('Y-m-d 00:00:00');
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $yesterday) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $toDate   = date('Y-m-d 23:59:59', strtotime('-1 day'));
            } elseif ($dateRange == $last_7) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_14) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-14 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $last_30) {
                $fromDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $toDate   = date('Y-m-d 23:59:59');
            } elseif ($dateRange == $this_month) {
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            } elseif ($dateRange == $last_month) {
                $fromDate = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $toDate   = date('Y-m-t 23:59:59', strtotime('-1 month'));
            } elseif ($dateRange == $this_year) {
                $fromDate = date('Y-01-01 00:00:00');
                $toDate   = date('Y-12-31 23:59:59');
            } elseif ($dateRange == $last_year) {
                $fromDate = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $toDate   = date('Y-12-31 23:59:59', strtotime('-1 year'));
            } elseif ($dateRange == $custom) {
                $fromDateParam = $this->getRequest()->getParam('from_date');
                $toDateParam   = $this->getRequest()->getParam('to_date');

                if ($fromDateParam && $toDateParam) {
                    $fromDate = date('Y-m-d 00:00:00', strtotime($fromDateParam));
                    $toDate   = date('Y-m-d 23:59:59', strtotime($toDateParam));
                }
            } else {
                // Default ke bulan berjalan
                $fromDate = date('Y-m-01 00:00:00');
                $toDate   = date('Y-m-t 23:59:59');
            }

            // $fromDate = '2025-09-01 00:00:00';
            // $toDate   = '2025-12-01 23:59:59';
            
            $collection->getSelect()->where(
                "main_table.creation_time >= ?",
                $fromDate
            )->where(
                "main_table.creation_time <= ?",
                $toDate
            );
        }

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            // dd($categoryId);
            
            $soTable  = $collection->getTable('sales_order');
            $soiTable = $collection->getTable('sales_order_item');
            $cpeTable = $collection->getTable('catalog_product_entity');
            $ccpTable = $collection->getTable('catalog_category_product');
            $cceTable = $collection->getTable('catalog_category_entity');

            $collection->getSelect()->where(
                "EXISTS (
                    SELECT 1
                    FROM {$soTable} AS so
                    INNER JOIN {$soiTable} AS soi ON soi.order_id = so.entity_id
                    INNER JOIN {$cpeTable} AS cpe ON cpe.entity_id = soi.product_id
                    INNER JOIN {$ccpTable} AS ccp ON ccp.product_id = cpe.entity_id
                    INNER JOIN {$cceTable} AS cce ON cce.entity_id = ccp.category_id
                    WHERE so.customer_id = main_table.customer_id
                    AND cpe.seller_id = main_table.seller_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
                // ,
                // (int)$categoryId
            );
        }

        return $collection;
    }

    public function applyDateFilter($collection) 
    {
        $date = $this->getTimezoneDateTime();
        
        // First day on current month
        $first_day = date('Y-m-01', strtotime($date));

        // Today by default
        // $first_day = date('Y-m-d', strtotime($date));
        $last_day = date('Y-m-d', strtotime($date));
        
        $collection->setDateColumnFilter($this->_columnDate)
        ->addDateFromFilter($first_day, null)
        ->addDateToFilter($last_day, null);

        return $collection;
    }

    public function getTransactionDateOptions()
    {
        $now = $this->_timezoneInterface->date();

        $todayEnd = $now->format('d/m/Y');
        $last7Start  = (clone $now)->modify('-6 days')->format('d/m/Y');
        $last14Start = (clone $now)->modify('-13 days')->format('d/m/Y');
        $last30Start = (clone $now)->modify('-29 days')->format('d/m/Y');

        return [
            'all'        => __('All Periods'),

            'today'      => __('Today (%1)', $now->format('d/m/Y')),

            'yesterday'  => __('Yesterday (%1)', 
                (clone $now)->modify('-1 day')->format('d/m/Y')
            ),

            'last_7'     => __('Last 7 days (%1 - %2)', $last7Start, $todayEnd),

            'last_14'    => __('Last 14 days (%1 - %2)', $last14Start, $todayEnd),

            'last_30'    => __('Last 30 days (%1 - %2)', $last30Start, $todayEnd),

            'this_month' => __('This Month (%1)', $now->format('F')),

            'last_month' => __('Last Month (%1)', 
                (clone $now)->modify('-1 month')->format('F')
            ),

            'this_year'  => __('This Year (%1)', $now->format('Y')),

            'last_year'  => __('Last Year (%1)', 
                (clone $now)->modify('-1 year')->format('Y')
            ),

            'custom'     => __('Custom Date Range'),
        ];
    }

    /**
     * @param $dateTime
     * @return string
     */
    public function getTimezoneDateTime($dateTime = "today")
    {
        if ($dateTime === "today" || !$dateTime) {
            $dateTime = $this->_date->gmtDate();
        }
        $today = $this->_timezoneInterface->date(new \DateTime($dateTime))->format('Y-m-d H:i:s');

        return $today;
    }

    /**
     * @return string
     */
    public function getResourceCollectionName()
    {
        return \Lof\MarketPlace\Model\ResourceModel\Sales\Collection::class;
    }

    /**
     * @param $country_code
     * @return string
     */
    public function getCountry($country_code)
    {
        $country_name = $this->_localList->getCountryTranslation($country_code);
        $cell_value = ($country_name ? $country_name : $country_code);
        return $cell_value;
    }

    /**
     * @return array
     */
    public function getDataCountry()
    {
        $data = [];
        $data['country'] = $data['amount'] = 0;
        $country = $this->getTopCountries();

        foreach ($country as $_country) {
            $data['country'] = $data['country'] + 1;
            $data['amount'] = $data['amount'] + $_country['seller_amount'];
        }

        return $data;
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSellerCollection()
    {
        $sellerCollection = $this->_sellerFactory->getCollection();
        return $sellerCollection;
    }

    /**
     * @return mixed|string
     */
    public function getSellerId()
    {
        $seller_id = '';
        $seller = $this->_sellerFactory->getCollection()
            ->addFieldToFilter('customer_id', $this->session->getId())->getData();
        foreach ($seller as $_seller) {
            $seller_id = $_seller['seller_id'];
        }

        return $seller_id;
    }

    /**
     * @return Dashboard
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Dashboard'));
        return parent::_prepareLayout();
    }

    /**
     * @return int|string
     */
    public function getCreditAmount()
    {
        $credit = 0;
        $amount = $this->amount->getCollection()->addFieldToFilter('seller_id', $this->getSellerId());
        foreach ($amount as $_amount) {
            $credit = $this->_helper->getPriceFomat($_amount->getAmount(), $this->getCurrencyCode());
        }

        return $credit;
    }

    /**
     * @param $price
     * @return string
     */
    public function getPriceFomat($price, $originalCurrency = null)
    {
        if (!$this->getWebsiteCode() && $originalCurrency) {
            $price = $this->convertToUsd($price, $originalCurrency);
        }

        return $this->_helper->getPriceFomat($price, $this->getCurrencyCode());
    }

    /**
     * @return mixed
     */
    public function getTopCountries()
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $resourceCollection = $objectManager->create($this->getResourceCollectionName())
        //     ->prepareByCountryCollection()
        //     ->setMainTableId("country_id");
        // $resourceCollection->applyCustomFilter();
        // return $resourceCollection->getData();
        $collection = $this->amounttransaction->getCollection();
$collection->addFieldToFilter('seller_id', $this->getSellerId());
$collection->addFieldToFilter('main_table.amount', ['neq' => null]);
$collection = $this->filterTransactionByWebsiteCode($collection, true);

$connection = $collection->getResource()->getConnection();
$select = $collection->getSelect();
$select->reset(\Zend_Db_Select::COLUMNS);

$select->join(
    ['o' => $connection->getTableName('sales_order')],
    "o.entity_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(main_table.description, 'order #', -1), ',', 1) AS UNSIGNED)",
    []
)->join(
    ['oi' => $connection->getTableName('sales_order_item')],
    'oi.order_id = o.entity_id',
    []
)->join(
    ['s' => $connection->getTableName('store')],
    's.store_id = o.store_id',
    []
)->join(
    ['sw' => $connection->getTableName('store_website')],
    'sw.website_id = s.website_id',
    []
)->where('oi.parent_item_id IS NULL')
->columns([
    'website_name' => 'sw.name',
    'website_id' => 'sw.website_id',
    'total_sales'  => new \Zend_Db_Expr('SUM(oi.row_total)'),
    'currency'   => 'o.base_currency_code'
])
->group('sw.name')
->order('total_sales DESC')
->limit(2);

$results = $connection->fetchAll($select);

return $results;

    }

    /**
     * @return false|string
     */
    public function getSalesReport()
    {
        $data = [];
        $dates = [];
        $dateT = date('t');
        for ($i = 1; $i <= $dateT; $i++) {
            $dates[] = date('Y') . "-" . date('m') . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $data[] = [];
        foreach ($dates as $key => $date) {
            $credit = $i = 0;
            $orderitems = $this->orderitems->getCollection()
                ->addFieldToFilter('main_table.seller_id', $this->getSellerId())
                ->setDateColumnFilter($this->_columnDate)
                ->addDateFromFilter($date, null)->addDateToFilter($date, null);
            $orderitems->applyCustomFilter();

            $orderitems = $this->_helperReport->filterByWebsiteCode($orderitems);

            foreach ($orderitems as $_orderitems) {
                $credit = $credit + $_orderitems->getSellerCommission() - $_orderitems->getSellerCommissionRefund();
                $i = $i + $_orderitems->getQtyInvoiced() - $_orderitems->getQtyRefunded();
            }

            $data[$key]['earn'] = $credit;
            $data[$key]['sales'] = $i;
            $data[$key]['period'] = substr($date, 5);
        }
        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getEarningsToDay()
    {
        $credit = 0;
        $amount = $this->amounttransaction->getCollection()
            ->addFieldToFilter('seller_id', $this->getSellerId())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($this->getTimezoneDateTime(), null)
            ->addDateToFilter($this->getTimezoneDateTime(), null);
        $amount->applyCustomFilter();

        $amount = $this->_helperReport->filterTransactionByWebsiteCode($amount);

        foreach ($amount as $_amount) {
            $credit = $credit + $_amount->getAmount();
        }

        return $this->_helper->getPriceFomat($credit, $this->getCurrencyCode());
    }

    public function getCurrencyCode() 
    {
        $websiteCode = $this->_helperReport->getWebsiteCode();
        if ($websiteCode) {
            if (!isset($this->currencyCodes[$websiteCode])) {
                $collection = $this->websiteCollectionFactory->create()
                ->addFieldToFilter('code', $websiteCode);
                if ($collection->getSize()) {
                    $website = $collection->getFirstItem();
                    $this->currencyCodes[$websiteCode] = $website->getDefaultStore()->getDefaultCurrencyCode();

                    return $this->currencyCodes[$websiteCode];
                }
            } else {
                return $this->currencyCodes[$websiteCode];
            }
        }

        return 'USD';
    }

    /**
     * @return string
     */
    public function getEarningsToMonth()
    {
        $credit = 0;
        $date = $this->getTimezoneDateTime();
        $first_day = date('Y-m-01', strtotime($date));
        $last_day = date('Y-m-t', strtotime($date));
        $amount = $this->amounttransaction->getCollection()
            ->addFieldToFilter('seller_id', $this->getSellerId())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($first_day, null)
            ->addDateToFilter($last_day, null);
        $amount->applyCustomFilter();
        
        $amount = $this->_helperReport->filterTransactionByWebsiteCode($amount);
        
        foreach ($amount as $_amount) {
            $currentMonthAmount = $_amount->getAmount();
            if ($currentMonthAmount > 0 || $currentMonthAmount = 0) {
                $credit = $credit + $currentMonthAmount;
            }
        }

        return $this->_helper->getPriceFomat($credit, $this->getCurrencyCode());
    }

    /**
     * @return int
     */
    public function getTotalSales()
    {
        $total = 0;
        $orderitems = $this->orderitems->getCollection()
            ->addFieldToFilter('main_table.seller_id', $this->getSellerId())
            ->addFieldToFilter('main_table.status', 'complete');
        $orderitems = $this->_helperReport->filterByWebsiteCode($orderitems);
        foreach ($orderitems as $item) {
            $total = $total + $item->getQtyInvoiced() - $item->getQtyRefunded();
        }

        return $total;
    }

    /**
     * @param $orderid
     * @return mixed
     */
    public function getOrder($orderid)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderDatamodel = $objectManager->get(\Magento\Sales\Model\Order::class)->load($orderid, 'entity_id');
        return $orderDatamodel;
    }

    /**
     * @return int
     */
    public function getTotalSalesDay()
    {
        $total = 0;
        $orderitems = $this->orderitems->getCollection()
            ->addFieldToFilter('main_table.seller_id', $this->getSellerId())
            ->addFieldToFilter('main_table.status', 'complete')
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($this->getTimezoneDateTime(), null)
            ->addDateToFilter($this->getTimezoneDateTime(), null);
        $orderitems->applyCustomFilter();

        $orderitems = $this->_helperReport->filterByWebsiteCode($orderitems);

        foreach ($orderitems as $_orderitems) {
            $total = $total + $_orderitems->getQtyInvoiced() - $_orderitems->getQtyRefunded();
        }

        return $total;
    }

    /**
     * @return int
     */
    public function getTotalSalesMonth()
    {
        $total = 0;
        $date = $this->getTimezoneDateTime();
        $first_day = date('Y-m-01', strtotime($date));
        $last_day = date('Y-m-t', strtotime($date));
        $orderitems = $this->orderitems->getCollection()
            ->addFieldToFilter('main_table.seller_id', $this->getSellerId())
            ->addFieldToFilter('main_table.status', 'complete')
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($first_day, null)
            ->addDateToFilter($last_day, null);
        $orderitems->applyCustomFilter();
        $orderitems = $this->_helperReport->filterByWebsiteCode($orderitems);
        foreach ($orderitems as $_orderitems) {
            $total = $total + $_orderitems->getQtyInvoiced() - $_orderitems->getQtyRefunded();
        }

        return $total;
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     */
    public function getOrderSeller()
    {
        $order = $this->order->getCollection()
            ->addFieldToFilter('seller_id', $this->getSellerId())
            ->setOrder('id', 'desc');
        $order = $this->_helperReport->filterByWebsiteCode($order);
        return $order;
    }

    /**
     * @return \Magento\Reports\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBestSeller()
    {
        $websiteCode = $this->_helperReport->getWebsiteCode();

        $collection = $this->_collectionFactory->create();
        $connection = $collection->getConnection();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('seller_id', $this->getSellerId());

        $resource = $collection->getResource();

        // join sales_order_item
        $collection->joinTable(
            ['order_items' => $resource->getTable('sales_order_item')],
            'product_id = entity_id',
            ['qty_ordered' => 'SUM(order_items.qty_ordered)'],
            null,
            'left'
        );

        $orderJoinCondition = [
            'order.entity_id = order_items.order_id',
            $connection->quoteInto("order.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        // join sales_order
        $collection->getSelect()
            ->joinInner(
                ['order' => $resource->getTable('sales_order')],
                implode(' AND ', $orderJoinCondition),
                []
            )
            // join store
            ->joinInner(
                ['st' => $resource->getTable('store')],
                'order.store_id = st.store_id',
                []
            )
            // join store_website
            ->joinInner(
                ['sw' => $resource->getTable('store_website')],
                'st.website_id = sw.website_id',
                []
            )
            ->where('sw.code = ?', $websiteCode) // << filter website code
            ->where('parent_item_id IS NULL')
            ->group('order_items.product_id')
            ->order('qty_ordered DESC');

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            $ccpTable = $resource->getTable('catalog_category_product');
            $cceTable = $resource->getTable('catalog_category_entity');

            $collection->getSelect()->where(
                "EXISTS (
                    SELECT 1
                    FROM {$ccpTable} AS ccp
                    INNER JOIN {$cceTable} AS cce ON cce.entity_id = ccp.category_id
                    WHERE ccp.product_id = e.entity_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
                // ,
                // (int)$categoryId
            );
        }

        return $collection;
    }

    public function getMyProductsUrlKey($categoryId) 
    {
        $categoryName = $this->getCategoryNameById($categoryId);
        if ($categoryName) {
            if ($categoryName == "Games") {
                return $cardgame;
            }
        }

        return null;
    }

    public function getCategoryNameById($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            return $category->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null; // category not found
        }

        return null;
    }

    /**
     * @return \Magento\Reports\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMostView()
    {
        $collection = $this->_collectionFactory->create();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('seller_id', $this->getSellerId());

        $resource = $collection->getResource();

        // join report_event
        $collection->joinTable(
            ['report_table_views' => $resource->getTable('report_event')],
            'object_id = entity_id',
            ['views' => 'COUNT(report_table_views.event_id)', 'store_id'], // ambil store_id juga
            null,
            'right'
        );

        // join store
        $collection->getSelect()->joinInner(
            ['st' => $resource->getTable('store')],
            'report_table_views.store_id = st.store_id',
            []
        );

        // join store_website
        $collection->getSelect()->joinInner(
            ['sw' => $resource->getTable('store_website')],
            'st.website_id = sw.website_id',
            []
        );

        // filter website code
        $collection->getSelect()->where('sw.code = ?', $this->_helperReport->getWebsiteCode());

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            $ccpTable = $resource->getTable('catalog_category_product');
            $cceTable = $resource->getTable('catalog_category_entity');

            $collection->getSelect()->where(
                "EXISTS (
                    SELECT 1
                    FROM {$ccpTable} AS ccp
                    INNER JOIN {$cceTable} AS cce ON cce.entity_id = ccp.category_id
                    WHERE ccp.product_id = e.entity_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
                // ,
                // (int)$categoryId
            );
        }


        // group & order
        $collection->getSelect()
            ->group('e.entity_id')
            ->order('views DESC');

        return $collection;

    }

    /**
     * @return int
     */
    public function getTotalProduct()
    {
        return $this->_helperReport->getTotalProduct($this->getSellerId());
    }
}
