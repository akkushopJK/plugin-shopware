
Ext.define('Shopware.apps.Lengow', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.Lengow',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'main.Window',
        'export.CategoryTree',
        'export.Container',
        'export.Grid'
    ],

    models: [
        'Article',
        'Logs'
    ],
    stores: [
        'Article',
        'Logs'
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});