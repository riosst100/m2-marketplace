<?php

namespace Lof\TrackorderGraphQl\Model\Api;

use Magento\Framework\HTTP\Client\Curl;

class RajaOngkirService
{
    protected $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    public function trackWaybill($awb, $courier, $apiKey)
    {
        $url = "https://rajaongkir.komerce.id/api/v1/track/waybill?awb={$awb}&courier={$courier}";

        $headers = [
            "key: {$apiKey}"
        ];

        $this->curl->setHeaders($headers);
        $this->curl->post($url, []);

        $response = json_decode($this->curl->getBody(), true);

        if (!isset($response["data"])) {
            throw new \Exception("Invalid API response");
        }

        return $response["data"];
    }
}
