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
 * @package    Lofmp_SplitOrderGraphQl
 * @copyright  Copyright (c) 2022 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
declare(strict_types=1);

namespace Lofmp\SplitOrderGraphQl\Model\Resolver;

use Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForCheckout;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrderMutexInterface;
use Magento\GraphQl\Helper\Error\AggregateExceptionMessageFormatter;
use Magento\Framework\App\ObjectManager;

class PlaceOrder extends \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder
{
    /**
     * @var CheckCartCheckoutAllowance
     */
    private $checkCartCheckoutAllowance;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GetCartForCheckout
     */
    private $getCartForCheckout;

    /**
     * @var PlaceOrderModel
     */
    private $placeOrder;

    /**
     * PlaceOrder constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckCartCheckoutAllowance $checkCartCheckoutAllowance
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CheckCartCheckoutAllowance $checkCartCheckoutAllowance,
        GetCartForCheckout $getCartForCheckout,
        PlaceOrderModel $placeOrder,
        private readonly OrderFormatter $orderFormatter,
        AggregateExceptionMessageFormatter $errorMessageFormatter,
        ?PlaceOrderMutexInterface $placeOrderMutex = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkCartCheckoutAllowance = $checkCartCheckoutAllowance;
        $this->errorMessageFormatter = $errorMessageFormatter;
        $this->placeOrderMutex = $placeOrderMutex ?: ObjectManager::getInstance()->get(PlaceOrderMutexInterface::class);
        parent::__construct(
            $getCartForCheckout,
            $placeOrder,
            $orderRepository,
            $errorMessageFormatter,
            $placeOrderMutex
        );
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->errors = [];
        $order = null;
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $userId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $cart = $this->getCartForCheckout->execute($maskedCartId, $userId, $storeId);
            $orderId = $this->placeOrder->execute($cart, $maskedCartId, $userId);
            if (is_array($orderId)) {
                $orders = [];
                foreach ($orderId as $id) {
                    $order = $this->orderRepository->get($id);
                    $orders[] = [
                        'order_number' => $order->getIncrementId(),
                        'order_id' => $order->getIncrementId(),
                    ];
                }
            } else {
                $order = $this->orderRepository->get($orderId);
                $orders[] = [
                    'order_number' => $order->getIncrementId(),
                    'order_id' => $order->getIncrementId(),
                ];
            }
        } catch (NoSuchEntityException $exception) {
            $this->addError($exception->getMessage());
        } catch (GraphQlInputException $exception) {
            $this->addError($exception->getMessage());
        } catch (AuthorizationException $exception) {
            throw new GraphQlAuthorizationException(
                __($exception->getMessage())
            );
        } catch (LocalizedException $e) {
            $this->addError($e->getMessage());
        }
        if ($this->errors) {
            return [
                'errors' =>
                    $this->errors
            ];
        }
        
        return ['orders' => $orders];
        
        // $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        // $this->checkCartCheckoutAllowance->execute($cart);

        // if ((int)$context->getUserId() === 0) {
        //     if (!$cart->getCustomerEmail()) {
        //         throw new GraphQlInputException(__("Guest email for cart is missing."));
        //     }
        //     $cart->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        // }

        // try {
            // $orderId = $this->cartManagement->placeOrder($cart->getId());
            // if (is_array($orderId)) {
            //     $orders = [];
            //     foreach ($orderId as $id) {
            //         $order = $this->orderRepository->get($id);
            //         $orders[] = [
            //             'order_number' => $order->getIncrementId(),
            //             'order_id' => $order->getIncrementId(),
            //         ];
            //     }
            // } else {
            //     $order = $this->orderRepository->get($orderId);
            //     $orders[] = [
            //         'order_number' => $order->getIncrementId(),
            //         'order_id' => $order->getIncrementId(),
            //     ];
            // }

            // return ['orders' => $orders];
        // } catch (NoSuchEntityException $e) {
        //     throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        // } catch (LocalizedException $e) {
        //     throw new GraphQlInputException(__('Unable to place order: %message', ['message' => $e->getMessage()]), $e);
        // }
    }

    /**
     * Add order line item error
     *
     * @param string $message
     * @return void
     */
    private function addError(string $message): void
    {
        $this->errors[] = [
            'message' => $message,
            'code' => $this->getErrorCode($message)
        ];
    }

    /**
     * Get message error code. Ad-hoc solution based on message parsing.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        $code = self::ERROR_UNDEFINED;

        $matchedCodes = array_filter(
            self::MESSAGE_CODES,
            function ($key) use ($message) {
                return false !== strpos($message, $key);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($matchedCodes)) {
            $code = current($matchedCodes);
        }

        return $code;
    }
}