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

    buildBtn: function (modules, parent) {
        parent = parent || '';
        var btns = [];
        for (var key in modules) {
            var m = modules[key];
            var theNode = (parent) ? parent + '.'+key : key;
            var btn = {
                text: m.text,
                tooltip: m.text,
                iconCls: m.logo,
                module: theNode,
                handler: Wiewind.isEmpty(m.fn) ? 'onOpenModule' : m.fn
            };
            if (!parent) {
                Ext.apply(btn, {
                    cls: 'app-header-btn',
                    margin: 2,
                    padding: 7,
                    width: (Wiewind.isEmpty(m.menu)) ? 80 : 100
                });
            }

            if (!Wiewind.isEmpty(m.menu)) {
                btn.menu = this.buildBtn(m.menu, theNode);

            }
            btns.push(btn);
        }
        return btns;
    },

    buildItems: function () {
        var btns = this.buildBtn(MainConfig.modules);

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