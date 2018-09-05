/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.skuarticles.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'exportskuarticlesgrid',

    requires: [
        'SRX.view.export.skuarticles.GridController',
        'SRX.view.export.skuarticles.GridViewModel'
    ],
    controller: 'exportskuarticlesgrid',
    viewModel: {
        type: 'exportskuarticlesgrid'
    },

    config: {
        // title: T.__('Articles'),
        // iconCls: 'x-fa fa-cube',
        scrollable: true,
        border: 1,
        forceFit: true,
        bind: {
            store: '{skuarticlesStore}'
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
            // text: false,
            dataIndex: 'number',
            sortable: true
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