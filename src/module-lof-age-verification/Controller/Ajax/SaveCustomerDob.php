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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\PageCache\Model\Config;
use Psr\Log\LoggerInterface;

class SaveCustomerDob extends \Magento\Framework\App\Action\Action
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * SwatchOptions constructor.
     * @param Context $context
     * @param Data $helperData
     * @param Config $config
     * @param Http $http
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Config $config,
        Http $http,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->http = $http;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $msg = '';

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $customerId = $this->getRequest()->getParam('customer_id');
        $dob = $this->getRequest()->getParam('dob');

        if (!$customerId) {
            $msg = 'Something went wrong while saving the dob customer';
            return $resultJson->setData([
                'message' => $msg
            ]);
        }

        $customer = $this->customerRepository->getById($customerId);
        $customer->setDob($dob);

        try {
            $this->customerRepository->save($customer);
            $msg = 'customer date of birth saved success';
        } catch (\Exception $e) {
            $msg = 'Something went wrong while saving the dob customer';
        }
        return $resultJson->setData([
            'message' => $msg
        ]);
    }
}
