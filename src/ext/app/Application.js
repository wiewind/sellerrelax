/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.define('SRX.Application', {
    extend: 'Ext.app.Application',

    name: 'SRX',

    requires: [
        'SRX.utils.*',
        'SRX.model.*',
        'SRX.store.*',
        'SRX.ux.*',

        'SRX.view.main.Main'
    ],

    quickTips: false,
    platformConfig: {
        desktop: {
            quickTips: true
        }
    },

    onAppUpdate: function () {
        window.location.reload();
    }
});
