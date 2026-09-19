(function () {
    'use strict';
    function initialize() {
        var $ = window.jQuery;
        var form = document.querySelector('form[action*="store_redeem"], form[action*="Store_part_payment"], form[action*="store_redeem_old"]');
        if (!$ || !form) return;
        var generation = 0;
        var eligible = false;
        var mainPaths = ['/search_receipt_ajax', '/search_ticket_ajax', '/search_invoice_ajax', '/search_part_payment_receipt_ajax', '/search_part_payment_ticket_ajax', '/search_part_payment_invoice_ajax', '/search_old_receipt_ajax'];
        var articlePaths = ['/view_article_details_ajax', '/view_article_details_ajax_old'];
        var historyPaths = ['/view_dynamicCusDetailsView_details_ajax', '/view_dynamicCusDetailsView_details_ajax_partpayment'];

        function setEligible(value) {
            eligible = value;
            form.querySelectorAll('[type="submit"]').forEach(function (button) { button.disabled = !value; });
        }
        function clearReceipt(message) {
            setEligible(false);
            $('#articleDetails, #CustomerDetails').empty();
            $('.dynamic-area').empty();
            if (message) $('.dynamic-area').append($('<p class="text-danger" role="status"></p>').text(message));
        }
        setEligible(false);
        form.addEventListener('input', function (event) {
            if (['search_receipt', 'search_ticket', 'search_invoice'].includes(event.target.id)) {
                generation += 1;
                clearReceipt();
            }
        }, true);
        form.addEventListener('reset', function () { generation += 1; clearReceipt(); });
        form.addEventListener('submit', function (event) {
            if (!eligible || !document.getElementById('r_number')) {
                event.preventDefault();
                event.stopImmediatePropagation();
                clearReceipt('Search for an active receipt before making a payment. Redeemed and forfeited receipts cannot be paid here.');
            }
        }, true);

        $.ajaxPrefilter(function (options) {
            var path = new URL(options.url, window.location.href).pathname;
            var main = mainPaths.includes(path);
            var articles = articlePaths.includes(path);
            var history = historyPaths.includes(path);
            if (!main && !articles && !history) return;
            var requestedGeneration = generation;
            options.data = (options.data ? options.data + '&' : '') + 'payment_workflow=1';
            var success = options.success;
            var error = options.error;
            options.success = function (response) {
                if (requestedGeneration !== generation) return;
                if (typeof success === 'function') success.apply(this, arguments);
                if (main) {
                    if (typeof response === 'string' && document.getElementById('r_number')) setEligible(true);
                    else clearReceipt('No active receipt found. Redeemed and forfeited receipts are excluded.');
                } else if (response.status === 'not_found') {
                    $(articles ? '#articleDetails' : '#CustomerDetails').empty();
                }
            };
            options.error = function () {
                if (requestedGeneration !== generation) return;
                if (typeof error === 'function') error.apply(this, arguments);
                if (main) clearReceipt('Receipt search failed. Please search again.');
                else $(articles ? '#articleDetails' : '#CustomerDetails').empty();
            };
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
