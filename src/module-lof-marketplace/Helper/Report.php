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

namespace Lof\MarketPlace\Helper;

use Lof\MarketPlace\Model\SellerFactory;
use Lof\MarketPlace\Model\SellerProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;

class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var SellerProductFactory
     */
    protected $sellerProductFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $_sellerHelper;

    /**
     * @var \Lof\MarketPlace\Model\Order
     */
    protected $_marketOrder;
    protected $request;
    protected $storeManager;
    protected $productFactory;

    /**
     * Seller constructor.
     * @param Context $context
     * @param SellerFactory $sellerFactory
     * @param SellerProductFactory $sellerProductFactory
     * @param CustomerFactory $customerFactory
     * @param Data $sellerHelper
     * @param Session $customerSession
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        SellerFactory $sellerFactory,
        SellerProductFactory $sellerProductFactory,
        CustomerFactory $customerFactory,
        Data $sellerHelper,
        Session $customerSession,
        \Lof\MarketPlace\Model\Order $marketOrder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->sellerFactory = $sellerFactory;
        $this->sellerProductFactory = $sellerProductFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->_marketOrder = $marketOrder;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getSellerIdByProduct($productId)
    {
        $seller = $this->sellerProductFactory->create()->load($productId, 'product_id');
        return $seller->getSellerId();
    }

    /**
     * @param $productId
     * @return \Lof\MarketPlace\Model\SellerProduct
     */
    public function getSellerByProduct($productId)
    {
        return $this->sellerProductFactory->create()->load($productId, 'product_id');
    }

    /**
     * @return array|mixed|null
     */
    public function getSellerByCustomer()
    {
        $seller = $this->sellerFactory->create()->load($this->getCustomerId(), 'customer_id');
        return $seller->getData();
    }

    /**
     * @param $sellerId
     * @return Customer
     */
    public function getCustomerBySeller($sellerId)
    {
        $seller = $this->sellerFactory->create()->load($sellerId, 'seller_id');
        return $this->customerFactory->create()->load($seller->getCustomerId());
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        $customer = $this->customerSession->getCustomer();

        return $customer->getId();
    }

    /**
     * @param $country
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCountry($country)
    {
        $availableCountries = $this->_sellerHelper->getConfig('available_countries/available_countries');
        $enableAvailableCountries = $this->_sellerHelper->getConfig('available_countries/enable_available_countries');
        if ($enableAvailableCountries == '1' && $availableCountries) {
            $availableCountries = explode(',', $availableCountries);
            if (!in_array($country, $availableCountries)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * @param $sellerGroup
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkSellerGroup($sellerGroup)
    {
        $enableSellerGroup = $this->_sellerHelper->getConfig('group_seller/enable_group_seller');
        $availableSellerGroup = $this->_sellerHelper->getConfig('group_seller/group_seller');
        if ($enableSellerGroup == '1' && $availableSellerGroup) {
            $availableSellerGroup = explode(',', $availableSellerGroup);
            if (!in_array($sellerGroup, $availableSellerGroup)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * @param $sellerUrl
     * @return bool
     */
    public function checkSellerUrl($sellerUrl)
    {
        $collection = $this->sellerFactory->create()->getCollection();
        $collection->addFieldToFilter('url_key', $sellerUrl);
        if ($collection->getData()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function checkSellerExist($customerId)
    {
        $collection = $this->sellerFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $customerId);
        if ($collection->getData()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return int
     */
    public function getTotalOrders($sellerId)
    {
        $total = $this->_marketOrder->getCollection()
            ->addFieldToFilter('main_table.seller_id', $sellerId);
        $total = $this->filterByWebsiteCode($total);
        $total = $total->getSize();

        return $total ?: 0;
    }

    /**
     * @return int
     */
    public function getTotalCompletedOrder($sellerId)
    {
        $total = $this->_marketOrder->getCollection()
            ->addFieldToFilter('main_table.seller_id', $sellerId)
            ->addFieldToFilter('main_table.status', 'complete');
        $total = $this->filterByWebsiteCode($total);
        $total = $total->getSize();

        return $total ?: 0;
    }

    public function filterByWebsiteCode($collection) 
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

        // $fromDate = '2025-09-01 00:00:00';
        // $toDate   = '2025-09-30 23:59:59';

        // $collection->getSelect()->where(
        //     "so.created_at >= ?",
        //     $fromDate
        // )->where(
        //     "so.created_at <= ?",
        //     $toDate
        // );

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
                // ,
                // (int)$categoryId
            );
        }

        return $collection;
    }

    public function filterTransactionByWebsiteCode($collection) 
    {
        $websiteCode = $this->getWebsiteCode();
        if ($websiteCode) {
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
                ['increment_id', 'created_at', 'status', 'store_id']
            );

            // Join ke store & website
            $select->join(
                ['st' => $collection->getTable('store')],
                'so.store_id = st.store_id',
                ['store_code' => 'code', 'website_id']
            )->join(
                ['sw' => $collection->getTable('store_website')],
                'st.website_id = sw.website_id',
                ['website_code' => 'code']
            );

            // Filter by website code
            $select->where('sw.code = ?', $websiteCode);
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
                    INNER JOIN {$soTable} AS so ON so.entity_id = soi.order_id
                    INNER JOIN {$ccpTable} AS ccp ON soi.product_id = ccp.product_id
                    INNER JOIN {$cceTable} AS cce ON ccp.category_id = cce.entity_id
                    WHERE soi.order_id = so.entity_id
                    AND (cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})
                )"
                // ,
                // (int)$categoryId
            );
        }

        return $collection;

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

    /**
     * @param $sellerId
     * @return int
     */
    public function getTotalCompletedOrders($sellerId)
    {
        return $this->getTotalCompletedOrder($sellerId);
    }

    /**
     * @return int
     */
    public function getTotalProduct($sellerId)
    {
       /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productFactory->create()->getCollection()
        ->addFieldToFilter('publish_status', 1)
            ->addAttributeToSelect('*'); // ambil atribut produk kalau perlu

        $resource = $collection->getResource();

        // join ke lof_marketplace_product (alias lmp)
        $collection->getSelect()->join(
            ['lmp' => $resource->getTable('lof_marketplace_product')],
            'e.entity_id = lmp.product_id',
            []
        );

        // join ke catalog_product_website
        $collection->getSelect()->join(
            ['cpw' => $resource->getTable('catalog_product_website')],
            'e.entity_id = cpw.product_id',
            []
        );

        // join ke store_website
        $collection->getSelect()->join(
            ['sw' => $resource->getTable('store_website')],
            'cpw.website_id = sw.website_id',
            []
        );

        // filter seller_id + publish_status + website code
        $collection->getSelect()
            ->where('lmp.seller_id = ?', $sellerId)
            ->where('sw.code = ?', $this->getWebsiteCode());

        $categoryId = $this->request->getParam('category');
        if ($categoryId) {
            $collection->getSelect()
            ->join(
                ['ccp' => $resource->getTable('catalog_category_product')],
                'ccp.product_id = e.entity_id',
                []
            )
            ->join(
                ['cce' => $resource->getTable('catalog_category_entity')],
                'cce.entity_id = ccp.category_id',
                []
            )
            // ->where('cce.parent_id = ?', (int)$categoryId);
            ->where("(cce.entity_id = {$categoryId} OR cce.parent_id = {$categoryId})");
        }

        $total = $collection->getSize();


        return $total ?: 0;
    }
}
