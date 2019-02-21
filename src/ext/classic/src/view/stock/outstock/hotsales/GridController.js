/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.stock.outstock.hotsales.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.stockoutstockhotsalesgrid',

    onChangeFilter: function () {
        this.getViewModel().getStore('hotsalesstore').reload({page: 1});
    }
});
