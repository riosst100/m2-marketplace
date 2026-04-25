<?php
namespace Lofmp\CouponCode\Block\MarketPlace\Rule\Promo\Widget;

class Chooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;

    /**
     * @var string
     */
    protected $_template = 'Lofmp_FeaturedProducts::widget/grid/extended.phtml';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Lofmp\FeaturedProducts\Model\ResourceModel\FeaturedProduct\CollectionFactory
     */
    protected $featuredProductCollectionFactory;

    /**
     * @var \Lof\MarketPlace\Model\ResourceModel\SellerProduct\CollectionFactory
     */
    protected $sellerProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Lof\MarketPlace\Helper\Data $marketplaceHelper
     * @param \Lof\MarketPlace\Model\ResourceModel\SellerProduct\CollectionFactory $sellerProductCollectionFactory
     * @param \Lofmp\FeaturedProducts\Model\ResourceModel\FeaturedProduct\CollectionFactory $featuredProductCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Lof\MarketPlace\Helper\Data $marketplaceHelper,
        \Lof\MarketPlace\Model\ResourceModel\SellerProduct\CollectionFactory $sellerProductCollectionFactory,
        \Lofmp\FeaturedProducts\Model\ResourceModel\FeaturedProduct\CollectionFactory $featuredProductCollectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productCollectionFactory = $collectionFactory;
        $this->featuredProductCollectionFactory = $featuredProductCollectionFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->sellerProductCollectionFactory = $sellerProductCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('block_identifier');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
        // if ($this->getRequest()->getParam('current_grid_id')) {
        //     $this->setId($this->getRequest()->getParam('current_grid_id'));
        // } else {
        //     $this->setId('skuChooserGrid_' . $this->getId());
        // }

        // $form = $this->getJsFormObject();
        // $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        // $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        // $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        // $this->setDefaultSort('sku');
        // $this->setUseAjax(true);
        // if ($this->getRequest()->getParam('collapse')) {
        //     $this->setIsCollapsed(true);
        // }
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $selected = $this->_getSelectedProducts();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('sku', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('featuredproducts/product/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($product = $element->getFeaturedProduct()) {
            $chooser->setLabel($this->escapeHtml($product->getName()));
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * @return string
     */
    public function getRowClickCallback()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);            
        $logger->info('Generating row click callback for chooser grid');

        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var blockTitle = trElement.down("td").next().innerHTML;
                ' .
            $chooserJsObject .
            '.setElementValue(blockId);
                ' .
            $chooserJsObject .
            '.setElementLabel(blockTitle);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);            
        $logger->info('Preparing collection for chooser grid');

        $sellerId = $this->marketplaceHelper->getSellerId();
        $logger->info('Current seller ID: ' . $sellerId);
        $currentDate = date('Y-m-d');
        $includeIds = [];
        $excludeIds = [];

        $sellerProductCollection = $this->sellerProductCollectionFactory->create()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        foreach ($sellerProductCollection as $item){
            $includeIds[] = $item->getProductId();
        }
        $featuredProductCollection = $this->featuredProductCollectionFactory->create()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
            //->addFieldToFilter('featured_from', ['lteq' => $currentDate])
            //->addFieldToFilter('featured_to', ['gteq' => $currentDate]);
        foreach ($featuredProductCollection as $item){
            $excludeIds[] = $item->getProductId();
        }

        $includeIds = array_diff($includeIds, $excludeIds);

        $productCollection =$this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $includeIds]);
        $this->setCollection($productCollection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'sku',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'chooser_id',
            ['header' => __('ID'), 'align' => 'right', 'index' => 'entity_id', 'width' => 50]
        );

        $this->addColumn('chooser_title', ['header' => __('Product Name'), 'align' => 'left', 'index' => 'name']);

        $this->addColumn(
            'chooser_identifier',
            ['header' => __('SKU'), 'align' => 'left', 'index' => 'sku']
        );

        $this->addColumn(
            'chooser_is_active',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [0 => __('Disabled'), 1 => __('Enabled')]
            ]
        );


        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    // public function getGridUrl()
    // {
    //     return $this->getUrl('featuredproducts/product/chooser', ['_current' => true]);
    // }

    /**
     * Override getGridUrl to point to your own controller
     */
    public function getGridUrl()
    {
        $attribute = $this->getData('attribute') ?: 'sku';
        $form      = $this->getData('form') ?: 'rule_conditions_fieldset';
        $uniqId    = $this->getData('uniq_id') ?: $this->getId();

        // Build URL that will be: /marketplace/lofmpcouponcode/rule/promo_widget/chooser/attribute/sku/form/...
        // If your system uses a slightly different base path, adjust 'marketplace/lofmpcouponcode' accordingly.
        return $this->getUrl(
            'lofmpcouponcode/rule/chooser',
            ['attribute' => $attribute, 'form' => $form, 'uniq_id' => $uniqId]
        );
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected', []);

        return $products;
    }
}


