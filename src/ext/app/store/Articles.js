Ext.define('SRX.store.Articles', {
    extend: 'SRX.store.Base',

    alias: 'store.articles',

    model: 'SRX.model.Article',

    proxy: {
        url: Cake.api.path + '/Items/json/listItems'
    }
});
