Ext.define ('SRX.view.import.variationsuppliers.SettingWindow', {
    extend: 'SRX.ux.MusterFormWindow',
    xtype: 'importvariationsupplierssettingwindow',

    requires: [
        'SRX.view.import.variationsuppliers.SettingWindowController',
        'SRX.view.import.variationsuppliers.SettingWindowViewModel'
    ],
    controller: 'importvariationsupplierssettingwindow',
    viewModel: {
        type: 'importvariationsupplierssettingwindow'
    },

    config: {
        title: T.__('Settings'),
        iconCls: 'x-fa fa-gear',
        width: 500,
        maxHeight: 700
    },

    input: {
        url: Cake.api.path + '/ImportVariationSuppliers/json/importVariationSuppliersCsv'
    },

    configForm: function () {
        return {};
    },

    buildFormItems: function () {
        var vm = this.getViewModel(),
            rows = vm.get('rows'),
            fields = [
                {
                    xtype: 'component',
                    html: '<h2>' + Wiewind.String.sprintf(T.__('It would import %d Suppliers!'), rows) + '</h2><hr />'
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
                            name: 'delete_other',
                            fieldLabel:T.__('What do you want to do with another suppliers?'),
                            labelAlign: 'top',
                            width: '100%',
                            queryMode: 'local',
                            displayField: 'display',
                            valueField: 'operation',
                            store: [
                                { operation: 0, display: T.__('don\'t delete another suppliers') },
                                { operation: 1, display: T.__('delete another suppliers')}
                            ],
                            allowBlank: false,
                            value: 0
                        }
                    ]
                }
            ];

        return fields;
    }
});