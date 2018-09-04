/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.settings.MainPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.settingstmainpanel',

    onClickLogout: function () {
        Glb.Ajax({
            url: Cake.api.path + '/system/json/doLogout',
            success: function () {
                window.location.assign('/');
            }
        });
    },

    onClickUpdate: function () {
        Wiewind.Action.click({
            url: '/update',
            target: '_blank'
        })
    }
});
