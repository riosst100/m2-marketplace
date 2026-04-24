<?php


namespace Lof\Faq\Api;

interface QuestionListByProductSku
{
    /**
     * GET for questionList api by product sku
     * @param string $product_sku
     * @return \Lof\Faq\Api\Data\QuestionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuestionByProductSkuForApi($product_sku);

}
