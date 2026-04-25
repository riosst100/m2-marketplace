<?php
namespace Lofmp\CouponCode\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CategoryOptions implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Constructor
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Return list of categories as options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        // Get category collection (exclude root category)
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', ['gt' => 1]) // skip "Default Category"
            ->addIsActiveFilter()
            ->setOrder('path', 'ASC');

        foreach ($collection as $category) {
            $options[] = [
                'label' => str_repeat('-- ', $category->getLevel() - 2) . $category->getName(),
                'value' => $category->getId()
            ];
        }

        return $options;
    }
}
