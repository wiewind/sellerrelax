/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.EditWindow', {
    extend: 'SRX.ux.MusterFormWindow',
    xtype: 'exporteditwindow',

    requires: [
        'SRX.view.export.EditWindowController',
        'SRX.view.export.EditWindowViewModel'
    ],
    controller: 'exporteditwindow',
    viewModel: {
        type: 'exporteditwindow'
    },

    setting: {
        url: Cake.api.path + '/exportsettings/json/save'
    },

    configForm: function () {
        return {
            bind: {
                title: '{getTitle}'
            },
            iconCls: Glb.btnSetting.editIconCls,
            width: 500
        };
    },

    buildFormItems: function () {
        return [
            {
                xtype: 'hiddenfield',
                name: 'id',
                bind: {
                    value: '{id}'
                }
            },
            {
                xtype: 'hiddenfield',
                name: 'type',
                bind: {
                    value: '{type}'
                }
            },
            {
                xtype: 'textfield',
                fieldLabel: T.__('Old Value'),
                disabled: true,
                bind: {
                    hidden: '{hiddenOldValue}',
                    value: '{oldValue}'
                }
            },
            {
                xtype: 'textfield',
                fieldLabel: T.__('Value'),
                name: 'value',
                bind: {
                    value: '{value}'
                },
                listeners: {
                    specialkey: 'submitOnEnter'
                }
            }
        ];
    }
});