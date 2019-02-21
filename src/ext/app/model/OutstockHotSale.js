/**
 * Created by benying.zou on 06.09.2018.
 */
Ext.define('SRX.model.OutstockHotSale', {
    extend: 'SRX.model.Base',

    fields: [
        {name: 'stock_id', mapping: 'HotSales.stock_id', type: 'int'},
        {name: 'number', mapping: 'HotSales.number'},
        {name: 'ean', mapping: 'HotSales.ean'},
        {name: 'warehouse_id', mapping: 'HotSales.warehouse_id', type: 'int'},
        {name: 'warehouse_name', mapping: 'HotSales.warehouse_name'},
        {name: 'item_id', mapping: 'HotSales.item_id', type: 'int'},
        {name: 'variation_id', mapping: 'HotSales.variation_id', type: 'int'},
        {name: 'sales', mapping: 'HotSales.sales', type: 'number'},
        {name: 'purchase', mapping: 'HotSales.purchase', type: 'number'}
    ]
});
