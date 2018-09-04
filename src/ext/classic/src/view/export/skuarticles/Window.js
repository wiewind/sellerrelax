/**
 * Created by benying.zou on 04.09.2018.
 */
/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.export.skuarticles.Window', {
    extend: 'Ext.window.Window',
    xtype: 'exportskuarticleswindow',

    requires: [
        // 'SRX.view.export.skuarticles.Grid',
        // 'SRX.view.export.skuarticles.EditPanel'
    ],


    config: {
        title: T.__('Edit SKU Articles'),
        iconCls: 'x-fa fa-magic',
        modal: true,
        width: 1200,
        height: 800,
        autoShow: true,
        layout: 'hbox'
    },

    items: [
        
    ]

});