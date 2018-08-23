/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
Ext.application({
    extend: 'SRX.Application',

    name: 'SRX',

    requires: [
        // This will automatically load all classes in the SRX namespace
        // so that application classes do not need to require each other.
        'SRX.*'
    ],

    // The name of the initial view to create.
    mainView: 'SRX.view.main.Main'
});
