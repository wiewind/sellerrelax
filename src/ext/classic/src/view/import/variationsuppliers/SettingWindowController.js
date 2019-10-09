/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.variationsuppliers.SettingWindowController', {
    extend: 'SRX.ux.MusterFormWindowController',

    alias: 'controller.importvariationsupplierssettingwindow',

    submitSuccess: function (form, action) {
        var res = Ext.decode(action.response.responseText);

        ABox.info(Wiewind.String.sprintf(T.__('%s supplier(s) are imported!'), res.data));

        var grid = Ext.ComponentQuery.query('importvariationsuppliersgrid');
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
