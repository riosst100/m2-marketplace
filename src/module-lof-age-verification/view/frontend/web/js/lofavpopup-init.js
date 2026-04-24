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
], function ($, LofAvPopup) {
    'use strict';

    $.widget('mage.lofavPopupInit', {
        options: {
            popupConfigData:{}
        },

        _create: function () {
            LofAvPopup(this.options.popupConfigData)
            $('.lofav-button').on('click', function () {
                var data = $(this).data('config');
                var popupConfigData = data.popupConfigData,
                    isRequiredLogin = popupConfigData.is_required_login,
                    purchaseMessage = popupConfigData.purchase_message,
                    purchaseNotice = popupConfigData.purchase_notice,
                    buttonConfirm = popupConfigData.button_confirm,
                    customerLoggedIn = popupConfigData.customer_logged_in,
                    dobByCustomer = popupConfigData.dob_by_customer,
                    hasDobByCustomer = popupConfigData.has_dob_by_customer,
                    verifyAge = popupConfigData.verify_age;
                if (isRequiredLogin) {
                    if (customerLoggedIn && hasDobByCustomer) {
                        var today = new Date(),
                            dob = new Date(dobByCustomer),
                            ageByCustomer = Math.floor((today - dob) / (365.25 * 86400000));
                        if (ageByCustomer < verifyAge) {
                            alert({
                                title: purchaseNotice,
                                content: purchaseMessage,
                                buttons: [{
                                    text: buttonConfirm,
                                    class: 'action-primary action-accept',
                                }]
                            });
                            return;
                        }
                    }
                }

                LofAvPopup(popupConfigData)._initModal(verifyAge)
            })
        },

    });

    return $.mage.lofavPopupInit;
});
