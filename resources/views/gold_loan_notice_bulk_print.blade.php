<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Letter Print</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #fff;
        }

        .letter-page {
            position: relative;
            width:  21cm;
            height: 27cm;
            overflow: hidden;
            margin: 0 auto 20px;
            border: 1px solid #ccc;
            page-break-after: always;
            page-break-inside: avoid;
        }
        .letter-page:last-child { page-break-after: auto; }

        .field {
            position: absolute;
            font-size: 10pt;
            white-space: nowrap;
        }

        /* Fixed fields */
        .customer-address-Receipt      { left: 18cm; top: 0.5cm;  }
        .customer-name      { left: 13cm; top: 2.3cm;  }
                    .customer-address {
                position: absolute;
                left: 13cm;
                top: 2.8cm;
                width: 6cm;           /* control line width */
                word-wrap: break-word;
                white-space: normal;
            }
        .ticket-number      { left: 10cm;   top: 2.8cm;  }
        .branch             { left: 10cm;   top: 3.3cm;  }
        .branch-contact     { left: 10cm;   top: 3.6cm;  }
        .cashier            { left: 10cm;   top: 4.2cm;  }
        .market-value       { left: 17.2cm; top: 4.2cm;  }
        .intrest            { left: 17.2cm; top: 3.65cm; }
        .receipt-date       { left: 17.2cm; top: 2.8cm;  }
        .acc                { left: 14cm;   top: 5.2cm;  }

        /* Bottom summary */
        .expiry-date {
    left: 16.3cm;
    top: 9.6cm;
    color: red;
}

.expiry-date-tamil {
    left: 17.1cm;
    top: 12cm;
    color: red;
}

