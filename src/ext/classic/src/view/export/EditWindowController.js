/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.EditWindowController', {
    extend: 'SRX.ux.MusterFormWindowController',

    alias: 'controller.exporteditwindow',

    afterRender: function () {
        var vm = this.getViewModel(),
            id = vm.get('id'),
            type = vm.get('type');

        if (id > 0) {
            Glb.Ajax({
                url: Cake.api.path + '/ExportSettings/json/getExportSettingValue',
                params: {
                    id: id,
                    type: type
                },
                success: function (response, options) {
                    var data = Ext.decode(response.responseText).data;
                    data.oldValue = data.value;
                    vm.setData(data);
                }
            });
        }
    },

    submitSuccess: function (form, action) {
        var className = (this.getViewModel().get('type') === 'sku_a') ? 'exportskuarticlesgrid' : 'exportfbacustomersgrid',
            cq = Ext.ComponentQuery.query(className);
        if (cq) {
            cq[0].getStore().load();
        }
        this.closeView();
    }
});