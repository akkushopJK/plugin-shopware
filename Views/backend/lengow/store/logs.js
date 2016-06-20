/**
 * Created by nicolasmaugendre on 17/06/16.
 */

Ext.define('Shopware.apps.Lengow.store.Logs', {
    extend:'Shopware.store.Listing',
    alias:  'store.article-logs',
    model: 'Shopware.apps.Lengow.model.Logs',

    configure: function() {
        return { controller: 'Lengow' };
    },

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="Lengow" action="getLogsFiles"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    },
});