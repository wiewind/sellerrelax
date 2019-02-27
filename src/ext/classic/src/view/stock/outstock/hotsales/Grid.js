/**
 * Created by benying.zou on 20.02.2019.
 */

Ext.define('SRX.view.stock.outstock.hotsales.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'stockoutstockhotsalesgrid',

    requires: [
        'SRX.view.stock.outstock.hotsales.GridController',
        'SRX.view.stock.outstock.hotsales.GridViewModel'
    ],
    controller: 'stockoutstockhotsalesgrid',
    viewModel: {
        type: 'stockoutstockhotsalesgrid'
    },

    config: {
        forceFit: true,
        scrollable: true,
        bind: {
            store: '{hotsalesstore}'
        }
    },
    emptyText: T.__("This list is empty."),

    // selModel: {
    //     type: 'spreadsheet',
    //     rowSelect: false
    // },
    // plugins: [
    //     'cellediting',
    //     {
    //         ptype: 'clipboard'
    //     }
    // ],

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true,
        displayMsg: '',
        emptyMsg: T.__("This list is empty.")
    },

    tbar: [
        T.__('Lager') + ':',
        {
            xtype: 'combo',
            name: 'warehouse',
            bind: {
                store: '{warehouses}',
                value: '{warehouse_id}'
            },
            width: 200,
            queryMode: 'local',
            displayField: 'name',
            valueField: 'id',
            editable: false,
            forceSelection: true
        },
        T.__('Date') + ': ',
        {
            xtype: 'datefield',
            name: 'from',
            format: SSD.data.formatting.date_format,
            submitFormat: 'Y-m-d',
            bind: {
                value: '{from}'
            }
        },
        T.__(' - '),
        {
            xtype: 'datefield',
            name: 'to',
            format: SSD.data.formatting.date_format,
            submitFormat: 'Y-m-d',
            bind: {
                value: '{to}'
            }
        },
        T.__('Sort by') + ':',
        {
            xtype: 'combo',
            name: 'sort',
            store: Ext.create('Ext.data.Store', {
                fields: ['value', 'display'],
                data: [
                    {value: "sales", display: T.__('Sales quantity')},
                    {value: "purchase", display: T.__('Purchase')}
                ]
            }),
            bind: {
                value: '{sort}'
            },
            width: 100,
            queryMode: 'local',
            displayField: 'display',
            valueField: 'value',
            editable: false,
            forceSelection: true
        },
        T.__('TOP') + ':',
        {
            xtype: 'combo',
            name: 'limit',
            store: Ext.create('Ext.data.Store', {
                fields: ['value'],
                data: [
                    {value: 10},
                    {value: 20},
                    {value: 30},
                    {value: 50},
                    {value: 100}
                ]
            }),
            bind: {
                value: '{limit}'
            },
            width: 100,
            queryMode: 'local',
            displayField: 'value',
            valueField: 'value',
            editable: false,
            forceSelection: true
        },
        '->',
        {
            text: Glb.btnSetting.searchText,
            tooltip: Glb.btnSetting.searchText,
            iconCls: Glb.btnSetting.searchIconCls,
            handler: 'onChangeFilter'
        }
    ],

    columns: [
        {
            dataIndex: 'rowIndex',
            sortable : false,
            width: 50,
            renderer : function(value, metaData, record, rowIndex)
            {
                return rowIndex + 1;
            }
        },
        {
            text: T.__("Number"),
            dataIndex: 'number',
            width: 150
        },
        {
            text: T.__("EAN"),
            dataIndex: 'ean',
            width: 150
        },
        {
            text: T.__("Lager"),
            dataIndex: 'warehouse_name',
            width: 200
        },
        {
            text: T.__("Item"),
            dataIndex: 'item_id',
            width: 100
        },
        {
            text: T.__("Variation"),
            dataIndex: 'variation_id',
            width: 100
        },
        {
            text: T.__("Sales quantity"),
            dataIndex: 'sales',
            flex: 1
        },
        {
            text: T.__("Purchase"),
            dataIndex: 'purchase',
            flex: 1
        }
    ]
});