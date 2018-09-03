/**
 * Created by benying.zou on 02.02.2018.
 */
Ext.define ('SRX.ux.MusterFormWindowController', {
    extend: 'SRX.ux.MusterFormController',

    alias: 'controller.musterformwindow',

    getViewForm: function () {
        return this.getView().down('form');
    },

    onClose: function () {
        this.beforeclose();
        this.getView().close();
    },

    beforeclose: function () {}
});