/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.tracking.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.importtrackinggrid',

    data: {
        type: 'orders',
        from: Wiewind.Date.getDate(-3, 'M'),
        to: new Date(),
        hasMenge: true
    },

    stores: {
        trackingstore: {
            type: 'imports',
            autoLoad: true,
            listeners: {
                beforeload: function (store) {
                    var grid = Ext.ComponentQuery.query('importtrackinggrid')[0],
                        vm = grid.getViewModel(),
                        params = {
                            type: vm.get('type'),
                            from: Wiewind.Date.dateToSqlStr(vm.get('from')),
                            to: Wiewind.Date.dateToSqlStr(vm.get('to')),
                            hasMenge: vm.get('hasMenge')
                        };
                    store.setExtraParams(params);
                }
            }
        }
    }
});