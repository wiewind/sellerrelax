/**
 * Created by benying.zou on 04.09.2018.
 */

Ext.define('SRX.view.settings.MainPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'settingsmainpanel',

    requires: [
        'SRX.view.settings.MainPanelController'
    ],

    controller: 'settingsmainpanel',

    config: {
        title: T.__('Settings'),
        iconCls: 'x-fa fa-cog',
        border: 1,
        layout: 'vbox',
        bodyPadding: 10
    },

    items: [
        {
            xtype: 'component',
            margin: 10,
            html: T.__('Current User') + ': ' + '<h3>' + SSD.data.user.username + '</h3>'
        },
        {
            xtype: 'container',
            layout: 'hbox',
            width: '100%',
            defaults: {
                margin: 10
            },
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
                    xtype: 'button',
                    text: T.__('Rest Test'),
                    tooltip: T.__('Rest Test'),
                    iconCls: 'x-fa fa-exchange',
                    // padding: 20,
                    handler: 'onClickRest'
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
                    handler: 'onClickLogout'
                }
            ]
        }
    ]


});