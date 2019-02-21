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

    launch: function () {
        // Ext.tip.QuickTipManager.init();
        // WWS.currentLocale = 'de';


        Ext.History.init();
        Ext.History.on('change', Glb.History.onChange);

        $('#welcome_box').html('');
        $('#welcome_box').hide();


        if (Glb.common.checkLogin()) {
            var timerId = window.setInterval(function () {
                if (SSD) {
                    Glb.Ajax({
                        url: Cake.api.path + '/system/json/keeplive',
                        timerId: timerId
                    });
                }
            }, 60000);
        }
    },

    onAppUpdate: function () {
        ABox.info(T.__('The new version is online!'), function () {
            window.location.reload();
        });
    }
});
