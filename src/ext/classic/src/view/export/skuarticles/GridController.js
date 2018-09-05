/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.export.skuarticles.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.exportskuarticlesgrid',

    onClickImport: function (field, event) {
        var view = this.getView(),
            window = view.up('window'),
            editCont = window.down('[itemId="editContainer"]');
        editCont.removeAll();
        editCont.add({
            xtype: 'exportskuarticlesuploadpanel'
        });
    },

    onClickAdd: function () {
        Ext.create('SRX.view.export.EditWindow', {
            viewModel: {
                data: {
                    id: 0,
                    type: 'sku_a'
                }
            }
        });
    },

    onItemdblclick: function (grid, record) {
        var id = record.get('id');
        Ext.create('SRX.view.export.EditWindow', {
            viewModel: {
                data: {
                    id: id,
                    type: 'sku_a'
                }
            }
        });
    }
});
