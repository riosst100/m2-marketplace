<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Rma\Api\Repository\CustomerMessageRepositoryInterface;
use Lofmp\Rma\Api\Data\MessageInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Lofmp\Rma\Api\Repository\AttachmentRepositoryInterface;
use Lofmp\Rma\Helper\Data as RmaHelper;

class SendMessage implements ResolverInterface
{
    private $customerMessageRepository;
    private $messageFactory;
    private $customerRepository;
    private $attachmentRepository;
    private $rmaHelper;

    public function __construct(
        CustomerMessageRepositoryInterface $customerMessageRepository,
        MessageInterfaceFactory $messageFactory,
        CustomerRepositoryInterface $customerRepository,
        AttachmentRepositoryInterface $attachmentRepository,
        RmaHelper $rmaHelper
    ) {
        $this->customerMessageRepository = $customerMessageRepository;
        $this->messageFactory = $messageFactory;
        $this->customerRepository = $customerRepository;
        $this->attachmentRepository = $attachmentRepository;
        $this->rmaHelper = $rmaHelper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = (int)$context->getUserId();

        if (empty($args['input']) || empty($args['input']['rma_id']) || empty($args['input']['text'])) {
            throw new GraphQlInputException(__('Invalid input for send message.'));
        }

        $customer = $this->customerRepository->getById($customerId);
        $customerName = trim($customer->getFirstname() . ' ' . $customer->getLastname());

        $messageData = $this->messageFactory->create();
        $messageData->setRmaId($args['input']['rma_id']);
        $messageData->setCustomerId($customerId);
        $messageData->setCustomerName($customerName);
        $messageData->setText($args['input']['text']);

        try {
            $savedMessage = $this->customerMessageRepository->save($customerId, $messageData);

            // Handle attachments
            if (!empty($args['input']['attachments'])) {
                foreach ($args['input']['attachments'] as $file) {
                    $type = $file['type'];
                    $size = (int)$file['size'];
                    $check = $this->rmaHelper->CheckFile($type, $size);
                    if (!$check) {
                        continue;
                    }

                    $attachment = $this->attachmentRepository->create();
                    $attachment->setItemType('message')
                        ->setItemId($savedMessage->getId())
                        ->setName($file['name'])
                        ->setSize($size)
                        ->setBody(base64_decode($file['content']))
                        ->setType($type)
                        ->save();
                }
            }

            return [
                'success' => true,
                'message' => __('Message sent.'),
                'created_message' => [
                    'id' => $savedMessage->getId(),
                    'rma_id' => $savedMessage->getRmaId(),
                    'sender_name' => $savedMessage->getCustomerName(),
                    'text' => $savedMessage->getText(),
                    'created_at' => $savedMessage->getCreatedAt(),
                ]
            ];
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
