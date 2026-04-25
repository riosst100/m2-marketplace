<?php
namespace Lof\Quickrfq\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Quickrfq\Api\QuickrfqRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class QuickrfqList implements ResolverInterface
{
    /**
     * @var QuickrfqRepositoryInterface
     */
    private $quickrfqRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * QuickrfqList constructor.
     * @param QuickrfqRepositoryInterface $quickrfqRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        QuickrfqRepositoryInterface $quickrfqRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->quickrfqRepository = $quickrfqRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Resolve GraphQL query
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = $context->getUserId();
        
        $args = $args ?? [];

        $pageSize = isset($args['pageSize']) && (int)$args['pageSize'] > 0 ? (int)$args['pageSize'] : 20;
        $currentPage = isset($args['currentPage']) && (int)$args['currentPage'] > 0 ? (int)$args['currentPage'] : 1;

        $this->searchCriteriaBuilder->setPageSize($pageSize);
        $this->searchCriteriaBuilder->setCurrentPage($currentPage);

        // handle simple filters: customer_id, status, email, product_id
        if (!empty($args['filter']) && is_array($args['filter'])) {
            foreach ($args['filter'] as $fieldName => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                // allow partial search for email
                $conditionType = $fieldName === 'email' ? 'like' : 'eq';
                $filter = $this->filterBuilder
                    ->setField($fieldName)
                    ->setValue($conditionType === 'like' ? "%{$value}%" : $value)
                    ->setConditionType($conditionType)
                    ->create();
                $this->searchCriteriaBuilder->addFilters([$filter]);
            }
        }

        // sort
        if (!empty($args['sort']) && is_array($args['sort'])) {
            $field = isset($args['sort']['field']) ? $args['sort']['field'] : 'update_date';
            $dir = isset($args['sort']['direction']) ? strtoupper($args['sort']['direction']) : 'DESC';
            $sortOrder = $this->sortOrderBuilder->setField($field)->setDirection($dir)->create();
            $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        } else {
            // default sort by update_date DESC if available
            try {
                $sortOrder = $this->sortOrderBuilder->setField('update_date')->setDirection('DESC')->create();
                $this->searchCriteriaBuilder->addSortOrder($sortOrder);
            } catch (\Exception $e) {
                // ignore if field missing
            }
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->quickrfqRepository->getList($searchCriteria);

        $items = [];
        foreach ($searchResults->getItems() as $item) {
            // convert data object to array of fields expected by GraphQL
            $items[] = [
                'quickrfq_id' => $item->getQuickrfqId(),
                'contact_name' => $item->getContactName(),
                'email' => $item->getEmail(),
                'phone' => $item->getPhone(),
                'product_id' => $item->getProductId(),
                'product_name' => method_exists($item, 'getProductName') ? $item->getProductName() : $item->getData('product_name'),
                'quantity' => $item->getQuantity(),
                'price_per_product' => [
                    'value' => (float)$item->getPricePerProduct(),
                    'currency' => $item->getStoreCurrencyCode()
                ],
                'comment' => $item->getComment(),
                'overview' => $item->getOverview(),
                'status' => $item->getStatus(),
                'update_date' => $item->getUpdateDate(),
                'create_date' => $item->getCreateDate(),
                'attachment' => $item->getAttachment(),
                'cart_id' => $item->getCartId(),
                'store_id' => $item->getStoreId(),
                'website_id' => $item->getWebsiteId(),
                'store_currency_code' => $item->getStoreCurrencyCode(),
                'info_buy_request' => $item->getInfoBuyRequest(),
                'attributes' => $item->getAttributes()
            ];
        }

        return [
            'items' => $items,
            'total_count' => $searchResults->getTotalCount(),
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage
            ]
        ];
    }
}
