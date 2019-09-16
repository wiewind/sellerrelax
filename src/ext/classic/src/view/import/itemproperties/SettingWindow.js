Ext.define ('SRX.view.import.itemproperties.SettingWindow', {
    extend: 'SRX.ux.MusterFormWindow',
    xtype: 'importitempropertiessettingwindow',

    requires: [
        'SRX.view.import.itemproperties.SettingWindowController',
        'SRX.view.import.itemproperties.SettingWindowViewModel'
    ],
    controller: 'importitempropertiessettingwindow',
    viewModel: {
        type: 'importitempropertiessettingwindow'
    },

    config: {
        title: T.__('Settings'),
        iconCls: 'x-fa fa-gear',
        width: 500,
        maxHeight: 700
    },

    input: {
        url: Cake.api.path + '/ImportVariationProperties/json/importItemPropertiesCsv'
    },

    configForm: function () {
        return {};
    },

    buildFormItems: function () {
        var vm = this.getViewModel(),
            variationCount = vm.get('variationCount'),
            propertyCount = vm.get('propertyCount'),
            properties = vm.get('properties');
            fields = [
                {
                    xtype: 'component',
                    html: '<h2>' + Wiewind.String.sprintf(T.__('It would import %d Properties of %d Variations!'), propertyCount, variationCount) + '</h2><hr />'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'filename',
                    value: vm.get('file')
                },
                {
                    xtype: 'fieldset',
                    //title: T.__('Fieldset 1'),
                    //layout: 'fit',
                    items: [
                        {
                            xtype: 'combobox',
                            name: 'operation_all',
                            fieldLabel: T.__('change all of properties'),
                            labelWidth: 280,
                            width: '100%',
                            queryMode: 'local',
                            displayField: 'display',
                            valueField: 'operation',
                            store: [
                                { operation: 0, display: T.__('without null value') },
                                { operation: 1, display: T.__('whith null value')},
                                { operation: 2, display: T.__('delete null value')}
                            ],
                            allowBlank: false,
                            value: 0,
                            listeners: {
                                change: 'onChange'
                            }
                        }
                    ]
                }
            ];

        for (var propertyId in properties) {
            var name = properties[propertyId]['name'];
            fields.push({
                xtype: 'combobox',
                name: 'operation[' + propertyId + ']',
                fieldLabel: name,
                labelWidth: 280,
                width: '100%',
                queryMode: 'local',
                displayField: 'display',
                valueField: 'operation',
                store: [
                    { operation: 0, display: T.__('without null value') },
                    { operation: 1, display: T.__('whith null value')},
                    { operation: 2, display: T.__('delete null value')}
                ],
                allowBlank: false,
                value: 0
            });
        }

        return fields;
    }
});