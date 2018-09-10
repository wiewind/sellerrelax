/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.export.MainPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.exportmainpanel',

    openSkuVariations: function () {
        Ext.create('SRX.view.export.skuarticles.Window');
    },

    openFbaCustomers: function () {
        Ext.create('SRX.view.export.fbacustomers.Window');
    },

    openExportInventoryForecast: function () {
        Wiewind.Action.click({
            url: Cake.api.path + '/export/getStorePlan',
            target: '_blank'
        })
    }
});
