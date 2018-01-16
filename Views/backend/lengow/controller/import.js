//{namespace name="backend/lengow/controller"}
//{block name="backend/lengow/controller/import"}
Ext.define('Shopware.apps.Lengow.controller.Import', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'importPanel', selector: 'lengow-import-panel' }
    ],

    // Translations
    snippets: {
        order_error: '{s name="order/panel/order_error" namespace="backend/Lengow/translation"}{/s}',
        last_import: '{s name="order/panel/last_import" namespace="backend/Lengow/translation"}{/s}',
        to_be_sent: '{s name="order/panel/to_be_sent" namespace="backend/Lengow/translation"}{/s}',
        close: '{s name="order/panel/close" namespace="backend/Lengow/translation"}{/s}',
        synchronisation_report: '{s name="order/panel/synchronisation_report" namespace="backend/Lengow/translation"}{/s}',
        order_not_imported: '{s name="order/panel/order_not_imported" namespace="backend/Lengow/translation"}{/s}',
        order_imported: '{s name="order/panel/order_imported" namespace="backend/Lengow/translation"}{/s}',
        order_shipped_by_marketplace: '{s name="order/panel/order_shipped_by_marketplace" namespace="backend/Lengow/translation"}{/s}',
        action_confirmation_title: '{s name="order/panel/action_confirmation_title" namespace="backend/Lengow/translation"}{/s}',
        action_confirmation_message: '{s name="order/panel/action_confirmation_message" namespace="backend/Lengow/translation"}{/s}',
        action_success_message: '{s name="order/panel/action_success_message" namespace="backend/Lengow/translation"}{/s}',
        action_fail_message: '{s name="order/panel/action_fail_message" namespace="backend/Lengow/translation"}{/s}',
        synchronize_confirmation_title: '{s name="order/panel/synchronize_confirmation_title" namespace="backend/Lengow/translation"}{/s}',
        synchronize_confirmation_message: '{s name="order/panel/synchronize_confirmation_message" namespace="backend/Lengow/translation"}{/s}',
        synchronize_success_message: '{s name="order/panel/synchronize_success_message" namespace="backend/Lengow/translation"}{/s}',
        synchronize_fail_message: '{s name="order/panel/synchronize_fail_message" namespace="backend/Lengow/translation"}{/s}',
        reimport_confirmation_title: '{s name="order/panel/reimport_confirmation_title" namespace="backend/Lengow/translation"}{/s}',
        reimport_confirmation_message: '{s name="order/panel/reimport_confirmation_message" namespace="backend/Lengow/translation"}{/s}',
        reimport_success_message: '{s name="order/panel/reimport_success_message" namespace="backend/Lengow/translation"}{/s}',
        reimport_fail_message: '{s name="order/panel/reimport_fail_message" namespace="backend/Lengow/translation"}{/s}',
        ok: '{s name="order/panel/ok" namespace="backend/Lengow/translation"}{/s}',
        success_message: '{s name="order/details/success_message" namespace="backend/Lengow/translation"}{/s}',
        fail_message: '{s name="order/details/fail_message" namespace="backend/Lengow/translation"}{/s}',
        ship_confirmation_title: '{s name="order/details/ship_confirmation_title" namespace="backend/Lengow/translation"}{/s}',
        cancel_confirmation_title: '{s name="order/details/cancel_confirmation_title" namespace="backend/Lengow/translation"}{/s}'
    },

    init: function () {
        var me = this;

        me.control({
            'order-listing-grid': {
                selectOrder: me.onSelectOrder,
                showDetail: me.onShowDetail,
                reSendActionGrid: me.reSendActionGrid
            },
            'lengow-import-container': {
                launchImportProcess: me.onLaunchImportProcess,
                initImportPanels: me.onInitImportPanels
            },
            'lengow-import-panel' : {
                reSendAction: me.reSendAction,
                synchronize: me.synchronize,
                cancelAndReImport: me.cancelAndReImport
            }
        });

        me.callParent(arguments);
    },

    onSelectOrder: function(record) {
        var me = this,
            importPanel = me.getImportPanel(),
            preprodMode = Ext.get('lgw-preprod'),
            message;
        // get record and load information
        if (!(record instanceof Ext.data.Model)) {
            return;
        }
        importPanel.loadRecord(record);
        // update order summary information
        if (record.get('orderProcessState') === 0) {
            message = me.snippets.order_not_imported;
        } else {
            if (record.get('sentByMarketplace')) {
                message = me.snippets.order_shipped_by_marketplace;
            } else {
                message = me.snippets.order_imported;
            }
        }
        Ext.get('lgw-summary-text').update(message);
        // show or hide action buttons
        if(record.get('orderId') > 0 && preprodMode == null) {
            Ext.get('lgw-action-buttons').set({ orderId: record.get('orderId') });
            Ext.getCmp('lgw-action-buttons').show();
            if (record.get('orderProcessState') === 2) {
                Ext.getCmp('lgw-send-ship-action').hide();
                Ext.getCmp('lgw-send-cancel-action').hide();
            }
        } else {
            Ext.get('lgw-action-buttons').set({ orderId: 0 });
            Ext.getCmp('lgw-action-buttons').hide();
        }
        // open container automatically
        if (importPanel.collapsed) {
            importPanel.toggleCollapse(true);
        }
    },

    /**
     * Start import listener
     */
    reSendActionGrid: function (id, type, lastActionType) {
        var me = this;
        if (type == 'send') {
            url = '{url controller=LengowImport action=reSendAction}';
        } else {
            url = '{url controller=LengowImport action=reImport}';
        }
        var loading = new Ext.LoadMask(Ext.getBody(), {
            hideModal: true
        });
        loading.show();
        Ext.Ajax.request({
            url: url,
            method: 'POST',
            type: 'json',
            params: {
                orderId: id,
                actionName: lastActionType
            },
            success: function (response) {
                var grid = Ext.getCmp('importGrid');
                loading.hide();
                grid.getStore().load();
                grid.getView().refresh();
                me.onInitImportPanels();
            }
        });
    },

    /**
     * Init/update import window labels (description and last synchronization date)
     */
    onInitImportPanels: function () {
        var me = this;
        Ext.Ajax.request({
            url: '{url controller="LengowImport" action="getPanelContents"}',
            method: 'POST',
            type: 'json',
            success: function(response) {
                var data = Ext.decode(response.responseText)['data'];
                Ext.getCmp('nb_order_in_error').update(
                    '<p>' + Ext.String.format(me.snippets.order_error, data['nb_order_in_error']) + '</p>'
                );
                Ext.getCmp('nb_order_to_be_sent').update(
                    '<p>' + Ext.String.format(me.snippets.to_be_sent,  data['nb_order_to_be_sent']) + '</p>'
                );
                Ext.getCmp('last_import').update(
                    '<p>' + Ext.String.format(me.snippets.last_import, data['last_import']) + '</p>'
                );
                Ext.getCmp('mail_report').update(
                    '<p>' + data['mail_report'] + '</p>'
                );
            }
        });
    },

    /**
     * Start import listener
     */
    onLaunchImportProcess: function () {
        var me = this;
        // Display waiting message
       Ext.MessageBox.show({
            msg: '{s name="order/screen/import_charge_second" namespace="backend/Lengow/translation"}{/s}',
            width:300,
            wait:true
        });

        Ext.Ajax.request({
            url: '{url controller="LengowImport" action="launchImportProcess"}',
            method: 'POST',
            type: 'json',
            success: function(response) {
                var result = Ext.decode(response.responseText),
                    success = result['success'],
                    data = result['data'],
                    grid = Ext.getCmp('importGrid');
                // Update last synchronization date
                me.onInitImportPanels();
                // Hide waiting message
                Ext.MessageBox.hide();
                grid.getStore().load();
                grid.getView().refresh();
                Ext.MessageBox.show({
                    title: me.snippets.synchronisation_report,
                    msg: data.messages,
                    width: 600,
                    buttons: Ext.Msg.YES,
                    buttonText :
                    {
                        yes : me.snippets.ok
                    }
                });
            }
        });
    },

    /**
     * Event listener method which fired when the user clicks the pencil button
     * in the order list to show the order detail page.
     * @param record
     */
    onShowDetail: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId: record.get('orderId')
            }
        });
    },

    /**
     * Resend action (ship or cancel) from panel
     * @param type
     */
    reSendAction: function (type) {
        var me = this,
            orderId = parseInt(Ext.get('lgw-action-buttons').getAttribute('orderId')),
            buttonId = 'lgw-send-' + type + '-action';

        Ext.MessageBox.confirm(
            Ext.String.format(me.snippets.action_confirmation_title, type),
            Ext.String.format(me.snippets.action_confirmation_message, type),
            function (response) {
                if (response !== 'yes') {
                    return;
                }
                var loading = new Ext.LoadMask(Ext.getBody(), {
                    hideModal: true
                });
                loading.show();
                Ext.getCmp(buttonId).disable();
                Ext.Ajax.request({
                    url: '{url controller="LengowImport" action="reSendAction"}',
                    method: 'POST',
                    type: 'json',
                    params: {
                        orderId: orderId,
                        actionName: type
                    },
                    success: function (response) {
                        var success = Ext.decode(response.responseText)['data'],
                            grid = Ext.getCmp('importGrid'),
                            lengowMessage;
                        if (success) {
                            lengowMessage = me.snippets.action_success_message;
                        } else {
                            lengowMessage = me.snippets.action_fail_message;
                        }
                        Ext.MessageBox.alert(
                            Ext.String.format(me.snippets.action_confirmation_title, type),
                            lengowMessage
                        );
                        grid.getStore().load();
                        grid.getView().refresh();
                        Ext.getCmp(buttonId).enable();
                        loading.hide();
                    }
                });
            }
        );
    },

    /**
     * Synchronize order with Lengow
     */
    synchronize: function () {
        var me = this,
            orderId = parseInt(Ext.get('lgw-action-buttons').getAttribute('orderId'));

        Ext.MessageBox.confirm(
            me.snippets.synchronize_confirmation_title,
            me.snippets.synchronize_confirmation_message,
            function (response) {
                if (response !== 'yes') {
                    return;
                }
                var loading = new Ext.LoadMask(Ext.getBody(), {
                    hideModal: true
                });
                loading.show();
                Ext.getCmp('lgw-synchronize-order').disable();
                Ext.Ajax.request({
                    url: '{url controller="LengowImport" action="synchronize"}',
                    method: 'POST',
                    type: 'json',
                    params: {
                        orderId: orderId
                    },
                    success: function (response) {
                        var success = Ext.decode(response.responseText)['data'],
                            lengowMessage;
                        if (success) {
                            lengowMessage = me.snippets.synchronize_success_message;
                        } else {
                            lengowMessage = me.snippets.synchronize_fail_message;
                        }
                        Ext.MessageBox.alert(me.snippets.synchronize_confirmation_title, lengowMessage);
                        Ext.getCmp('lgw-synchronize-order').enable();
                        loading.hide();
                    }
                });
            }
        );
    },

    /**
     * Cancel and reimport order
     */
    cancelAndReImport: function () {
        var me = this,
            orderId = parseInt(Ext.get('lgw-action-buttons').getAttribute('orderId'));

        Ext.MessageBox.confirm(
            me.snippets.reimport_confirmation_title,
            me.snippets.reimport_confirmation_message,
            function (response) {
                if (response !== 'yes') {
                    return;
                }
                var loading = new Ext.LoadMask(Ext.getBody(), {
                    hideModal: true
                });
                loading.show();
                Ext.getCmp('lgw-reimport-order').disable();
                Ext.Ajax.request({
                    url:  '{url controller="LengowImport" action="cancelAndReImport"}',
                    method: 'POST',
                    type: 'json',
                    params: {
                        orderId: orderId
                    },
                    success: function (response) {
                        var success = Ext.decode(response.responseText)['data'],
                            grid = Ext.getCmp('importGrid'),
                            lengowMessage;
                        if (success) {
                            lengowMessage = Ext.String.format(
                                me.snippets.reimport_success_message,
                                success.marketplace_sku,
                                success.order_sku
                            );
                            Ext.get('lgw-action-buttons').set({ orderId: success.order_id });
                            grid.getStore().load();
                            grid.getView().refresh();
                        } else {
                            lengowMessage = me.snippets.reimport_fail_message;
                        }
                        Ext.MessageBox.alert(me.snippets.reimport_confirmation_title, lengowMessage);
                        Ext.getCmp('lgw-reimport-order').enable();
                        loading.hide();
                    }
                });
            }
        );
    }
});
// {/block}