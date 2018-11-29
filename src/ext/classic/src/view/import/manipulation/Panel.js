/**
 * Created by benying.zou on 21.09.2018.
 */
Ext.define('SRX.view.import.manipulation.Panel', {
    extend: 'Ext.panel.Panel',
    xtype: 'importmanipanel',

    requires: [
        'SRX.view.import.manipulation.PanelController'
    ],
    controller: 'importmanipanel',

    config: {
        layout: 'vbox',
        bodyPadding: 10,
        defaults: {
            margin: 10,
            collapsible: true,
            collapseToolText: T.__('Collapse'),
            expandToolText: T.__('Expand'),
            defaults: {
                margin: 10
            }
        }
    },

    items: [
        {
            xtype: 'musterform',
            title: T.__('Exact Import'),
            width: '100%',
            input: {
                url: Cake.api.path + '/rest/import/json/importById'
            },
            border: 1,
            layout: 'hbox',
            items: [
                {
                    xtype: 'textfield',
                    name: 'id',
                    emptyText: T.__('Enter ID...'),
                    width: '400',
                    listeners: {
                        specialkey: 'submitOnEnter'
                    }
                },
                {
                    xtype: 'radiogroup',
                    columns: 2,
                    vertical: true,
                    defaults: {
                        margin: '0 10px 0 0'
                    },
                    items: [
                        { boxLabel: 'Order', name: 'type', inputValue: 'order', checked: true },
                        { boxLabel: 'Item', name: 'type', inputValue: 'item' }
                    ]
                },
                {
                    xtype: 'button',
                    text: Glb.btnSetting.submitText,
                    handler: 'onSubmit'
                }
            ]
        },
        {
            xtype: 'panel',
            title: T.__('Reset Orders'),
            width: '100%',
            border: 1,
            bodyPadding: 10,
            layout: 'hbox',
            collapsed: true,
            items: [
                {
                    xtype: 'button',
                    text: Glb.btnSetting.resetText,
                    handler: 'onReset'
                }
            ]
        },
        {
            xtype: 'panel',
            title: T.__('Import Items'),
            width: '100%',
            border: 1,
            bodyPadding: 10,
            layout: 'hbox',
            collapsed: true,
            items: [
                {
                    xtype: 'button',
                    text: T.__('Import Items'),
                    handler: 'onImportItems'
                },
                {
                    xtype: 'button',
                    text: T.__('Import Variations'),
                    handler: 'onImportVariations'
                }
            ]
        },
        {
            xtype: 'panel',
            title: T.__('Stock managment'),
            width: '100%',
            border: 1,
            layout: 'vbox',
            // collapsed: true,
            bodyPadding: 10,
            items: [
                {
                    xtype: 'button',
                    text: T.__('Import together'),
                    handler: 'onImportAllWarehouses'
                },
                {
                    xtype: 'container',
                    layout: 'vbox',
                    hidden: true,
                    items: [
                        {
                            html: '<B>' + T.__('Import separately') + ': </B>'
                        },
                        {
                            xtype: 'container',
                            layout: 'hbox',
                            defaults: {
                                margin: '0 20 0 0'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    text: T.__('Warehouses'),
                                    handler: 'onImportWarehouses'
                                },
                                {
                                    xtype: 'button',
                                    text: T.__('Dimensions'),
                                    handler: 'onImportDimensions'
                                },
                                {
                                    xtype: 'button',
                                    text: T.__('Levels'),
                                    handler: 'onImportlevels'
                                },
                                {
                                    xtype: 'button',
                                    text: T.__('Locations'),
                                    handler: 'onImportLocations'
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
});