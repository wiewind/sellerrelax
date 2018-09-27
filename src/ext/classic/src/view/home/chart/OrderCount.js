/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.OrderCount', {
    extend: 'Ext.chart.CartesianChart',
    xtype: 'homechartordercount',

    requires: [
        'SRX.view.home.chart.OrderCountController',
        'SRX.view.home.chart.OrderCountViewModel'
    ],

    controller: 'homechartordercount',
    viewModel: {
        type: 'homechartordercount'
    },

    config: {
        title: T.__('Count of Orders'),
        width: '100%',
        height: 500,
        insetPadding: 20
    },
    bind: {
        store: '{ordercount}'
    },

    tbar: [
        T.__('Select Period') + ':',
        {
            xtype: 'combo',
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
        }
    ],

    plugins: {
        chartitemevents: {
            moveEvents: true
        }
    },

    interactions: {
        type: 'panzoom',
        zoomOnPanGesture: true
    },

    axes: [
        {
            type: 'numeric',
            position: 'left',
            grid: true,
            minimum: 0
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
            type: 'bar',
            xField: 'date',
            yField: 'sum',
            highlight: {
                fillStyle: '#000',
                // radius: 5,
                // lineWidth: 2,
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