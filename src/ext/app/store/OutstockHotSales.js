/**
 * Created by benying.zou on 20.02.2019.
 */
Ext.define('SRX.store.OutstockHotSales', {
    extend: 'SRX.store.Base',

    alias: 'store.outstockhotsales',

    model: 'SRX.model.OutstockHotSale',

    proxy: {
        url: Cake.api.path + '/Outstocks/json/ShowHotSales'
    }
});