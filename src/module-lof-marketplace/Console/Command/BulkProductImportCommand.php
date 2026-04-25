<?php
namespace Lof\MarketPlace\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Lof\MarketPlace\Model\Queue\ProductImportConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class BulkProductImportCommand extends Command
{
    const OPTION_ID = 'id';

    protected $appState;
    protected $resource;
    protected $consumer;

    public function __construct(
        State $appState,
        ResourceConnection $resource,
        ProductImportConsumer $consumer
    ) {
        $this->appState = $appState;
        $this->resource = $resource;
        $this->consumer = $consumer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bulk:import:product')
            ->setDescription('Run product import manually from import_history table')
            ->addOption(
                self::OPTION_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                'Import history ID (optional)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            // area already set
        }

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->createProductHelper = $objectManager->create('\CoreMarketplace\MarketPlace\Helper\CreateProductHelper');

        // $message = '{"data":{"product":{"attribute_set_id":"559","links_title":"Links","links_purchased_separately":"0","samples_title":"Samples","status":"1","affect_product_custom_options":"1","link_to_other_stores":[{"record_id":"0","stores":"Shopee","product_link":"","initialize":"true"},{"record_id":"1","stores":"Lazada","product_link":"","initialize":"true"}],"stock_data":{"min_qty_allowed_in_shopping_cart":[{"customer_group_id":"32000","min_sale_qty":"","record_id":"0"}],"min_qty":"0","max_sale_qty":"10000","notify_stock_qty":"1","min_sale_qty":"1","qty_increments":"1","use_config_min_sale_qty":"1","use_config_manage_stock":"1","manage_stock":"1","use_config_min_qty":"1","use_config_max_sale_qty":"1","use_config_backorders":"1","backorders":"0","use_config_deferred_stock_update":"1","deferred_stock_update":"1","use_config_notify_stock_qty":"1","use_config_enable_qty_inc":"1","enable_qty_increments":"0","use_config_qty_increments":"1","is_qty_decimal":"0","is_decimal_divided":"0"},"name":"Bloomburrow Commander Deck Bundle - Includes All 4 Decks | Bloomburrow Commander | Magic: The Gathering","supplier_sku":"JFP0HY88C9","weight":"","source":"database","mapping_group_name":"Collectible Card Games | Magic: The Gathering | English | Sealed Products","mapping_priority":"3","is_having_singles":"","is_sealedproducts":"true","is_having_release":"true","url_key":"bloomburrow-commander-deck-bundle---includes-all-4-decks-jfp0hy88c9","special_price":"","msrp":"","quantity_and_stock_status":{"qty":"","is_in_stock":"1"},"auction":"0","sale":"0","website_ids":{"1":"1","3":"0","4":"0","5":"0"},"price":"0","sku":"JFP0HY88C9","meta_title":"BLOOMBURROW-COMMANDER-DECK-BUNDLE-INCLUDES-ALL-4-DECKS-BLOOM","meta_keyword":"BLOOMBURROW-COMMANDER-DECK-BUNDLE-INCLUDES-ALL-4-DECKS-BLOOM","meta_description":"BLOOMBURROW-COMMANDER-DECK-BUNDLE-INCLUDES-ALL-4-DECKS-BLOOM","term_and_conditions":"No term and conditions.","short_description":"","description":"","custom_design_from":"","custom_design_to":"","special_from_date":"","special_to_date":"","tax_class_id":"2","product_has_weight":"1","visibility":"4","country_of_manufacture":"","cod_available":"0","publish_status":"2","ship_from":"1","page_layout":"","options_container":"container2","custom_layout_update_file":"__no_update__","custom_design":"1","custom_layout":"","gift_message_available":"0","msrp_display_actual_price_type":"3","category_ids":"12","card_game":"3944","language":"23689","card_product_type":"4058","release_date":"","bar_code_no":"","mtg_format":"","sealedproducts_data":[{"entity_id":"999","images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Bundle.jpg","sealed_products":"Bloomburrow Commander Deck Bundle - Includes All 4 Decks","set_id":"3754","created_at":"2025-06-22 07:03:48","updated_at":"2025-12-11 05:48:31","card_release":"Bloomburrow Commander","release_date":"02\/08\/2024","release_year":"2024","language":"English","card_game":"3944","name":"Magic: The Gathering: Bloomburrow Commander Deck Bundle - Includes All 4 Decks","card_drop":"0","print_version":"","card_set_jp_en":"","all_images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Bundle.jpg","_rowIndex":"0"},{"entity_id":"998","images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Squirreled-Away.jpg","sealed_products":"Bloomburrow Commander Deck - Squirreled Away","set_id":"3754","created_at":"2025-06-22 07:03:48","updated_at":"2025-12-11 05:48:31","card_release":"Bloomburrow Commander","release_date":"02\/08\/2024","release_year":"2024","language":"English","card_game":"3944","name":"Magic: The Gathering: Bloomburrow Commander Deck - Squirreled Away","card_drop":"0","print_version":"","card_set_jp_en":"","all_images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Squirreled-Away.jpg","_rowIndex":"1"}],"release_data":{"entity_id":"3755","card_game":"3944","card_set":"Bloomburrow Commander Tokens","card_set_abbreviation":"TBLC","release_type":"","release_date":"02\/08\/2024","release_year":"2024","number_of_cards":"41","card_manufacturer":"Wizards of the Coast","language":"English","created_at":"2025-06-08 23:12:40","updated_at":"2025-07-01 04:26:44","card_series":"Token","images":"<img src=\"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/singles\/placeholder.jpg\" style=\"width: 100px;padding-top: 10px;\"\/>","card_set_jp":"","print_version":"","card_set_jp_en":"","card_release":"Bloomburrow Commander","card_print_version":"","age_level":"13+","language_version":"English","all_images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/<img src=\"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/singles\/placeholder.jpg\" style=\"width: 100px;padding-top: 10px;\"\/>","_rowIndex":"8"},"image":"","small_image":"","thumbnail":"","swatch_image":""},"is_downloadable":"0","affect_configurable_product_attributes":"1","new-variations-attribute-set-id":"559","release-matrix":[{"card_release":"Bloomburrow Commander","release_year":"2024","record_id":"0"}],"sealedproducts-matrix":[{"images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Bundle.jpg","sealed_products":"Bloomburrow Commander Deck Bundle - Includes All 4 Decks","card_release":"Bloomburrow Commander","language":"English","release_year":"2024","record_id":"0"},{"images":"https:\/\/seller.tcgcollective.co\/media\/.thumbs\/catalog\/category\/collectible_card_games\/magic_the_gathering\/bloomburrow\/sealed_products\/Bloomburrow-Commander-Deck-Squirreled-Away.jpg","sealed_products":"Bloomburrow Commander Deck - Squirreled Away","card_release":"Bloomburrow Commander","language":"English","release_year":"2024","record_id":"1"}],"configurable-matrix-serialized":"[]","associated_product_ids_serialized":"[]","is_duplicate":"0","is_publish":"0","form_key":"t6HGzfMKOqjMnBm7"},"seller_id":"6","store_id":"0","build_payload":{"id":0,"store":"0","set":0,"type":"simple","is_publish":"0","is_duplicate":"0","configurable_matrix_serialized":"[]","is_ajax":null,"is_preview":null,"configurable_attributes_data":null,"category_id":"12","new-variations-attribute-set-id":0,"affect_configurable_product_attributes":"1","associated_product_ids_serialized":"[]"},"queue_id":"34"}';

        // $this->createProductHelper->processCreateProductRabbitMq($message);

        // dd('ok');


        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('import_history');

        $id = $input->getOption(self::OPTION_ID);
        if ($id) {
            $query = $connection->select()
                ->from($tableName)
                ->where('history_id = ?', $id)
                ->limit(1);
        } else {
            $query = $connection->select()
                ->from($tableName)
                ->where('execution_time = ?', 'Validation')
                ->where('data_import IS NOT NULL')
                ->order('history_id ASC');
        }

        $rows = $connection->fetchAll($query);
        if (empty($rows)) {
            $output->writeln('<comment>No pending import found.</comment>');
            return Command::SUCCESS;
        }

        foreach ($rows as $row) {
            $importId = $row['history_id'];
            $dataImport = $row['data_import'];

            $output->writeln("<info>Processing import ID: {$importId}</info>");
            $this->consumer->process($dataImport);
            
            $output->writeln("<info>Import ID {$importId} completed.</info>");
        }

        $output->writeln('<info>All imports processed successfully.</info>');
        return Command::SUCCESS;
    }
}
