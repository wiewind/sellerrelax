/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.EditWindowViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.exporteditwindow',

    data: {
        id: 0,
        type: ''
    },

    formulas: {
        getTitle: function (get) {
            if (get('type') === 'sku_a') {
                return get('id') > 0 ? T.__('Edit SKU Article') : T.__('New SKU Article');
            }
            return get('id') > 0 ? T.__('Edit FBA Customer') : T.__('New FBA Customer');
        },

        hiddenOldValue: function (get) {
            return get('id') === 0;
        }
    }
});