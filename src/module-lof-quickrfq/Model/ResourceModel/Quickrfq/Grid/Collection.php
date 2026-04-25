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
 * @package    Lof_Quickrfq
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
namespace Lof\Quickrfq\Model\ResourceModel\Quickrfq\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Lof\Quickrfq\Model\ResourceModel\Quickrfq\Collection as QuoteCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Collection
 * Collection for displaying grid of sales documents
 */
class Collection extends QuoteCollection implements SearchResultInterface
{
    protected $_eventPrefix;
    protected $_eventObject;
        /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var AdminWebsite
     */
    protected $adminWebsite;

    /**
     * @var AggregationInterface
     */
    protected $aggregations;


    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        
        $objectManager = ObjectManager::getInstance();
        $this->adminWebsite = $objectManager->get(
            \TCGCollective\MarketPlace\Helper\AdminWebsite::class
        );

        /** @var \Magento\Framework\App\RequestInterface $request */
        $this->request = $objectManager->get(
            \Magento\Framework\App\RequestInterface::class
        );
        
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }
    
    protected function _renderFiltersBefore()
    {        
        parent::_renderFiltersBefore();        

        // Super admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return;
        }

        $websiteId = (int) $this->request->getParam('website');
        if (!$websiteId) {
            return;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        // Hard guard (URL tampering protection)
        if (!in_array($websiteId, $allowedWebsiteIds, true)) {
            $this->getSelect()->where('1 = 0');
            return;
        }

        $this->getSelect()
        ->where(
            'main_table.website_id = ?',
            $websiteId
        );                        
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }


    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }



    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }


    public function getSearchCriteria()
    {
        return null;
    }


    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }


    public function getTotalCount()
    {
        return $this->getSize();
    }


    public function setTotalCount($totalCount)
    {
        return $this;
    }


    public function setItems(array $items = null)
    {
        return $this;
    }
}
