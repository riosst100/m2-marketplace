<?php


namespace Lof\Faq\Api;

interface QuestionManagementInterface
{
    /**
     * Save Question.
     *
     * @param \Lof\Faq\Api\Data\QuestionInterface $question
     * @return \Lof\Faq\Api\Data\QuestionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\Faq\Api\Data\QuestionInterface $question);

    /**
     * Save Question.
     *
     * @param \Lof\Faq\Api\Data\QuestionInterface $question
     * @return \Lof\Faq\Api\Data\QuestionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveInFrontend(\Lof\Faq\Api\Data\QuestionInterface $question);

}
