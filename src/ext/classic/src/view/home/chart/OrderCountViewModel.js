/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.OrderCountViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.homechartordercount',

    data: {
        typesDisplay: [T.__('Day'), T.__('Month'), T.__('Year')],
        period: 'month'
    },

    stores: {
        ordercount: {
            type: 'base',
            fields: [
                {name: 'date', mapping: 'date', type: 'string'},
                {name: 'sum', mapping: 'sum', type: 'float'}
            ],
            autoLoad: true,
            proxy: {
                url: Cake.api.path + '/chart/json/getOrderCount'
            },
            listeners: {
                beforeLoad: function (store) {
                    var chart = Ext.ComponentQuery.query('homechartordercount')[0],
                        vm = chart.getViewModel();
                    store.setExtraParams({
                        period: vm.get('period')
                    });
                }
            }
        }
    }
});