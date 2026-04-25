define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        getLabel: function (row) {
            const value = this._super(row);

            if (value === null || value === undefined || value === '') {
                return value;
            }

            return value + '%';
        }
    });
});
