/**
 * Created by benying.zou on 21.09.2018.
 */

Ext.define('SRX.view.main.Config', {
    singleton: true,
    alternateClassName: ['MainConfig'],

    modules: {
        import: {text: T.__('Import'), logo: 'x-fa fa-ge'},
        export: {text: T.__('Export'), logo: 'x-fa fa-ge'},
        article: {text: T.__('Articles'), logo: 'x-fa fa-cube'},
        order: {text: T.__('Orders'), logo: 'x-fa fa-file-text-o', fn: 'noEvent'},
        settings: {text: T.__('Settings'), logo: 'x-fa fa-cog'}
    },

    defaultModule: 'import',
    moduleDelimiter: '_',

    openModule: function (module) {
        var m = MainConfig.modules[module],
            strModule = module.toLowerCase(),
            modules = strModule.split(MainConfig.moduleDelimiter),
            panel_xtype = '',
            panel_classname = '';
        for (var i=0; i<modules.length; i++) {
            panel_xtype += modules[i];
            if (panel_classname) panel_classname += '.';
            panel_classname += modules[i];
        }
        panel_xtype += 'mainpanel';
        panel_classname = 'SRX.view.' + panel_classname + '.MainPanel';

        var panel = Ext.ComponentQuery.query(panel_xtype),
            tabPanel = Ext.getCmp('mainTabpanel');
        if (panel.length === 0) {
                panel = Ext.create(panel_classname, {
                    title: m.text,
                    iconCls: m.logo,
                    closable: true
                });
            tabPanel.add(panel);
        } else {
            panel = panel[0];
        }
        tabPanel.setActiveTab(panel);
    }
});