<?php
namespace Lof\Faq\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Faq\Model\ResourceModel\QuestionUser\CollectionFactory;

class QuestionList implements ResolverInterface
{
    protected $questionCollection;

    public function __construct(CollectionFactory $questionCollection)
    {
        $this->questionCollection = $questionCollection;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->questionCollection->create();

        if (!empty($args['category_id'])) {
            $collection->getSelect()->join(
                ['faq_cat' => $collection->getTable('lof_faq_question_category')],
                'main_table.question_id = faq_cat.question_id',
                []
            )->where('faq_cat.category_id = ?', (int)$args['category_id']);
        }
        
        if (!empty($args['tag'])) {
            $collection->addTagFilter($args['tag']);
        }

        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('question_type', 'user');

        $pageSize = $args['pageSize'] ?? 20;
        $currentPage = $args['currentPage'] ?? 1;
        $search = $args['search'] ?? null;

        $collection->setPageSize($pageSize)
        ->setCurPage($currentPage);

        if ($search) {
            $search = strtolower($search);
            $collection->getSelect()->where(
                '(LOWER(answer) LIKE ? OR LOWER(title) LIKE ?)',
                "%{$search}%"
            );
        }

        $data = [];
        foreach ($collection as $q) {
            // dd($q->getData());
            $data[] = [
                'question_id' => (int)$q->getQuestionId(),
                'title' => $q->getTitle(),
                'answer' => $q->getData()['answer'] == null ? '' : $q->getAnswer(),                
                'tags' => $q->getTag() ? explode(',', $q->getTag()) : [],                
                'author_email' => $q->getAuthorEmail(),
                'author_name' => $q->getAuthorName(),
                'creation_time' => $q->getCreationTime(),
                'update_time' => $q->getUpdateTime(),
                'is_featured' => $q->getIsFeatured(),
                'is_active' => $q->getIsActive(),
                'page_title' => $q->getPageTitle(),
                'meta_keywords' => $q->getMetaKeywords(),
                'meta_description' => $q->getMetaDescription(),
                'question_position' => $q->getQuestionPosition(),
                'like' => $q->getLike(),
                'disklike' => $q->getDisklike(),
                'title_size' => $q->getTitleSize(),
                'title_color' => $q->getTitleColor(),
                'title_color_active' => $q->getTitleColorActive(),
                'title_bg' => $q->getTitleBg(),
                'title_bg_active' => $q->getTitleBgActive(),
                'border_width' => $q->getBorderWidth(),
                'title_border_color' => $q->getTitleBorderColor(),
                'title_border_radius' => $q->getTitleBorderRadius(),
                'body_size' => $q->getBodySize(),
                'body_color' => $q->getBodyColor(),
                'body_bg' => $q->getBodyBg(),
                'question_margin' => $q->getQuestionMargin(),
                'question_icon' => $q->getQuestionIcon(),
                'question_active_icon' => $q->getQuestionActiveIcon(),
                'animation_class' => $q->getAnimationClass(),
                'animation_speed' => $q->getAnimationSpeed(),
                'question_url' => $q->getQuestionUrl(),
                'question_type' => $q->getQuestionType(),
                '_first_store_id' => $q->getFirstStoreId(),
                'store_code' => $q->getStoreCode(),
                'store_id' => $q->getStoreId()
            ];            
        }

        $totalPages = $args['pageSize'] ? ((int)ceil($collection->getSize() / $args['pageSize'])) : 0;

        return [
            'total_count' => $collection->getSize(),
            'items'       => $data,
            'page_info' => [
                'page_size' => $args['pageSize'],
                'current_page' => $args['currentPage'],
                'total_pages' => $totalPages
            ]
        ];
    }
}
