/**
 * Created by benying.zou on 31.01.2018.
 */
Ext.define('SRX.view.main.HeaderPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.mainheader',

    onOpenModule: function (btn) {
        MainConfig.openModule(btn.module);
    },

    noEvent: function () {}
});