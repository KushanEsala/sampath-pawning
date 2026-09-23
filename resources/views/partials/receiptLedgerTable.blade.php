@php
    $ledgerRows = collect($ledgerRows ?? []);
    $ledgerExpanded = (bool) ($ledgerExpanded ?? false);
    $totalDr = $ledgerRows->sum('dr');
    $totalCr = $ledgerRows->sum('cr');
    $firstLedgerRow = $ledgerRows->first();
    $currentBalance = (float) ($firstLedgerRow['balance'] ?? 0);
@endphp
<table class="table table-bordered table-hover align-middle receipt-ledger-table">
    <thead>
        <tr>
            <th class="ledger-date">Date</th>
            <th class="ledger-description">Description</th>
            <th class="ledger-money">DR</th>
            <th class="ledger-money">CR</th>
            <th class="ledger-money">Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ledgerRows as $row)
            @php($detailId = 'ledger-detail-'.$loop->iteration.'-'.substr(md5(json_encode($row)), 0, 8))
            <tr>
                <td class="ledger-date">{{ $row['date'] ?: '—' }}</td>
                <td class="ledger-description">
                    <div class="ledger-title">{{ $row['description'] }}</div>
                    @if(!empty($row['summary']))
                        <div class="ledger-summary">{{ $row['summary'] }}</div>
                    @endif
                    @unless($ledgerExpanded)
                        <button type="button" class="ledger-toggle" data-ledger-detail="{{ $detailId }}" aria-expanded="false">▶ View details</button>
                    @endunless
                </td>
                <td class="ledger-money ledger-dr">{{ number_format($row['dr'], 2) }}</td>
                <td class="ledger-money ledger-cr">{{ number_format($row['cr'], 2) }}</td>
                <td class="ledger-money ledger-balance {{ $row['balance'] < 0 ? 'ledger-balance-negative' : '' }}">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            <tr id="{{ $detailId }}" class="ledger-detail-row {{ $ledgerExpanded ? '' : 'd-none' }}">
                <td colspan="5">
                    <div class="ledger-detail-grid">
                        <div>
                            @if(!empty($row['details']))
                                <div class="ledger-meta">{{ implode(' • ', $row['details']) }}</div>
                            @else
                                <span class="text-muted">No additional details.</span>
                            @endif
                        </div>
                        <div class="ledger-operator"><i class="fa fa-user me-1"></i>{{ $row['operator'] }}</div>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No payment history found.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-end">Ledger totals / Current balance</th>
            <th class="ledger-money ledger-dr">{{ number_format($totalDr, 2) }}</th>
            <th class="ledger-money ledger-cr">{{ number_format($totalCr, 2) }}</th>
            <th class="ledger-money ledger-balance {{ $currentBalance < 0 ? 'ledger-balance-negative' : '' }}">{{ number_format($currentBalance, 2) }}</th>
        </tr>
    </tfoot>
</table>
