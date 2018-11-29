/**
 * Created by benying.zou on 31.08.2018.
 */
Ext.define('SRX.view.import.tracking.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'importtrackinggrid',

    requires: [
        'SRX.view.import.tracking.GridController',
        'SRX.view.import.tracking.GridViewModel'
    ],
    controller: 'importtrackinggrid',
    viewModel: {
        type: 'importtrackinggrid'
    },

    config: {
        forceFit: true,
        scrollable: true,
        bind: {
            store: '{trackingstore}'
        }
    },
    emptyText: T.__("This list is empty."),

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true,
        displayMsg: '{0} - {1} of {2}',
        emptyMsg: T.__("This list is empty.")
    },

    tbar: [
        T.__('Type') + ':',
        {
            xtype: 'combo',
            name: 'type',
            store: Ext.create('Ext.data.Store', {
                fields: ['type', 'display'],
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: Cake.api.path + '/import/json/listTypes',
                    // actionMethods: {
                    //     read: 'POST'
                    // },
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                }
            }),
            width: 100,
            queryMode: 'local',
            displayField: 'display',
            valueField: 'type',
            bind: {
                value: '{type}'
            },
            editable: false,
            forceSelection: true,
            listeners: {
                change: 'onChangeFilter'
            }
        },
        T.__('Date') + ': ',
        {
            xtype: 'datefield',
            name: 'from',
            format: SSD.data.formatting.date_format,
            submitFormat: 'Y-m-d',
            bind: {
                value: '{from}'
            },
            listeners: {
                change: 'onChangeFilter'
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
            },
            listeners: {
                change: 'onChangeFilter'
            }
        },
        {
            xtype: 'checkbox',
            name: 'hasMenge',
            padding: '0 10',
            boxLabel: T.__('realy import'),
            bind: {
                value: '{hasMenge}'
            },
            listeners: {
                change: 'onChangeFilter'
            }
        }
    ],

    columns: [
        {
            text: T.__("ID"),
            dataIndex: 'id',
            width: 80,
            hidden: true
        },
        {
            text: T.__("Beginn"),
            dataIndex: 'import_beginn',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        },
        {
            text: T.__("End"),
            dataIndex: 'import_end',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        },
        {
            text: T.__("Type"),
            dataIndex: 'type',
            width: 100
        },
        {
            text: T.__("Plenty Updated"),
            dataIndex: 'update_from',
            width: 400,
            hidden: true,
            renderer: function (date, meta, rec) {
                var date_to = rec.get('update_to');
                if (!date) {
                    return Wiewind.String.sprintf(T.__('before %s'), Glb.Date.displayDateFromString(date_to));
                }
                if (!date_to) {
                    return Wiewind.String.sprintf(T.__('from %s'), Glb.Date.displayDateFromString(date));
                }

                return Glb.Date.displayDateFromString(date, ' H:i:s') + ' - ' + Glb.Date.displayDateFromStringtoFormat(date_to, ' H:i:s');
            }
        },
        {
            text: T.__("Page"),
            dataIndex: 'page',
            width: 80,
            align: 'end'
        },
        {
            text: T.__("Last Page"),
            dataIndex: 'last_page_no',
            width: 80,
            align: 'end',
            hidden: true
        },
        {
            text: T.__("Total"),
            dataIndex: 'total',
            width: 80,
            align: 'end'
        },
        {
            text: T.__("Quantity"),
            dataIndex: 'menge',
            width: 80,
            align: 'end'
        },
        {
            text: T.__("Errors"),
            dataIndex: 'errors',
            width: 600,
            renderer: function (date, meta, rec) {
                meta.tdAttr = 'data-qtip="' + date.replace(/"/g, '&quot;') + '"';
                return date;
            }
        }
    ]
});