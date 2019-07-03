/**
 * Created by benying.zou on 21.09.2018.
 */
Ext.define('SRX.view.import.MainPanel', {
    extend: 'Ext.container.Container',
    xtype: 'importmainpanel',

    requires: [
        'SRX.view.import.tracking.Grid',
        'SRX.view.import.manipulation.Panel',
        'SRX.view.import.robots.Grid',
        'SRX.view.import.itemproperties.Grid',
        'SRX.view.import.variationsuppliers.Grid',
        'SRX.view.import.MainPanelController'
    ],

    controller: 'importmainpanel',

    config: {
        tabPosition: 'left',
        layout: 'border'
    },

    items: [
        {
            xtype: 'container',
            region: 'west',
            itemId: 'menu',
            width: 300,
            padding: 5,
            defaults: {
                xtype: 'button',
                width: 280,
                margin: 5,
                padding: 5,
                scale: 'medium'
            },
            items: [
                {
                    text: T.__('Tracking'),
                    iconCls: 'x-fa fa-th',
                    itemId: 'tracking',
                    handler: 'onClickTracking'
                },
                {
                    text: T.__('Manipulation'),
                    iconCls: 'x-fa fa-cogs',
                    itemId: 'manipulation',
                    handler: 'onClickMani'
                },
                {
                    text: T.__('Robots'),
                    iconCls: 'x-fa fa-android',
                    itemId: 'robots',
                    handler: 'onClickRobots'
                },
                {
                    text: T.__('Item Properties'),
                    iconCls: 'x-fa fa-tag',
                    itemId: 'itemproperties',
                    handler: 'onClickItemProperties'
                },
                {
                    text: T.__('Variation Suppliers'),
                    iconCls: 'x-fa fa-tag',
                    itemId: 'variationsuppliers',
                    handler: 'onClickVariationSuppliers'
                }
            ]
        },
        {
            xtype: 'container',
            region: 'center',
            itemId: 'importMainCt',
            layout: 'fit'
        }
    ]
});