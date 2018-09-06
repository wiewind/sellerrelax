/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.export.fbacustomers.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.exportfbacustomersgrid',

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

    onClickAdd: function () {
        Ext.create('SRX.view.export.EditWindow', {
            viewModel: {
                data: {
                    id: 0,
                    type: 'fba_c'
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
                    type: 'fba_c'
                }
            }
        });
    }
});
