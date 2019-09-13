/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.itemproperties.SettingWindowViewModel', {
    extend: 'SRX.ux.MusterFormWindowViewModel',

    alias: 'viewmodel.importitempropertiessettingwindow',


    data: {
        file: '',
        variationCount: 0,
        propertyCount: 0,
        properties: []
    }
});