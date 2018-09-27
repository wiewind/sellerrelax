/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.store.SalesVolume', {
    extend: 'SRX.store.Base',

    alias: 'store.salesvolume',

    fields: [
        {name: 'date', mapping: 'date', type: 'string'},
        {name: 'sum', mapping: 'sum', type: 'float'}
    ]
});
