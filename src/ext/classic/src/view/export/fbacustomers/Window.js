/**
 * Created by benying.zou on 04.09.2018.
 */
/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.export.fbacustomers.Window', {
    extend: 'Ext.window.Window',
    xtype: 'exportfbacustomerswindow',

    requires: [
        'SRX.view.export.fbacustomers.Grid'
    ],


    config: {
        title: T.__('FBA Customers'),
        iconCls: 'x-fa fa-magic',
        modal: true,
        width: 600,
        height: 800,
        layout: 'fit',
        closable: true,
        autoShow: true
    },

    items: [
        {
            xtype: 'exportfbacustomersgrid'
        }
    ]

});