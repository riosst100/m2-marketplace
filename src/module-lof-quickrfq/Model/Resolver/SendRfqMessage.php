<?php
namespace Lof\Quickrfq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Quickrfq\Model\MessageFactory;
use Lof\Quickrfq\Model\ResourceModel\Message as MessageResource;
use Lof\Quickrfq\Model\AttachmentFactory;
use Lof\Quickrfq\Model\ResourceModel\Attachment as AttachmentResource;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

class SendRfqMessage implements ResolverInterface
{
    protected $messageFactory;
    protected $messageResource;
    protected $attachmentFactory;
    protected $attachmentResource;
    protected $getCustomer;

    public function __construct(
        MessageFactory $messageFactory,
        MessageResource $messageResource,
        AttachmentFactory $attachmentFactory,
        AttachmentResource $attachmentResource,
        GetCustomer $getCustomer
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageResource = $messageResource;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResource = $attachmentResource;
        $this->getCustomer = $getCustomer;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customer = $this->getCustomer->execute($context);
        $input = $args['input'] ?? [];

        if (empty($input['quickrfq_id']) || empty($input['message'])) {
            throw new GraphQlInputException(__('Required parameters are missing.'));
        }

        $messageModel = $this->messageFactory->create();
        $messageModel->setData([
            'quickrfq_id' => $input['quickrfq_id'],
            'customer_id' => $customer->getId(),
            'message' => $input['message'],
            'is_main' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->messageResource->save($messageModel);

        // Attachments
        if (!empty($input['attachments'])) {
            foreach ($input['attachments'] as $attachment) {
                $attachModel = $this->attachmentFactory->create();
                $attachModel->setData([
                    'quickrfq_id' => $input['quickrfq_id'],
                    'message_id' => $messageModel->getId(),
                    'file_name' => $attachment['file_name'] ?? '',
                    'file_path' => $attachment['file_path'] ?? '',
                    'file_type' => $attachment['file_type'] ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $this->attachmentResource->save($attachModel);
            }
        }

        return [
            'success' => true,
            'message' => __('Message sent successfully.'),
            'rfq_message' => [
                'id' => $messageModel->getId(),
                'quickrfq_id' => $messageModel->getQuickrfqId(),
                'customer_id' => $messageModel->getCustomerId(),
                'message' => $messageModel->getMessage(),
                'is_main' => $messageModel->getIsMain(),
                'created_at' => $messageModel->getCreatedAt(),
                'attachments' => $this->getAttachments($messageModel->getId(), $input['quickrfq_id'])
            ]
        ];
    }

    protected function getAttachments($messageId, $quickrfqId)
    {
        $collection = $this->attachmentFactory->create()->getCollection();
        $collection->addFieldToFilter('message_id', $messageId);
        $collection->addFieldToFilter('quickrfq_id', $quickrfqId);

        $attachments = [];
        foreach ($collection as $attach) {
            $attachments[] = [
                'id' => $attach->getId(),
                'quickrfq_id' => $attach->getQuickrfqId(),
                'message_id' => $attach->getMessageId(),
                'file_name' => $attach->getFileName(),
                'file_path' => $attach->getFilePath(),
                'file_type' => $attach->getFileType(),
                'created_at' => $attach->getCreatedAt()
            ];
        }
        return $attachments;
    }
}
