<?php
namespace Lof\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Faq\Model\ResourceModel\CategoryUser\CollectionFactory;

class CategoryList implements ResolverInterface
{
    protected $categoryCollection;

    public function __construct(CollectionFactory $categoryCollection)
    {
        $this->categoryCollection = $categoryCollection;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->categoryCollection->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('category_type', 'user')
            ->setOrder('position', 'ASC');

        $data = [];
        foreach ($collection as $cat) {
            $data[] = [
                'category_id' => (int)$cat->getCategoryId(),
                'title' => $cat->getTitle(),
                'description' => $cat->getDescription(),
                'position' => (int)$cat->getPosition(),
                'is_active' => (int)$cat->getIsActive()
            ];
        }
        return $data;
    }
}
