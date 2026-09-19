@extends('layouts.topnavbar')
@extends('layouts.sidebar')

@section('content')
<style>
    .reminder-report-shell { width: 100%; overflow: visible; }
    .reminder-report { width: 100%; table-layout: fixed; margin-bottom: 0; }
    .reminder-report th, .reminder-report td { padding: .65rem .5rem; white-space: normal; overflow-wrap: anywhere; vertical-align: middle; font-size: .88rem; }
    .reminder-report thead th { background: #343b53; color: #fff; text-align: center; }
    .reminder-report .money, .reminder-report .date-value { white-space: nowrap; }
    .reminder-report .detail-row > td { padding: 0; background: #f7f8fc; }
    .reminder-report .reminder-overdue > td { background: #fff0ee !important; color: #762d28; }
    .reminder-report .reminder-overdue > td:first-child { border-left: 4px solid #c7483d; }
    .reminder-details { padding: 1rem; border-left: 4px solid #6f42c1; }
    .reminder-detail-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(250px, 1fr); gap: 1rem; }
    .reminder-detail-card { background: #fff; border: 1px solid #e1e4eb; border-radius: 8px; padding: 1rem; }
    .reminder-detail-card h6 { color: #343b53; margin-bottom: .8rem; }
    .reminder-finance { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .6rem; }
    .reminder-finance div { background: #f5f6fa; border-radius: 6px; padding: .55rem; }
    .reminder-finance small { display: block; color: #73788a; }
    .reminder-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .reminder-promise-form { display: grid; grid-template-columns: minmax(145px, .65fr) minmax(200px, 1.35fr) auto; gap: .5rem; align-items: end; }
    .reminder-promise-form textarea { min-height: 38px; resize: vertical; }
    @media (max-width: 1100px) {
        .reminder-report th, .reminder-report td { padding: .5rem .35rem; font-size: .78rem; }
        .reminder-detail-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767px) { .reminder-promise-form, .reminder-finance { grid-template-columns: 1fr; } }
</style>
<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <h4>Forfeit Reminder List</h4>
                <p class="text-muted">Review unpaid receipts after the third-letter waiting period. Use View to manage the customer promise or move the receipt to the Forfeit List.</p>
            </div>

            @if(session('done'))<div class="alert alert-success">{{ session('done') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <div class="card mb-3"><div class="card-body">
                <form method="GET" action="{{ route('forfeit.reminders.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4"><label class="form-label" for="reminder_receipt_number">Receipt Number</label><input class="form-control" id="reminder_receipt_number" name="receipt_number" value="{{ request('receipt_number') }}"></div>
                    <div class="col-auto"><label class="form-label" for="reminder_per_page">Rows per page</label><select class="form-control" name="per_page" id="reminder_per_page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>@endforeach</select></div>
                    <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button></div>
                    <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('forfeit.reminders.index') }}">Clear</a></div>
                </form>
            </div></div>

            <div class="card"><div class="card-body">
                <p class="small text-muted">Pale red rows indicate a customer promise date that has passed without payment.</p>
                <div class="reminder-report-shell">
                    <table class="table table-bordered table-hover align-middle reminder-report">
                        <colgroup><col style="width:7%"><col style="width:10%"><col style="width:21%"><col style="width:11%"><col style="width:15%"><col style="width:12%"><col style="width:12%"><col style="width:12%"></colgroup>
                        <thead><tr><th>View</th><th>Receipt No</th><th>Customer</th><th>Expiry</th><th>3rd Letter / Reminder</th><th>Principal</th><th>Interest</th><th>Total to Pay</th></tr></thead>
                        <tbody>
                        @forelse($receipts as $receipt)
                            @php
                                $financial = $receipt->financial_breakdown;
                                $promise = $receipt->current_promise;
                                $overdue = $promise && $promise->promise_date->lt(today());
                                $detailId = 'reminder-details-'.$receipt->id;
                            @endphp
                            <tr class="receipt-summary-row{{ $overdue ? ' reminder-overdue' : '' }}">
                                <td class="text-center"><button type="button" class="btn btn-success btn-sm" data-row-details="{{ $detailId }}" aria-controls="{{ $detailId }}" aria-expanded="false"><i class="fa fa-eye"></i> <span>View</span></button></td>
                                <td class="text-center fw-bold">{{ $receipt->Receipt_Number }}</td>
                                <td><strong>{{ $receipt->current_customer_name }}</strong><br><small>{{ $receipt->current_customer_phone ?: 'No telephone recorded' }}</small>@if($overdue)<br><span class="badge bg-danger">Promise overdue</span>@endif</td>
                                <td class="text-center date-value">{{ optional($receipt->Final_date)->format('Y-m-d') }}</td>
                                <td class="text-center date-value">{{ optional($receipt->letter_3_date)->format('Y-m-d') }}<br><strong>{{ $receipt->reminder_due_date }}</strong></td>
                                <td class="text-end money">{{ number_format($financial['principal'], 2) }}</td>
                                <td class="text-end money">{{ number_format($financial['interest'], 2) }}</td>
                                <td class="text-end money fw-bold">{{ number_format($financial['arrears_total'], 2) }}</td>
                            </tr>
                            <tr id="{{ $detailId }}" class="detail-row" hidden><td colspan="8"><div class="reminder-details">
                                <div class="reminder-finance mb-3">
                                    <div><small>Service charge</small><strong>{{ number_format($financial['service_charge'], 2) }}</strong></div>
                                    <div><small>Letter / postage charge</small><strong>{{ number_format($financial['letter_charge'], 2) }}</strong></div>
                                    <div><small>Principal + arrears</small><strong>{{ number_format($financial['redemption_total'], 2) }}</strong></div>
                                </div>
                                <div class="reminder-detail-grid">
                                    <section class="reminder-detail-card">
                                        <h6><i class="fa fa-calendar-check me-1"></i> Customer Promise</h6>
                                        @if($promise)
                                            <p class="mb-2"><strong>Current date:</strong> {{ $promise->promise_date->format('Y-m-d') }}<br><strong>Promise:</strong> {{ $promise->remark }}</p>
                                            <span class="badge {{ $promise->promise_date->lt(today()) ? 'bg-danger' : 'bg-info' }} mb-2">{{ $promise->promise_date->lt(today()) ? 'Overdue' : 'Awaiting payment' }}</span>
                                        @endif
                                        @if(!$promise || today()->lt($promise->promise_date))
                                            <form class="reminder-promise-form mt-2" method="POST" action="{{ route('forfeit.reminders.promise') }}">
                                                @csrf
                                                <input type="hidden" name="pawn_sum_id" value="{{ $receipt->id }}">
                                                <div><label class="form-label mb-1">Promise date</label><input class="form-control form-control-sm" type="date" name="promise_date" min="{{ now()->toDateString() }}" required></div>
                                                <div><label class="form-label mb-1">Customer promise</label><textarea class="form-control form-control-sm" name="remark" rows="1" maxlength="2000" required></textarea></div>
                                                <button class="btn btn-sm btn-primary" type="submit">{{ $promise ? 'Extend Promise' : 'Save Promise' }}</button>
                                            </form>
                                        @else
                                            <p class="small text-muted mt-2 mb-0">The promise date has arrived and can no longer be changed.</p>
                                        @endif
                                    </section>
                                    <section class="reminder-detail-card">
                                        <h6><i class="fa fa-gavel me-1"></i> Forfeit Review</h6>
                                        <div class="reminder-actions">
                                            <a class="btn btn-outline-success btn-sm" href="{{ route('receipt.search', ['receipt_number' => $receipt->Receipt_Number]) }}"><i class="fa fa-history"></i> Full History</a>
                                            @if($receipt->can_queue_forfeit)
                                                <form method="POST" action="{{ route('forfeit.reminders.queue') }}" onsubmit="return confirm('Move this reviewed, unpaid receipt to Forfeit List?')">
                                                    @csrf
                                                    <input type="hidden" name="pawn_sum_id" value="{{ $receipt->id }}">
                                                    <label class="me-2"><input type="checkbox" name="confirm_forfeit" value="1" required> Forfeit — reviewed</label>
                                                    <button class="btn btn-danger btn-sm" type="submit">Move to Forfeit List</button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Await promised payment</span>
                                            @endif
                                        </div>
                                    </section>
                                </div>
                            </div></td></tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No receipts are currently in the Forfeit Reminder List.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @include('partials.report-pagination', ['paginator' => $receipts])
            </div></div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-row-details]').forEach(function (button) {
    button.addEventListener('click', function () {
        var row = document.getElementById(button.getAttribute('data-row-details'));
        var opening = row.hasAttribute('hidden');
        row.toggleAttribute('hidden', !opening);
        button.setAttribute('aria-expanded', String(opening));
        button.querySelector('span').textContent = opening ? 'Hide' : 'View';
    });
});
</script>
@endsection
