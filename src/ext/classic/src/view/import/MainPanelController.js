/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.import.MainPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.importmainpanel',

    afterRender: function () {
        this.onClickTracking();
    },

    afterClick: function (itemId) {
        var view = this.getView(),
            menuCt = view.getComponent('menu');
        menuCt.items.items.forEach(function (item, index) {
            if (item.xtype === 'button') {
                if (item.itemId === itemId) {
                    item.setStyle({
                        background: 'red'
                    });
                } else {
                    item.setStyle({
                        background: '#35384c'
                    });
                }
            }
        });
    },

    onClickTracking: function () {
        var view = this.getView(),
            container = view.down('container[itemId="importMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'importtrackinggrid'
        });
        this.afterClick('tracking');
    },

    onClickMani: function () {
        var view = this.getView(),
            container = view.down('container[itemId="importMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'importmanipanel'
        });
        this.afterClick('manipulation');
    },

    onClickRobots: function () {
        var view = this.getView(),
            container = view.down('container[itemId="importMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'importrobotsgrid'
        });
        this.afterClick('robots');
    },

    onClickItemProperties: function () {
        var view = this.getView(),
            container = view.down('container[itemId="importMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'importitempropertiesgrid'
        });
        this.afterClick('itemproperties');
    },

    onClickVariationSuppliers: function () {
        var view = this.getView(),
            container = view.down('container[itemId="importMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'importvariationsuppliersgrid'
        });
        this.afterClick('variationsuppliers');
    }
});
