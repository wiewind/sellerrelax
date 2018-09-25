/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


Ext.define ('SRX.view.main.HeaderPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'mainheader',

    requires: [
        'SRX.view.main.HeaderPanelController'
    ],

    controller: 'mainheader',

    config: {
        layout: 'hbox',
        bodyStyle: 'background:#35384c;'
    },

    initComponent: function () {
        this.items = this.buildItems();
        this.callParent();
    },

    buildItems: function () {
        var btns = [];
        for (var key in MainConfig.modules) {
            var m = MainConfig.modules[key];
            btns.push({
                text: m.text,
                tooltip: m.text,
                iconCls: m.logo,
                cls: 'app-header-btn',
                margin: 2,
                padding: 7,
                width: 80,
                module: key,
                handler: Wiewind.isEmpty(m.fn) ? 'onOpenModule' : m.fn
            });
        }

        return [
            {
                xtype: 'container',
                flex: 1,
                items: [
                    {
                        xtype: 'image',
                        margin: '20px 0 0 10px',
                        alt: 'logo',
                        src: Cake.image.path + '/logo/logo.png'
                    }
                ]
            },
            {
                xtype: 'container',
                layout: 'hbox',
                padding: '0 10px',
                defaults: {
                    xtype: 'button',
                    scale: 'large',
                    split: false,
                    iconAlign: 'top',
                    hideBorders:'false'
                },
                items: btns
            }
        ];
    }
});