/**
 * Created by benyingz on 02.07.2019.
 */
Ext.define('SRX.store.ImportVariationSuppliers', {
    extend: 'SRX.store.Base',

    alias: 'store.importvariationsuppliers',

    fields: [
        {name: 'id', mapping: 'ImportVariationSupplier.id', type: 'int'},
        {name: 'itemId', mapping: 'ImportVariationSupplier.item_id', type: 'int'},
        {name: 'variationId', mapping: 'ImportVariationSupplier.variation_id', type: 'int'},
        {name: 'supplierId', mapping: 'ImportVariationSupplier.supplier_id', type: 'int'},
        {name: 'itemNo', mapping: 'ImportVariationSupplier.item_no'},
        {name: 'supplierItemNo', mapping: 'ImportVariationSupplier.supplier_item_no'},
        {name: 'minPurchase', mapping: 'ImportVariationSupplier.min_purchase'},
        {name: 'purchasePrice', mapping: 'ImportVariationSupplier.purchase_price'},
        {name: 'deliveryTime', mapping: 'ImportVariationSupplier.delivery_time'},
        {name: 'packagingUnit', mapping: 'ImportVariationSupplier.packaging_unit'},
        {name: 'free20', mapping: 'ImportVariationSupplier.free20'},
        {name: 'lastPriceQuery', mapping: 'ImportVariationSupplier.last_price_query', type: 'date'},
        {name: 'status', mapping: 'ImportVariationSupplier.status', type: 'int'},
        {name: 'deleteOther', mapping: 'ImportVariationSupplier.delete_other', type: 'boolean'},
        {name: 'created', mapping: 'ImportVariationSupplier.created', type: 'date'},
        {name: 'imported', mapping: 'ImportVariationSupplier.imported', type: 'date'}
    ],

    proxy: {
        url: Cake.api.path + '/ImportVariationSuppliers/json/listAll'
    }
});