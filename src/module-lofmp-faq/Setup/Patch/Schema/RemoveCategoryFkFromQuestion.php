<?php

namespace Lofmp\Faq\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class RemoveCategoryFkFromQuestion implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $setup = $this->schemaSetup;
        $connection = $setup->getConnection();

        $setup->startSetup();

        $tableName = $setup->getTable('lofmp_faq_question');

        if ($connection->isTableExists($tableName)) {
            // FK name must match the one generated in InstallSchema
            $fkName = $connection->getForeignKeyName(
                $tableName,
                'category_id',
                $setup->getTable('lofmp_faq_category'),
                'category_id'
            );

            if ($fkName) {
                $connection->dropForeignKey($tableName, $fkName);
            }
        }

        $setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
