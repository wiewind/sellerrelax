/**
 * Created by benying.zou on 25.09.2018.
 */
Ext.define('SRX.view.home.MainPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'homemainpanel',

    requires: [
        'SRX.view.home.chart.SalesAmount',
        'SRX.view.home.chart.OrderCount'
    ],

    config: {
        title: T.__('Home'),
        iconCls: 'x-fa fa-home',
        layout: 'border'
    },

    items: [
        {
            region: 'north',
            bodyPadding: 20,
            html: '<h1>hard working...</h1>'
        },
        {
            region: 'center',
            scrollable: true,
            layout: 'column',
            defaults: {
                xtype: 'container',
                layout: 'vbox',
                columnWidth: 0.5,
                defaults: {
                    margin: 5,
                    border: 1,
                    padding: 10,
                    width: '100%'
                }
            },
            items: [
                {
                    items: [
                        {
                            xtype: 'homechartsalesamount'
                        }
                    ]
                },
                {
                    items: [
                        {
                            xtype: 'homechartordercount'
                        }
                    ]
                }
            ]
        }
    ]
});