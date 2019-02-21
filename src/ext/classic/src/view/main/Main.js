/**
 * This class is the main view for the application. It is specified in app.js as the
 * "mainView" property. That setting automatically applies the "viewport"
 * plugin causing this view to become the body element (i.e., the viewport).
 *
 * TODO - Replace this content of this view to suite the needs of your application.
 */
Ext.define('SRX.view.main.Main', {
    extend: 'Ext.container.Container',
    xtype: 'app-main',

    requires: [
        'Ext.chart.*',
        'Ext.plugin.Viewport',
        'Ext.window.MessageBox',

        'SRX.overrides.form.field.Base',
        'SRX.overrides.button.Button',

        'SRX.ux.*',

        'SRX.view.main.Config',

        'SRX.view.home.MainPanel',
        'SRX.view.article.MainPanel',
        'SRX.view.export.MainPanel',
        'SRX.view.settings.MainPanel',
        'SRX.view.stock.outstock.MainPanel',

        'SRX.view.main.HeaderPanel'
    ],

    id: 'appmain',

    config: {
        layout: 'border'
    },

    items: [
        {
            xtype: 'mainheader',
            region: 'north'
        },
        {
            xtype: 'tabpanel',
            id: 'mainTabpanel',
            region: 'center',
            ui: 'navigation',
            items: [
                {
                    xtype: 'homemainpanel'
                }
            ]
        }
    ]
});
