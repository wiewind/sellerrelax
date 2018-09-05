/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.skuarticles.UploadPanel', {
    extend: 'Ext.panel.Panel',
    xtype: 'exportskuarticlesuploadpanel',

    requires: [
        'SRX.view.export.skuarticles.UploadPanelController'
    ],
    controller: 'exportskuarticlesuploadpanel',
    config: {
        layout: 'vbox',
        bodyPadding: 20
    },

    defaults: {
        width: '100%'
    },

    items: [
        {
            xtype: 'component',
            region: 'north',
            html: '<h1>'+T.__('Import data from an external file')+':</h1>'
        },
        {
            xtype: 'checkbox',
            boxLabel: T.__("remove old records"),
            name: 'removeOldData',
            inputValue: 1,
            uncheckedValue: 0
        },
        {
            xtype: 'component',
            flex: 1,
            html: '<div id="drop_area">' + T.__('Drag file to this area...') + '</div>'
        }
    ]
});