/**
 * Created by benying.zou on 20.02.2019.
 */
Ext.define('SRX.store.OutstockWarehouses', {
    extend: 'SRX.store.Base',

    alias: 'store.outstockwarehouses',

    model: 'SRX.model.OutstockWarehouse',

    proxy: {
        url: Cake.api.path + '/Outstocks/json/ShowWarehouses'
    }
});