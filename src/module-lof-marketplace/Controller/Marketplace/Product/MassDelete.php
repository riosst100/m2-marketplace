<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\MarketPlace\Controller\Marketplace\Product;

use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassDelete extends \Magento\Framework\App\Action\Action
{
    protected $mappingHelper;
    protected $helper;


    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $productType;

    /**
     * MassDelete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param Filter $filter
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        \Magento\Framework\Registry $registry,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $registry->register('isSecureArea', true);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // dd($this->getRequest()->getParams());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\CoreMarketplace\ProductAttributesLink\Helper\Data::class);
        $this->helper = $objectManager->get(\Lof\MarketPlace\Helper\Data::class);

        if ($this->getRequest()->getParam('selected')) {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } else {
            $collection = $this->collectionFactory->create();
        }

        $collection->addAttributeToSelect('card_product_type');
        
        $collection->addAttributeToFilter('seller_id', $this->helper->getSellerId());

        $namespace = $this->getRequest()->getParam('namespace') ?? '';
        if (str_contains($namespace, 'configurable')) {
            $collection->addFieldToFilter('type_id', 'configurable');
        } else {
            $collection->addFieldToFilter('type_id', 'simple');
        }

        $productType = null;
        $preOrderProductType = null;
        

        if (str_contains($namespace, 'lots')) {
            $productType = 'Lots/Sets';
        }

        if (str_contains($namespace, 'preorders')) {
            $productType = 'Pre Orders';

            if (str_contains($namespace, 'singles')) {
                $preOrderProductType = 'Singles';
            }

            if (str_contains($namespace, 'sealedproducts')) {
                $preOrderProductType = 'Sealed Products';
            }
        } elseif (str_contains($namespace, 'singles')) {
            $productType = 'Singles';
        } elseif (str_contains($namespace, 'sealedproducts')) {
            $productType = 'Sealed Products';
        }

        if (str_contains($namespace, 'supplies')) {
            $productType = 'Supplies';
        }

        if ($productType) {
            $productTypeVal = $this->mappingHelper->getOptionId('card_product_type', $productType);
            $collection->addAttributeToFilter('card_product_type', $productTypeVal);
        }

        if ($preOrderProductType) {
            $preOrderProductTypeVal = $this->mappingHelper->getOptionId('card_pre_order_product_type', $preOrderProductType);
            $collection->addAttributeToFilter('card_pre_order_product_type', $preOrderProductTypeVal);
        }

        // dd($collection->getSelect()->__toString());
        // dd($collection->getItems());

        $productDeleted = 0;
        $categoryId = null;
        $productCardProductType = null;
        $typeId = null;
        foreach ($collection->getItems() as $product) {
            $categoryId = $product->getCategoryIds() ? $product->getCategoryIds()[0] : null;
            $productCardProductType = $product->getCardProductType() ?? null;
            $typeId = $product->getTypeId() ?? null;

            // dd($product->getName());

            $product->delete();
            $productDeleted++;
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $productDeleted)
        );

        

        $manageProductKey = 'games';
            
        // $categoryId = $productData['category_ids'] ?? null;
        // dd($typeId);
        if ($categoryId) {
            $category = $this->mappingHelper->getCategoryById($categoryId);
            if ($category) {
                $parentCategory = $category->getParentCategory();
                if ($parentCategory) {
                    if ($parentCategory->getName() == "Action Toys") {
                        $manageProductKey = 'actiontoy';
                    }

                    if ($parentCategory->getName() == "Anime") {
                        $manageProductKey = 'anime';
                    }

                    if ($parentCategory->getName() == "Fashion Dolls") {
                        $manageProductKey = 'dolls';
                    }

                    if ($category->getName() == "Collectible Card Games") {
                        $manageProductKey = 'cardgame';

                        $productTypeVal = $productCardProductType ?? null;
                        if ($productTypeVal) {
                            $productTypeLabel = $this->mappingHelper->getOptionLabelById('card_product_type', $productTypeVal);
                            if ($productTypeLabel) {
                                $productTypeLabel = strtolower($productTypeLabel);
                                if (str_contains($productTypeLabel, 'supplies')) {
                                    $this->productType = 'supplies';
                                }

                                if (str_contains($productTypeLabel, 'sealedproducts')) {
                                    $this->productType = 'sealedproducts';
                                }

                                if (str_contains($productTypeLabel, 'singles')) {
                                    $this->productType = 'singles';
                                }

                                if (str_contains($productTypeLabel, 'lots')) {
                                    $this->productType = 'lots';
                                }

                                if (str_contains($productTypeLabel, 'pre orders')) {
                                    $this->productType = 'preorders';
                                }
                            }
                        }
                    }

                    if ($parentCategory->getName() == "Miniature Games") {
                        $manageProductKey = 'miniaturegames';
                    }

                    if ($parentCategory->getName() == "Cards") {
                        $manageProductKey = 'cards';
                    }

                    if ($parentCategory->getName() == "Bricks") {
                        $manageProductKey = 'lego';
                    }

                    if ($parentCategory->getName() == "Diecast") {
                        $manageProductKey = 'diecast';
                    }

                    if ($parentCategory->getName() == "Model Kits") {
                        $manageProductKey = 'modelkits';
                    }

                    if ($parentCategory->getName() == "Trains") {
                        $manageProductKey = 'trains';
                    }
                }
            }
        }

        $productType = $this->productType ?? 'sealedproducts';

        // dd($typeId);

        if ($typeId == "configurable") {
            $manageProductKey .= 'configurable';
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/'.$manageProductKey . '/' . $productType);
    }
}
