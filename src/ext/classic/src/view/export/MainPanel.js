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
            collapsible: true,
            collapseToolText: T.__('Collapse'),
            expandToolText: T.__('Expand'),
            // collapsed: true,
            layout: 'hbox',
            defaults: {
                xtype: 'button',
                margin: 10,
                padding: 10,
                width: 200
            },
            items: [
                {
                    text: T.__('Edit SKU Articles'),
                    iconCls: Glb.btnSetting.editIconCls,
                    handler: 'openSkuVariations'
                },
                {
                    text: T.__('Edit FBA Customers'),
                    iconCls: Glb.btnSetting.editIconCls,
                    handler: 'openFbaCustomers'
                }
            ]
        },
        {
            title: T.__('Export'),
            iconCls: 'x-fa fa-ge',
            defaults: {
                xtype: 'button',
                margin: 10,
                padding: 10,
                width: 200
            },
            items: [
                {
                    text: T.__('Inventory Forecast'),
                    iconCls: 'x-fa fa-hand-o-right',
                    handler: 'openExportInventoryForecast'
                }
            ]
        }
    ]
});
