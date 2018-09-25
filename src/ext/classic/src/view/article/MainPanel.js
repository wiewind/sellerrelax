/**
 * Created by benying.zou on 21.09.2018.
 */
Ext.define('SRX.view.article.MainPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'articlemainpanel',

    requires: [
        'SRX.view.article.Grid'
    ],

    config: {
        layout: 'fit'
    },

    items: [
        {
            xtype: 'articlegrid'
        }
    ]
});