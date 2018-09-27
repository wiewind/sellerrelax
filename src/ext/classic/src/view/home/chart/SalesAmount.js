/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmount', {
    extend: 'Ext.chart.CartesianChart',
    xtype: 'homechartsalesamount',

    requires: [
        'SRX.view.home.chart.SalesAmountController',
        'SRX.view.home.chart.SalesAmountViewModel'
    ],

    controller: 'homechartsalesamount',
    viewModel: {
        type: 'homechartsalesamount'
    },

    config: {
        title: T.__('Sales Amount'),
        width: '100%',
        height: 500,
        insetPadding: 20,
        innerPadding: {
            left: 5,
            right: 5,
            top: 5
        }
    },
    bind: {
        store: '{salesamount}'
    },

    tbar: [
        T.__('Select Period') + ':',
        {
            xtype: 'combo',
            itemId: 'comboPeriod',
            store: Ext.create('Ext.data.Store', {
                fields: ['display', 'value'],
                data : [
                    {"display":T.__('Day'), "value":"day"},
                    {"display":T.__('Month'), "value":"month"},
                    {"display":T.__('Year'), "value":"year"}
                ]
            }),
            width: 100,
            queryMode: 'local',
            displayField: 'display',
            valueField: 'value',
            bind: {
                value: '{period}'
            },
            listeners: {
                change: 'onChangePeriod'
            }
        },
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
        }
    ],

    interactions: {
        type: 'panzoom',
        zoomOnPanGesture: true
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
            position: 'bottom',
            label: {
                rotate: {
                    degrees: 270
                }
            }
        }
    ],
    series: [
        {
            type: 'line',
            xField: 'date',
            yField: 'sum',
            style: {
                lineWidth: 4
            },
            marker: {
                radius: 4
            },
            highlight: {
                fillStyle: '#000',
                radius: 5,
                lineWidth: 2,
                strokeStyle: '#fff'
            },
            tooltip: {
                trackMouse: true,
                showDelay: 0,
                dismissDelay: 0,
                hideDelay: 0,
                renderer: 'onSeriesTooltipRender'
            }
        }
    ]
});