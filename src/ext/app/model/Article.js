Ext.define('SRX.model.Article', {
    extend: 'SRX.model.Base',

    fields: [
        {name: 'id', mapping: 'Item.id', type: 'int'},
        {name: 'extern_id', mapping: 'Item.extern_id', type: 'int'},
        {name: 'main_variation_id', mapping: 'Item.main_variation_id', type: 'int'},
        {name: 'name', mapping: 'Item.name'},
        {name: 'description', mapping: 'Item.description'},
        {name: 'currency', mapping: 'Item.currency', default: 'EUR'},
        {name: 'count_orders', mapping: 'Item.count_orders', type: 'int'},
        {name: 'sum_quantity', mapping: 'Item.sum_quantity', type: 'number'},
        {name: 'sum_price_net', mapping: 'Item.sum_price_net', type: 'number'}
    ]
});
