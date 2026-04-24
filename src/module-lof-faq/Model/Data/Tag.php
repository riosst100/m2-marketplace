<?php

namespace Lof\Faq\Model\Data;

use Lof\Faq\Model\ResourceModel\Tag as ResourceTag;
use Magento\Framework\Exception\CouldNotSaveException;
use Lof\Faq\Model\QuestionFactory;

class Tag extends \Magento\Framework\Api\AbstractExtensibleObject implements \Lof\Faq\Api\TagsInterface
{
    protected $_tagFactory;
    protected $resource;
    protected $_questionFactory;

    public function __construct(\Lof\Faq\Model\TagFactory $tagFactory,
                                ResourceTag $resource,
                                QuestionFactory $questionFactory)
    {
        $this->_tagFactory = $tagFactory;
        $this->_questionFactory = $questionFactory;
        $this->resource = $resource;
    }

    /**
     * @param \Lof\Faq\Api\Data\TagInterface $tag
     * @return \Lof\Faq\Api\Data\TagInterface
     */
    public function save(\Lof\Faq\Api\Data\TagInterface $tag)
    {
        try {
            if ($tag['name'] && $tag['categories'] && $tag['stores']) {
                if (!$tag['alias']) {
                    $tag['alias'] = $this->generateAlias($tag['name']);
                }
                $question = $this->_questionFactory->create()
                    ->load($tag['question_id']);
                $tagCollection = $this->_tagFactory->create()->getCollection();
                $checkUniqueTag = $tagCollection->addFieldToFilter('question_id', $tag['question_id'])->addFieldToFilter('alias', $tag['alias'])->count();

                $getTag = $this->_tagFactory->create()->load($tag['tag_id']);

                if (!empty($getTag->getId())) {
                    $this->resource->save($tag);
                    $question->setData([
                        'question_id' => $tag['question_id'],
                        'tag' => $tag['alias']
                    ])->save();
                    return $tag;
                }
                if ($checkUniqueTag) {
                    return false;
                } else {
                    $tags = !empty($question->getTag()) ? $question->getTag() . ',' . $tag['name'] : $tag['name'];
                    $tag['tag'] = $tags;
                    $question->setData([
                        'question_id' => $tag['question_id'],
                        'tag' => $tags,
                        'stores' => $tag['stores'],
                        'categories' => $tag['categories'],
                    ])->save();
                    return $tag;
                }
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the tag: %1', $exception->getMessage()),
                $exception
            );
        }
        return $tag;
    }

    protected function generateAlias($tag_name)
    {
        return strtolower(str_replace(["_", " "], "-", trim($tag_name)));
    }
}