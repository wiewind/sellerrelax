/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmount3YViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.homechartsalesamount3y',

    data: {
        priceType: 'net',
        year1: new Date().getFullYear() - 2,
        year2: new Date().getFullYear() - 1,
        year3: new Date().getFullYear()
    },

    stores: {
        salesamount3y: {
            type: 'base',
            fields: [
                {name: 'date', mapping: 'date', type: 'string'},
                {name: 'mon_num', mapping: 'mon_num', type: 'int'},
                {name: 'sum1', mapping: 'sum1', type: 'float'},
                {name: 'sum2', mapping: 'sum2', type: 'float'},
                {name: 'sum3', mapping: 'sum3', type: 'float'}
            ],
            autoLoad: true,
            proxy: {
                url: Cake.api.path + '/chart/json/getSalesAmount3Y'
            },
            listeners: {
                beforeLoad: function (store) {
                    var chart = Ext.ComponentQuery.query('homechartsalesamount3y')[0],
                        vm = chart.getViewModel();
                    store.setExtraParams({
                        priceType: vm.get('priceType'),
                        years: vm.get('year1') + ':' + vm.get('year2') + ':' + vm.get('year3')
                    });
                }
            }
        }
    }
});