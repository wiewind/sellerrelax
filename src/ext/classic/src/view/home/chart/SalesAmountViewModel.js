/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmountViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.homechartsalesamount',

    data: {
        period: 'month',
        priceType: 'net'
    },

    stores: {
        salesamount: {
            type: 'base',
            fields: [
                {name: 'date', mapping: 'date', type: 'string'},
                {name: 'sum', mapping: 'sum', type: 'float'}
            ],
            autoLoad: true,
            proxy: {
                url: Cake.api.path + '/chart/json/getSalesAmount'
            },
            listeners: {
                beforeLoad: function (store) {
                    var chart = Ext.ComponentQuery.query('homechartsalesamount')[0],
                        vm = chart.getViewModel();
                    store.setExtraParams({
                        period: vm.get('period'),
                        priceType: vm.get('priceType')
                    });
                }
            }
        }
    }
});