/**
 * Link group action buttons, for example in a table or on a specific mailing list's page.
 * @param {module:jQuery} el jQuery wrapped element to target.
 * @param {{delete_redirect: string}} options Options used to modify behaviour of button actions.
 */
function bindMQButtons(el, options) {
    if (!options) options = {};

    /**
     * Link row buttons after table is loaded.
     */

    /**
     * Buttons that launch a modal dialog
     */
    // Delete mailing list button
    el.find('.js-mq-delete').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/mailing_queue/confirm-delete",
            ajaxParams: {
                ml_id: $(this).data('id')
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function() {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
                .on("submitSuccess.ufForm", function() {
                    // Navigate or reload page on success
                    if (options.delete_redirect) window.location.href = options.delete_redirect;
                    else window.location.reload();
                });
        });
    });
}

function bindMQClearButton(el) {
    // Link clear button
    el.find('.js-mq-clear').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/mailing_queue/confirm-delete-all",
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function() {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
                .on("submitSuccess.ufForm", function() {
                    // Navigate or reload page on success
                    if (options.delete_redirect) window.location.href = options.delete_redirect;
                    else window.location.reload();
                });
        });
    });
};