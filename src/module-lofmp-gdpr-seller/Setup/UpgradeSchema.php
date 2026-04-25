<?php
namespace Lofmp\GdprSeller\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection = $setup->getConnection();
            $tableName = $setup->getTable('lof_marketplace_seller');

            if ($connection->isTableExists($tableName)) {

                if (!$connection->tableColumnExists($tableName, 'is_delete_request')) {
                    $connection->addColumn(
                        $tableName,
                        'is_delete_request',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => 'Delete Request Flag'
                        ]
                    );
                }

                if (!$connection->tableColumnExists($tableName, 'delete_request_at')) {
                    $connection->addColumn(
                        $tableName,
                        'delete_request_at',
                        [
                            'type' => Table::TYPE_DATETIME,
                            'nullable' => true,
                            'comment' => 'Delete Request Created At'
                        ]
                    );
                }
            }
        }

        $setup->endSetup();
    }
}
