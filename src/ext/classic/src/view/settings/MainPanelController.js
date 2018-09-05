/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.settings.MainPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.settingstmainpanel',

    onClickLogout: function () {
        Glb.common.mask(T.__('Please waite...'));
        Glb.Ajax({
            url: Cake.api.path + '/system/json/doLogout',
            success: function () {
                window.location.assign(Cake.api.path + '/login');
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
