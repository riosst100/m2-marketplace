<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Lofmp\Rma\Api\Repository\CustomerRmaRepositoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Lofmp\Rma\Helper\Data as RmaHelper;

class RmaList implements ResolverInterface
{
    private $searchCriteriaBuilder;
    private $customerRmaRepository;
    private $sortOrderBuilder;
    protected $orderRepository;
    protected $rmaHelper;
    protected $imageHelper;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRmaRepositoryInterface $customerRmaRepository,
        SortOrderBuilder $sortOrderBuilder,
        OrderRepositoryInterface $orderRepository,
        RmaHelper $rmaHelper,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRmaRepository = $customerRmaRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->orderRepository = $orderRepository;
        $this->rmaHelper = $rmaHelper;
        $this->imageHelper = $imageHelper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = $context->getUserId();

        $pageSize = isset($args['pageSize']) ? (int)$args['pageSize'] : 20;
        $currentPage = isset($args['currentPage']) ? (int)$args['currentPage'] : 1;

        // build search criteria
        $this->searchCriteriaBuilder->setCurrentPage($currentPage);
        $this->searchCriteriaBuilder->setPageSize($pageSize);

        // optional sorting: newest first
        $sort = $this->sortOrderBuilder->setField('created_at')->setDirection('DESC')->create();
        $this->searchCriteriaBuilder->addSortOrder($sort);

        // optional: simple text filter if provided (you can expand to proper filters)
        if (!empty($args['filter'])) {
            // Example: search by increment_id or order id (module might require different filter API)
            $this->searchCriteriaBuilder->addFilter('order.increment_id', $args['filter'], 'like');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        try {
            $list = $this->customerRmaRepository->getList($customerId, $searchCriteria);
            $items = [];
            foreach ($list->getItems() as $rma) {
                $items[] = $this->mapRmaToGraph($rma, $context);
            }

            return [
                'items' => $items,
                'total_count' => $list->getTotalCount()
            ];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    private function mapRmaToGraph($rma, $context)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/rma.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);  
        $logger->info('RMA Data: '.json_encode($rma->getData()));

        $order = $this->orderRepository->get($rma->getOrderId());
        $seller = $rma->getSellerId() ? $this->rmaHelper->getSellerById($rma->getSellerId()) : null;

        // RMA items
        $rmaItems = [];
        foreach ($order->getAllItems() as $item) {
            $itemData = $this->rmaHelper->getRmaItemData($item, $rma->getId());
            if (empty($itemData) || ($itemData['qty_requested'] == 0)) {
                continue;
            }

            $options = [];
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['attributes_info'])) {
                foreach ($productOptions['attributes_info'] as $opt) {
                    $options[] = [
                        'label' => $opt['label'],
                        'value' => $opt['value']
                    ];
                }
            }
            // dd($this->rmaHelper->getSellerName($rma->getSellerId()));
            $store = $context->getExtensionAttributes()->getStore();
            $currency = $store->getCurrentCurrencyCode();

            $rmaItems[] = [
                'id' => $item->getItemId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'price' => [
                    'value' => (float)$item->getPrice(),
                    'currency' => $store->getCurrentCurrencyCode() // or base currency
                ],
                'price_incl_tax' => [
                    'value' => (float)$item->getPriceInclTax(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'amount_refunded' => [
                    'value' => (float)$item->getAmountRefunded(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'discount_amount' => [
                    'value' => (float)$item->getDiscountAmount(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'qty_requested' => $itemData['qty_requested'] ?? 0,
                'reason' => $itemData['reason_name'] ?? '',
                'condition' => $itemData['condition_name'] ?? '',
                'resolution' => $itemData['resolution_name'] ?? '',
                'image_url' => $this->initImage($item)->resize(150)->getUrl(),
                'product_options' => $options,
                'seller_name' => $this->rmaHelper->getSellerName($rma->getSellerId())
            ];
        }

        return [
            'rma_id' => $rma->getRmaId() ?? $rma->getId() ?? null,
            'increment_id' => $rma->getIncrementId() ?? null,
            'order_id' => $rma->getOrderId() ?? null,
            'order_increment_id' => $rma->getOrderIncrementId() ?? null,
            // 'status_id' => $rma->getStatusId() ?? null,
            'status_label' => $rma->getStatusName() ?? null,
            'created_at' => $rma->getCreatedAt() ?? null,
            'items' => $rmaItems
        ];
    }

    public function initImage($item)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku', $item->getData('sku'));
        return $this->imageHelper->init($product, 'product_page_image_small', ['type' => 'small_image']);
    }    
}
