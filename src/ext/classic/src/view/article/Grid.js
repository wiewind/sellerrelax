/**
 * Created by benying.zou on 31.08.2018.
 */
Ext.define('SRX.view.article.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'articlegrid',

    requires: [
        'SRX.view.article.GridController',
        'SRX.view.article.GridViewModel'
    ],
    controller: 'articlegrid',
    viewModel: {
        type: 'articlegrid'
    },

    config: {
        title: T.__('Articles'),
        iconCls: 'x-fa fa-cube',
        border: 0,
        selModel: {
            selType: 'checkboxmodel',
            ignoreRightMouseSelection: true
        },
        forceFit: true,
        bind: {
            store: '{articlegridstore}'
        }
    },
    emptyText: T.__("This list is empty."),

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true,
        displayMsg: '{0} - {1} of {2}',
        emptyMsg: T.__("This list is empty.")
    },

    columns: [
        {
            text: T.__("Article ID"),
            dataIndex: 'extern_id',
            sortable: true,
            width: 120
        },
        {
            text: T.__("name"),
            flex: 1,
            sortable: true,
            dataIndex: 'name'
        },
        {
            text: T.__("count of orders"),
            dataIndex: 'count_orders',
            sortable: true,
            align: 'right',
            width: 120,
            renderer: function (data) {
                if (!data) {
                    return 0;
                }
                return data;
            }
        },
        {
            text: T.__("quantity"),
            dataIndex: 'sum_quantity',
            sortable: true,
            align: 'right',
            width: 120,
            renderer: function (data) {
                if (!data) {
                    return 0;
                }
                return data;
            }
        },
        {
            text: T.__("price net"),
            dataIndex: 'sum_price_net',
            sortable: true,
            align: 'right',
            width: 120,
            renderer: function (data, metaData, record) {
                var num = '';
                if (!data) {
                    num = Glb.formatMoney(0);
                }
                num = Glb.formatMoney(data);
                var currency = record.get('currency');
                if (currency) {
                    currency = currency.toUpperCase()
                } else {
                    currency = 'EUR';
                }
                switch (currency) {
                    case 'EUR':
                    case 'EURO':
                        currency = '€';
                        break;
                }
                return currency + ' ' + num;
            }
        }
    ],

    tbar: [
        '->',
        T.__('orders in '),
        {
            xtype: 'numberfield',
            filedId: 'searchDays',
            width: 100,
            value: 7
        },
        T.__('days '),
        {
            xtype: 'searchfield',
            filedId: 'searchText',
            emptyText: T.__('Search with article name...'),
            width: 300,
            listeners: {
                specialkey: 'enterSearch'
            },
            onClickCancel: 'onClickCancel',
            onClickSearch: 'onClickSearch'
        }
    ]
});