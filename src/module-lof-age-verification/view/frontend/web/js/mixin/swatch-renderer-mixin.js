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
    'underscore',
    'jquery',
    'lofAgeVerificationPopup',
    'Magento_Ui/js/modal/alert',
    'lofAgeVerificationPopupInit'
], function (_, $, LofAvPopup, alert, lofAgeVerificationPopupInit) {
    'use strict';

    return function (targetWidget) {

        $.widget('mage.SwatchRenderer', targetWidget, {
            _OnClick: function ($this, $widget) {
                this._super($this, $widget)

                if (!this.options.jsonConfig.popupConfigData
                    && typeof this.options.jsonConfig.popupConfigData === 'undefined'
                ) {
                    return;
                }

                var  popupConfigData = this.options.jsonConfig.popupConfigData,
                    addToCartSelector = popupConfigData.addtocart_selector,
                    $addToCartClass = $widget.element.parents($widget.options.selectorProduct).find(addToCartSelector),
                    ageVerifyButtonClass = '.lofav-button',
                    purchaseMessage = popupConfigData.purchase_message,
                    purchaseNotice = popupConfigData.purchase_notice,
                    isRequiredLogin = popupConfigData.is_required_login,
                    redirectUrl = popupConfigData.redirect_url,
                    customerLoggedIn = popupConfigData.customer_logged_in,
                    buttonConfirm = popupConfigData.button_confirm,
                    dobByCustomer = popupConfigData.dob_by_customer,
                    hasDobByCustomer = popupConfigData.has_dob_by_customer,
                    childProductData = popupConfigData.lofavChildProductData,
                    verifyAge = childProductData[this.getProduct()].verify_age,
                    $buttonHtml = $(childProductData[this.getProduct()].html),
                    ageCookie = $.cookie('Lof_AgeVerification');
                //Check cookie has been save
                if ( typeof ageCookie !== 'undefined' && ageCookie >= verifyAge) {
                    return;
                }
                if ($addToCartClass.length > 0) {
                    $addToCartClass.parent().find(ageVerifyButtonClass).remove()
                    $addToCartClass.show()
                    if (this.getProduct() && typeof this.getProduct() !== 'undefined') {
                        if (childProductData[this.getProduct()].prevent_purchase) {
                            $addToCartClass.hide()
                            $addToCartClass.parent().append($buttonHtml)
                        }
                    }

                    // lofAgeVerificationPopupInit(popupConfigData)

                    // $(ageVerifyButtonClass).on('click', function () {
                    //     if (isRequiredLogin) {
                    //         if (customerLoggedIn && hasDobByCustomer) {
                    //             var today = new Date(),
                    //                 dob = new Date(dobByCustomer),
                    //                 ageByCustomer = Math.floor((today - dob) / (365.25 * 86400000));
                    //             if (ageByCustomer < verifyAge) {
                    //                 alert({
                    //                     title: $.mage.__(purchaseNotice),
                    //                     content: $.mage.__(purchaseMessage),
                    //                     buttons: [{
                    //                         text: $.mage.__(buttonConfirm),
                    //                         class: 'action-primary action-accept',
                    //                     }]
                    //                 });
                    //                 return;
                    //             }
                    //         }
                    //     }
                    //
                    //     // LofAvPopup(popupConfigData)._initModal(verifyAge)
                    // })
                }

            },
        });

        return $.mage.SwatchRenderer;
    };
});
