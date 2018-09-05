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
        'SRX.view.export.skuarticles.Grid',
        'SRX.view.export.skuarticles.UploadPanel'
    ],


    config: {
        title: T.__('SKU Articles'),
        iconCls: 'x-fa fa-magic',
        modal: true,
        width: 1200,
        height: 800,
        layout: 'border',
        closable: true,
        autoShow: true
    },

    items: [
        {
            xtype: 'exportskuarticlesgrid',
            region: 'west',
            width: 600
        },
        {
            xtype: 'container',
            region: 'center',
            layout: 'fit',
            itemId: 'editContainer',
            items: [
                {
                    xtype: 'exportskuarticlesuploadpanel'
                }
            ]
        }
    ]

});