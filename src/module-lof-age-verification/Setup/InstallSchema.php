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
 * @package    Lof_AgeVerification
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\AgeVerification\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $lof_ageverification_products = $installer->getConnection()
            ->newTable($installer->getTable('lof_ageverification_products'))
            ->addColumn(
                'custom_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Custom ID'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )->addColumn(
                'use_custom',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Use Custom'
            )->addColumn(
                'prevent_view',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Prevent View'
            )->addColumn(
                'prevent_purchase',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Prevent Purchase'
            )->addColumn(
                'verify_age',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true,
                ],
                'Type Id'
            )->addForeignKey(
                $installer->getFkName(
                    'lof_ageverification_products',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($lof_ageverification_products);
        $installer->endSetup();
    }
}
