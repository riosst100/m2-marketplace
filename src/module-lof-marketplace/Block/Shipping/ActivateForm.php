<?php
namespace Lof\MarketPlace\Block\Shipping;

use Magento\Framework\View\Element\Template;

class ActivateForm extends Template
{
    protected $method;

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function isRajaOngkir()
    {
        return $this->method === "rajaongkir";
    }

    public function isTableRate()
    {
        return $this->method === "lofmptablerateshipping";
    }

    public function isFlatRate()
    {
        return $this->method === "lofmpflatrateshipping";
    }
}
