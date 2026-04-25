<?php
namespace Lof\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Faq\Model\ResourceModel\TagUser\CollectionFactory;

class TagList implements ResolverInterface
{
    protected $tagCollection;

    public function __construct(CollectionFactory $tagCollection)
    {
        $this->tagCollection = $tagCollection;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->tagCollection->create()->setOrder('tag_id', 'ASC');
        $collection->addFieldToFilter('tag_type', 'user');

        $data = [];
        foreach ($collection as $tag) {
            $data[] = [                
                'tag_id' => $tag->getTagId(),
                'question_id' => $tag->getQuestionId(),
                'name' => $tag->getName(),
                'alias' => $tag->getAlias()
            ];
        }

        return $data;
    }
}
