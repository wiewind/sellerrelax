/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.store.Imports', {
    extend: 'SRX.store.Base',

    alias: 'store.imports',

    model: 'SRX.model.Import',

    proxy: {
        url: Cake.api.path + '/import/json/getImports'
    }
});
