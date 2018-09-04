/**
 * This class is the main view for the application. It is specified in app.js as the
 * "mainView" property. That setting automatically applies the "viewport"
 * plugin causing this view to become the body element (i.e., the viewport).
 *
 * TODO - Replace this content of this view to suite the needs of your application.
 */
Ext.define('SRX.view.main.Main', {
    extend: 'Ext.tab.Panel',
    xtype: 'app-main',

    requires: [
        'Ext.chart.*',
        'Ext.plugin.Viewport',
        'Ext.window.MessageBox',

        'SRX.view.article.Grid',
        'SRX.view.export.MainPanel',
        'SRX.view.settings.MainPanel'
    ],

    ui: 'navigation',

    tabBarHeaderPosition: 1,
    titleRotation: 0,
    tabRotation: 0,

    header: {
        layout: {
            align: 'stretchmax'
        },
        title: {
            text: '<img src="'+Cake.image.logo+'" style="position: absolute; left: 0; top: -18px;" />'
        }
    },

    tabBar: {
        layout: {
            align: 'stretch',
            overflowHandler: 'none'
        }
    },

    responsiveConfig: {
        tall: {
            headerPosition: 'top'
        },
        wide: {
            headerPosition: 'top'
        }
    },

    defaults: {
        bodyPadding: 20,
        scrollable: true,
        tabConfig: {
            plugins: 'responsive',
            responsiveConfig: {
                wide: {
                    iconAlign: 'left',
                    textAlign: 'left'
                },
                tall: {
                    iconAlign: 'top',
                    textAlign: 'center',
                    width: 120
                }
            }
        }
    },

    items: [
        {
            title: T.__('Export'),
            iconCls: 'fa-ge',
            items: [
                {
                    xtype: 'exportmainpanel'
                }
            ]
        },
        {
            title: T.__('Articles'),
            iconCls: 'fa-cube',
            items: [
                {
                    xtype: 'articlegrid'
                }
            ]
        },
        {
            title: T.__('Orders'),
            iconCls: 'fa-file-text-o',
            // bind: {
            //     html: '{loremIpsum}'
            // }
        },
        {
            title: 'Settings',
            iconCls: 'fa-cog',
            items: [
                {
                    xtype: 'settingstmainpanel'
                }
            ]
        }
    ]
});
