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
        ABox.confirm(
            T.__('All items will be removed and the data re-imported. Are you sure you want to do this?'),
            function () {
                Glb.common.mask();
                timeout: 300000,
                Glb.Ajax({
                    url: Cake.api.path + '/rest/import/transjson/importItemsAll',
                    success: function (response, options) {
                        ABox.info(T.__('Items have been updated!'));
                    }
                });
            }
        );
    },

    onImportVariations: function () {
        ABox.confirm(
            T.__('All variations will be removed and the data re-imported. Are you sure you want to do this?'),
            function () {
                Glb.common.mask();
                Glb.Ajax({
                    url: Cake.api.path + '/rest/import/transjson/importVariationsAll',
                    timeout: 300000,
                    success: function (response, options) {
                        ABox.info(T.__('Variations have been updated!'));
                    }
                });
            }
        );
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