@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
@php $status = $status ?? request('status', 'all'); @endphp
<style>
    .forfeit-report-shell { width: 100%; overflow: visible; }
    .forfeit-report { width: 100%; table-layout: fixed; margin-bottom: 0; }
    .forfeit-report th, .forfeit-report td { padding: .65rem .5rem; white-space: normal; overflow-wrap: anywhere; vertical-align: middle; font-size: .88rem; }
    .forfeit-report thead th { color: #343b53; text-align: center; }
    .forfeit-report .money, .forfeit-report .date-value { white-space: nowrap; }
    .forfeit-report .detail-row > td { padding: 0; background: #f7f8fc; }
    .forfeit-details { padding: 1rem; border-left: 4px solid #6f42c1; }
    .forfeit-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1rem; }
    .forfeit-fact { background: #fff; border: 1px solid #e1e4eb; border-radius: 8px; padding: .85rem; }
    .forfeit-details table { width: 100%; table-layout: fixed; }
    .forfeit-details td, .forfeit-details th { white-space: normal; overflow-wrap: anywhere; }
    .forfeit-primary-actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: center; }
    @media (max-width: 1100px) {
        .forfeit-report th, .forfeit-report td { padding: .5rem .35rem; font-size: .78rem; }
        .forfeit-facts { grid-template-columns: 1fr; }
    }
    @media print {
        .sidebar, .header, .no-report-print { display: none !important; }
        .page-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
        .forfeit-report-shell { overflow: visible !important; }
    }
</style>
<div class="main-wrapper"><div class="page-wrapper"><div class="content container-fluid">
    <h4>Forfeit Receipt List</h4>
    <p class="text-muted">Reviewed receipts awaiting final forfeiture and previously forfeited receipts. Use View to open full details beneath a receipt.</p>
    @if(session('done'))<div class="alert alert-success">{{ session('done') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card no-report-print"><div class="card-body">
        <form method="GET" action="{{ route('forfeitReceipt_List') }}" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label" for="forfeit_search">Receipt Number</label><input class="form-control" id="forfeit_search" name="receipt_number" value="{{ request('receipt_number') }}"></div>
            <div class="col-md-3"><label class="form-label" for="forfeit_status">Status</label><select class="form-control" id="forfeit_status" name="status" onchange="this.form.submit()">
                @foreach(['all'=>'All', 'pending'=>'Awaiting final forfeiture', 'forfeited'=>'Forfeited'] as $value=>$label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach
            </select></div>
            <div class="col-auto"><label class="form-label" for="forfeit_page_size">Rows per page</label><select class="form-control" id="forfeit_page_size" name="per_page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int) request('per_page',25) === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button></div>
            <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('forfeitReceipt_List') }}">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body">
        <div class="mb-3 no-report-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-report-expand="true">Expand all</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-report-expand="false">Collapse all</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">Print displayed report</button>
        </div>
        <div class="forfeit-report-shell">
            <table class="table table-bordered table-hover align-middle forfeit-report">
                <colgroup><col style="width:7%"><col style="width:13%"><col style="width:27%"><col style="width:12%"><col style="width:13%"><col style="width:14%"><col style="width:14%"></colgroup>
                <thead><tr><th>View</th><th>Receipt / Stock</th><th>Customer</th><th>Expiry</th><th>Principal</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($recipts as $receipt)
                    @php
                        $history = $receipt->receipt_history;
                        $customer = $history['customer'];
                        $financial = $history['financial'];
                        $forfeit = $receipt->forfeit_record;
                        $detailId = 'forfeit-details-'.$receipt->id;
                    @endphp
                    <tr class="receipt-summary-row">
                        <td class="text-center"><button type="button" class="btn btn-success btn-sm" data-row-details="{{ $detailId }}" aria-controls="{{ $detailId }}" aria-expanded="false"><i class="fa fa-eye"></i> <span>View</span></button></td>
                        <td class="text-center"><strong>{{ $receipt->Receipt_Number }}</strong><br><small>Stock: {{ $receipt->Invoice_Number ?: '—' }}</small></td>
                        <td><strong>{{ optional($customer)->Name ?: $receipt->Customer_Name }}</strong><br><small>{{ $receipt->Customer_NIC }} · {{ optional($customer)->Contact_1 ?? $receipt->Customer_Phone }}</small></td>
                        <td class="text-center date-value">{{ optional($receipt->Final_date)->format('Y-m-d') }}</td>
                        <td class="text-end money">{{ number_format($financial['principal'],2) }}</td>
                        <td class="text-center"><span class="badge {{ $receipt->isForfeit ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $receipt->isForfeit ? 'Forfeited' : 'Awaiting final' }}</span></td>
                        <td><div class="forfeit-primary-actions"><a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('receipt.search.print', ['receipt_number'=>$receipt->Receipt_Number]) }}"><i class="fa fa-print"></i> History</a>@if(!$receipt->isForfeit)<a class="btn btn-danger btn-sm" href="{{ route('pawning_forfeit_receipt', ['receipt_number'=>$receipt->Receipt_Number]) }}">Process</a>@endif</div></td>
                    </tr>
                    <tr id="{{ $detailId }}" class="detail-row" hidden><td colspan="7"><div class="forfeit-details">
                        <div class="forfeit-facts">
                            <div class="forfeit-fact"><strong>Customer and receipt</strong><br>Address: {{ optional($customer)->Address_1 ?? $receipt->Customer_Address }}<br>Stock (Invoice): {{ $receipt->Invoice_Number }}<br>Ticket: {{ $receipt->Ticket_Number }}<br>Receipt type: {{ $receipt->Receipt_Type }}</div>
                            <div class="forfeit-fact"><strong>Receipt movement</strong><br>Receipt date/time: {{ optional($receipt->Receipt_Date)->format('Y-m-d') }} {{ optional($receipt->created_at)->format('H:i:s') }}<br>Moved to Forfeit List: {{ $receipt->forfeit_queued_at ?? 'Legacy record' }}</div>
                            <div class="forfeit-fact"><strong>Forfeit record</strong><br>Number: {{ optional($forfeit)->Forfeit_Number ?? 'Pending' }}<br>Date: {{ optional($forfeit)->Forfeit_Date ?? 'Pending' }}<br>Recorded amount: {{ $forfeit ? number_format($forfeit->Payable_Total,2) : 'Pending' }}</div>
                        </div>
                        <p><strong>Letters issued:</strong> 1st {{ optional($receipt->letter_1_date)->format('Y-m-d') ?? '—' }}; 2nd {{ optional($receipt->letter_2_date)->format('Y-m-d') ?? '—' }}; 3rd {{ optional($receipt->letter_3_date)->format('Y-m-d') ?? '—' }}</p>
                        <h6>{{ $receipt->isForfeit ? 'Calculated amounts (as of today)' : 'Current amounts to pay' }}</h6>
                        <table class="table table-sm table-bordered"><thead><tr><th>Interest</th><th>Service charge</th><th>Letter charge</th><th>Total arrears</th><th>Principal + arrears</th></tr></thead><tbody><tr><td>{{ number_format($financial['interest'],2) }}@if(!empty($financial['days']))<br><small class="text-muted">(Days: {{ $financial['days'] }})</small>@endif</td>@foreach(['service_charge','letter_charge','arrears_total','redemption_total'] as $key)<td>{{ number_format($financial[$key],2) }}</td>@endforeach</tr></tbody></table>
                        <h6>Articles</h6>
                        <table class="table table-sm table-bordered"><thead><tr><th>Category</th><th>Article</th><th>Condition</th><th>Karatage</th><th>Weight</th><th>Qty</th><th>Value</th></tr></thead><tbody>@forelse($history['details'] as $article)<tr><td>{{ $article->Category }}</td><td>{{ $article->Articles }}</td><td>{{ $article->Condition }}</td><td>{{ $article->Karatage }}</td><td>{{ $article->Weight }}</td><td>{{ $article->QTY }}</td><td>{{ number_format($article->Value,2) }}</td></tr>@empty<tr><td colspan="7">No article details recorded.</td></tr>@endforelse</tbody></table>
                        @if($receipt->stock_items->isNotEmpty())
                            <h6>Forfeited stock</h6><table class="table table-sm table-bordered"><thead><tr><th>Item code</th><th>Barcode</th><th>Article</th><th>Weight</th><th>Qty</th></tr></thead><tbody>@foreach($receipt->stock_items as $item)<tr><td>{{ $item->Item_code }}</td><td>{{ $item->Bar_code }}</td><td>{{ $item->Item_description }}</td><td>{{ $item->Weight }}</td><td>{{ $item->QTY }}</td></tr>@endforeach</tbody></table>
                        @endif
                        <h6>History, customer promises and remarks — newest first</h6>
                        <table class="table table-sm table-bordered"><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Details</th></tr></thead><tbody>@forelse($history['timeline'] as $event)<tr><td>{{ $event['date'] }}</td><td>{{ $event['type'] }}</td><td>{{ $event['amount'] !== null ? number_format($event['amount'],2) : '' }}</td><td>@foreach($event['details'] as $label=>$value)<strong>{{ $label }}:</strong> {{ $value }}<br>@endforeach</td></tr>@empty<tr><td colspan="4">No history recorded.</td></tr>@endforelse</tbody></table>
                        <a class="btn btn-outline-success btn-sm no-report-print" href="{{ route('receipt.search', ['receipt_number'=>$receipt->Receipt_Number]) }}"><i class="fa fa-history"></i> Open Receipt Search History</a>
                    </div></td></tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No matching receipts. Choose All or clear the filters to show every receipt.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.report-pagination', ['paginator'=>$recipts])
    </div></div>
</div></div></div>
<script>
function setForfeitDetail(button, opening) {
    var row = document.getElementById(button.getAttribute('data-row-details'));
    row.toggleAttribute('hidden', !opening);
    button.setAttribute('aria-expanded', String(opening));
    button.querySelector('span').textContent = opening ? 'Hide' : 'View';
}
document.querySelectorAll('[data-row-details]').forEach(function (button) {
    button.addEventListener('click', function () { setForfeitDetail(button, document.getElementById(button.getAttribute('data-row-details')).hasAttribute('hidden')); });
});
document.querySelectorAll('[data-report-expand]').forEach(function (button) {
    button.addEventListener('click', function () {
        var opening = button.getAttribute('data-report-expand') === 'true';
        document.querySelectorAll('[data-row-details]').forEach(function (toggle) { setForfeitDetail(toggle, opening); });
    });
});
</script>
@endsection
