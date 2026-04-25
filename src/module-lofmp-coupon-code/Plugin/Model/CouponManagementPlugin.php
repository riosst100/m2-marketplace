<?php
namespace Lofmp\CouponCode\Plugin\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Lofmp\CouponCode\Model\CouponFactory as LofCouponFactory;
use Lofmp\CouponCode\Helper\Data as HelperData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CouponManagementPlugin
{
    protected $quoteRepository;
    protected $couponFactory;
    protected $lofCouponFactory;
    protected $helperData;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CouponFactory $couponFactory,
        LofCouponFactory $lofCouponFactory,
        HelperData $helperData
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->couponFactory = $couponFactory;
        $this->lofCouponFactory = $lofCouponFactory;
        $this->helperData = $helperData;
    }

    /**
     * Around plugin for set() to validate coupon code before applying
     */
    public function aroundSet(
        \Magento\Quote\Model\CouponManagement $subject,
        callable $proceed,
        $cartId,
        $couponCode
    ) {
        if (!$this->helperData->isEnabled()) {
            return $proceed($cartId, $couponCode);
        }

        $couponCode = trim($couponCode);
        $flag = false;
        $couponCollection = $this->lofCouponFactory->create()->getCollection();
        $data = $couponCollection->getByCouponCode($couponCode);

        if (count($data) > 0 && $couponCode) {
            $rule = $couponCollection->getRule($data["rule_id"]);
            if ($rule) {
                $quote = $this->quoteRepository->getActive($cartId);
                $customer = $quote->getCustomer();
                $customerEmail = $customer->getEmail();
                $customerId = $customer->getId();

                if ($rule["is_check_email"]) {
                    if (
                        (isset($data["email"]) && $data["email"] == $customerEmail) ||
                        (isset($data["customer_id"]) && $data["customer_id"] == $customerId)
                    ) {
                        $flag = true;
                    }
                } else {
                    $flag = true;
                }
            } else {
                $flag = true;
            }
        } elseif (!$couponCode) {
            $flag = true; // allow removing
        }

        if (!$flag) {
            // Stop the default apply, throw error            
            throw new NoSuchEntityException(__('The coupon code "%1" is not valid for this customer.', $couponCode));
        }

        // continue with default coupon apply logic
        return $proceed($cartId, $couponCode);
    }
}
