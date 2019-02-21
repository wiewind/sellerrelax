/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.model.OutstockWarehouse', {
    extend: 'SRX.model.Base',

    fields: [
        {name: 'id', mapping: 'Warehouse.id', type: 'int'},
        {name: 'name', mapping: 'Warehouse.name'},
        {name: 'protokoll', mapping: 'Warehouse.protokoll'},
        {name: 'fdate', mapping: 'Warehouse.fdate', type: 'date'}
    ]
});
