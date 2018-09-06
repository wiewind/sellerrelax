/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.model.Import', {
    extend: 'SRX.model.Base',

    fields: [
        {name: 'id', mapping: 'Import.id', type: 'int'},
        {name: 'type', mapping: 'Import.type'},
        {name: 'update_from', mapping: 'Import.update_from', type: 'date'},
        {name: 'update_to', mapping: 'Import.update_to', type: 'date'},
        {name: 'page', mapping: 'Import.page', type: 'int'},
        {name: 'menge', mapping: 'Import.menge', type: 'int'},
        {name: 'last_page_no', mapping: 'Import.last_page_no', type: 'int'},
        {name: 'total', mapping: 'Import.total', type: 'int'},
        {name: 'errors', mapping: 'Import.errors'},
        {name: 'import_beginn', mapping: 'Import.import_beginn', type: 'date'},
        {name: 'import_end', mapping: 'Import.import_end', type: 'date'},
        {name: 'url', mapping: 'Import.url'},
        {name: 'ip', mapping: 'Import.ip'}
    ]
});
