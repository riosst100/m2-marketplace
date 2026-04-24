<?php
namespace Lof\Faq\Model\Data;
use Lof\Faq\Api\Data;

class QuestionByCategory implements \Lof\Faq\Api\QuestionListByCategoryInterface
{
    protected $searchResultsFactory;
    protected $_productFaq;
    protected $_questionFactory;
    public function __construct(\Lof\Faq\Model\QuestionFactory $questionFactory,
                                Data\QuestionSearchResultsInterfaceFactory $searchResultsFactory)
    {
        $this->_questionFactory = $questionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }
    /**
     * GET for questionList api by category id
     * @param string $categoryId
     * @return \Lof\Faq\Api\Data\QuestionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuestionByCategoryForApi($categoryId){
        $questionCollection = $this->_questionFactory->create()->getCollection();
        $questionCollection->addFieldToFilter('is_active',1);
        $questionCollection->addCategoryFilter($categoryId)
                            ->setOrder('position', 'ASC');
        /** @var Data\QuestionSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($questionCollection->getItems());
        $searchResults->setTotalCount($questionCollection->getSize());
        return $searchResults;
    }

}
