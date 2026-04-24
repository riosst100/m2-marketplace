<?php

namespace Lof\Faq\Model\Data;

use Magento\Store\Model\StoreManagerInterface;
use Lof\Faq\Model\ResourceModel\Question as ResourceQuestion;
use Magento\Framework\Exception\CouldNotSaveException;

class Question implements \Lof\Faq\Api\QuestionManagementInterface
{
    protected $_questionFactory;
    protected $jsHelper;
    /**
     * @var ResourceQuestion
     */
    protected $resource;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Question constructor.
     * @param ResourceQuestion $resource
     * @param \Lof\Faq\Model\QuestionFactory $questionFactory
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(\Lof\Faq\Model\QuestionFactory $questionFactory,
                                \Magento\Backend\Helper\Js $jsHelper,
                                StoreManagerInterface $storeManager,
                                ResourceQuestion $resource)
    {
        $this->_questionFactory = $questionFactory;
        $this->jsHelper = $jsHelper;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
    }

    /**
     * Save Question data
     *
     * @param \Lof\Faq\Api\Data\QuestionInterface $question
     * @return Question
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\Faq\Api\Data\QuestionInterface $question)
    {
        if ($question['title'] && $question['categories'] && $question['stores']) {
            try {
                $this->resource->save($question);
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(
                    __('Could not save the question: %1', $exception->getMessage()),
                    $exception
                );
            }
            return $question;
        } else {
            return false;
        }
    }

    public function saveInFrontend(\Lof\Faq\Api\Data\QuestionInterface $question)
    {
        if ($question['author_name'] && $question['title'] && $question['author_email'] && $question['stores']) {
            try {
                $this->resource->save($question);
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(
                    __('Could not save the question: %1', $exception->getMessage()),
                    $exception
                );
            }
            return $question;
        } else {
            return false;
        }
    }

}