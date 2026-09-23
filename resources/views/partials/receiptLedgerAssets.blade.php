<style>
    .receipt-ledger-table { table-layout: fixed; width: 100%; font-size: .9rem; }
    .receipt-ledger-table th { background: #0c77f1; color: #fff; vertical-align: middle; }
    .receipt-ledger-table .ledger-date { width: 13%; white-space: nowrap; }
    .receipt-ledger-table .ledger-description { width: 51%; }
    .receipt-ledger-table .ledger-money { width: 12%; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .receipt-ledger-table .ledger-dr { color: #dc2626; }
    .receipt-ledger-table .ledger-cr { color: #079447; }
    .receipt-ledger-table .ledger-balance { color: #172554; font-weight: 700; }
    .receipt-ledger-table .ledger-balance-negative { color: #dc2626; }
    .receipt-ledger-table .ledger-title { color: #172554; font-weight: 700; }
    .receipt-ledger-table .ledger-summary { color: #475569; font-size: .8rem; line-height: 1.3; margin-top: .18rem; }
    .receipt-ledger-table .ledger-meta { color: #64748b; font-size: .76rem; line-height: 1.35; margin-top: .2rem; overflow-wrap: anywhere; }
    .receipt-ledger-table .ledger-operator { color: #475569; font-size: .72rem; margin-top: .2rem; }
    .receipt-ledger-table tbody tr:nth-child(even) { background: #f8fafc; }
    .receipt-ledger-table tbody tr:hover { background: #eff6ff; }
    .receipt-ledger-table .ledger-detail-row > td { background: #f8fafc; padding: .7rem 1rem; }
    .receipt-ledger-table .ledger-detail-grid { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .75rem 1rem; align-items: start; }
    .receipt-ledger-table .ledger-detail-grid > * { min-width: 0; overflow-wrap: anywhere; }
    .receipt-ledger-table .ledger-toggle { border: 0; background: transparent; color: #2563eb; padding: 0; font-size: .76rem; font-weight: 600; }
    .receipt-ledger-table tfoot th { background: #f1f5f9; color: #172554; }
    @media (max-width: 992px) {
        .receipt-ledger-table { font-size: .8rem; }
        .receipt-ledger-table .ledger-date { width: 16%; }
        .receipt-ledger-table .ledger-description { width: 42%; }
        .receipt-ledger-table .ledger-money { width: 14%; }
    }
    @media print {
        .receipt-ledger-table .ledger-detail-row.d-none { display: table-row !important; }
        .receipt-ledger-table .ledger-toggle, .receipt-ledger-table .fa { display: none !important; }
        .receipt-ledger-table th,
        .receipt-ledger-table td,
        .receipt-ledger-table .ledger-dr,
        .receipt-ledger-table .ledger-cr,
        .receipt-ledger-table .ledger-balance,
        .receipt-ledger-table .ledger-title,
        .receipt-ledger-table .ledger-summary,
        .receipt-ledger-table .ledger-meta,
        .receipt-ledger-table .ledger-operator {
            color: #000 !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .receipt-ledger-table .ledger-detail-grid { display: block; }
    }
</style>
<script>
window.ReceiptHistoryLedger = window.ReceiptHistoryLedger || {
    escape(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    },
    money(value) {
        return (Number(value) || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    renderRows(rows) {
        return rows.map((row, index) => {
            const details = Array.isArray(row.details)
                ? row.details.map(item => this.escape(item)).join(' &nbsp;•&nbsp; ')
                : '';
            const balanceClass = Number(row.balance) < 0 ? ' ledger-balance-negative' : '';
            const detailId = `ledger-detail-${Date.now()}-${index}`;
            return `<tr>
                <td class="ledger-date">${this.escape(row.date || '-')}</td>
                <td class="ledger-description">
                    <div class="ledger-title">${this.escape(row.description || row.type || 'Transaction')}</div>
                    <div class="ledger-summary">${this.escape(row.summary || '')}</div>
                    <button type="button" class="ledger-toggle" data-ledger-detail="${detailId}" aria-expanded="false">▶ View details</button>
                </td>
                <td class="ledger-money ledger-dr">${this.money(row.dr)}</td>
                <td class="ledger-money ledger-cr">${this.money(row.cr)}</td>
                <td class="ledger-money ledger-balance${balanceClass}">${this.money(row.balance)}</td>
            </tr>
            <tr id="${detailId}" class="ledger-detail-row d-none">
                <td colspan="5">
                    <div class="ledger-detail-grid">
                        <div>${details ? `<div class="ledger-meta">${details}</div>` : '<span class="text-muted">No additional details.</span>'}</div>
                        <div class="ledger-operator"><i class="fa fa-user me-1"></i>${this.escape(row.operator || 'Not recorded')}</div>
                    </div>
                </td>
            </tr>`;
        }).join('');
    },
    updateTotals(rows) {
        const totalDr = rows.reduce((sum, row) => sum + (Number(row.dr) || 0), 0);
        const totalCr = rows.reduce((sum, row) => sum + (Number(row.cr) || 0), 0);
        const currentBalance = rows.length ? Number(rows[0].balance) || 0 : 0;
        const totalDrCell = document.getElementById('historyTotalDr');
        const totalCrCell = document.getElementById('historyTotalCr');
        const balanceCell = document.getElementById('historyCurrentBalance');
        if (totalDrCell) totalDrCell.textContent = this.money(totalDr);
        if (totalCrCell) totalCrCell.textContent = this.money(totalCr);
        if (balanceCell) {
            balanceCell.textContent = this.money(currentBalance);
            balanceCell.classList.toggle('ledger-balance-negative', currentBalance < 0);
        }
    }
};

if (!window.receiptLedgerToggleRegistered) {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-ledger-detail]');
        if (!button) return;

        const target = document.getElementById(button.dataset.ledgerDetail);
        if (!target) return;

        const willOpen = target.classList.contains('d-none');
        target.classList.toggle('d-none', !willOpen);
        button.setAttribute('aria-expanded', String(willOpen));
        button.textContent = willOpen ? '▼ Hide details' : '▶ View details';
    });
    window.receiptLedgerToggleRegistered = true;
}
</script>
