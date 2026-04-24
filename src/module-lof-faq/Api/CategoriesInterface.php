<?php


namespace Lof\Faq\Api;

interface CategoriesInterface
{
    /**
     * Get categories
     * @return \Lof\Faq\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListInBackend();

    /**
     * Get categories
     * @return \Lof\Faq\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListInFrontend();

    /**
     * Save Category.
     *
     * @param \Lof\Faq\Api\Data\CategoryInterface $category
     * @return \Lof\Faq\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\Faq\Api\Data\CategoryInterface $category);
    
}
