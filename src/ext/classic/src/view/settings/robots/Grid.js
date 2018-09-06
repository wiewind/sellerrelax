/**
 * Created by benying.zou on 31.08.2018.
 */
Ext.define('SRX.view.settings.robots.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'settingsrobotsgrid',

    requires: [
        'SRX.view.settings.robots.GridController',
        'SRX.view.settings.robots.GridViewModel'
    ],
    controller: 'settingsrobotsgrid',
    viewModel: {
        type: 'settingsrobotsgrid'
    },

    config: {
        title: T.__('Robots'),
        iconCls: 'x-fa fa-android',
        border: 0,
        forceFit: true,
        scrollable: true,
        bind: {
            store: '{robotsstore}'
        }
    },
    emptyText: T.__("This list is empty."),

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true,
        displayMsg: '{0} - {1} of {2}',
        emptyMsg: T.__("This list is empty.")
    },

    columns: [
        {
            text: T.__("Time"),
            dataIndex: 'import_beginn',
            width: 200,
            renderer: function (date) {
                return Glb.Date.displayDateFromString(date, ' H:i:s');
            }
        },
        {
            text: T.__("IP"),
            dataIndex: 'ip',
            width: 150
        },
        {
            text: T.__("URL"),
            dataIndex: 'url',
            flex: 1
        }
    ]
});