.expiry-date-english {
    left: 2.6cm;
    top: 15.1cm;
    color: red;
}
        .Receipt-Number-two     { left: 2cm;      top: 18.8cm; }
        .Receipt-Amount         { left: 11.5cm;   top: 18.8cm; }
        .Receipt-interest       { left: 16cm;     top: 18.8cm; }
        .Receipt-total-amount   { left: 16.5cm;   top: 26.5cm; color: Green;   }
        .Receipt-charge-details { left: 2.4cm; top: 25.6cm; font-size: 11px; color: #111; }
        .advance-amount         { left: 12cm;     top: 12cm;   }
        .branch-name         { left: 2.3cm;     top: 2.4cm;    }
        .branch-address         { left: 2.3cm;    top: 2.8cm;    }
        .Receipt-curent-date        { left: 3.6cm;    top: 11.3cm;    }
        .Receipt-curent-date-tamil       { left: 6.7cm;    top: 13.6cm;    }
         .Receipt-letter-type_sinhala      { left: 9.3cm;    top: 7.1cm;    }
        .Receipt-letter-type_tamil       { left: 9.7cm;    top: 7.5cm;    }
        .Receipt-letter-type_english      { left: 2.2cm;    top: 8.1cm;    }

        /* Articles field */
        .customer-Articles {
            position: absolute;
            left: 6cm;
            top: 18.8cm;
            font-size: 9pt;
            white-space: normal;
            max-width: 4.5cm;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
        }

        /* Dynamic item row positions */
        .item-row {
            position: absolute;
            font-size: 10pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-article { left: 0.5cm;  width: 3.3cm; max-width: 3.3cm; }
        .col-desc    { left: 4cm;    width: 4.3cm; max-width: 4.3cm; }
        .col-net     { left: 8.5cm;  }
        .col-gold    { left: 11.7cm; }
        .col-karat   { left: 14.8cm; }
        .col-int     { left: 17.6cm; }

        /* Print button bar */
        .print-bar {
            text-align: center;
            padding: 14px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .print-bar button {
            background: #1a3c5e;
            color: #fff;
            border: none;
            padding: 9px 28px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-bar button:hover { background: #265a8a; }
        .badge-count {
            display: inline-block;
            background: #28a745;
            color: #fff;
            border-radius: 12px;
            padding: 2px 10px;
            font-size: 13px;
            margin-left: 10px;
        }

        /* Summary tables wrapper — screen only, hidden on print */
        .summary-tables {
            width: 21cm;
            margin: 0 auto 30px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px dashed #aaa;
        }
        .summary-tables h4 {
            font-size: 11pt;
            color: #1a3c5e;
            margin-bottom: 6px;
        }
        .summary-tables table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9.5pt;
            margin-bottom: 10px;
        }
        .summary-tables th {
            background: #1a3c5e;
            color: #fff;
            padding: 5px 4px;
            text-align: center;
        }
        .summary-tables td {
            border: 1px solid #ccc;
            padding: 4px 5px;
            text-align: center;
        }
        .highlight-days {
            background: #fff3cd;
            font-weight: bold;
            color: #856404;
        }
        .highlight-grand {
            background: #d4edda;
            font-weight: bold;
            color: #155724;
            font-size: 11pt;
        }
        .text-danger { color: red; }

        @media print {
            .print-bar     { display: none !important; }
            .summary-tables { display: none !important; }
            .letter-page   { border: none; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="print-bar">
        <button onclick="window.print()">🖨 Print All Letters</button>
        <span class="badge-count">{{ count($letters) }} letter(s)</span>
    </div>

    @foreach ($letters as $item)

        @php
            $receipt     = $item['receipt'];
            $lno         = $item['letter_no'];
            $pawnDetails = $item['pawnDetails'];
            $receiptType = $item['receiptType'];
            $branchDetails = $item['branchDetails'];

            // ──────────────────────────────────────────────
            // Interest Calculation (mirrors JS logic exactly)
            // ──────────────────────────────────────────────
            $repawnDate   = \Carbon\Carbon::parse($receipt->RePawning_date)->startOfDay();
            $today        = \Carbon\Carbon::today();
            $daysDiff     = (int) $repawnDate->diffInDays($today) + 1;

            $amount       = (float) $receipt->Pawn_Amount;
            $old_Interest       = (float) $receipt->old_Interest;
            $paidInterest = (float) ($receipt->interest_Paid ?? 0);
            $validPeriod  = (float) ($receiptType?->validPeriod ?? 0);
            $receiptName  = strtoupper($receiptType?->receiptname ?? '');

            $period1 = (float) ($receiptType?->period1 ?? 0);
            $period2 = (float) ($receiptType?->period2 ?? 0);
            $period3 = (float) ($receiptType?->period3 ?? 0);
            $rate1   = (float) ($receiptType?->rate1 ?? 0);
            $rate2   = (float) ($receiptType?->rate2 ?? 0);
            $rate3   = (float) ($receiptType?->rate3 ?? 0);

            $months      = (int) ceil($daysDiff / 30);
            $penaltyDays = $daysDiff - $validPeriod;
            $calcInterest     = 0;
            $appliedRate = 0;
            $periodLabel = '';

            if ($receiptName === 'SILVER') {
                $calcInterest = \App\Services\SilverInterest::amount($amount, $rate1, $daysDiff, (int) ($period3 ?: 30));
                $appliedRate = $rate1;
                $periodLabel = 'Silver Flat Rate';

            } elseif ($receiptName === 'D' && $daysDiff > $validPeriod) {
                // D-type with penalty
                $penaltyMonths = (int) ceil($penaltyDays / 30);
                $penaltyRate   = 0.5;
                $interestSet   = ($amount / 100) * $rate2 * $months;
                $penaltyCharge = ($amount / 100) * ($penaltyRate * $penaltyMonths);
                $calcInterest       = $interestSet + $penaltyCharge - $paidInterest;
                $appliedRate   = $rate2;
                $periodLabel   = 'D-Type + Penalty (' . $penaltyDays . ' penalty days)';

            } else {
                // Normal tiered calculation
                if ($daysDiff <= $period1) {
                    $calcInterest     = ($amount / 100) * $rate1;
                    $appliedRate = $rate1;
                    $periodLabel = 'Period 1 (1 – ' . $period1 . ' days)';

                } elseif ($daysDiff <= $period2) {
                    $calcInterest     = ($amount / 100) * $rate2;
                    $appliedRate = $rate2;
                    $periodLabel = 'Period 2 (' . ($period1 + 1) . ' – ' . $period2 . ' days)';

                } elseif ($daysDiff <= 30) {
                    $calcInterest     = ($amount / 100) * $rate3;
                    $appliedRate = $rate3;
                    $periodLabel = 'Period 3 (' . ($period2 + 1) . ' – 30 days)';

                } else {
                    // Full months + remaining days per-day rate3
                    $fullMonths  = (int) floor($daysDiff / 30);
                    $remainDays  = $daysDiff % 30;
                    $totalRate   = ($rate3 * $fullMonths) + (($rate3 / 30) * $remainDays);
                    $calcInterest     = ($amount / 100) * $totalRate;
                    $appliedRate = $rate3;
                    $periodLabel = 'Period 3 (30+ days)';
                }
            }

            $calcInterest = max(0, round($calcInterest, 2));

            // ──────────────────────────────────────────────
            // Grand Total: Interest + Postage + Service Charge
            // ──────────────────────────────────────────────
            $letterPayOne   = (float) ($receipt->letter_pay_one   ?? 0);
            $letterPayTwo   = (float) ($receipt->letter_pay_two   ?? 0);
            $letterPayThree = (float) ($receipt->letter_pay_three ?? 0);
            $serviceCharge  = (float) ($receiptType?->service_charge ?? 0);

            // The backend calculator is the single source of truth. It uses the
            // receipt-type values captured for this pawn and includes service
            // and every issued letter's postage charge as interest/arrears.
            $financial      = $item['financial'];
            $amount         = (float) $financial['principal'];
            $calcInterest   = (float) $financial['interest'];
            $serviceCharge  = (float) $financial['service_charge'];
            $postageTotal   = (float) $financial['letter_charge'];
            $arrearsTotal   = (float) $financial['arrears_total'];
            $grandTotal     = (float) $financial['redemption_total'];
        @endphp

        {{-- ══════════════════════════════════════════ --}}
        {{-- LETTER PAGE (positioned layout for print)  --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="letter-page">


<div class="field Receipt-letter-type_sinhala">
    @if($receipt->is_letter_1 && $receipt->is_letter_2)
        3rd
    @elseif($receipt->is_letter_1)
        2nd
    @else
        1st  {{-- is_letter_1 = null --}}
    @endif
</div>

<div class="field Receipt-letter-type_tamil">
    @if($receipt->is_letter_1 && $receipt->is_letter_2)
        3rd
    @elseif($receipt->is_letter_1)
        2nd
    @else
        1st
    @endif
</div>

<div class="field Receipt-letter-type_english">
    @if($receipt->is_letter_1 && $receipt->is_letter_2)
        3rd
    @elseif($receipt->is_letter_1)
        2nd
    @else
        1st
    @endif
</div>

             <div class="field branch-name">{{ $branchDetails->name }}</div>
            <div class="field branch-address">{{ $branchDetails->address }}</div>
            <div class="field customer-name">{{ $receipt->Customer_Name }}</div>
            <div class="field customer-address">{{ $receipt->Customer_Address }}</div>
            <div class="field customer-address-Receipt">
                {{ $receipt->Receipt_Number ?? $receipt->old_Receipt_Number }}
            </div>
            <div class="field expiry-date">{{ date('Y-m-d', strtotime($receipt->Final_date)) }}</div>
            <div class="field expiry-date-tamil">{{ date('Y-m-d', strtotime($receipt->Final_date)) }}</div>
            <div class="field expiry-date-english">{{ date('Y-m-d', strtotime($receipt->Final_date)) }}</div>
            <div class="field Receipt-Number-two">{{ $receipt->Receipt_Number }}</div>
            <div class="field Receipt-Amount">
                    {{ number_format(
                        $receipt->RePawning_amount !== null
                            ? $receipt->RePawning_amount
                            : $receipt->Pawn_Amount,
                        2
                    ) }}
                </div>
            <div class="field Receipt-interest">{{ number_format($arrearsTotal, 2) }}</div>
            <div class="field Receipt-total-amount">{{ number_format($grandTotal, 2) }}</div>
            <div class="field Receipt-charge-details">
                Interest Rs. {{ number_format($calcInterest, 2) }} + Service Rs. {{ number_format($serviceCharge, 2) }} + Letter/Postage Rs. {{ number_format($postageTotal, 2) }} = Arrears Rs. {{ number_format($arrearsTotal, 2) }}
            </div>

            <div class="field Receipt-curent-date">
                {{ \Carbon\Carbon::today()->format('Y-m-d') }}
            </div>
            
                 <div class="field Receipt-curent-date-tamil">
                {{ \Carbon\Carbon::today()->format('Y-m-d') }}
            </div>

            <div class="customer-Articles">
                {{ $pawnDetails->pluck('Articles')->filter()->implode(', ') }}
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- SUMMARY TABLES (screen only — hidden on print via CSS)    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="summary-tables">

            {{-- Table 1: Interest Calculation Breakdown --}}
            <h4>📋 Interest Calculation — {{ $receipt->Receipt_Number }} | {{ $receipt->Customer_Name }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Receipt Type</th>
                        <th>Pawn Date</th>
                        <th>Current Date</th>
                        <th>Days</th>
                        <th>Months (ceil)</th>
                        <th>Period / Range</th>
                        <th>Rate (%)</th>
                        <th>Interest</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($amount, 2) }}</td>
                        <td>{{ $receipt->Receipt_Type }}</td>
                        <td>{{ $repawnDate->format('d/m/Y') }}</td>
                        <td>{{ $today->format('d/m/Y') }}</td>
                        <td class="highlight-days">{{ $daysDiff }} days</td>
                        <td>{{ $months }}</td>
                        @if($receiptType)
                            <td>{{ $periodLabel }}</td>
                            <td>{{ $appliedRate }}%</td>
                            <td><strong>{{ number_format($calcInterest, 2) }}</strong></td>
                        @else
                            <td colspan="3" class="text-danger">No receipt type found</td>
                        @endif
                    </tr>
                </tbody>
            </table>

            {{-- Table 2: Grand Total Breakdown --}}
            <h4>💰 Grand Total — Interest + Postage + Service Charge</h4>
            <table>
                <thead>
                    <tr>
                        <th>Interest</th>
                        <th>Letter Pay 1</th>
                        <th>Letter Pay 2</th>
                        <th>Letter Pay 3</th>
                        <th>Postage Total</th>
                        <th>Service Charge</th>
                        <th>Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($calcInterest, 2) }}</td>
                        <td>{{ number_format($letterPayOne, 2) }}</td>
                        <td>{{ number_format($letterPayTwo, 2) }}</td>
                        <td>{{ number_format($letterPayThree, 2) }}</td>
                        <td><strong>{{ number_format($postageTotal, 2) }}</strong></td>
                        <td>{{ number_format($serviceCharge, 2) }}</td>
                        <td class="highlight-grand">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Table 3: Receipt Type Config --}}
            <h4>⚙️ Receipt Type Config</h4>
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Recr Type</th>
                        <th>Doc Charges</th>
                        <th>Service Charge</th>
                        <th>Stamp Duty</th>
                        <th>Valid Period</th>
                        <th>Period 1</th>
                        <th>Rate 1</th>
                        <th>Period 2</th>
                        <th>Rate 2</th>
                        <th>Period 3</th>
                        <th>Rate 3</th>
                        <th>Pawn Amount</th>
                        <th>Postage Charge</th>
                        <th>Interest</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($receipt->RePawning_amount, 2) }}</td>
                        <td>{{ $receipt->Receipt_Type }}</td>
                        @if($receiptType)
                            <td>{{ $receiptType->documentCharges }}</td>
                            <td>{{ $receiptType->service_charge }}</td>
                            <td>{{ $receiptType->stampduty }}</td>
                            <td>{{ $receiptType->validPeriod }}</td>
                            <td>{{ $receiptType->period1 }}</td>
                            <td>{{ $receiptType->rate1 }}</td>
                            <td>{{ $receiptType->period2 }}</td>
                            <td>{{ $receiptType->rate2 }}</td>
                            <td>{{ $receiptType->period3 }}</td>
                            <td>{{ $receiptType->rate3 }}</td>
                            <td>{{ number_format($receiptType->pawn_amount, 2) }}</td>
                            <td>{{ number_format($receiptType->Postage_charge, 2) }}</td>
                        @else
                            <td colspan="12" class="text-danger">No receipt type found</td>
                        @endif
                        <td><strong>{{ number_format($calcInterest, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

        </div>{{-- end .summary-tables --}}

    @endforeach

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
