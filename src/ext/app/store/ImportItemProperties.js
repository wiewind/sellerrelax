/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.store.ImportItemProperties', {
    extend: 'SRX.store.Base',

    alias: 'store.importitemproperties',

    //model: 'SRX.model.Import',
    fields: [
        {name: 'id', mapping: 'ImportItemProperty.id', type: 'int'},
        {name: 'itemId', mapping: 'ImportItemProperty.item_id', type: 'int'},
        {name: 'variationId', mapping: 'ImportItemProperty.variation_id', type: 'int'},
        {name: 'propertyId', mapping: 'ImportItemProperty.property_id', type: 'int'},
        {name: 'lang', mapping: 'ImportItemProperty.lang'},
        {name: 'value', mapping: 'ImportItemProperty.value'},
        {name: 'status', mapping: 'ImportItemProperty.status', type: 'int'},
        {name: 'info', mapping: 'ImportItemProperty.info'},
        {name: 'created', mapping: 'ImportItemProperty.created', type: 'date'},
        {name: 'imported', mapping: 'ImportItemProperty.imported', type: 'date'},
        {name: 'modified', mapping: 'ImportItemProperty.modified', type: 'date'}
    ],

    proxy: {
        url: Cake.api.path + '/import/json/getImportItemPropertiesList'
    }
});
