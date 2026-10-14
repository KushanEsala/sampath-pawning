@php($view = $view ?? 'pending')
@php($isAdmin = auth()->check() && auth()->user()->isAdmin())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock check report</title>
    <style>
        :root { --ink:#17243b; --muted:#59677a; --line:#dce3eb; --accent:#0b7169; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f5f7fa; color:var(--ink); font:14px/1.45 system-ui,-apple-system,"Segoe UI",sans-serif; }
        button,input,a { font:inherit; }
        .report { width:min(100% - 32px,1800px); margin:20px auto 40px; }
        .topline { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:16px; }
        h1 { margin:0 0 3px; font-size:23px; line-height:1.2; letter-spacing:-.025em; }
        .subtitle,.hint,.note { color:var(--muted); margin:0; }
        .back-link { color:var(--accent); text-decoration:none; font-weight:650; white-space:nowrap; }
        .back-link:hover { text-decoration:underline; }
        .panel { background:white; border:1px solid var(--line); border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(19,37,60,.035); }
        .status-tabs { display:flex; gap:3px; padding:0 18px; border-bottom:1px solid var(--line); }
        .status-tab { padding:13px 15px 11px; color:var(--muted); text-decoration:none; font-weight:650; border-bottom:3px solid transparent; }
        .status-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
        .status-tab:hover { color:var(--ink); }
        .filter-area { padding:14px 18px; border-bottom:1px solid var(--line); }
        .hint { font-size:13px; margin-bottom:12px; }
        .filter-form,.tools,.summary { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
        .date-field,.search-field { display:grid; gap:4px; }
        .date-field label,.search-field label { font-size:12px; font-weight:700; color:var(--muted); }
        input[type=date],input[type=search] { min-height:36px; padding:6px 10px; background:white; color:var(--ink); border:1px solid #bfcbd8; border-radius:6px; }
        input[type=date] { width:158px; }
        input[type=search] { width:260px; max-width:100%; }
        input:focus-visible,button:focus-visible,a:focus-visible,th:focus-visible { outline:2px solid var(--accent); outline-offset:2px; }
        .button { min-height:36px; border:1px solid #bdc9d5; border-radius:6px; background:white; color:var(--ink); padding:6px 12px; font-weight:600; cursor:pointer; }
        .button:hover { background:#f1f5f8; }
        .button-primary { background:var(--accent); border-color:var(--accent); color:white; }
        .button-primary:hover { background:#095e57; }
        .button-danger { color:#a02b35; border-color:#e5bdc0; margin-left:auto; }
        .button-danger:hover { background:#fff3f3; }
        .tools { padding:12px 18px; border-bottom:1px solid var(--line); }
        .search-field { margin-right:auto; }
        .summary { gap:18px; align-items:center; padding:10px 18px; background:#f8fafb; border-bottom:1px solid var(--line); font-size:13px; }
        .summary strong { font-variant-numeric:tabular-nums; }
        .table-wrap { width:100%; overflow-x:auto; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th { background:#edf2f5; color:#344458; text-align:left; text-transform:uppercase; letter-spacing:.045em; font-size:11px; font-weight:750; padding:10px 12px; border-bottom:1px solid #ccd7e0; }
        th.sortable { cursor:pointer; user-select:none; }
        th.sortable:hover { background:#e4ecf0; }
        th[aria-sort=ascending]::after { content:" ↑"; color:var(--accent); }
        th[aria-sort=descending]::after { content:" ↓"; color:var(--accent); }
        td { padding:11px 12px; vertical-align:top; border-bottom:1px solid #e6ebf0; overflow-wrap:anywhere; }
        tbody tr:last-child td { border-bottom:0; }
        tbody tr:hover { background:#f7fbfa; }
        tbody tr[hidden] { display:none; }
        .check-col { width:62px; text-align:center; }
        .stock-col { width:18%; }
        .customer-col { width:21%; }
        .contact-col { width:31%; }
        .receipt-col { width:14%; }
        .amount-col { width:15%; text-align:right; }
        .primary-value { display:block; font-weight:700; }
        .secondary-value { display:block; margin-top:3px; color:var(--muted); font-size:12px; }
        .field-name { color:#657486; margin-right:4px; }
        .number { font-variant-numeric:tabular-nums; }
        .amount-value { white-space:nowrap; }
        .check-input { width:18px; height:18px; margin:2px 0 0; accent-color:var(--accent); cursor:pointer; }
        .empty { padding:34px 18px; color:var(--muted); text-align:center; border-top:1px solid var(--line); }
        .empty[hidden] { display:none; }
        .footer-note { padding:10px 18px; border-top:1px solid var(--line); color:var(--muted); font-size:12px; }
        @media(max-width:850px) { .report { width:min(100% - 20px,1800px); margin-top:12px; } .button-danger { margin-left:0; } table { min-width:780px; } }
        @media print {
            @page { size:A4 landscape; margin:9mm; }
            body { background:white; font-size:9px; }
            .report { width:100%; margin:0; }
            .panel { border:0; box-shadow:none; }
            .back-link,.status-tabs,.filter-area,.tools,.footer-note,.check-col { display:none !important; }
            .topline { margin-bottom:7px; } h1 { font-size:17px; }
            .summary { padding:5px 0; background:white; }
            .table-wrap { overflow:visible; } table { min-width:0; }
            th,td { padding:5px 6px; } th { background:#e9eef2 !important; print-color-adjust:exact; }
            tr { break-inside:avoid; }
        }
    </style>
</head>
<body>
<main class="report">
    <div class="topline"><div><h1>Stock check report</h1><p class="subtitle">{{ $view === 'checked' ? 'Checked stock' : 'Stock awaiting a check' }}</p></div><a class="back-link" href="{{ route('home') }}">Back to dashboard</a></div>
    <section class="panel" aria-label="Stock check records">
        <nav class="status-tabs" aria-label="Stock check status">
            <a class="status-tab {{ $view === 'pending' ? 'active' : '' }}" href="{{ route('StockCheckreport', array_merge(request()->only(['from_date', 'to_date']), ['view' => 'pending'])) }}" @if($view === 'pending') aria-current="page" @endif>To Check</a>
            <a class="status-tab {{ $view === 'checked' ? 'active' : '' }}" href="{{ route('StockCheckreport', array_merge(request()->only(['from_date', 'to_date']), ['view' => 'checked'])) }}" @if($view === 'checked') aria-current="page" @endif>Checked</a>
        </nav>
        <div class="filter-area">
            <p class="hint">@if($view === 'checked'){{ request()->filled('from_date') || request()->filled('to_date') ? 'Showing checked records for the selected dates.' : 'Showing all checked records. Use dates to narrow this list.' }}@else{{ request()->filled('from_date') || request()->filled('to_date') ? 'Showing records awaiting a check for the selected dates.' : 'Choose a date range to see records awaiting a check.' }}@endif</p>
            <form action="" method="get" class="filter-form">
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="date-field"><label for="from_date">From date</label><input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"></div>
                <div class="date-field"><label for="to_date">To date</label><input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"></div>
                <button type="submit" class="button button-primary">Apply dates</button>
                @if($view === 'checked')<button type="button" id="resetCheckboxStock" class="button button-danger">Reset All Checked in Branch</button>@endif
            </form>
        </div>
        <div class="tools">
            <div class="search-field"><label for="report-search">Search these records</label><input id="report-search" type="search" placeholder="Stock, customer, receipt…" autocomplete="off"></div>
            <button type="button" class="button" id="copy-records">Copy</button>
            <button type="button" class="button" id="csv-records">Download CSV</button>
            <button type="button" class="button" id="print-records">Print / PDF</button>
        </div>
        <div class="summary" aria-live="polite"><span><strong id="visible-count">{{ $pawnCount }}</strong> records</span><span>Total amount: <strong id="visible-amount">{{ $amount }}</strong></span><span class="note" id="filter-note">All matching records shown on this page</span></div>
        <div class="table-wrap">
            <table id="t_pawn_sums">
                <thead><tr>
                    <th class="check-col" scope="col">Check</th>
                    <th class="stock-col sortable" scope="col" data-sort="stock" tabindex="0" aria-sort="none">Stock / receipt</th>
                    @if($isAdmin)<th class="customer-col sortable" scope="col" data-sort="customer" tabindex="0" aria-sort="none">Customer</th><th class="contact-col" scope="col">Contact</th>@endif
                    <th class="receipt-col sortable" scope="col" data-sort="date" tabindex="0" aria-sort="none">Receipt</th>
                    <th class="amount-col sortable" scope="col" data-sort="amount" tabindex="0" aria-sort="none">Amount</th>
                </tr></thead>
                <tbody>
                @foreach($recipts as $receipt)
                    @php($receiptDateTime = (string) ($receipt->Receipt_Date ?? ''))
                    @php($receiptDate = substr($receiptDateTime, 0, 10))
                    @php($receiptTime = ltrim(substr($receiptDateTime, 10), ' T'))
                    <tr class="record-row" data-stock="{{ $receipt->Invoice_Number }}" data-date="{{ $receiptDateTime }}" data-type="{{ $receipt->Receipt_Type }}" data-amount="{{ $receipt->Amount }}" @if($isAdmin) data-receipt="{{ $receipt->Receipt_Number }}" data-ticket="{{ $receipt->Ticket_Number }}" data-customer="{{ $receipt->Customer_Name }}" data-nic="{{ $receipt->Customer_NIC }}" data-address="{{ $receipt->Customer_Address }}" data-phone="{{ $receipt->Customer_Phone }}" @endif>
                        <td class="check-col">@if($receipt instanceof \App\Models\TPawnSum)<input class="check-input save-checkbox" type="checkbox" data-record-id="{{ $receipt->id }}" aria-label="{{ $receipt->checkbox_stock == 1 ? 'Mark stock ' . $receipt->Invoice_Number . ' unchecked' : 'Mark stock ' . $receipt->Invoice_Number . ' checked' }}" {{ $receipt->checkbox_stock == 1 ? 'checked' : '' }}>@else<span title="Opening stock cannot be checked here">—</span>@endif</td>
                        <td><span class="primary-value number">{{ $receipt->Invoice_Number ?: '—' }}</span>@if($isAdmin)<span class="secondary-value"><span class="field-name">Receipt</span><span class="number">{{ $receipt->Receipt_Number ?? '—' }}</span> <span class="field-name">· Ticket</span><span class="number">{{ $receipt->Ticket_Number ?? '—' }}</span></span>@endif</td>
                        @if($isAdmin)<td><span class="primary-value">{{ $receipt->Customer_Name ?: '—' }}</span><span class="secondary-value"><span class="field-name">NIC</span>{{ $receipt->Customer_NIC ?: '—' }}</span></td><td><span class="primary-value">{{ $receipt->Customer_Address ?: '—' }}</span><span class="secondary-value"><span class="field-name">Phone</span><span class="number">{{ $receipt->Customer_Phone ?: '—' }}</span></span></td>@endif
                        <td><span class="primary-value number">{{ $receiptDate ?: '—' }}</span>@if($receiptTime !== '')<span class="secondary-value"><span class="field-name">Time</span><span class="number">{{ $receiptTime }}</span></span>@endif<span class="secondary-value"><span class="field-name">Type</span>{{ $receipt->Receipt_Type ?? '—' }}</span></td>
                        <td class="amount-col"><span class="primary-value amount-value number">{{ number_format((float) $receipt->Amount, 2) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="empty" id="empty-message" @if($pawnCount > 0) hidden @endif>{{ $view === 'pending' && !request()->filled('from_date') && !request()->filled('to_date') ? 'Select a date range to load stock awaiting a check.' : 'No records match this view or search.' }}</div>
        <div class="footer-note">{{ $view === 'checked' ? 'Uncheck a record to move it back to To Check.' : 'Checking a record moves it to the Checked tab.' }} Opening stock entries cannot be checked here.</div>
    </section>
</main>
<script>
(function () {
    const body = document.querySelector('#t_pawn_sums tbody');
    const rows = Array.from(body.querySelectorAll('.record-row'));
    const search = document.getElementById('report-search');
    const admin = @json($isAdmin);
    const token = @json(csrf_token());
    const headers = admin ? ['Stock Number','Receipt Number','Ticket Number','Customer NIC','Customer Name','Customer Address','Customer Phone','Receipt Type','Receipt Date','Amount'] : ['Stock Number','Receipt Type','Receipt Date','Amount'];
    const visibleRows = () => rows.filter(row => !row.hidden);
    function updateSummary() {
        const visible = visibleRows();
        const total = visible.reduce((sum, row) => sum + (Number(row.dataset.amount) || 0), 0);
        document.getElementById('visible-count').textContent = visible.length.toLocaleString('en-US');
        document.getElementById('visible-amount').textContent = total.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
        document.getElementById('filter-note').textContent = search.value.trim() ? 'Filtered from ' + rows.length.toLocaleString('en-US') + ' records' : 'All matching records shown on this page';
        document.getElementById('empty-message').hidden = visible.length !== 0;
    }
    search.addEventListener('input', () => {
        const term = search.value.trim().toLocaleLowerCase();
        rows.forEach(row => { row.hidden = !!term && !row.textContent.toLocaleLowerCase().includes(term); });
        updateSummary();
    });
    function sortBy(th) {
        const key = th.dataset.sort;
        const direction = th.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';
        document.querySelectorAll('th.sortable').forEach(header => header.setAttribute('aria-sort','none'));
        th.setAttribute('aria-sort', direction);
        const sign = direction === 'ascending' ? 1 : -1;
        rows.sort((a,b) => key === 'amount' ? (Number(a.dataset.amount) - Number(b.dataset.amount)) * sign : String(a.dataset[key] || '').localeCompare(String(b.dataset[key] || ''),undefined,{numeric:true,sensitivity:'base'}) * sign);
        rows.forEach(row => body.appendChild(row));
    }
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => sortBy(th));
        th.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); sortBy(th); } });
    });
    function exportRows() {
        return visibleRows().map(row => {
            const d = row.dataset;
            return admin ? [d.stock,d.receipt,d.ticket,d.nic,d.customer,d.address,d.phone,d.type,d.date,d.amount] : [d.stock,d.type,d.date,d.amount];
        });
    }
    const safeCell = value => /^[=+\-@\t\r]/.test(String(value || '')) ? "'" + value : String(value || '');
    const csvCell = value => '"' + safeCell(value).replace(/"/g,'""') + '"';
    document.getElementById('csv-records').addEventListener('click', () => {
        const csv = [headers,...exportRows()].map(row => row.map(csvCell).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob(['\ufeff',csv],{type:'text/csv;charset=utf-8'}));
        const link = document.createElement('a');
        link.href = url; link.download = 'stock-check-{{ $view }}-{{ now()->format('Y-m-d') }}.csv';
        document.body.appendChild(link); link.click(); link.remove();
        setTimeout(() => URL.revokeObjectURL(url),1000);
    });
    document.getElementById('copy-records').addEventListener('click', async function () {
        const value = [headers,...exportRows()].map(row => row.map(safeCell).join('\t')).join('\n');
        try { await navigator.clipboard.writeText(value); this.textContent = 'Copied'; setTimeout(() => this.textContent = 'Copy',1500); }
        catch (_) { alert('Copy is unavailable in this browser. Use Download CSV instead.'); }
    });
    document.getElementById('print-records').addEventListener('click', () => window.print());
    document.querySelectorAll('.save-checkbox').forEach(checkbox => checkbox.addEventListener('change', async function () {
        const checked = this.checked ? 1 : 0;
        this.disabled = true;
        try {
            const response = await fetch('/update-checkbox',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},body:JSON.stringify({record_id:this.dataset.recordId,checked})});
            if (!response.ok || !(await response.json()).success) throw new Error('Update failed');
            location.reload();
        } catch (_) { this.checked = !checked; this.disabled = false; alert('Could not update this stock record. Please try again.'); }
    }));
    const reset = document.getElementById('resetCheckboxStock');
    if (reset) reset.addEventListener('click', async function () {
        if (!confirm('Reset all checked stock records in your branch?')) return;
        this.disabled = true;
        try {
            const response = await fetch('/reset-checkbox-stock',{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});
            if (!response.ok || !(await response.json()).success) throw new Error('Reset failed');
            location.reload();
        } catch (_) { this.disabled = false; alert('Could not reset checked records. Please try again.'); }
    });
})();
</script>
</body>
</html>
