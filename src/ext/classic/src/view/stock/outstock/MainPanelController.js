/**
 * Created by benying.zou on 04.09.2018.
 */
Ext.define('SRX.view.stock.outstock.MainPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.stockoutstockmainpanel',

    afterRender: function () {
        this.onClickHotsales();
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

    onClickHotsales: function () {
        var view = this.getView(),
            container = view.down('container[itemId="outstockMainCt"]');
        container.removeAll();
        container.add({
            xtype: 'stockoutstockhotsalesgrid'
        });
        this.afterClick('hotsales');
    }
});
