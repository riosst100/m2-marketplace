<?php
namespace Lof\Gdpr\Controller\Address;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 *
 * @package Lof\Gdpr\Controller\Address
 */
class Delete extends Action
{
    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->_addressRepository = $addressRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id');
        try {
            $this->_addressRepository->deleteById($addressId);
            $this->messageManager->addSuccess(__('Successfully deleted customer address'));
            $this->_redirect('customer/address/');
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('customer/address/');
        }
    }
}
