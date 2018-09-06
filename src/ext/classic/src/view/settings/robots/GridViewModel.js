/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.settings.robots.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.settingsrobotsgrid',

    data: {},

    stores: {
        robotsstore: {
            type: 'robots',
            autoLoad: true
        }
    }
});