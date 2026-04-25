define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        var modalElement = $('#add_products_modal');
        if (!modalElement.length) {
            console.warn('Modal element not found');
            return;
        }

        var modalOptions = {
            type: 'popup',
            responsive: true,
            title: 'Add Products',
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action-secondary',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(modalOptions, modalElement);

        $(element).on('click', function () {
            modalElement.modal('openModal');
        });
    };
});
