//{namespace name="backend/lengow/view/export"}
//{block name="backend/lengow/view/export/panel"}
Ext.define('Shopware.apps.Lengow.view.export.Panel', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.lengow-category-panel',

    layout: 'fit',
    bodyStyle: 'background:#fff;',

    // Translations
    snippets: {
        export: {
            label: {
                shop: '{s name="export/panel/label/shop" namespace="backend/Lengow/translation"}{/s}'
            },
            button: {
                shop: '{s name="export/panel/button/shop" namespace="backend/Lengow/translation"}{/s}'
            }
        }
    },

    initComponent: function () {
        var me = this;

        me.items = me.getPanels();

        me.addEvents(
            'filterByCategory'
        );

        me.callParent(arguments);
    },

    /**
     * Returns the tree panel with and a toolbar
     */
    getPanels: function () {
        var me = this;

        var activeStores = Ext.create('Ext.data.Store',{
            model: 'Shopware.apps.Lengow.model.Shops',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '{url controller="LengowExport" action="getActiveShop"}'
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        me.treePanel = Ext.create('Ext.panel.Panel', {
            margin : '2px',
            bodyStyle: 'background:#fff;',
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            items: [
                me.createTree()
            ]
        });

        return [me.treePanel];
    },

    /**
     * Creates the category tree
     *
     * @return [Ext.tree.Panel]
     */
    createTree: function () {
        var me = this,
                tree;

        tree = Ext.create('Shopware.apps.Lengow.view.export.Tree', {
            listeners: {
                load: function(view, record){
                    if(record.get('id') === 'root' && record.childNodes.length) {
                       var firstChild = record.childNodes[0]; 
                       tree.getSelectionModel().select(firstChild);
                       tree.fireEvent('itemclick', view, firstChild);
                    }
                    // if (record.get('id') === 'root') {
                    //     Ext.each(record.childNodes, function(child) {
                    //         if (child.raw.lengowStatus) {
                    //             child.set('cls', 'lengow-enabled');
                    //         } else {
                    //             child.set('cls', 'lengow-disabled');
                    //         }
                    //     });
                    // }
                },
                itemclick: {
                    fn: function (view, record) {
                        var me = this,
                            store =  me.store,
                            grid = Ext.getCmp('exportGrid');

                        if (record.get('id') === 'root') {
                            store.getProxy().extraParams.categoryId = null;
                            return false; // Do nothing if root is selected
                        } 

                        store.getProxy().extraParams.categoryId = record.get('id');

                        if (record.get('parentId') === 'root') {
                            grid.setNumberOfProductExported();
                            grid.setLengowShopStatus();
                        } else {
                            store.load();
                        }

                        //scroll the store to first page
                        store.currentPage = 1;
                    }
                },
                scope: me
            }
        });

        return tree;
    }

});
//{/block}