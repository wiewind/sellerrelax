/**
 * Created by benying.zou on 25.09.2018.
 */
Ext.define('SRX.view.home.MainPanel', {
    extend: 'Ext.container.Container',
    xtype: 'homemainpanel',

    config: {
        title: T.__('Home'),
        iconCls: 'x-fa fa-home',

        layout: 'fit'
    },

    items: [
        {
            bodyPadding: 20,
            html: '<h1>hard working...</h1>'
        }
    ]
});