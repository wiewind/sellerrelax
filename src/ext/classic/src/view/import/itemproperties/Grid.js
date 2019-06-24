/**
 * Created by benyingz on 06.06.2019.
 */
Ext.define('SRX.view.import.itemproperties.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'importitempropertiesgrid',

    requires: [
        'SRX.view.import.itemproperties.GridController',
        'SRX.view.import.itemproperties.GridViewModel'
    ],
    controller: 'importitempropertiesgrid',
    viewModel: {
        type: 'importitempropertiesgrid'
    },

    config: {
        forceFit: true,
        scrollable: true,
        bind: {
            store: '{importstore}'
        }
    },
    emptyText: T.__("This list is empty."),

    tbar: [
        T.__('Choose Status')+': ',
        {
            xtype: 'combobox',
            name: 'status',
            queryMode: 'local',
            displayField: 'name',
            valueField: 'abbr',
            store: [
                { abbr: 0, name: T.__('All') },
                { abbr: 1, name: T.__('New') },
                { abbr: 2, name: T.__('Success') },
                { abbr: 3, name: T.__('Failure') },
                { abbr: 4, name: T.__('Overridden') },
                { abbr: 5, name: T.__('Deleted') }
            ],
            width: 100,
            bind: {
                value: '{status}'
            }
        },
        T.__('Item')+': ',
        {
            xtype: 'textfield',
            name: 'itemId',
            width: 150,
            bind: {
                value: '{itemId}'
            }
        },
        T.__('Created') + ': ',
        {
            xtype: 'datefield',
            name: 'from',
            format: SSD.data.formatting.date_format,
            submitFormat: 'Y-m-d',
            bind: {
                value: '{from}'
            }
        },
        T.__(' - '),
        {
            xtype: 'datefield',
            name: 'to',
            format: SSD.data.formatting.date_format,
            submitFormat: 'Y-m-d',
            bind: {
                value: '{to}'
            }
        },
        {
            text: T.__('Search'),
            handler: 'onChangeFilter'
        },
        '->',
        {
            text: T.__('to Plenty'),
            handler: 'onClickToPlenty'
        },
        {
            text: T.__('CSV Upload'),
            handler: 'onClickUpload'
        }
    ],

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true,
        displayMsg: '{0} - {1} of {2}',
        emptyMsg: T.__("This list is empty.")
    },

    columns: [
        {
            text: T.__("Status"),
            dataIndex: 'status',
            width: 60,
            renderer: function (data) {
                var v = {
                    //1: T.__('New'),
                    1: '<div class="x-fa fa-plus-circle orange" title="'+T.__('New')+'"></div>',
                    2: '<div class="x-fa fa-check-circle green" title="'+T.__('Success')+'"></div>',
                    3: '<div class="x-fa fa-times-circle red" title="'+T.__('Failure')+'"></div>',
                    4: '<div class="x-fa fa-exclamation-circle lightblue" title="'+T.__('Overridden')+'"></div>',
                    5: '<div class="x-fa fa-minus-circle grey" title="'+T.__('Deleted')+'"></div>'
                };

                return v[data];
            }
        },
        {
            text: T.__("Created at"),
            dataIndex: 'created',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        },
        {
            text: T.__("Item"),
            dataIndex: 'itemId',
            width: 100
        },
        {
            text: T.__("Variation"),
            dataIndex: 'variationId',
            width: 100
        },
        {
            text: T.__('Property'),
            dataIndex: 'propertyId',
            width: 100
        },
        {
            text: T.__("Language"),
            dataIndex: 'lang',
            width: 100
        },
        {
            text: T.__("Value"),
            dataIndex: 'value',
            flex: 1,
            renderer: function (v, meta, rec) {
                var dummy = document.createElement('div');
                dummy.textContent = v;
                var text = dummy.innerHTML;
                meta.tdAttr = 'data-qtip="' + v + '"';
                return text;
            }
        },
        {
            text: T.__("Imported at"),
            dataIndex: 'imported',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        },
        {
            text: T.__("Modified at"),
            dataIndex: 'modified',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        }
    ]
});