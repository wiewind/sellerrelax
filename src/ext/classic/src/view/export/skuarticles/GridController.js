/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.export.skuarticles.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.exportskuarticlesgrid',

    onDelete: function(grid, rowIndex, colIndex) {
        ABox.confirm(
            T.__("Are you sure you want to delete the record?"),
            function () {
                var store = grid.getStore(),
                    rec = store.getAt(rowIndex),
                    id = rec.get('id');
                Glb.Ajax({
                    url: Cake.api.path + '/ExportSettings/json/delete',
                    params: {id: id},
                    success: function () {
                        ABox.info(T.__('The record has been deleted!'));
                    }
                });

                store.loadPage(1);
            }
        );
    },

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
