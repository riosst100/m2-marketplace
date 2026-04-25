<?php
namespace Lofmp\CouponCode\Controller\Marketplace\Rule;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class LoadProductModal extends Action
{
    protected $resultJsonFactory;
    protected $resource;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resource
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $couponRuleId = $this->getRequest()->getParam('coupon_rule_id');

        if (!$couponRuleId) {
            return $result->setData(['success' => false, 'message' => 'Missing coupon_rule_id']);
        }

        $connection = $this->resource->getConnection();
        $tableLof = $this->resource->getTableName('lofmp_couponcode_rule');
        $tableSales = $this->resource->getTableName('salesrule');

        $query = $connection->select()
            ->from(['l' => $tableLof], ['rule_id'])
            ->join(['s' => $tableSales], 'l.rule_id = s.rule_id', ['conditions_serialized'])
            ->where('l.coupon_rule_id = ?', $couponRuleId);

        $row = $connection->fetchRow($query);
        if (!$row || empty($row['conditions_serialized'])) {
            return $result->setData(['success' => true, 'products' => []]);
        }

        $data = @json_decode($row['conditions_serialized'], true);
        $products = [];

        // Log for debugging
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Decoded condition structure: ' . print_r($data, true));

        if (
            isset($data['conditions'][0]) &&
            isset($data['conditions'][0]['conditions']) &&
            is_array($data['conditions'][0]['conditions'])
        ) {
            $productConditions = $data['conditions'][0]['conditions']; // inner Product\Found conditions
        } else {
            $productConditions = [];
        }

        if (!empty($productConditions)) {
            // Get attribute IDs for name and supplier_sku
            $eavAttrTable = $this->resource->getTableName('eav_attribute');
            $attrTableVarchar = $this->resource->getTableName('catalog_product_entity_varchar');

            $attrIds = $connection->fetchPairs(
                $connection->select()
                    ->from($eavAttrTable, ['attribute_code', 'attribute_id'])
                    ->where('attribute_code IN (?)', ['name', 'supplier_sku'])
                    ->where('entity_type_id = ?', 4)
            );

            $logger->info('Attribute IDs: ' . print_r($attrIds, true));

            foreach ($productConditions as $cond) {
                if (
                    isset($cond['attribute'], $cond['value']) &&
                    $cond['attribute'] === 'supplier_sku' &&
                    !empty($cond['value'])
                ) {
                    $supplierSku = $cond['value'];

                    // Fetch product entity_id based on supplier_sku
                    $productId = $connection->fetchOne(
                        $connection->select()
                            ->from(['v' => $attrTableVarchar], ['entity_id'])
                            ->where('v.attribute_id = ?', $attrIds['supplier_sku'])
                            ->where('v.value = ?', $supplierSku)
                            ->limit(1)
                    );

                    if (!$productId) {
                        $logger->info("No product found for supplier_sku = {$supplierSku}");
                        continue;
                    }

                    // Get product name
                    $name = $connection->fetchOne(
                        $connection->select()
                            ->from(['n' => $attrTableVarchar], ['value'])
                            ->where('n.attribute_id = ?', $attrIds['name'])
                            ->where('n.entity_id = ?', $productId)
                            ->order('store_id ASC')
                            ->limit(1)
                    );

                    $products[] = [
                        'id' => $productId,
                        'name' => $name ?: 'Product #' . $productId,
                        'sku' => $supplierSku,
                    ];

                    $logger->info("Loaded product: [ID: {$productId}] [supplier_sku: {$supplierSku}] [name: {$name}]");
                }
            }
        }

        return $result->setData(['success' => true, 'products' => $products]);
    }
}
