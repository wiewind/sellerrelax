/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.store.Robots', {
    extend: 'SRX.store.Base',

    alias: 'store.robots',

    model: 'SRX.model.Import',

    proxy: {
        url: Cake.api.path + '/import/json/getRobots'
    }
});
