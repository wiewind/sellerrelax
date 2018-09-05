/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.export.skuarticles.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.exportskuarticlesgrid',

    data: {},

    stores: {
        skuarticlesStore: Ext.create('SRX.store.Base', {
            fields: [
                {name: 'id', mapping: 'ExportSetting.id', type: 'int'},
                {name: 'number', mapping: 'ExportSetting.value'}
            ],

            autoLoad: true,
            remoteSort: true,

            proxy: {
                url: Cake.api.path + '/exportsettings/json/getSkuArticles'
            }
        })
    }
});