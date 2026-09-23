@extends('layouts.topnavbar')
@extends('layouts.sidebar')

@section('content')
<div class="main-wrapper"><div class="page-wrapper"><div class="content container-fluid">
    <div class="page-header"><h4>Block Receipts</h4></div>
    @if(session('done'))<div class="alert alert-success">{{ session('done') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @unless($ready)
        <div class="alert alert-warning">Receipt blocking is not available until manual database script 009 is applied to this database.</div>
    @endunless

    <div class="card mb-3"><div class="card-body">
        <form method="GET" action="{{ route('blocked.receipts.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2"><label class="form-label" for="receipt_type">Receipt source</label>
                <select class="form-select" id="receipt_type" name="receipt_type">
                    <option value="Pawn" @selected($type === 'Pawn')>Pawn</option>
                    <option value="Opening_Pawn" @selected($type === 'Opening_Pawn')>Opening Pawn</option>
                </select></div>
            <div class="col-md-4"><label class="form-label" for="receipt_number">Receipt, ticket or invoice number</label>
                <input class="form-control" id="receipt_number" name="receipt_number" value="{{ request('receipt_number') }}"></div>
            <div class="col-md-2"><label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" @selected(request('status', 'all') === 'all')>All</option>
                    <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                </select></div>
            <div class="col-md-auto"><button class="btn btn-primary" type="submit">Search</button></div>
            <div class="col-md-auto"><a class="btn btn-outline-secondary" href="{{ route('blocked.receipts.index') }}">Clear</a></div>
        </form>
    </div></div>

    @if($receipts)
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead><tr><th>Receipt / Ticket</th><th>Stock (Invoice)</th><th>Customer</th><th>Expiry</th><th>Status</th><th>Reason / Blocked by</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td>{{ $receipt->Receipt_Number }}<br><small>{{ $receipt->Ticket_Number }}</small></td>
                    <td>{{ $receipt->Invoice_Number ?: '—' }}</td>
                    <td>{{ $receipt->Customer_Name }}<br><small>{{ $receipt->Customer_NIC }}</small></td>
                    <td>{{ $receipt->Final_date ? substr((string) $receipt->Final_date, 0, 10) : ($receipt->To_Date ? substr((string) $receipt->To_Date, 0, 10) : '—') }}</td>
                    <td><span class="badge {{ $receipt->is_blocked ? 'bg-danger' : 'bg-success' }}">{{ $receipt->is_blocked ? 'Blocked' : 'Active' }}</span></td>
                    <td>{{ $receipt->block_reason ?: '—' }}@if($receipt->blocked_by)<br><small>{{ $receipt->blocked_by }} · {{ $receipt->blocked_at ? $receipt->blocked_at->format('Y-m-d H:i') : '' }}</small>@endif</td>
                    <td>
                        <form method="POST" action="{{ route('blocked.receipts.update') }}" class="d-flex gap-1 align-items-center">
                            @csrf
                            <input type="hidden" name="receipt_type" value="{{ $type }}">
                            <input type="hidden" name="receipt_number" value="{{ $receipt->Receipt_Number }}">
                            <input type="hidden" name="action" value="{{ $receipt->is_blocked ? 'unblock' : 'block' }}">
                            <input class="form-control form-control-sm" name="reason" maxlength="1000" placeholder="{{ $receipt->is_blocked ? 'Unblock note (optional)' : 'Block reason' }}" @required(!$receipt->is_blocked)>
                            <button type="submit" class="btn btn-sm text-nowrap {{ $receipt->is_blocked ? 'btn-success' : 'btn-danger' }}" onclick="return confirm('Confirm {{ $receipt->is_blocked ? 'unblock' : 'block' }} for receipt {{ $receipt->Receipt_Number }}?')">{{ $receipt->is_blocked ? 'Unblock' : 'Block' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No active receipts match this search.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $receipts->links() }}
    </div></div>
    @endif
</div></div></div>
@endsection
