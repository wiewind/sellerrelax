/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.itemproperties.SettingWindowController', {
    extend: 'SRX.ux.MusterFormWindowController',

    alias: 'controller.importitempropertiessettingwindow',

    onChange: function (combo, newValue, oldValue) {
        var view = this.getView(),
            form = view.down('form');


        Ext.Array.forEach(form.items.items, function (item) {
            if (item.xtype == 'combobox') {
                item.setValue(newValue);
            }
        });
    },

    submitSuccess: function (form, action) {
        var res = Ext.decode(action.response.responseText);

        ABox.info(Wiewind.String.sprintf(T.__('%s variation(s) are imported!'), res.data));

        var grid = Ext.ComponentQuery.query('importitempropertiesgrid');
        if (grid) {
            grid[0].getStore().loadPage(1);
        }
        this.closeView();
    },

    beforeclose: function () {
        var vm = this.getViewModel();
        Glb.Ajax({
            url: Cake.api.path + '/ImportVariationProperties/json/deleteCsvFile',
            params: {
                filename: vm.get('file')
            }
        });
    }
});
