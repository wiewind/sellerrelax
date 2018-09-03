/**
 * Created by benying.zou on 26.04.2018.
 */
Ext.define('SRX.overrides.button.Button', {
    override: 'Ext.button.Button',

    requires: [
        'SRX.plugin.badge.Badge'
    ],

    plugins: ['badge']
});