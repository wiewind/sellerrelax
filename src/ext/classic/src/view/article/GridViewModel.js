/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.article.GridViewModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.articlegrid',

    data: {},

    stores: {
        articlegridstore: {
            type: 'articles',
            autoLoad: true,
            remoteSort: true,
            sortOnLoad: true
        }
    }
});