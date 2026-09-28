<div class="modal fade" id="viewPaymentHistoryModel" tabindex="-1" aria-labelledby="viewPaymentHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title m-2" id="viewPaymentHistoryLabel">Payment History</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 receipt-ledger-table">
                        <thead><tr><th class="ledger-date">Date</th><th class="ledger-description">Description</th><th class="ledger-money">DR</th><th class="ledger-money">CR</th><th class="ledger-money">Balance</th></tr></thead>
                        <tbody id="CustomerDetails"></tbody>
                        <tfoot><tr><th colspan="2" class="text-end">Ledger totals / Current balance</th><th id="historyTotalDr" class="ledger-money ledger-dr">0.00</th><th id="historyTotalCr" class="ledger-money ledger-cr">0.00</th><th id="historyCurrentBalance" class="ledger-money ledger-balance">0.00</th></tr></tfoot>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a class="btn btn-outline-dark d-none" data-payment-history-print target="_blank" rel="noopener" href="#">Print History</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.receiptLedgerAssets')
<script>
document.getElementById('viewPaymentHistoryModel').addEventListener('show.bs.modal', function () {
    const receiptNumber = document.getElementById('r_number')?.value
        || document.getElementById('search_receipt')?.value.trim();
    const ledger = window.ReceiptHistoryLedger;
    ledger.setPrintReceipt(null);
    ledger.updateTotals([]);
    if (!receiptNumber) {
        $('#CustomerDetails').html('<tr><td colspan="5" class="text-center">Find a receipt first.</td></tr>');
        return;
    }

    $('#CustomerDetails').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
    $.ajax({
        url: "{{ route('view_dynamicCusDetailsView_details_ajax') }}",
        method: 'GET',
        data: { search_receipt_no: receiptNumber },
        success: function (response) {
            const rows = response.status === 'success' ? response.data || [] : [];
            ledger.updateTotals(rows);
            ledger.setPrintReceipt(response.receipt_number || null);
            $('#CustomerDetails').html(rows.length
                ? ledger.renderRows(rows)
                : '<tr><td colspan="5" class="text-center">No payment history found.</td></tr>');
        },
        error: function () {
            ledger.updateTotals([]);
            $('#CustomerDetails').html('<tr><td colspan="5" class="text-center text-danger">Error fetching payment history.</td></tr>');
        }
    });
});
</script>
