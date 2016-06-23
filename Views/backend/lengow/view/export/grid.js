

Ext.define('Shopware.apps.Lengow.view.export.Grid', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.product-listing-grid',

    configure: function() {
        var me = this;

        return {
            addButton: false,
            deleteButton: false
        };
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);
    }
});
