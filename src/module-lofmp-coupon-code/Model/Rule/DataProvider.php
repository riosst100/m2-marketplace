<?php
/**
 * Lof CouponCode is a powerful tool for managing the processing return and exchange requests within your workflow. This, in turn, allows your customers to request and manage returns and exchanges directly from your webstore. The Extension compatible with magento 2.x
 * Copyright (C) 2017  Landofcoder.com
 * 
 * This file is part of Lofmp/CouponCode.
 * 
 * Lof/CouponCode is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Lofmp\CouponCode\Model\Rule;

use Lofmp\CouponCode\Model\ResourceModel\Rule\CollectionFactory;
use Lofmp\CouponCode\Model\RuleFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var mixed
     */
    protected $loadedData;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistorry
     * @param Registry $coreRegistry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('getData DataProvider');

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $data = $model->getData();

            // Unserialize rule conditions (Magento serializes them as JSON)
            if (!empty($data['conditions_serialized'])) {
                $conditions = json_decode($data['conditions_serialized'], true);
                
                if (is_array($conditions) && isset($conditions['conditions'])) {
                    // Try to extract entity_id, category_id, or other rule-related data
                    foreach ($conditions['conditions'] as $cond) {
                        if (!empty($cond['attribute']) && $cond['attribute'] === 'entity_id') {
                            // This is your selected product IDs condition
                            $data['selected_product_ids'] = $cond['value'];
                        }
                        if (!empty($cond['attribute']) && $cond['attribute'] === 'category_ids') {
                            // Category condition
                            $data['select_category'] = $cond['value'];
                            $data['category_condition'] = 'specific';
                        }
                    }
                }
            }

            // Provide sensible defaults if missing
            // $data['products_condition'] = $data['products_condition'] ?? 'specific';
            $data['products_condition'] = 'specific';
            // $data['category_condition'] = $data['category_condition'] ?? 'specific';
            $data['category_condition'] = 'specific';
            $data['selected_product_ids'] = $data['selected_product_ids'] ?? '';
            $data['select_category'] = $data['select_category'] ?? '';
            $logger->info('Data for model ID ' . $model->getId() . ': ' . print_r($data, true));
            $this->loadedData[$model->getId()] = $data;
        }

        $currentModel = $this->coreRegistry->registry('current_model');
        if ($currentModel && $currentModel->getId()) {
            $this->loadedData[$currentModel->getId()] = $currentModel->getData();
        }

        $data = $this->dataPersistor->get('lofmpcouponcode_rule');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('lofmpcouponcode_rule');
        }
        $logger->info('loadedData ' . print_r($this->loadedData, true));
        
        return $this->loadedData;
    }
}
