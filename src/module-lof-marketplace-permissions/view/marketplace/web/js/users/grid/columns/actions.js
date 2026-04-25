define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'Lof_MarketPermissions/js/user-edit',
    'Lof_MarketPermissions/js/user-delete',
    'Lof_MarketPermissions/js/role-delete'
], function ($, Actions) {
    'use strict';

    return Actions.extend({
        defaults: {
            template: 'Lof_MarketPermissions/users/grid/columns/actions'
        },

        /**
         * IMPORTANT: override this
         */
        getActionHandler: function (action) {
            return function () {
                switch (action.type) {
                    case 'edit-user':
                        $(this).userEdit(action.options)
                            .trigger('editUser');
                        break;

                    case 'delete-user':
                        $(this).userDelete(action.options)
                            .trigger('deleteUser');
                        break;

                    case 'delete-role':
                        $(this).roleDelete(action.options)
                            .trigger('deleteRole');
                        break;
                }

                // 🚨 THIS is what stops href="#"
                return false;
            }.bind(this);
        }
    });
});
