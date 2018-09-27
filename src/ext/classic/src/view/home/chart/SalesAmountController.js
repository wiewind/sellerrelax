/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmountController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.homechartsalesamount',

    onAxisLabelRender: function (axis, label, layoutContext) {
        // Custom renderer overrides the native axis label renderer.
        // Since we don't want to do anything fancy with the value
        // ourselves except appending a '%' sign, but at the same time
        // don't want to loose the formatting done by the native renderer,
        // we let the native renderer process the value first.
        return layoutContext.renderer(label);
    },

    onSeriesTooltipRender: function (tooltip, record, item) {
        tooltip.setHtml(record.get('date') + ': ' + Wiewind.Number.format(record.get('sum'), 2, SSD.data.formatting.decimal_separator, SSD.data.formatting.thousands_separator) + ' €');
    },

    onChangePeriod: function (combo, newValue) {
        this.getViewModel().getStore('salesamount').reload();
    }
});