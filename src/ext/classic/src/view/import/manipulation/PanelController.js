/**
 * Created by benying.zou on 21.09.2018.
 */
Ext.define('SRX.view.import.manipulation.PanelController', {
    extend: 'SRX.ux.MusterFormController',

    alias: 'controller.importmanipanel',

    submitSuccess: function (form, action) {
        ABox.info(T.__('Import erledigt!'));
    },

    onReset: function () {
        ABox.confirm(
            T.__('Do you want to reset the database?'),
            function () {
                Glb.Ajax({
                    url: Cake.api.path + '/rest/import/transjson/clearOrderImports',
                    success: function (response, options) {
                        ABox.info(T.__('The database is empty, it will refill at next run of import conjob!'));
                    }
                });
            }
        );
    },

    onImportItems: function () {
        var me = this;
        ABox.confirm(
            T.__('All items will be removed and the data re-imported. Are you sure you want to do this?'),
            function () {
                me.doImportItems(1);
            }
        );
    },

    doImportItems: function (newImport) {
        newImport = newImport || 0;
        Glb.common.mask();
        var me = this;
        Glb.Ajax({
            url: Cake.api.path + '/rest/import/transjson/importItems/' + newImport,
            timeout: 300000,
            success: function (response, options) {
                var resp = Ext.decode(response.responseText);
                if (resp.success) {
                    var data = resp.data;
                    if (!Wiewind.isEmpty(data) && data.is_last_page) {
                        ABox.info(T.__('Items have been updated!'));
                    } else {
                        me.doImportItems(0)
                    }
                }
            }
        });
    },

    onImportVariations: function () {
        var me = this;
        ABox.confirm(
            T.__('All variations will be removed and the data re-imported. Are you sure you want to do this?'),
            function () {
                me.doImportVariations(1);
            }
        );
    },

    doImportVariations: function (newImport) {
        newImport = newImport || 0;
        Glb.common.mask();
        var me = this;
        Glb.Ajax({
            url: Cake.api.path + '/rest/import/transjson/importVariations/' + newImport,
            timeout: 300000,
            success: function (response, options) {
                var resp = Ext.decode(response.responseText);
                if (resp.success) {
                    var data = resp.data;
                    if (!Wiewind.isEmpty(data) && data.is_last_page) {
                        ABox.info(T.__('Variations have been updated!'));
                    } else {
                        me.doImportVariations(0)
                    }
                }
            }
        });
    },

    onImportAllWarehouses: function () {
        ABox.confirm(
            T.__('All warehouses (inc. dimensions, levels and locations) will be removed and the data re-imported. Are you sure you want to do this?'),
            function () {
                Glb.common.mask();
                Glb.Ajax({
                    url: Cake.api.path + '/rest/StockManagement/transjson/renewAllWarehouses',
                    timeout: 300000,
                    success: function (response, options) {
                        ABox.info(T.__('All warehouses have been updated!'));
                    }
                });
            }
        );
    },

    onImportWarehouses: function () {

    },

    onImportDimensions: function () {

    },

    onImportlevels: function () {

    },

    onImportLocations: function () {

    }
});