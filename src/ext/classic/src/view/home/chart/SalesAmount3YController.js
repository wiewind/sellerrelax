/**
 * Created by benying.zou on 26.09.2018.
 */
Ext.define('SRX.view.home.chart.SalesAmount3YController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.homechartsalesamount3y',

    afterRender: function () {
        this.refreshSeries();

        // set Textfield color
        var view = this.getView(),
            tbar = view.getDockedItems()[1];
        tbar.getComponent('year1').setFieldStyle ({color: view.colors[0]});
        tbar.getComponent('year2').setFieldStyle ({color: view.colors[1]});
        tbar.getComponent('year3').setFieldStyle ({color: view.colors[2]});
    },

    refreshSeries: function () {
        var view = this.getView(),
            vm = this.getViewModel(),
            years = [vm.get('year1'), vm.get('year2'), vm.get('year3')];
        Ext.Array.forEach(years, function (year, index) {
            if (year > 0) {
                view.addSeries({
                    type: 'line',
                    xField: 'date',
                    yField: 'sum' + (index+1),
                    style: {
                        lineWidth: 4
                    },
                    marker: {
                        radius: 4
                    },
                    highlight: {
                        fillStyle: '#000',
                        radius: 5,
                        lineWidth: 2,
                        strokeStyle: '#fff'
                    },
                    tooltip: {
                        trackMouse: true,
                        showDelay: 0,
                        dismissDelay: 0,
                        hideDelay: 0,
                        renderer: 'onSeriesTooltipRender'
                    }
                });
            }
        });
    },

    onAxisLabelRender: function (axis, label, layoutContext) {
        // Custom renderer overrides the native axis label renderer.
        // Since we don't want to do anything fancy with the value
        // ourselves except appending a '%' sign, but at the same time
        // don't want to loose the formatting done by the native renderer,
        // we let the native renderer process the value first.
        return layoutContext.renderer(label);
    },

    onSeriesTooltipRender: function (tooltip, record, item) {
        var vm = this.getViewModel(),
            str = '',
            sum = record.get(item.field),
            year = vm.get('year' + item.field.charAt(3)),
            mon = record.get('mon_num') + 1;

        if (year > 0) {
            if (mon < 10) mon = '0' + mon;
            var date = Wiewind.String.sprintf('%s-%s', year, mon);
            str = date + ': ' + Wiewind.Number.format(sum, 2, SSD.data.formatting.decimal_separator, SSD.data.formatting.thousands_separator) + ' €';
        } else {
            str = '0';
        }

        tooltip.setHtml(str);
    },

    onChangePeriod: function (combo, newValue) {
        this.getViewModel().getStore('salesamount3y').reload();
    },

    onChangeYear: function () {
        var view = this.getView(),
            vm = this.getViewModel(),
            tbar = view.getDockedItems()[1],
            yearField1 = tbar.getComponent('year1'),
            yearField2 = tbar.getComponent('year2'),
            yearField3 = tbar.getComponent('year3');

        if (yearField1.validate() && yearField2.validate() && yearField3.validate()) {
            this.getViewModel().getStore('salesamount3y').reload();
        }
    }
});