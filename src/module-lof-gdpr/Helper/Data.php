<?php

namespace Lof\Gdpr\Helper;

use Magento\Backend\App\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Class Data
 *
 * @package Lof\Gdpr\Helper
 */
class Data extends AbstractHelper
{
    const CONFIG_MODULE_PATH = 'gdpr';

    /**
     * @type array
     */
    protected $_data = [];

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    protected $backendConfig;

    /**
     * @var array
     */
    protected $isArea = [];

    /**
     * AbstractData constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigGeneral($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowDeleteAccount($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('allow_delete_customer', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDeleteAccountMessage($storeId = null)
    {
        return $this->getConfigGeneral('delete_customer_message', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowDeleteSeller($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('allow_delete_seller', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDeleteSellerMessage($storeId = null)
    {
        return $this->getConfigGeneral('delete_seller_message', $storeId);
    }

    /**
     * @return string
     */
    public function getDeleteAccountUrl()
    {
        return $this->_getUrl('customer/account/delete');
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowDeleteDefaultAddress($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('allow_delete_default_address', $storeId);
    }

    /**
     * get Extra Data
     *
     * @return string
     */
    public function getExtraData()
    {
        return $this->jsonEncode([]);
    }

    /**
     * @param $field
     * @param null $scopeValue
     * @param string $scopeType
     *
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($scopeValue === null && !$this->isArea()) {
            /** @var Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get(\Magento\Backend\App\ConfigInterface::class);
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * @param string $area
     *
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->objectManager->get(\Magento\Framework\App\State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     *
     * @return string
     */
    public static function jsonEncode($valueToEncode)
    {
        try {
            $encodeValue = self::getJsonHelper()->jsonEncode($valueToEncode);
        } catch (Exception $e) {
            $encodeValue = '{}';
        }

        return $encodeValue;
    }

    /**
     * @return JsonHelper|mixed
     */
    public static function getJsonHelper()
    {
        return ObjectManager::getInstance()->get(JsonHelper::class);
    }
}
