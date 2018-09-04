/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.export.MainPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'exportmainpanel',

    requires: [
        'SRX.view.export.MainPanelController',

        'SRX.view.export.skuarticles.Window'
    ],

    controller: 'exportmainpanel',
    // viewModel: {
    //     type: 'exportmainpanel'
    // },


    config: {
        layout: 'vbox',
        bodyPadding: 10
    },

    defaults: {
        xtype: 'panel',
        padding: 10,
        layout: 'vbox',
        bodyPadding: 10,
        width: '100%',
        border: 1
    },

    items: [
        {
            title: T.__('Export Setting'),
            iconCls: 'x-fa fa-edit',
            defaults: {
                xtype: 'button',
                margin: '10px auto',
                padding: 10,
                width: 200
            },
            items: [
                {
                    text: T.__('Edit SKU Articles'),
                    iconCls: 'x-fa fa-magic',
                    handler: 'openSkuVariations'
                },
                {
                    text: T.__('Edit FBA Customers'),
                    iconCls: 'x-fa fa-magic',
                    handler: 'openFbaCustomers'
                }
            ]
        },
        {
            title: T.__('Export'),
            iconCls: 'x-fa fa-ge',
            defaults: {
                xtype: 'button',
                margin: '10px auto',
                padding: 10,
                width: 200
            },
            items: [

            ]
        }
    ]


});
