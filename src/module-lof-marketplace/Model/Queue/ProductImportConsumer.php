<?php
namespace Lof\MarketPlace\Model\Queue;

use Psr\Log\LoggerInterface;
use Lof\MarketPlace\Model\SellerProductFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\File\Csv;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Lof\MarketPlace\Model\SellerFactory;
use Lof\MarketPlace\Helper\Data;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

class ProductImportConsumer
{
    protected $logger;
    protected $sellerProductFactory;
    protected $productFactory;
    protected $productAction;
    protected $csvProcessor;
    protected $resourceConnection;
    protected $storeManager;
    protected $sellerFactory;
    protected $helper;
    protected $stockItemFactory;
    protected $_attrSetColFactory;
    protected $_categoryFactory;
    protected $eventManager;
    protected $importModel;
    protected $filesystem;
    protected $notificationFactory;
    protected $notificationResource;
    protected $detailFactory;
    protected $detailResource;

    public function __construct(
        LoggerInterface $logger,
        SellerProductFactory $sellerProductFactory,
        ProductFactory $productFactory,
        ProductAction $productAction,
        Csv $csvProcessor,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        SellerFactory $sellerFactory,
        Data $helper,
        ItemFactory $stockItemFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        EventManager $eventManager,
        ImportModel $importModel,
        \Magento\Framework\Filesystem $filesystem,
        \CoreMarketplace\MarketPlace\Model\SellerNotificationsFactory $notificationFactory,
        \CoreMarketplace\MarketPlace\Model\ResourceModel\SellerNotificationsFactory $notificationResource,
        \Lof\MarketPlace\Model\RabbitmqImportDbNotificationDetailFactory $detailFactory,
        \Lof\MarketPlace\Model\ResourceModel\RabbitmqImportDbNotificationDetail $detailResource
    ) {
        $this->logger = $logger;
        $this->sellerProductFactory = $sellerProductFactory;
        $this->productFactory = $productFactory;
        $this->productAction = $productAction;
        $this->csvProcessor = $csvProcessor;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->sellerFactory = $sellerFactory;
        $this->helper = $helper;
        $this->stockItemFactory = $stockItemFactory;
        $this->_attrSetColFactory = $attrSetColFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->eventManager = $eventManager;
        $this->importModel = $importModel;
        $this->filesystem = $filesystem;
        $this->notificationFactory = $notificationFactory;
        $this->notificationResource = $notificationResource;
        $this->detailFactory = $detailFactory;
        $this->detailResource = $detailResource;
    }

