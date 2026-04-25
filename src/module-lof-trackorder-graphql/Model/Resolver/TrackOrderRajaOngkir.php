<?php

namespace Lof\TrackorderGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use CoreMarketplace\MarketplaceRajaOngkir\Helper\Data as ROHelper;
use Lof\TrackorderGraphQl\Model\Api\RajaOngkirService;
use Magento\Sales\Model\OrderFactory;

class TrackOrderRajaOngkir implements ResolverInterface
{
    const TESTING_MODE_CONFIG = "carriers/rajaongkir/testingmode";

    protected $orderRepository;
    protected $scopeConfig;
    protected $helperData;
    protected $rajaOngkirService;
    protected $orderFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        ROHelper $helperData,
        RajaOngkirService $rajaOngkirService,
        OrderFactory $orderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->orderFactory = $orderFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['increment_id'])) {
            throw new GraphQlInputException(__('Order increment_id is required.'));
        }
        
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($args['increment_id']);
        // $order = $this->orderRepository->get($args['increment_id']);
        
        $tracking = null;
        foreach ($order->getShipmentsCollection() as $shipment) {
            foreach ($shipment->getTracks() as $track) {
                $tracking = $track;
                break;
            }
        }
        
        if (!$tracking) {
            throw new GraphQlInputException(__('No tracking information found for this order.'));
        }

        $awb = $tracking->getTrackNumber();
        $courier = $tracking->getTitle();
        // $sellerId = $order->getSellerId() ?? null;

        /**
         * ============================
         *  GET SELLER ID FROM ITEMS
         * ============================
         */
        $sellerId = null;

        foreach ($order->getAllItems() as $item) {
            if ($item->getLofSellerId()) {
                $sellerId = (int)$item->getLofSellerId();
                break;
            }
        }

        if (!$sellerId) {
            throw new GraphQlInputException(__('Seller ID not found in order items.'));
        }
        // dd($awb.' - '.$courier.' - '.$sellerId);
        
        // check testing mode
        $testingMode = $this->scopeConfig->getValue(self::TESTING_MODE_CONFIG);
        if ($testingMode) {
            return $this->getDummyResponse();
        }

        $apiKey = $this->helperData->getApiKey($sellerId);
        // dd($apiKey);

        return $this->rajaOngkirService->trackWaybill($awb, $courier, $apiKey);
    }

    private function getDummyResponse()
    {
        return [
            "delivered" => true,
            "summary" => [
                "courier_code" => "JNE",
                "courier_name" => "JNE Express",
                "waybill_number" => "012345678901",
                "service_code" => "REG",
                "waybill_date" => "2025-01-12",
                "shipper_name" => "PT Sumber Makmur",
                "receiver_name" => "Andi Pratama",
                "origin" => "Jakarta",
                "destination" => "Bandung",
                "status" => "Delivered"
            ],
            "details" => [
                "waybill_number" => "012345678901",
                "waybill_date" => "2025-01-12",
                "waybill_time" => "14:32",
                "weight" => "1.25 KG",
                "origin" => "Jakarta",
                "destination" => "Bandung",
                "shipper_name" => "PT Sumber Makmur",
                "shipper_address1" => "Jl. Merdeka No. 12",
                "shipper_address2" => "Gedung A",
                "shipper_address3" => "Kecamatan Gambir",
                "shipper_city" => "Jakarta Pusat",
                "receiver_name" => "Andi Pratama",
                "receiver_address1" => "Jl. Kopo No. 45",
                "receiver_address2" => "Perumahan Griya Indah",
                "receiver_address3" => "Kecamatan Bojongloa",
                "receiver_city" => "Bandung"
            ],
            "delivery_status" => [
                "status" => "Delivered",
                "pod_receiver" => "Andi Pratama",
                "pod_date" => "2025-01-14",
                "pod_time" => "10:15"
            ],
            "manifest" => [
                [
                    "manifest_code" => "PU",
                    "manifest_description" => "Package picked up",
                    "manifest_date" => "2025-01-12",
                    "manifest_time" => "14:32",
                    "city_name" => "Jakarta"
                ],
                [
                    "manifest_code" => "OT",
                    "manifest_description" => "In transit",
                    "manifest_date" => "2025-01-13",
                    "manifest_time" => "08:10",
                    "city_name" => "Cikarang"
                ],
                [
                    "manifest_code" => "AR",
                    "manifest_description" => "Arrived at destination hub",
                    "manifest_date" => "2025-01-13",
                    "manifest_time" => "18:22",
                    "city_name" => "Bandung"
                ],
                [
                    "manifest_code" => "DL",
                    "manifest_description" => "Delivered",
                    "manifest_date" => "2025-01-14",
                    "manifest_time" => "10:15",
                    "city_name" => "Bandung"
                ]
            ]
        ];
    }
}
