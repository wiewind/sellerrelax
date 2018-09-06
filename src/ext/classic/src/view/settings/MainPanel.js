/**
 * Created by benying.zou on 04.09.2018.
 */

Ext.define('SRX.view.settings.MainPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'settingstmainpanel',

    requires: [
        'SRX.view.settings.MainPanelController',
        'SRX.view.settings.robots.Grid'
    ],

    controller: 'settingstmainpanel',

    config: {
        title: T.__('Settings'),
        iconCls: 'x-fa fa-cog',
        border: 1,
        layout: 'vbox',
        bodyPadding: 10
    },

    defaults: {
        margin: '10px auto',
        width: 200
    },

    items: [
        {
            xtype: 'component',
            html: T.__('Current User') + ': ' + '<h3>' + SSD.data.user.username + '</h3>'
        },
        {
            xtype: 'container',
            layout: 'hbox',
            width: '100%',
            items: [
                {
                    xtype: 'button',
                    text: T.__('Sytem Update'),
                    tooltip: T.__('Sytem Update'),
                    iconCls: 'x-fa fa-retweet',
                    // padding: 20,
                    handler: 'onClickUpdate'
                },
                {
                    xtype: 'component',
                    flex: 1
                },
                {
                    xtype: 'button',
                    text: Glb.btnSetting.logoutText,
                    tooltip: Glb.btnSetting.logoutText,
                    iconCls: Glb.btnSetting.logoutIconCls,
                    // padding: 20,
                    handler: 'onClickLogout'
                }
            ]
        },
        {
            xtype: 'settingsrobotsgrid',
            width: '100%',
            height: 500
        }
    ]


});