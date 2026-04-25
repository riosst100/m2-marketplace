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

use Lof\MarketPlace\Controller\Marketplace\Product\ImportResult as ImportResultController;
use Lof\MarketPlace\Helper\Data;
use Lof\MarketPlace\Model\SellerProduct;
use Lof\MarketPlace\Model\SellerProductFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\History as ModelHistory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Processimport extends ImportResultController implements HttpGetActionInterface, HttpPostActionInterface
{
    protected $customerSession;
    protected $_frontendUrl;
    protected $_actionFlag;
    protected $mappingHelper;
    protected $request;
    protected $storeManager;
    protected $productAction;


    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SellerProduct
     */
    protected $_sellerProduct;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;


    /**
     * @var CollectionFactory
     */
    private $_attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_entityCollectionFactory;

    /**
     * @var CategoryFactory
     */
    private $_categoryFactory;

    /**
     * @var ItemFactory
     */
    private $_stockItemFactory;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    // NEW: message publisher
    protected $publisher;

    // Topic name
    const TOPIC_PRODUCT_IMPORT = 'lof.marketplace.product_import';

    /**
     * @var string[]
     */
    protected $_fieldsMap = [
        'attribute_set_code' => 'attribute_set_id',
        'name' => 'name',
        'sku' => 'sku',
        'price' => 'price',
        'tax_class_name' => 'tax_class_id',
        'qty' => 'qty',
        'is_in_stock' => 'is_in_stock',
        'weight' => 'weight',
        'visibility' => 'visibility',
        'categories' => 'categories',
        'description' => 'description',
        'short_description' => 'short_description',
        'base_image' => 'image',
        'url_key' => 'url_key'
    ];

    /**
     * @var string[]
     */
    protected $_pendingAttributeFieldsMap = [
        'attribute_set_id' => 'attribute_set_id',
        'name' => 'name',
        'sku' => 'sku',
        'price' => 'price',
        'tax_class_id' => 'tax_class_id',
        'qty' => 'qty',
        'is_in_stock' => 'is_in_stock',
        'weight' => 'weight',
        'visibility' => 'visibility',
        'category_ids' => 'categories',
        'description' => 'description',
        'short_description' => 'short_description',
        'images' => 'image',
        'url_key' => 'url_key'
    ];

    /**
     * Process Import constructor.
     * @param Context $context
     * @param ReportProcessorInterface $reportProcessor
     * @param History $historyModel
     * @param Report $reportHelper
     * @param Csv $csvProcessor
     * @param ProductFactory $productFactory
     * @param Data $helper
     * @param SellerProductFactory $sellerProduct
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $attrSetColFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param CategoryFactory $categoryFactory
     * @param ItemFactory $stockItemFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ReportProcessorInterface $reportProcessor,
        History $historyModel,
        Report $reportHelper,
        Csv $csvProcessor,
        ProductFactory $productFactory,
        Data $helper,
        SellerProductFactory $sellerProduct,
        \Magento\ImportExport\Model\Import $importModel,
        ResourceConnection $resourceConnection,
        CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        ItemFactory $stockItemFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        PublisherInterface $publisher
    ) {
        parent::__construct(
            $context,
            $reportProcessor,
            $historyModel,
            $reportHelper
        );

        $this->importModel = $importModel;
        $this->csvProcessor = $csvProcessor;
        $this->_productFactory = $productFactory;
        $this->helper = $helper;
        $this->_sellerProduct = $sellerProduct;
        $this->resourceConnection = $resourceConnection;
        $this->_attrSetColFactory = $attrSetColFactory;
        $this->_entityCollectionFactory = $collectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_stockItemFactory = $stockItemFactory;
        $this->customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->_frontendUrl = $frontendUrl;
        $this->_actionFlag = $context->getActionFlag();
        $this->publisher = $publisher;
    }

    /**
     * @param $route
     * @param $params
     * @return string|null
     */
    public function getFrontendUrl($route, $params)
    {
        return $this->_frontendUrl->getUrl($route, $params);
    }

    /**
     * Redirect to URL
     * @param string $url
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function _redirectUrl($route = '', $params = [])
    {
        $this->getResponse()->setRedirect($this->getFrontendUrl($route, $params));
        $this->customerSession->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        return $this->getResponse();
    }

    /**
     * Start import process action
     *
     * @return ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
     */
    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productAction = $objectManager->create(\Magento\Catalog\Model\Product\Action::class);

        $customerSession = $this->customerSession;
        $customerId = $customerSession->getId();
        $sellerModel = $this->sellerFactory->create()->load($customerId, 'customer_id');
        $status = $sellerModel->getStatus();
        if ($customerSession->isLoggedIn() && $status == 1) {
            $data = $this->getRequest()->getPostValue();
            if ($data) {
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);

                $this->_eventManager->dispatch('marketplace_start_import_product', [
                    'object' => $this,
                    'seller' => $sellerModel,
                    'data' => $data
                ]);
                /** @var Layout $resultLayout */
                $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
                /** @var Result $resultBlock */
                $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
                $resultBlock
                    ->addAction('show', 'import_validation_container')
                    ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                    ->addAction('hide', ['edit_form', 'upload_button', 'messages']);

                $approval = $this->helper->getConfig('seller_settings/approval');
                $approvalEditing = $this->helper->getConfig('seller_settings/approval_editing');
                $pendingProductAttributes = $this->helper
                    ->getConfig('seller_settings/pending_product_attributes');
                $behavior = $this->getRequest()->getParam('behavior');
                $productUpdateIds = [];

                if ($approval && $approvalEditing && $behavior != 'delete') {
                    if ($pendingProductAttributes != 0) {
                        $pendingProductAttributes = explode(',', $pendingProductAttributes);
                        foreach ($pendingProductAttributes as $k => $v) {
                            $pendingProductAttributes[$k] = $this->_pendingAttributeFieldsMap[$v];
                        }
                    }

                    // sample a few rows to detect productUpdateIds (same logic as before)
                    $fileUpload = $this->getRequest()->getFiles();
                    $tmp_name = $fileUpload['import_file']['tmp_name'] ?? null;
                    if ($tmp_name && file_exists($tmp_name)) {
                        try {
                            $csvData = $this->csvProcessor->getData($tmp_name);
                            if ($csvData) {
                                $csvData = $this->mergeData($csvData);
                                foreach ($csvData as $_csvData) {
                                    $orgProductData = $this->_productFactory->create()
                                        ->loadByAttribute('sku', $_csvData['sku']);
                                    if ($orgProductData && $pendingProductAttributes == 0) {
                                        foreach ($_csvData as $key => $value) {
                                            $attributeCode = isset($this->_fieldsMap[$key]) ? $this->_fieldsMap[$key] : '';
                                            if ($attributeCode != null && $value != null) {
                                                if ($value != $this->getProductData($orgProductData, $attributeCode)) {
                                                    array_push($productUpdateIds, $orgProductData->getId());
                                                }
                                            }
                                        }
                                    } elseif ($orgProductData) {
                                        foreach ($_csvData as $key => $value) {
                                            $attributeCode = isset($this->_fieldsMap[$key]) ? $this->_fieldsMap[$key] : '';
                                            if ($attributeCode != null
                                                && in_array($attributeCode, $pendingProductAttributes)
                                                && $value != null
                                            ) {
                                                if ($value != $this->getProductData($orgProductData, $attributeCode)) {
                                                    array_push($productUpdateIds, $orgProductData->getId());
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            // ignore sampling errors — still allow queueing
                        }
                    }
                }

                // Move uploaded file to var/import/lof_marketplace
                $fileUpload = $this->getRequest()->getFiles();
                $tmp_name = $fileUpload['import_file']['tmp_name'] ?? null;
                // Custom tmp_name
                $targetDirValidated  = BP . '/var/importexport';
                $tmp_name = $targetDirValidated . '/catalog_product.csv';

                $origName = $fileUpload['import_file']['name'] ?? 'upload.csv';

                if (!$tmp_name || !file_exists($tmp_name)) {
                    $resultBlock->addError(__('No import file uploaded or file could not be read.'));
                    return $resultLayout;
                }

                $targetDir = BP . '/var/import/lof_marketplace';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($origName));
                $targetFile = $targetDir . '/' . uniqid('import_') . '_' . $safeName;

                $moved = false;
                try {
                    if (is_uploaded_file($tmp_name)) {
                        $moved = @move_uploaded_file($tmp_name, $targetFile);
                    } else {
                        $moved = @copy($tmp_name, $targetFile);
                    }
                    @chmod($targetFile, 0644);
                } catch (\Exception $e) {
                    $moved = false;
                }

                if (!$moved || !file_exists($targetFile)) {
                    $resultBlock->addError(__('Failed to move uploaded file to %1', $targetDir));
                    return $resultLayout;
                }

                // Use validated file from var/importexport instead of uploaded one
                // $targetDir  = BP . '/var/importexport';
                // $targetFile = $targetDir . '/catalog_product.csv';
                // $origName   = 'catalog_product.csv';

                if (!file_exists($targetFile)) {
                    $resultBlock->addError(__('Validated import file not found: %1', $targetFile));
                    return $resultLayout;
                }

                // Basic header check: must contain 'sku'
                try {
                    // $logger->info('ProcessImport: Validating import file header: ' . $targetFile);
                    $csvHead = $this->csvProcessor->getData($targetFile);
                    if (!$csvHead || !isset($csvHead[0]) || !in_array('sku', $csvHead[0])) {
                        @unlink($targetFile);
                        $resultBlock->addError(__('Uploaded CSV must contain a "sku" column.'));
                        return $resultLayout;
                    }
                } catch (\Exception $e) {
                    $logger->info('CSV read failed: ' . $e->getMessage());
                }

                // Build payload with only primitive-friendly values
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->session = $objectManager->create(\Magento\Framework\Session\SessionManager::class);                
                $historyId = $this->session->getData('history_id');
                $data['history_id'] = $historyId;

                $payload = [
                    'file' => $targetFile,
                    'data' => $data,
                    'seller_customer_id' => $customerId,
                    'seller_id' => $sellerModel->getSellerId(),
                    'approval' => (int)$approval,
                    'approval_editing' => (int)$approvalEditing,
                    'pending_attributes' => $pendingProductAttributes,
                    'product_update_ids' => $productUpdateIds
                ];
                $this->session->unsetData('history_id');
                try {
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('===== PUBLISHING TO QUEUE =====');
                    $logger->info('Payload: '.json_encode($payload));

                    $this->publisher->publish(self::TOPIC_PRODUCT_IMPORT, json_encode($payload));
                    
                    // Save payload to history 
                    $history = $this->historyModel->load($historyId);

                    if ($history->getId()) {                        
                        $history->setData('data_import', json_encode($payload));
                        $history->save();
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(__('History not found'));
                    }

                    $this->_eventManager->dispatch('marketplace_import_product_queued', [
                        'object' => $this,
                        'seller' => $sellerModel,
                        'payload' => $payload
                    ]);

                    $resultBlock->addSuccess(__('Import queued. The file will be processed in the background.'));
                } catch (\Exception $e) {
                    // $logger->info('Failed to queue import: ' . $e->getMessage());
                    $resultBlock->addError(__('Could not queue import: %1', $e->getMessage()));
                }

                return $resultLayout;
            }

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/import');
            return $resultRedirect;
        } elseif ($customerSession->isLoggedIn() && $status == 0) {
            $this->_redirectUrl('lofmarketplace/seller/becomeseller');
        } else {
            $this->messageManager->addNoticeMessage(__('You must have a seller account to access'));
            $this->_redirectUrl('lofmarketplace/seller/login');
        }
    }

    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getImportedFile());
        $writeOnlyErrorItems = true;
        if ($this->historyModel->getData('execution_time') == ModelHistory::IMPORT_VALIDATION) {
            $writeOnlyErrorItems = false;
        }
        $fileName = $this->reportProcessor->createReport($sourceFile, $errorAggregator, $writeOnlyErrorItems);
        $this->historyModel->addErrorReportFile($fileName);
        return $fileName;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isNewProduct($productId)
    {
        $sellerProduct = $sellerProduct = $this->_sellerProduct->create()->load($productId, 'product_id');
        if (!$sellerProduct->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isUpdateProduct($productId)
    {
        $sellerProduct = $sellerProduct = $this->_sellerProduct->create()->load($productId, 'product_id');
        if ($sellerProduct->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return array
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function mergeData($data)
    {
        $dataLength = count($data);
        $dataKeyLength = count($data[0]);
        $result = [];
        for ($dataCount = 1; $dataCount < $dataLength; $dataCount++) {
            $_data = [];
            for ($i = 0; $i < $dataKeyLength; $i++) {
                $_data[$data[0][$i]] = $data[$dataCount][$i];
            }
            array_push($result, $_data);
        }
        return $result;
    }

    /**
     * @param $attributeId
     * @return |null
     */
    public function isUpdatedAttributeSet($attributeId)
    {
        $attribute = $this->_attrSetColFactory->create()->getItemById($attributeId);
        if ($attribute) {
            return $attribute->getAttributeSetName();
        }
        return null;
    }

    /**
     * @param $attributeSetId
     * @return |null
     */
    public function getProductAttributeName($attributeSetId)
    {
        $item = $this->_attrSetColFactory->create()->load();
        $item = $item->getItemById($attributeSetId);
        $attributeSetName = $item->getAttributeSetName();
        if ($attributeSetName) {
            return $attributeSetName;
        }
        return null;
    }

    /**
     * @param $productId
     * @return float|null
     */
    public function getProductQty($productId)
    {
        $qty = $this->_stockItemFactory->create()->load($productId, 'product_id')->getQty();
        if ($qty) {
            return $qty;
        }
        return null;
    }

    /**
     * @param $productId
     * @return bool|int|null
     */
    public function getProductStockStatus($productId)
    {
        $stockStatus = $this->_stockItemFactory->create()->load($productId, 'product_id')->getIsInStock();
        if ($stockStatus) {
            return $stockStatus;
        }
        return null;
    }

    /**
     * @param $categoryIds
     * @return string
     */
    public function getProductCategory($categoryIds)
    {
        $categoryNames = [];
        foreach ($categoryIds as $categoryId) {
            $data = $this->_categoryFactory->create();
            $_categoryId = $categoryId;
            $categoryName = '';
            do {
                if ($categoryId != $_categoryId) {
                    $categoryName = '/' . $categoryName;
                }
                $data->load($categoryId);
                $name = $data->getName();
                $categoryName = $name . $categoryName;
                $categoryId = $data->getParentId();
            } while ($categoryId > 1);
            if ($categoryName != '') {
                array_push($categoryNames, $categoryName);
            }
        }
        if (count($categoryNames) > 0) {
            return implode(",", $categoryNames);
        }
        return null;
    }

    /**
     * @param $product
     * @param $key
     * @return string|bool|int
     */
    public function getProductData($product, $key)
    {
        if ($key == 'attribute_set_id') {
            return $this->getProductAttributeName($product->getData('attribute_set_id'));
        }
        if ($key == 'qty') {
            return $this->getProductQty($product->getId());
        }
        if ($key == 'is_in_stock') {
            return $this->getProductStockStatus($product->getId());
        }
        if ($key == 'categories') {
            return $this->getProductCategory($product->getCategoryIds());
        }
        if ($key == 'tax_class_id') {
            return $product->getAttributeText('tax_class_id');
        }
        if ($key == 'visibility') {
            return $product->getAttributeText('visibility')->getText();
        }
        return $product->getData($key);
    }
}
