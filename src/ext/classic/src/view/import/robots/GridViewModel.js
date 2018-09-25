/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.robots.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.importrobotsgrid',

    data: {},

    stores: {
        robotsstore: {
            type: 'robots',
            autoLoad: true
        }
    }
});