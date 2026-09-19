@extends('layouts.topnavbar')
@extends('layouts.sidebar')

@section('content')
<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header"><h4>Receipt Search</h4></div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('receipt.search') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="search_type">Search By</label>
                            <select class="form-select" id="search_type" name="search_type" onchange="updateSearchPlaceholder()">
                                <option value="ticket" {{ ($searchType ?? 'ticket') === 'ticket' ? 'selected' : '' }}>Ticket / Receipt Number</option>
                                <option value="nic" {{ ($searchType ?? '') === 'nic' ? 'selected' : '' }}>Customer NIC</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="search_query" id="search_query_label">Ticket / Receipt Number</label>
                            <input class="form-control" id="search_query" name="search_query" value="{{ $searchQuery ?? '' }}" placeholder="Enter Ticket or Receipt Number" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── NIC Search Results: Available Tickets List ──────────────── --}}
            @if(($searchType ?? '') === 'nic' && isset($tickets))
                @if($tickets->isEmpty())
                    <div class="alert alert-warning">No tickets found for Customer NIC: <strong>{{ $searchQuery }}</strong> in your branch.</div>
                @else
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Available Tickets for NIC: <strong>{{ $searchQuery }}</strong>
                                @if($customer)
                                    <span class="text-muted ms-2">({{ trim($customer->First_name.' '.$customer->Middle_name.' '.$customer->Last_name) ?: $customer->Name }})</span>
                                @endif
                            </h5>
                            <span class="badge bg-info text-dark">{{ $tickets->count() }} ticket(s) found</span>
                        </div>
                        @if($customer)
                            <div class="card-body pb-0">
                                <div class="row text-muted small mb-2">
                                    <div class="col-md-4"><strong>Customer:</strong> {{ trim($customer->First_name.' '.$customer->Middle_name.' '.$customer->Last_name) ?: $customer->Name }}</div>
                                    <div class="col-md-4"><strong>Address:</strong> {{ $customer->Address_1 ?? '—' }}</div>
                                    <div class="col-md-4"><strong>Telephone:</strong> {{ $customer->Contact_1 ?? '—' }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="card-body table-responsive pt-2">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket No</th>
                                        <th>Stock No (Invoice)</th>
                                        <th>Receipt No</th>
                                        <th>Receipt Date</th>
                                        <th>Expiry Date</th>
                                        <th class="text-end">Pawn Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tickets as $t)
                                        @php
                                            $statusBadge = '<span class="badge bg-success">Active</span>';
                                            if ($t->isForfeit) {
                                                $statusBadge = '<span class="badge bg-danger">Forfeited</span>';
                                            } elseif ($t->IsRedeemed) {
                                                $statusBadge = '<span class="badge bg-secondary">Redeemed</span>';
                                            }
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $t->Ticket_Number ?? '—' }}</strong></td>
                                            <td>{{ $t->Invoice_Number ?? '—' }}</td>
                                            <td>{{ $t->Receipt_Number }}</td>
                                            <td>{{ optional($t->Receipt_Date)->format('Y-m-d') }}</td>
                                            <td>{{ optional($t->Final_date)->format('Y-m-d') }}</td>
                                            <td class="text-end">{{ number_format($t->Pawn_Amount ?: $t->Amount, 2) }}</td>
                                            <td class="text-center">{!! $statusBadge !!}</td>
                                            <td class="text-center">
                                                <a class="btn btn-sm btn-primary" href="{{ route('receipt.search', ['search_type' => 'ticket', 'search_query' => $t->Ticket_Number ?: $t->Receipt_Number]) }}">
                                                    <i class="fa fa-eye"></i> View Full Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ── Ticket Search Not Found ────────────────────────────────── --}}
            @if(($searchType ?? 'ticket') === 'ticket' && !empty($searchQuery) && !$history)
                <div class="alert alert-warning">No receipt was found for Ticket / Receipt Number: <strong>{{ $searchQuery }}</strong> in your branch.</div>
            @endif

            {{-- ── Full Receipt Details View ─────────────────────────────── --}}
            @if($history)
                @php
                    $receipt = $history['receipt'];
                    $customer = $history['customer'];
                    $financial = $history['financial'];
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Receipt {{ $receipt->Receipt_Number }} (Ticket: {{ $receipt->Ticket_Number }})</h5>
                    <div>
                        @if(($searchType ?? '') === 'nic' || request()->has('search_type'))
                            <a class="btn btn-outline-secondary me-2" href="{{ route('receipt.search', ['search_type' => 'nic', 'search_query' => $receipt->Customer_NIC]) }}">
                                <i class="fa fa-arrow-left"></i> Back to NIC Tickets
                            </a>
                        @endif
                        <a class="btn btn-outline-dark" target="_blank" href="{{ route('receipt.search.print', ['receipt_number' => $receipt->Receipt_Number]) }}"><i class="fa fa-print"></i> Print History</a>
                    </div>
                </div>

                <div class="card mb-3"><div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Ticket No</strong><br>{{ $receipt->Ticket_Number }}</div>
                        <div class="col-md-3"><strong>Stock No (Invoice No)</strong><br>{{ $receipt->Invoice_Number }}</div>
                        <div class="col-md-3"><strong>Receipt Date</strong><br>{{ optional($receipt->Receipt_Date)->format('Y-m-d') }}</div>
                        <div class="col-md-3"><strong>Expiry Date</strong><br>{{ optional($receipt->Final_date)->format('Y-m-d') }}</div>
                    </div><hr>
                    <div class="row">
                        <div class="col-md-4"><strong>Customer</strong><br>{{ $customer ? trim($customer->First_name.' '.$customer->Middle_name.' '.$customer->Last_name) : $receipt->Customer_Name }} (NIC: {{ $receipt->Customer_NIC }})</div>
                        <div class="col-md-4"><strong>Address</strong><br>{{ optional($customer)->Address_1 ?? $receipt->Customer_Address }}</div>
                        <div class="col-md-4"><strong>Telephone</strong><br>{{ optional($customer)->Contact_1 ?? $receipt->Customer_Phone }}</div>
                    </div>
                </div></div>

                <div class="card mb-3"><div class="card-body table-responsive">
                    <h5>Current Amounts</h5>
                    <table class="table table-bordered text-end">
                        <thead><tr><th>Principal</th><th>Interest</th><th>Service Charge</th><th>Letter Charge</th><th>Total Interest Payable</th><th>Redemption Total</th></tr></thead>
                        <tbody><tr>
                            <td>{{ number_format($financial['principal'], 2) }}</td>
                            <td>{{ number_format($financial['interest'], 2) }}@if(!empty($financial['days']))<br><small class="text-muted">(Days: {{ $financial['days'] }})</small>@endif</td>
                            <td>{{ number_format($financial['service_charge'], 2) }}</td>
                            <td>{{ number_format($financial['letter_charge'], 2) }}</td>
                            <td>{{ number_format($financial['arrears_total'], 2) }}</td>
                            <td class="fw-bold">{{ number_format($financial['redemption_total'], 2) }}</td>
                        </tr></tbody>
                    </table>
                </div></div>

                <div class="card mb-3"><div class="card-body table-responsive">
                    <h5>Articles</h5>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Category</th><th>Article</th><th>Condition</th><th>Karatage</th><th>Weight</th><th>Qty</th><th>Value</th></tr></thead>
                        <tbody>@foreach($history['details'] as $detail)<tr>
                            <td>{{ $detail->Category }}</td><td>{{ $detail->Articles }}</td><td>{{ $detail->Condition }}</td><td>{{ $detail->Karatage }}</td>
                            <td>{{ $detail->Weight }}</td><td>{{ $detail->QTY }}</td><td>{{ number_format($detail->Value, 2) }}</td>
                        </tr>@endforeach</tbody>
                    </table>
                </div></div>

                <div class="card"><div class="card-body table-responsive">
                    <h5>Complete History — Newest First</h5>
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark"><tr><th>Date/Time</th><th>Type</th><th>Amount</th><th>Details</th></tr></thead>
                        <tbody>
                        @forelse($history['timeline'] as $event)
                            <tr><td>{{ $event['date'] }}</td><td>{{ $event['type'] }}</td><td class="text-end">{{ $event['amount'] !== null ? number_format($event['amount'], 2) : '' }}</td><td>
                                @foreach($event['details'] as $label => $value)<strong>{{ $label }}:</strong> {{ $value }}@if(!$loop->last)<br>@endif @endforeach
                                @if(!empty($event['print_url']))<br><a target="_blank" class="btn btn-outline-secondary btn-sm" href="{{ $event['print_url'] }}">Print stock transfer</a>@endif
                            </td></tr>
                        @empty<tr><td colspan="4" class="text-center text-muted">No history entries found.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div></div>
            @endif
        </div>
    </div>
</div>

<script>
    function updateSearchPlaceholder() {
        const type = document.getElementById('search_type').value;
        const input = document.getElementById('search_query');
        const label = document.getElementById('search_query_label');
        if (type === 'nic') {
            label.innerText = 'Customer NIC';
            input.placeholder = 'Enter Customer NIC';
        } else {
            label.innerText = 'Ticket / Receipt Number';
            input.placeholder = 'Enter Ticket or Receipt Number';
        }
    }
    // Initialize label on load
    document.addEventListener('DOMContentLoaded', updateSearchPlaceholder);
</script>
@endsection
