/**
 * Created by benying.zou on 27.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmount3Y', {
    extend: 'Ext.chart.CartesianChart',
    xtype: 'homechartsalesamount3y',

    requires: [
        'SRX.view.home.chart.SalesAmount3YController',
        'SRX.view.home.chart.SalesAmount3YViewModel'
    ],

    controller: 'homechartsalesamount3y',
    viewModel: {
        type: 'homechartsalesamount3y'
    },

    config: {
        title: T.__('Sales Amount of 3 years'),
        width: '100%',
        height: 500,
        insetPadding: 20,
        innerPadding: {
            left: 5,
            right: 5,
            top: 5
        },
        colors: ['DarkKhaki', 'ForestGreen', 'Tomato']
    },

    bind: {
        store: '{salesamount3y}'
    },

    tbar: [
        T.__('Type Of Preis') + ':',
        {
            xtype: 'combo',
            itemId: 'comboPriceType',
            store: Ext.create('Ext.data.Store', {
                fields: ['display', 'value'],
                data : [
                    {"display":T.__('pre-tax'), "value":"gross"},
                    {"display":T.__('after-tax'), "value":"net"}
                ]
            }),
            width: 100,
            queryMode: 'local',
            displayField: 'display',
            valueField: 'value',
            bind: {
                value: '{priceType}'
            },
            listeners: {
                change: 'onChangePeriod'
            }
        },
        T.__('Years') + ':',
        {
            xtype: 'numberfield',
            itemId: 'year1',
            width: 100,
            minValue: 1990,
            maxValue: 2050,
            fieldStyle: {
                fontWeight: 'bold'
            },
            bind: {
                value: '{year1}'
            }
        },
        {
            xtype: 'numberfield',
            itemId: 'year2',
            width: 100,
            minValue: 1990,
            maxValue: 2050,
            fieldStyle: {
                fontWeight: 'bold'
            },
            bind: {
                value: '{year2}'
            }
        },
        {
            xtype: 'numberfield',
            itemId: 'year3',
            width: 100,
            minValue: 1990,
            maxValue: 2050,
            fieldStyle: {
                fontWeight: 'bold'
            },
            bind: {
                value: '{year3}'
            }
        },
        {
            text: Glb.btnSetting.refreshText,
            iconCls: Glb.btnSetting.refreshIconCls,
            handler: 'onChangeYear'
        }
    ],

    interactions: {
        type: 'panzoom'
    },

    sprites: [
        {
            type: 'text',
            text: '€',
            x: 40,
            y: 10
        }
    ],

    axes: [
        {
            type: 'numeric',
            position: 'left'
        },
        {
            type: 'category',
            position: 'bottom'
        }
    ]
});