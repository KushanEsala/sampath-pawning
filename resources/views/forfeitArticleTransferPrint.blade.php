<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forfeit Article Stock Transfer</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 24px; font-size: 13px; }
        h1 { font-size: 18px; margin-bottom: 6px; }
        p { color: #555; margin-top: 0; margin-bottom: 16px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        tfoot tr { font-weight: bold; background-color: #f9f9f9; }
        .no-print { margin-bottom: 16px; }
        .no-print button, .no-print a {
            display: inline-block;
            padding: 6px 14px;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .no-print button {
            background-color: #0d6efd;
            color: #fff;
            border: 1px solid #0d6efd;
        }
        .no-print a {
            background-color: #6c757d;
            color: #fff;
            border: 1px solid #6c757d;
            margin-left: 8px;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <a href="{{ route('forfeit_article_receipt') }}">Back to list</a>
    </div>

    <h1>Forfeit Article Stock Transfer</h1>
    <p>Articles transferred to sale stock. Financial amounts below are recorded at forfeiture.</p>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 60px;">#</th>
                <th>Ticket Number</th>
                <th>Stock Number (Invoice Number)</th>
                <th class="text-end">Capital</th>
                <th class="text-end">Interest</th>
                <th class="text-center">Article Count</th>
                <th class="text-end">Weight (g)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCapital = 0;
                $totalInterest = 0;
                $totalArticles = 0;
                $totalWeight = 0;
            @endphp
            @forelse($events as $index => $event)
                @php
                    $data = json_decode($event->event_data, true) ?: [];
                    $capital = (float) ($data['capital_outstanding'] ?? 0);
                    $interest = (float) ($data['interest_outstanding'] ?? 0);
                    $days = isset($data['interest_days']) ? (int) $data['interest_days'] : (isset($data['days']) ? (int) $data['days'] : 0);

                    if ($interest <= 0 || $days <= 0) {
                        $pawnSum = \App\Models\TPawnSum::where('id', $event->pawn_sum_id)
                            ->orWhere('Receipt_Number', $event->receipt_number)
                            ->first();
                        if ($pawnSum) {
                            $calcDate = $data['forfeited_date'] ?? $event->event_date ?? now();
                            $calc = app(\App\Services\ReceiptFinancialCalculator::class)->calculate($pawnSum, $calcDate);
                            if ($interest <= 0) {
                                $interest = (float) $calc['interest'];
                            }
                            if ($days <= 0) {
                                $days = (int) $calc['days'];
                            }
                            if ($capital <= 0) {
                                $capital = (float) $calc['principal'];
                            }
                        }
                    }

                    $articles = $data['articles'] ?? [];
                    $articleCount = count($articles);
                    $weight = collect($articles)->sum(function ($a) {
                        return (float) ($a['Total_Weight'] ?? $a['Weight'] ?? 0);
                    });

                    $totalCapital += $capital;
                    $totalInterest += $interest;
                    $totalArticles += $articleCount;
                    $totalWeight += $weight;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $data['ticket_number'] ?? '—' }}</strong></td>
                    <td>{{ $data['stock_number'] ?? '—' }}</td>
                    <td class="text-end">{{ number_format($capital, 2) }}</td>
                    <td class="text-end">
                        {{ number_format($interest, 2) }}
                        @if($days > 0)
                            <br><small class="text-muted">(Days: {{ $days }})</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $articleCount }}</td>
                    <td class="text-end">{{ number_format($weight, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($events) > 0)
        <tfoot>
            <tr>
                <td colspan="3" class="text-end">Total:</td>
                <td class="text-end">{{ number_format($totalCapital, 2) }}</td>
                <td class="text-end">{{ number_format($totalInterest, 2) }}</td>
                <td class="text-center">{{ $totalArticles }}</td>
                <td class="text-end">{{ number_format($totalWeight, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
