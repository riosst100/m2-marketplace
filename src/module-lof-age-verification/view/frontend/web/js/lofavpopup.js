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
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'redirectUrl',
    'dobPicker',
    'mage/translate',
    'jquery/jquery.cookie',
], function ($, modal, url) {
    'use strict';

    $.widget('mage.lofavPopup', {
        options: {
            can_display: false,
            is_applied_category: false,
            is_applied_product_detail: false,
            enable_cms_pages: false,
            customer_logged_in: false,
            is_required_login: false,
            has_dob_by_customer: false,
            addtocart_selector: '',
            product_item_selector: '',
            dob_by_customer: '',
            customer_id: '',
            popup_title: '',
            popup_desc: '',
            verify_age: '',
            verify_type: '',
            current_cms_identifier: '',
            redirect_url: '',
            cookieLifetime: '',
            cms_page_identifiers: {},
            modalEl: $('#lofav-modal'),
            modalCancelBtn: $('.lofav-btn-cancel'),
            modalConfirmBtn: $('.lofav-btn-confirm'),
            require_field_text: $.mage.__('This is a required field.'),
        },

        _create: function () {
            var canDisplay = this.options.can_display,
                verifyAge = this.options.verify_age,
                isAppliedCategory = this.options.is_applied_category,
                isAppliedProductDetail = this.options.is_applied_product_detail,
                cmsPageIdentifiers = this.options.cms_page_identifiers,
                currentCmsIdentifier = this.options.current_cms_identifier,
                isEnableCmsPages = this.options.enable_cms_pages,
                isRequiredLogin = this.options.is_required_login,
                customerLoggedIn = this.options.customer_logged_in,
                dobByCustomer = this.options.dob_by_customer,
                hasDobByCustomer = this.options.has_dob_by_customer,
                ageCookie = $.cookie('Lof_AgeVerification');

            //Check cookie has been save
            if ( typeof ageCookie !== 'undefined' && ageCookie >= verifyAge) {
                return;
            }

            //Check store_view, enable module
            if (!canDisplay || typeof canDisplay === 'undefined') {
                return;
            }

            //Cms pages
            if (currentCmsIdentifier) {
                if ($('.cms-' + currentCmsIdentifier).length > 0) {
                    if (!isEnableCmsPages) {
                        return;
                    }

                    if (isRequiredLogin) {
                        if (customerLoggedIn && hasDobByCustomer) {
                            var today = new Date(),
                                dob = new Date(dobByCustomer),
                                ageByCustomer = Math.floor((today - dob) / (365.25 * 86400000));
                            if (ageByCustomer >= verifyAge) {
                                return;
                            }
                        }
                    }

                    if (currentCmsIdentifier && cmsPageIdentifiers) {
                        if ($.inArray(currentCmsIdentifier, cmsPageIdentifiers) === -1) {
                            return;
                        }
                    }
                }
                this._initModal(verifyAge)
            }

            //Category pages
            if ($('.catalog-category-view').length > 0) {
                if (!isAppliedCategory) {
                    return;
                }
                this._initModal(verifyAge)
            }

            //Product detail
            if ($('.catalog-product-view').length > 0) {
                if (!isAppliedProductDetail) {
                    return;
                }
                this._initModal(verifyAge)
            }
        },

        _initModal: function (verifyAge) {
            this._clickCancel();
            this._clickConfirm(verifyAge)
            var lofavModal = {
                type: 'popup',
                responsive: true,
                title: '',
                buttons: false,
                autoOpen: true,
                modalClass: 'lofav-popup',
                clickableOverlay: false,
                focus: false,
            }

            modal(lofavModal, this.options.modalEl);
            if (this.options.popup_title) {
                $('.lofav-modal__title').html(this.options.popup_title)
            }
            if (this.options.popup_desc) {
                $('.lofav-modal__description-text').html(this.options.popup_desc)
            }
            $('.lofav-dob-picker').remove()
            $('.lofav-modal__verify--dobtype').dobPicker(
                {
                    maxAge: 100,
                    placeholder: true,
                    defaultDate: false,
                    monthFormat: 'long',
                    sizeClass: 'lofav-dob',
                    dateFormat: 'littleEndian'
                }
            );
        },

        _clickCancel: function () {
            var redirectUrl = this.options.redirect_url
            if (redirectUrl) {
                this.options.modalCancelBtn.redirectUrl({url: redirectUrl});
            }
            var self = this
            // this.options.modalCancelBtn.on('click', function () {
            //     self.options.modalEl.modal('closeModal')
            //     self.options.modalEl.remove()
            // })

            this.options.modalCancelBtn.on('click', $.proxy(function (e) {
                e.preventDefault();
                self.options.modalEl.modal('closeModal')
                self.options.modalEl.remove()
            }, this));
        },
        _clickConfirm: function (verifyAge) {
            var self = this,
                verifyType = this.options.verify_type,
                redirectUrl = this.options.redirect_url,
                isRequiredLogin = this.options.is_required_login,
                customerLoggedIn = this.options.customer_logged_in,
                dobByCustomer = this.options.dob_by_customer,
                hasDobByCustomer = this.options.has_dob_by_customer,
                date = new Date(),
                days = this.options.cookieLifetime * 24,
                duration = date.setTime(date.getTime() + days * 60 * 60 * 1000);

            this.options.modalConfirmBtn.on('click', function () {
                if (isRequiredLogin) {
                    if (customerLoggedIn && hasDobByCustomer) {
                        var today = new Date(),
                            dob = new Date(dobByCustomer),
                            ageByCustomer = Math.floor((today - dob) / (365.25 * 86400000));
                        if (ageByCustomer < verifyAge) {
                            location.href = redirectUrl
                        }
                    }
                }
                switch (verifyType) {
                    case '1':
                        self._verificationDobType(self, verifyAge, redirectUrl, duration)
                        break;
                    case '2':
                        self._verificationBoolType(verifyAge, duration)
                        break;
                    case '3':
                        self._verificationCheckBoxType(verifyAge, redirectUrl, duration)
                        break;
                    case '4':
                        self._verificationRequireLogin()
                        break;
                    default:
                        location.href = redirectUrl
                }
            })
        },

        _verificationRequireLogin: function () {
            var ajaxUrl = url.build('lof_ageverification/ajax/redirect');
            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    currentUrl: window.location.href
                }
            }).done(function (data) {
                if (data.url) {
                    window.location.href = data.url;
                }
            });
        },

        _verificationBoolType: function (verifyAge, duration) {
            $.cookie('Lof_AgeVerification', verifyAge, {path: '/', expires: duration});
            this.options.modalEl.modal('closeModal');
            location.reload()
        },

        _verificationCheckBoxType: function (verifyAge, redirectUrl, duration) {
            if ($('#lofav-checkbox').is(':checked')) {
                $.cookie('Lof_AgeVerification', verifyAge, {path: '/', expires: duration});
                this.options.modalEl.modal('closeModal');
                location.reload()
            } else {
                location.href = redirectUrl
            }
        },

        _verificationDobType: function (self, verifyAge, redirectUrl, duration) {
            var $day = $('.lofav-day'),
                $month = $('.lofav-month'),
                $year = $('.lofav-year'),
                $message = $('.dob-message'),
                $birthday = $('.lofav-birthday'),
                today = new Date(),
                dob = new Date($birthday.val()),
                age = Math.floor((today - dob) / (365.25 * 86400000));

            if (!isNaN($birthday.val())) {
                $birthday.addClass('invalid');
            }

            this._noticeInvalid($day, $message);
            this._noticeInvalid($month, $message);
            this._noticeInvalid($year, $message);
            if (age >= verifyAge) {
                if (!self.options.is_required_login) {
                    $.cookie('Lof_AgeVerification', age, {path: '/', expires: duration});
                    this.options.modalEl.modal('closeModal');
                    location.reload()
                }

                if (self.options.customer_logged_in) {
                    if (!self.options.has_dob_by_customer) {
                        $.ajax({
                            url: url.build('lof_ageverification/ajax/savecustomerdob'),
                            type: 'GET',
                            dataType: 'json',
                            cache:true,
                            data: {
                                customer_id: self.options.customer_id,
                                dob: $birthday.val()
                            }
                        }).done(function (data) {
                            self.options.modalEl.modal('closeModal');
                        });
                        location.reload()
                    }
                }
            } else {
                if (!isNaN($birthday.val())) {
                    return
                }
                if ($day.val() === '0') {
                    return
                }
                if ($month.val() === '0') {
                    return
                }
                if ($year.val() === '0') {
                    return
                }
                location.href = redirectUrl
            }
        },

        _noticeInvalid: function ($el, $message) {
            if ($el.val() === '0') {
                $el.addClass('invalid');
                $message.html(this.options.require_field_text);
            } else {
                $el.removeClass('invalid');
            }
        }
    });

    return $.mage.lofavPopup;
});
