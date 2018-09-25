/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.tracking.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.importtrackinggrid',

    onChangeFilter: function () {
        this.getViewModel().getStore('trackingstore').reload({page: 1});
    }
});
