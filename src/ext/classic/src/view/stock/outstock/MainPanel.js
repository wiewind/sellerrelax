/**
 * Created by benying.zou on 21.09.2018.
 */
Ext.define('SRX.view.stock.outstock.MainPanel', {
    extend: 'Ext.container.Container',
    xtype: 'stockoutstockmainpanel',

    requires: [
        'SRX.view.stock.outstock.hotsales.Grid',
        'SRX.view.stock.outstock.MainPanelController'
    ],

    controller: 'stockoutstockmainpanel',

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
                    text: T.__('Hot sales'),
                    iconCls: 'x-fa fa-th',
                    itemId: 'hotsales',
                    handler: 'onClickHotsales'
                }
            ]
        },
        {
            xtype: 'container',
            region: 'center',
            itemId: 'outstockMainCt',
            layout: 'fit'
        }
    ]
});