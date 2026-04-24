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

namespace Lof\AgeVerification\Controller\Ajax;

use Lof\AgeVerification\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;
use Magento\PageCache\Model\Config;

class Redirect extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * SwatchOptions constructor.
     * @param Context $context
     * @param Data $helperData
     * @param UrlInterface $urlInterface
     * @param Config $config
     * @param Http $http
     */
    public function __construct(
        Context $context,
        Data $helperData,
        UrlInterface $urlInterface,
        Config $config,
        Http $http
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->_urlInterface = $urlInterface;
        $this->http = $http;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->getResponse();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $currentUrl = $this->getRequest()->getParam('currentUrl');

        $loginUrl = $this->_urlInterface->getUrl(
            'customer/account/login',
            ['referer' => base64_encode($currentUrl)]
        );

        $response->setPublicHeaders($this->config->getTtl());
        return $resultJson->setData([
            'url' => $loginUrl
        ]);
    }
}
