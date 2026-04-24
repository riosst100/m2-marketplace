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

namespace Lof\AgeVerification\Model\Config\Source;

class PopupVerifyType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Popup verify type
     */
    const TYPE_DOB = '1';
    const TYPE_YES_NO_BUTTON = '2';
    const TYPE_CHECKBOX = '3';
    const TYPE_REQUIRE_LOGIN = '4';

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $option = [
            [
                'value' => self::TYPE_DOB,
                'label' => __('Input Date of Birth')
            ],
            [
                'value' => self::TYPE_YES_NO_BUTTON,
                'label' => __('Yes/No')
            ],
            [
                'value' => self::TYPE_CHECKBOX,
                'label' => __('Checkbox')
            ]
        ];
        return $option;
    }
}
