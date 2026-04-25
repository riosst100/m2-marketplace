<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Rma\Api\Repository\ReasonRepositoryInterface;
use Lofmp\Rma\Api\Repository\ConditionRepositoryInterface;
use Lofmp\Rma\Api\Repository\ResolutionRepositoryInterface;

class RmaConfigurations implements ResolverInterface
{
    /**
     * @var ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var ConditionRepositoryInterface
     */
    protected $conditionRepository;

    /**
     * @var ResolutionRepositoryInterface
     */
    protected $resolutionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param ReasonRepositoryInterface $reasonRepository
     * @param ConditionRepositoryInterface $conditionRepository
     * @param ResolutionRepositoryInterface $resolutionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ReasonRepositoryInterface $reasonRepository,
        ConditionRepositoryInterface $conditionRepository,
        ResolutionRepositoryInterface $resolutionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->reasonRepository = $reasonRepository;
        $this->conditionRepository = $conditionRepository;
        $this->resolutionRepository = $resolutionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        // Build a blank search criteria to fetch all items
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $reasons = $this->reasonRepository->getList($searchCriteria)->getItems();
        $conditions = $this->conditionRepository->getList($searchCriteria)->getItems();
        $resolutions = $this->resolutionRepository->getList($searchCriteria)->getItems();

        return [
            'reasons' => $this->formatList($reasons),
            'conditions' => $this->formatList($conditions),
            'resolutions' => $this->formatList($resolutions)
        ];
    }

    /**
     * Format a list of repository items for GraphQL response
     *
     * @param array|\Traversable $items
     * @return array
     */
    private function formatList($items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => (int) $item->getId(),
                'name' => $item->getName(),
                'is_active' => (int) ($item->getIsActive() ?? 1)
            ];
        }
        return $result;
    }
}
