/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.article.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.articlegrid',

    enterSearch: function (field, event) {
        if (event.getKey() == event.ENTER) {
            this.onClickSearch(field);
        }
    },

    onClickSearch: function (field, event) {
        var vm = this.getViewModel(),
            store = vm.getStore('articlegridstore'),
            text = field.getValue(),
            daysField = this.getView().down('[filedId="searchDays"]'),
            days = daysField.getValue();
        store.setExtraParams({'searchText': text, 'searchDays': days});
        store.reload();
    },

    onClickCancel: function (field) {
        field.setValue('');
        var vm = this.getViewModel(),
            store = vm.getStore('articlegridstore'),
            daysField = this.getView().down('[filedId="searchDays"]');
        daysField.setValue(7);
        store.setExtraParams({'searchText': '', 'searchDays': 7});
        store.reload();
    }

});