    /**
     * $message is the JSON string published by controller
     * @param string $message
     */
    public function process($message)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/importConsumer.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('ProductImportConsumer: process started');
        $logger->info('Message: ' . $message);
        $objectManager = ObjectManager::getInstance();
        try {            
            $payload = json_decode($message, true);
            if (!$payload || !isset($payload['file'])) {
                $this->logger->error('ProductImportConsumer: invalid payload');
                return;
            }

            $file = $payload['file'];
            $sellerId = $payload['seller_id'] ?? null;
            $approval = $payload['approval'] ?? 0;
            $approvalEditing = $payload['approval_editing'] ?? 0;
            $pendingProductAttributes = $payload['pending_attributes'] ?? 0;
            $productUpdateIds = $payload['product_update_ids'] ?? [];

            if (!file_exists($file)) {
                $this->logger->error("ProductImportConsumer: file not found: {$file}");
                return;
            }

            // Add notification
            // $notification = $this->notificationFactory->create();
            // $notification->setData([
            //     'seller_id' => $sellerId,
            //     'import_type' => 'bulk',
            //     'import_status' => 'processing',
            //     'message' => json_encode($message)
            // ]);
            // $this->notificationResource->save($notification);
            // $notifId = $notification->getId();
            // End add notification

            $payloadImport = $payload['data'];
            $payloadImport['seller_id'] = $sellerId;
            if (isset($payloadImport['entity']) && isset($payloadImport['behavior'])) {
                try {
                    // $logger->info('Payload for ImportModel: ' . print_r($payloadImport, true));
                    $this->importModel->setData($payloadImport);

                    try {
                        // tell import model which file to use
                        $directory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                        $relativePath = $directory->getRelativePath($file); 
                        // $logger->info('Relative path used for Import: ' . $relativePath);

                        $this->importModel->setSource(
                            new \Magento\ImportExport\Model\Import\Source\Csv($relativePath, $directory)
                        );
                        
                        // $logger->info('Finding import adapter for file: ' . $file);
                        $source = ImportAdapter::findAdapterFor(
                            $file,
                            $objectManager->create(\Magento\Framework\Filesystem::class)
                                ->getDirectoryWrite(DirectoryList::ROOT),
                            $payloadImport[$this->importModel::FIELD_FIELD_SEPARATOR]
                        );
                        
                        $this->importModel->validateSource($source);
                        // $logger->info('Import adapter found: ');
                        // $this->processValidationResult($import->validateSource($source), $resultBlock);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        // $logger->info('Import adapter error: ' . $e->getMessage());
                    } catch (\Exception $e) {
                        // $logger->info('Import adapter general error: ' . $e->getMessage());
                        // $resultBlock->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
                    }

                    // if (!$this->importModel->validateSource($this->importModel->getSource())) {
                        // $logger->info('Import validation failed: ' . implode(', ', $this->importModel->getErrorMessages()));
                    //     return;
                    // }

                    $this->importModel->importSource();
                    $this->importModel->invalidateIndex();

                    $notification = $this->notificationFactory->create();
                    $notification->setData([
                        'seller_id' => $seller_id,
                        'status' => 'done',
                        'is_read' => 0,
                        'url_path' => 'catalog/queues/index',
                        'title' => 'Bulk Add Products',
                        'message' => __("Your products have been processed successfully.")
                    ]);
                    $this->notificationResource->save($notification);
                    $logger->info("ImportModel successfully processed file: {$file}");
                } catch (\Exception $e) {
                    $logger->info("Import model failed: " . $e->getMessage());
                    return; // stop here, no need to continue with manual processing
                }
            }

            // Read CSV and perform the same post-import actions you had in controller
            $data = $this->csvProcessor->getData($file);
            if (!$data || !isset($data[0])) {
                $this->logger->warning("ProductImportConsumer: empty CSV file: {$file}");
                return;
            }

            $skuIndex = array_search("sku", $data[0]);
            $websiteIdIndex = array_search("website_id", $data[0]);
            $publishStatusIndex = array_search("publish_status", $data[0]);
            // $logger->info("skuIndex: {$skuIndex}, websiteIdIndex: {$websiteIdIndex}, publishStatusIndex: {$publishStatusIndex}");

            foreach ($data as $key => $_data) {
                if (!$key) {
                    continue;
                }

                $sku = isset($_data[$skuIndex]) ? $_data[$skuIndex] : null;
                if (!$sku) {
                    continue;
                }                
                $product = $this->productFactory->create()->loadByAttribute('sku', $sku);
                $productId = $product ? (int)$product->getId() : 0;
                if (!$productId) {
                    continue;
                }

                $productSellerId = $product->getSellerId();
                $isPendingProduct = false;

                if ($approval) {
                    if (!empty($productUpdateIds) && in_array($productId, $productUpdateIds)) {
                        $isPendingProduct = true;
                    } else {
                        $sellerProductCheck = $this->sellerProductFactory->create()->load($productId, 'product_id');
                        if (!$sellerProductCheck->getId()) {
                            $isPendingProduct = true;
                        }
                    }
                }

                $status = $approval ? 1 : 2;
                $connection = $this->resourceConnection->getConnection();
                $productData = ['approval' => $status];
                if (!$productSellerId) {
                    $productData['seller_id'] = $sellerId;
                }
                if (!$productSellerId || ($productSellerId && ($productSellerId == $sellerId))) {
                    try {
                        $connection->update(
                            $this->resourceConnection->getTableName('catalog_product_entity'),
                            $productData,
                            ['entity_id = ?' => $productId]
                        );
                    } catch (\Exception $e) {
                        $this->logger->error("Failed to update catalog_product_entity for product id {$productId}: " . $e->getMessage());
                    }
                }

                $publishStatus = isset($_data[$publishStatusIndex]) ? $_data[$publishStatusIndex] : 0;
                $listingWebsiteId = isset($_data[$websiteIdIndex]) ? $_data[$websiteIdIndex] : null;
                if ($listingWebsiteId) {
                    try {
                        $website = $this->storeManager->getWebsite($listingWebsiteId);
                        if ($website) {
                            $stores = $website->getStores();
                            if ($stores) {
                                foreach ($stores as $store) {
                                    try {
                                        $this->productAction->updateAttributes([$productId], [
                                            'publish_status' => $publishStatus == 1 ? 1 : 2
                                        ], $store->getId());
                                    } catch (\Exception $e) {
                                        $this->logger->error("Failed to update publish_status for product {$productId} on store {$store->getId()}: " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $this->logger->error("Error processing website id {$listingWebsiteId}: " . $e->getMessage());
                    }
                }

                $sellerProduct = $this->sellerProductFactory->create()->load($productId, 'product_id');
                if ($sellerProduct && count($sellerProduct->getData()) > 0) {
                    $sellerProductSellerId = $sellerProduct->getSellerId();
                    if (!$sellerProductSellerId) {
                        $sellerProduct
                            ->setData('seller_id', $sellerId)
                            ->setData('status', 3)
                            ->save();
                    } elseif ($sellerProductSellerId == $sellerId) {
                        if (!$approvalEditing) {
                            $sellerProduct
                                ->setData('status', 3)
                                ->save();
                        }
                    }
                } else {
                    try {
                        if (!$productSellerId) {
                            $sellerProduct
                                ->setData('seller_id', $sellerId)
                                ->setData('product_id', $productId)
                                ->setData('status', 0)
                                ->setData('product_name', $product->getName())
                                ->save();
                        } elseif ($productSellerId && ($productSellerId == $sellerId)) {
                            $sellerProduct
                                ->setData('seller_id', $sellerId)
                                ->setData('product_id', $productId)
                                ->setData('status', 0)
                                ->setData('product_name', $product->getName())
                                ->save();
                        }
                    } catch (\Exception $e) {
                        $this->logger->error("Failed to create seller_product for product {$productId}: " . $e->getMessage());
                    }
                }

                if ($approval && $isPendingProduct) {
                    try {
                        $sellerProduct
                            ->setData('status', 1)
                            ->setData('product_name', $product->getName())
                            ->save();
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to set pending status: ' . $e->getMessage());
                    }
                }
            }

            $this->eventManager->dispatch('marketplace_import_product_success', [
                'object' => $this,
                'seller_id' => $sellerId,
                'file' => $file
            ]);

            $this->logger->info('Product import processed by consumer for file: ' . $file);

            // optionally remove file after success:
            // @unlink($file);

        } catch (\Exception $e) {
            $this->logger->critical('ProductImportConsumer error: ' . $e->getMessage());
        }
    }
}
