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

define([
    'jquery',
    'lofAgeVerificationPopup',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery.cookie',
], function ($, LofAvPopup, alert) {
    'use strict';

    $.widget('mage.lofavReplaceButton', {
        options: {},

        _create: function () {
            if (this.element) {

                if (!this.options.buttonConfigData && typeof this.options.buttonConfigData === 'undefined') {
                    return;
                }

                var buttonConfigData = this.options.buttonConfigData,
                    addToCartSelector = buttonConfigData.addtocart_selector,
                    productItemSelector = buttonConfigData.product_item_selector,
                    verifyAge = buttonConfigData.verify_age,
                    $parent = this.element.parents(productItemSelector),
                    $addToCartClass = $parent.find(addToCartSelector),
                    buttonHtml = buttonConfigData.html,
                    popupCookie = $.cookie('Lof_AgeVerification');

                if (popupCookie < verifyAge) {
                    if ($addToCartClass[0]) {
                        // $addToCartClass[0].outerHTML = buttonHtml
                        $addToCartClass.hide()
                        $addToCartClass.parent().append(buttonHtml)
                    }

                    if ($('.catalog-product-view').length > 0) {
                        if ($(addToCartSelector)[0]) {
                            // $(addToCartSelector)[0].outerHTML = buttonHtml
                            $addToCartClass.hide()
                            $addToCartClass.parent().append(buttonHtml)
                        }
                    }
                }
            }
        }
    });

    return $.mage.lofavReplaceButton;
});
