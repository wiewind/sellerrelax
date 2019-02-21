/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.stock.outstock.hotsales.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.stockoutstockhotsalesgrid',

    data: {
        warehouse_id: 0,
        from: Wiewind.Date.getDate(-3, 'M'),
        to: new Date(),
        limit: 10,
        sort: 'sales'
    },

    stores: {
        hotsalesstore: {
            type: 'outstockhotsales',
            autoLoad: true,
            listeners: {
                beforeload: function (store) {
                    var grid = Ext.ComponentQuery.query('stockoutstockhotsalesgrid')[0],
                        vm = grid.getViewModel(),
                        params = {
                            warehouse_id: vm.get('warehouse_id'),
                            from: Wiewind.Date.dateToSqlStr(vm.get('from')),
                            to: Wiewind.Date.dateToSqlStr(vm.get('to')),
                            sort: vm.get('sort'),
                            limit: vm.get('limit')
                        };
                    store.setExtraParams(params);
                }
            }
        },
        warehouses: {
            type: 'outstockwarehouses',
            autoLoad: true
        }
    }
});