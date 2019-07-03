/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.variationsuppliers.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.importvariationsuppliersgrid',


    data: {
        status: 0,
        from: Wiewind.Date.getDate(-3, 'M'),
        to: new Date(),
        itemId: ''
    },

    stores: {
        importstore: {
            type: 'importvariationsuppliers',
            autoLoad: true,
            listeners: {
                beforeload: function (store) {
                    var grid = Ext.ComponentQuery.query('importvariationsuppliersgrid')[0],
                        vm = grid.getViewModel(),
                        params = {
                            status: vm.get('status'),
                            from: Wiewind.Date.dateToSqlStr(vm.get('from')),
                            to: Wiewind.Date.dateToSqlStr(vm.get('to')),
                            itemId: vm.get('itemId'),
                            variationId: vm.get('variationId')
                        };
                    store.setExtraParams(params);
                }
            }
        }
    }
});