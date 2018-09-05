/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.fbacustomers.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'exportfbacustomersgrid',

    requires: [
        'SRX.view.export.fbacustomers.GridController',
        'SRX.view.export.fbacustomers.GridViewModel'
    ],
    controller: 'exportfbacustomersgrid',
    viewModel: {
        type: 'exportfbacustomersgrid'
    },

    config: {
        scrollable: true,
        border: 1,
        forceFit: true,
        bind: {
            store: '{fbacustomersStore}'
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
            dataIndex: 'number',
            flex: 1,
            sortable: true
        },
        {
            xtype:'actioncolumn',
            width:30,
            items: [
                {
                    iconCls: Glb.btnSetting.deleteIconCls2,
                    tooltip: Glb.btnSetting.deleteText,
                    handler: 'onDelete'
                }
            ]
        }
    ],

    tbar: [
        {
            text: Glb.btnSetting.addText,
            tooltip: Glb.btnSetting.addText,
            iconCls: Glb.btnSetting.addIconCls,
            handler: 'onClickAdd'
        }
    ],

    listeners: {
        itemdblclick: 'onItemdblclick'
    }
});