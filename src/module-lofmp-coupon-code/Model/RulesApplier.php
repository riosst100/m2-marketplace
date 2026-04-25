<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lofmp\CouponCode\Model;

use Magento\Quote\Model\Quote\Address;

/**
 * Class RulesApplier
 * @package Magento\SalesRule\Model\Validator
 */
class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    public function __construct(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Utility $utility
    ) {
        parent::__construct($calculatorFactory, $eventManager, $utility);
    }
    
    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }
            $appliedRuleIds[] = $rule->getRuleId();
            if( $rule->getIsStopProcessing() ) break;
        }
        return $appliedRuleIds;
    }

}