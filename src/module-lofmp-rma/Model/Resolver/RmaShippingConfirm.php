<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Rma\Api\Repository\RmaRepositoryInterface;
use Lofmp\Rma\Api\Repository\MessageRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

class RmaShippingConfirm implements ResolverInterface
{
    protected $rmaRepository;
    protected $messageRepository;
    protected $eventManager;
    protected $getCustomer;

    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        MessageRepositoryInterface $messageRepository,
        EventManager $eventManager,
        GetCustomer $getCustomer
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->messageRepository = $messageRepository;
        $this->eventManager = $eventManager;
        $this->getCustomer = $getCustomer;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['rma_id'])) {
            throw new GraphQlInputException(__('RMA ID is required.'));
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->getCustomer->execute($context);

        try {
            $rma = $this->rmaRepository->getById((int)$args['rma_id']);

            // Validate RMA ownership
            if ($rma->getCustomerId() != $customer->getId()) {
                throw new GraphQlInputException(__('You are not allowed to confirm this RMA.'));
            }

            // Update RMA status to “package sent”
            $rma->setStatusId(4); // 4 = package sent
            $this->rmaRepository->save($rma);

            // Create confirmation message
            $messageText = __('I confirm that I have sent the package to the RMA department.');
            $message = $this->messageRepository->create();
            $message->setRmaId($rma->getId())
                ->setText($messageText, false)
                ->setIsCustomerNotified(1)
                ->setIsVisibleInFrontend(1)
                ->setCustomerId($customer->getId())
                ->setCustomerName($customer->getFirstname() . ' ' . $customer->getLastname());

            $this->messageRepository->save($message);

            // Dispatch event like the controller
            $this->eventManager->dispatch(
                'rma_add_message_after',
                ['rma' => $rma, 'message' => $message, 'user' => $customer]
            );

            return [
                'success' => true,
                'message' => __('Shipping confirmation successful.')
            ];
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
